<?php

namespace App\Services\CutBoard;

use App\Models\Master\WasteDetail;
use App\Models\Production\QcProcessGoods;
use App\Models\Production\QcProcessTran;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class GradeCWorkflow
{
    private const PRODUCT_TYPES = [
        'A1' => ['name' => 'ธรรมดา ขอบลาด', 'standard' => 'A10109', 'large' => 'A10209'],
        'A3' => ['name' => 'ทนชื้น ขอบลาด', 'standard' => 'A30109', 'large' => 'A30209'],
        'A4' => ['name' => 'กันร้อน ขอบลาด', 'standard' => 'A40109', 'large' => 'A40209'],
    ];

    public function productTypes(): array
    {
        return self::PRODUCT_TYPES;
    }

    public function create(string $line, array $data, int $employeeGroupId, string $employeeName, int|string $userId): QcProcessGoods
    {
        $lineNumber = substr($line, 1, 1);
        $type = self::PRODUCT_TYPES[$data['product_type']];

        return QcProcessGoods::query()->create([
            'qc_date' => now(),
            'product_type' => $data['product_type'],
            'product_type_full' => $data['sheet_size'] === 'large' ? $type['large'] : $type['standard'],
            'brand' => '09',
            'employee_id' => $employeeGroupId,
            'employee_name' => $employeeName,
            'line' => $line,
            'status' => 'in process',
            'created_by' => $userId,
            'shift_code' => $data['shift_code'],
            'shift_desc' => $lineNumber.$data['shift_code'],
            'amount' => 0,
            'loss_amount' => 0,
        ]);
    }

    public function finish(int $processId): QcProcessGoods
    {
        return DB::connection('legacy')->transaction(function () use ($processId): QcProcessGoods {
            $process = QcProcessGoods::query()->lockForUpdate()->findOrFail($processId);
            if ($process->status !== 'in process' || (int) $process->amount < 1) {
                throw ValidationException::withMessages(['process' => 'บันทึกยอดคัดอย่างน้อย 1 แผ่นก่อนจบงาน']);
            }

            $batch = Str::upper(Str::random(12));
            $lineNumber = substr((string) $process->line, 1, 1);
            $typeCode = (string) $process->product_type;

            $wasteRows = WasteDetail::query()->where('wwd_line', $process->line)
                ->whereNull('wwd_qc_date')
                ->where('wwd_barcode', 'like', '%'.$typeCode.'%')
                ->lockForUpdate()->get();
            foreach ($wasteRows as $row) {
                $row->wwd_qc_date = now();
                $row->wwd_batch = $batch;
                $row->save();
            }

            $runningNumbers = QcProcessGoods::query()->whereDate('qc_date', $process->qc_date->toDateString())
                ->where('product_type_full', $process->product_type_full)
                ->where('status', 'complete')->lockForUpdate()->pluck('running_no');
            $runningNo = sprintf('%03d', ((int) $runningNumbers->map(fn ($value) => (int) $value)->max()) + 1);
            $process->update([
                'batch' => $batch,
                'status' => 'complete',
                'running_no' => $runningNo,
            ]);

            $total = (int) $process->amount;
            $chunkSize = 100;
            $chunkCount = (int) ceil($total / $chunkSize);
            $skuCode = 'BZ'.$process->brand.'-'.$process->product_type_full;
            $wipCode = 'B'.$lineNumber.$process->brand.'-'.$process->product_type_full;
            $lot = $process->qc_date->format('ymd').'A';

            for ($sequence = 1; $sequence <= $chunkCount; $sequence++) {
                $amount = min($chunkSize, $total - (($sequence - 1) * $chunkSize));
                QcProcessTran::query()->create([
                    'qc_process_id' => $process->id,
                    'sku_code' => $skuCode,
                    'wip_code' => $wipCode,
                    'lot' => $lot,
                    'qc_lot' => '1111111111',
                    'fg_lot' => '0000000000',
                    'qc_date' => $process->created_at,
                    'created_by' => $process->created_by,
                    'state' => '4',
                    'wip_state' => '3',
                    'amount' => $amount,
                    'total_amount' => $total,
                    'status' => 'FN',
                    'prefix' => 'BZ',
                    'line' => $lineNumber,
                    'wh' => '01-01นว',
                    'checker' => $process->created_by,
                    'qc_group' => $process->employee_name,
                    'weight' => $process->weight,
                    'uom' => $process->uom,
                    'running_no' => $sequence,
                    'batch_no' => $batch,
                    'wms_lot' => $skuCode.$lot.$runningNo.'0000000',
                    'full_lot' => $skuCode.$lot.$runningNo.'0000'.sprintf('%03d', $sequence),
                    'wip_lot' => $wipCode.$lot.$runningNo.sprintf('%03d', $sequence).$amount,
                    'shift_code' => $process->shift_code,
                    'shift_desc' => $process->shift_desc,
                ]);
            }

            return $process->refresh();
        });
    }

    public function delete(int $processId): void
    {
        DB::connection('legacy')->transaction(function () use ($processId): void {
            $process = QcProcessGoods::query()->lockForUpdate()->findOrFail($processId);
            if ($process->batch) {
                WasteDetail::query()->where('wwd_batch', $process->batch)
                    ->update(['wwd_qc_date' => null, 'wwd_batch' => null]);
            }
            $process->transactions()->delete();
            $process->delete();
        });
    }
}
