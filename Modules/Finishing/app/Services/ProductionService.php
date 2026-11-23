<?php

namespace Modules\Finishing\App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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
            if ($log->station->code === 'ST2' && ! $log->sub_type) {
                throw ValidationException::withMessages(['sub_type' => 'สถานี PVC ต้องเลือกประเภทการเคลือบ']);
            }

            $remaining = max(0, (float) $material->quantity - $input);
            $material->quantity = $remaining;
            $material->status = $remaining > 0 ? 'pending' : 'completed';
            $material->save();

            if ($output > 0) {
                $outputName = $log->station->name.' · '.$log->shift_date->format('Y-m-d').' · #'.$log->id;
                $outputMaterial = FinishingMaterial::query()->create([
                    'code' => sprintf('FIN-%s-%06d', $log->station->code, $log->id),
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
                    if ($output->children()->exists() || $output->productionLogs()->where('status', 'confirmed')->exists()) {
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
}
