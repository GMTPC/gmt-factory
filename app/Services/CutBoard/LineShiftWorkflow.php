<?php

namespace App\Services\CutBoard;

use App\Models\CutJob;
use App\Models\CutJobHeader;
use App\Models\Master\WasteDetail;
use App\Models\Master\Worktime;
use App\Models\Master\ZipTape;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class LineShiftWorkflow
{
    public function currentWorktime(string $line): int
    {
        return (int) Worktime::query()->where('wwt_line', $line)->count();
    }

    /** @return Builder<CutJob> */
    public function jobsForShift(string $line, int $worktime): Builder
    {
        return CutJob::query()->where('production_line', $line)
            ->where(function (Builder $query) use ($worktime): void {
                $query->where('worktime', $worktime)
                    ->orWhere(fn (Builder $legacy) => $legacy->whereNull('worktime')
                        ->whereIn('status', ['draft', 'scanning']));

                if ($worktime === 0) {
                    $query->orWhere(fn (Builder $legacy) => $legacy->whereNull('worktime')
                        ->whereDate('work_date', now()->toDateString()));
                }
            });
    }

    public function close(string $line, float $endTape, float $wasteQty): void
    {
        DB::connection('legacy')->transaction(function () use ($line, $endTape, $wasteQty): void {
            $worktimeNumber = (int) Worktime::query()->where('wwt_line', $line)->lockForUpdate()->count();
            $hasOpenJob = $this->jobsForShift($line, $worktimeNumber)->whereIn('status', ['draft', 'scanning'])->exists();
            if ($hasOpenJob) {
                throw ValidationException::withMessages(['shift' => 'ยังมีงานที่กำลังคัด กรุณาจบงานทุกตัวก่อน']);
            }

            $worktime = Worktime::query()->create([
                'wwt_index' => $worktimeNumber,
                'wwt_status' => 1,
                'wwt_line' => $line,
                'wwt_date' => now(),
            ]);

            ZipTape::query()->create([
                'wz_line' => $line,
                'wz_worktime_id' => $worktime->wwt_id,
                'wz_amount' => $endTape - 0.01,
            ]);

            $lotCount = (int) WasteDetail::query()->where('wwd_line', $line)->where('wwd_index', $worktimeNumber)->count();
            $lot = now()->format('ymd').'CC'.str_pad((string) ($lotCount + 1), 2, '0', STR_PAD_LEFT);
            $typeCode = (string) CutJobHeader::query()
                ->whereIn('cut_job_id', $this->jobsForShift($line, $worktimeNumber)->select('id'))
                ->whereNotNull('product_type_code')->orderByDesc('id')->value('product_type_code');

            if ($wasteQty > 0 && $typeCode === '') {
                throw ValidationException::withMessages(['waste_qty' => 'ยังไม่มีชนิดสินค้า จึงบันทึกยอด NG ไม่ได้']);
            }

            $qty = (int) round($wasteQty);
            WasteDetail::query()->create([
                'wwd_line' => $line,
                'wwd_index' => $worktimeNumber,
                'wwd_barcode' => 'B'.substr($line, 1, 1).'09-'.$typeCode.$lot.str_pad((string) $qty, 3, '0', STR_PAD_LEFT),
                'wwd_lot' => $lot,
                'wwd_amount' => $qty,
                'wwd_date' => now(),
            ]);
        });
    }
}
