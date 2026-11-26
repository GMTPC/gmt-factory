<?php

namespace Modules\Finishing\App\Services;

use App\Services\CutBoard\TagCode;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Modules\Finishing\App\Models\FinishingMaterial;
use Modules\Finishing\App\Models\ProductionLog;

class ProductionService
{
    public function confirm(ProductionLog $productionLog): ProductionLog
    {
        return DB::transaction(function () use ($productionLog) {
            $log = ProductionLog::query()->with(['station', 'job'])->lockForUpdate()->findOrFail($productionLog->id);
            if ($log->status !== 'draft') {
                throw ValidationException::withMessages(['status' => 'ยืนยันได้เฉพาะรายการ draft']);
            }
            if (! $log->job || $log->job->status !== 'active' || (int) $log->job->station_id !== (int) $log->station_id || (int) $log->job->material_id !== (int) $log->material_id) {
                throw ValidationException::withMessages(['job_id' => 'รายการนี้ไม่มี Job ที่เปิดอยู่ของสถานีนี้']);
            }

            $material = FinishingMaterial::query()->lockForUpdate()->findOrFail($log->material_id);
            $input = (float) $log->quantity_in;
            $output = (float) $log->quantity_out;
            $defects = (float) $log->defect_count;

            if ($input <= 0 || $input > (float) $material->quantity) {
                throw ValidationException::withMessages(['quantity_in' => 'จำนวนรับเข้าเกินยอดคงเหลือของวัตถุดิบ']);
            }
            if ($output + $defects > $input) {
                throw ValidationException::withMessages(['quantity_out' => 'ยอดผลิตรวมยอดเสียต้องไม่เกินยอดรับเข้า']);
            }

            $remaining = max(0, (float) $material->quantity - $input);
            $material->quantity = $remaining;
            $material->status = $remaining > 0 ? 'pending' : 'completed';
            $material->save();

            if ($output > 0) {
                [$tagCode, $lot] = $this->outputTag($log, $output);
                $outputName = $log->station->name.' · '.$log->shift_date->format('Y-m-d').' · #'.$log->id;
                $outputMaterial = FinishingMaterial::query()->create([
                    'code' => $tagCode ?? sprintf('FIN-%s-%06d', $log->station->code, $log->id),
                    'name' => $outputName,
                    // The FG entry flow is intentionally held back for this rollout;
                    // all confirmed output remains available as WIP.
                    'type' => 'วิป',
                    'status' => 'pending',
                    'station_id' => $log->station->next_station_id ?? $log->station_id,
                    'quantity' => $output,
                    'parent_id' => $material->id,
                ]);
                $log->output_material_id = $outputMaterial->id;
                $log->output_tag_code = $tagCode;
                $log->output_lot = $lot;
            }

            $log->status = 'confirmed';
            $log->save();

            return $log->fresh(['station', 'material', 'outputMaterial', 'user']);
        });
    }

    public function cancel(ProductionLog $productionLog): ProductionLog
    {
        return DB::transaction(function () use ($productionLog) {
            $log = ProductionLog::query()->lockForUpdate()->findOrFail($productionLog->id);
            if ($log->status === 'cancelled') {
                return $log;
            }

            if ($log->status === 'confirmed') {
                $input = FinishingMaterial::query()->lockForUpdate()->findOrFail($log->material_id);
                if ($log->output_material_id) {
                    $output = FinishingMaterial::query()->lockForUpdate()->findOrFail($log->output_material_id);
                    $tagScanned = $log->output_tag_code
                        && DB::table('finishing_wip_receipts')->where('scan_code', $log->output_tag_code)->exists();
                    if ($tagScanned || $output->children()->exists() || $output->productionLogs()->where('status', 'confirmed')->exists()) {
                        throw ValidationException::withMessages(['status' => 'ยกเลิกรายการนี้ไม่ได้ เพราะผลผลิตถูกนำไปใช้ในขั้นตอนถัดไปแล้ว']);
                    }
                    // Keep the generated material row for the audit relationship;
                    // remove its available stock so it cannot enter another job.
                    $output->update(['quantity' => 0, 'status' => 'completed']);
                }
                $input->increment('quantity', (float) $log->quantity_in);
                $input->update(['status' => 'pending']);
            }

            $log->status = 'cancelled';
            $log->save();
            $log->delete();

            return $log;
        });
    }

    /**
     * Build the WIP tag for confirmed output so the next station can scan it like any other WIP pallet.
     * The source SKU (brand + product type) is kept; the lot becomes the Finishing lot
     * (yymmdd + A/B shift + sequence) and the quantity becomes the output quantity.
     *
     * @return array{0: ?string, 1: ?string} [tag code, lot]
     */
    private function outputTag(ProductionLog $log, float $output): array
    {
        $scanCode = DB::table('finishing_wip_receipts')->where('production_log_id', $log->id)->value('scan_code');
        if (! $scanCode) {
            return [null, null];
        }
        try {
            $source = TagCode::parse((string) $scanCode);
        } catch (InvalidArgumentException) {
            return [null, null];
        }

        $prefix = $log->shift_date->format('ymd').($log->shift === 'night' ? 'B' : 'A');
        $lastSequence = (int) ProductionLog::withTrashed()->where('output_lot', 'like', $prefix.'%')->lockForUpdate()
            ->pluck('output_lot')->map(fn ($lot) => (int) substr((string) $lot, -3))->max();
        $sequence = $lastSequence + 1;
        if ($sequence > 999) {
            throw ValidationException::withMessages(['status' => 'เลข LOT Tag WIP ของวันที่/กะนี้เต็มแล้ว (999)']);
        }
        $lot = $prefix.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);

        $wholeQty = floor($output) === $output && $output >= 1 && $output <= 999;
        if ($source->format === 'legacy_wip' && $wholeQty) {
            $code = $source->skuCode.$lot.str_pad((string) (int) $output, 3, '0', STR_PAD_LEFT);
        } else {
            $qty = rtrim(rtrim(number_format($output, 3, '.', ''), '0'), '.');
            $code = sprintf('SKUCODE:%s.LOT:%s.QTY:%s.L:%s', $source->skuCode, $lot, $qty, $source->line);
        }
        if (DB::table('finishing_wip_receipts')->where('scan_code', $code)->exists()) {
            throw ValidationException::withMessages(['status' => 'Tag WIP '.$code.' ซ้ำกับ Tag ที่รับเข้าแล้ว']);
        }

        return [$code, $lot];
    }
}
