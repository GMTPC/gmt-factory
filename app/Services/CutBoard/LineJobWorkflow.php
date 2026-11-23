<?php

namespace App\Services\CutBoard;

use App\Models\CutJob;
use App\Models\CutJobLine;
use App\Models\Warehouse\CsvCheck;
use App\Models\Master\FgBrand;
use App\Models\Master\Worktime;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class LineJobWorkflow
{
    public function create(string $line, array $data, ?int $createdBy): CutJob
    {
        return DB::connection(config('cutboard.connection'))->transaction(function () use ($line, $data, $createdBy): CutJob {
            $workDate = Carbon::parse($data['work_date'])->toDateString();
            $worktime = (int) Worktime::query()->where('wwt_line', $line)->count();
            $job = CutJob::query()->create([
                'job_no' => 'JB-'.$workDate.'-'.$line.'-'.Str::upper(Str::random(8)),
                'production_line' => $line,
                'worktime' => $worktime,
                'work_date' => $workDate,
                'status' => 'scanning',
                'created_by' => $createdBy,
                'started_at' => now(),
            ]);

            $product = $data['product'] ?? null;
            $job->headers()->create([
                'header_no' => 'H-001',
                'group_code' => $data['group_code'],
                'product_type_code' => $product?->pe_type_code,
                'product_type_name' => $product?->pe_type_name,
                'target_qty' => 0,
                'status' => 'scanning',
            ]);

            return $job;
        });
    }

    public function deleteEmpty(string $line, int $jobId): void
    {
        DB::connection(config('cutboard.connection'))->transaction(function () use ($line, $jobId): void {
            $job = CutJob::query()->where('production_line', $line)->lockForUpdate()->findOrFail($jobId);
            if (! in_array($job->status, ['draft', 'scanning'], true) || $job->lines()->exists()) {
                throw ValidationException::withMessages(['job' => 'ลบได้เฉพาะงานที่ยังไม่มีรายการ Scan']);
            }

            $job->headers()->delete();
            $job->delete();
        });
    }

    public function deleteOpen(string $line, int $jobId): void
    {
        DB::connection(config('cutboard.connection'))->transaction(function () use ($line, $jobId): void {
            $job = CutJob::query()->where('production_line', $line)->lockForUpdate()->findOrFail($jobId);
            if (! in_array($job->status, ['draft', 'scanning'], true)) {
                throw ValidationException::withMessages(['job' => 'ลบได้เฉพาะงานที่ยังไม่กดจบงานคัด']);
            }

            $lines = CutJobLine::on(config('cutboard.connection'))
                ->where('cut_job_id', $job->id)
                ->lockForUpdate()
                ->get();
            if ($lines->contains(fn (CutJobLine $item) => $item->receive_status === 'received' || $item->print_status === 'printed' || $item->wms_status === 'received')) {
                throw ValidationException::withMessages(['job' => 'ลบไม่ได้ เพราะมีรายการที่รับเข้า WMS หรือพิมพ์ Tag แล้ว']);
            }

            // Remove only unreceived legacy CSV rows created under this job's bridge index.
            // Existing rows shared with the old workflow are preserved.
            if (config('cutboard.legacy_bridge') && $job->legacy_scan_index) {
                DB::connection(config('cutboard.source_connection'))->transaction(function () use ($job, $lines): void {
                    $legacyIds = $lines->pluck('legacy_ccw_id')->filter()->unique()->values();
                    if ($legacyIds->isNotEmpty()) {
                        CsvCheck::query()
                            ->whereIn('ccw_id', $legacyIds)
                            ->where('ccw_index', $job->legacy_scan_index)
                            ->where('ccw_out', 0)
                            ->delete();
                    }

                    $otherBrandIds = CutJobLine::on(config('cutboard.connection'))
                        ->where('cut_job_id', '!=', $job->id)
                        ->get(['spec_snapshot'])
                        ->map(fn (CutJobLine $item) => data_get($item->spec_snapshot, 'legacy_brand_id'))
                        ->filter()
                        ->unique();
                    foreach ($lines as $item) {
                        $snapshot = $item->spec_snapshot ?? [];
                        $brandId = $snapshot['legacy_brand_id'] ?? null;
                        $previousStatus = $snapshot['legacy_brand_previous_status'] ?? null;
                        if ($brandId !== null && $previousStatus !== null && ! $otherBrandIds->contains($brandId)) {
                            FgBrand::query()->whereKey($brandId)->where('brd_status', 2)->update(['brd_status' => $previousStatus]);
                        }
                    }
                });
            }

            $job->headers()->delete();
            $job->delete();
        });
    }

    public function finishQuality(string $line, int $jobId): CutJob
    {
        return DB::connection(config('cutboard.connection'))->transaction(function () use ($line, $jobId): CutJob {
            $job = CutJob::query()->where('production_line', $line)->lockForUpdate()->findOrFail($jobId);
            if ($job->status !== 'scanning') {
                throw ValidationException::withMessages(['job' => 'งานนี้ไม่ได้อยู่ในสถานะกำลังคัด']);
            }
            if (! $job->lines()->exists()) {
                throw ValidationException::withMessages(['job' => 'ต้อง Scan รายการอย่างน้อยหนึ่งรายการก่อนจบงาน']);
            }

            $hasPendingReceipts = $job->lines()
                ->where('qc_status', 'passed')
                ->where('receive_status', 'pending')
                ->exists();
            $hasReceivedLines = $job->lines()
                ->where('qc_status', 'passed')
                ->where('receive_status', 'received')
                ->exists();

            $job->update([
                'status' => ! $hasPendingReceipts
                    ? 'received'
                    : ($hasReceivedLines ? 'receiving' : 'qc_completed'),
            ]);
            $job->headers()->where('status', 'scanning')->update(['status' => 'qc_completed']);

            return $job->refresh();
        });
    }
}
