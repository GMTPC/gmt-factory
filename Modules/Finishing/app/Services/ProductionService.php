<?php

namespace Modules\Finishing\App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Finishing\App\Models\FinishingJob;
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

            app(WipInputService::class)->release($log->id);
            $log->status = 'cancelled';
            $log->save();
            $log->delete();

            // A wrong first tag must not lock the Job forever: unlock when nothing active is left.
            if ($log->job_id && ! ProductionLog::query()->where('job_id', $log->job_id)->where('status', '!=', 'cancelled')->exists()) {
                FinishingJob::query()->whereKey($log->job_id)->update(['product_type_code' => null, 'product_type_name' => null]);
            }

            return $log;
        });
    }

    /**
     * Change the sheet count of an issued output tag (e.g. 100 in -> 98 out). Sheets come from / go back to the
     * output's own input tags, the tag code (lot + qty) is rebuilt and the tag must be printed again.
     */
    public function changeOutputQuantity(ProductionLog $productionLog, float $quantity): ProductionLog
    {
        return DB::transaction(function () use ($productionLog, $quantity) {
            $log = ProductionLog::query()->lockForUpdate()->findOrFail($productionLog->id);
            if ($log->status !== 'confirmed') {
                throw ValidationException::withMessages(['quantity_out' => 'แก้จำนวนได้เฉพาะรายการที่ออก Tag แล้ว']);
            }
            $current = (float) $log->quantity_out;
            if (abs($current - $quantity) < 0.0005) {
                return $log;
            }
            $output = $log->output_material_id ? FinishingMaterial::query()->lockForUpdate()->find($log->output_material_id) : null;
            $tagScanned = $log->output_tag_code
                && DB::table('finishing_wip_receipts')->where('scan_code', $log->output_tag_code)->exists();
            if ($tagScanned || ($output && ($output->children()->exists() || $output->productionLogs()->where('status', 'confirmed')->exists()))) {
                throw ValidationException::withMessages(['quantity_out' => 'แก้จำนวนไม่ได้ เพราะ Tag WIP ออกนี้ถูกนำไปใช้ในขั้นตอนถัดไปแล้ว']);
            }

            app(WipInputService::class)->resize($log->id, $current, $quantity);

            $delta = $quantity - $current;
            $material = FinishingMaterial::query()->lockForUpdate()->findOrFail($log->material_id);
            $remaining = round((float) $material->quantity - $delta, 3);
            if ($remaining < -0.0005) {
                throw ValidationException::withMessages(['quantity_out' => 'ยอด WIP รอแปรรูปของ Job ไม่พอ']);
            }
            $material->quantity = max(0, $remaining);
            $material->status = $remaining > 0 ? 'pending' : 'completed';
            $material->save();

            $log->quantity_in = $quantity;
            $log->quantity_out = $quantity;
            if ($log->output_lot) {
                $code = $log->output_lot.$this->tagQuantity($quantity);
                if (DB::table('finishing_wip_receipts')->where('scan_code', $code)->exists()) {
                    throw ValidationException::withMessages(['quantity_out' => 'Tag WIP '.$code.' ซ้ำกับ Tag ที่รับเข้าแล้ว']);
                }
                $log->output_tag_code = $code;
            }
            $log->save();
            $output?->update(['quantity' => $quantity] + ($log->output_lot ? ['code' => $log->output_tag_code] : []));

            return $log->fresh(['station', 'material', 'outputMaterial', 'user']);
        });
    }

    /** Quantity part of the tag code: 3 digits (100 -> "100", 98 -> "098"). */
    private function tagQuantity(float $quantity): string
    {
        return floor($quantity) === $quantity
            ? str_pad((string) (int) $quantity, 3, '0', STR_PAD_LEFT)
            : rtrim(rtrim(number_format($quantity, 3, '.', ''), '0'), '.');
    }

    /**
     * Finishing WIP tag, same format as the AppSheet Finishing tag:
     *   lot  = yymmdd + shift letter (A = day, B = night) + 3-digit sequence  e.g. 261123A002 (barcode)
     *   code = lot + quantity (3 digits)                                       e.g. 261123A002100 (QR)
     *
     * @return array{0: string, 1: string} [tag code, lot]
     */
    private function outputTag(ProductionLog $log, float $output): array
    {
        $prefix = $log->shift_date->format('ymd').($log->shift === 'night' ? 'B' : 'A');
        $lastSequence = (int) ProductionLog::withTrashed()->where('output_lot', 'like', $prefix.'%')->lockForUpdate()
            ->pluck('output_lot')->map(fn ($lot) => (int) substr((string) $lot, -3))->max();
        $sequence = $lastSequence + 1;
        if ($sequence > 999) {
            throw ValidationException::withMessages(['status' => 'เลข LOT Tag WIP ของวันที่/กะนี้เต็มแล้ว (999)']);
        }
        $lot = $prefix.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);

        $code = $lot.$this->tagQuantity($output);
        if (DB::table('finishing_wip_receipts')->where('scan_code', $code)->exists()) {
            throw ValidationException::withMessages(['status' => 'Tag WIP '.$code.' ซ้ำกับ Tag ที่รับเข้าแล้ว']);
        }

        return [$code, $lot];
    }
}
