<?php

namespace App\Http\Controllers\CutBoard;

use App\Http\Controllers\Controller;
use App\Http\Requests\CutBoard\ScanTagRequest;
use App\Models\CutJob;
use App\Models\CutJobHeader;
use App\Models\Warehouse\WmsLocation;
use App\Services\CutBoard\ScanTagService;
use App\Services\CutBoard\LineMasterDataService;
use App\Services\CutBoard\WmsExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ScanController extends Controller
{
    public function show(Request $request): Response
    {
        $jobs = CutJob::query()
            ->whereIn('status', ['draft', 'scanning'])
            ->latest('id')
            ->get(['id', 'job_no', 'production_line', 'work_date', 'status'])
            ->map(fn (CutJob $job) => [
                'id' => $job->id,
                'job_no' => $job->job_no,
                'production_line' => $job->production_line,
                'work_date' => $job->work_date?->toDateString(),
                'status' => $job->status,
            ]);

        $selectedJob = $jobs->firstWhere('id', (int) $request->integer('job')) ?? $jobs->first();
        $headers = $selectedJob
            ? CutJobHeader::query()->where('cut_job_id', $selectedJob['id'])->latest('id')->get(['id', 'header_no', 'group_code', 'sku_code', 'lot_no', 'status'])
            : collect();
        $employeeGroups = $selectedJob
            ? app(LineMasterDataService::class)->groups($selectedJob['production_line'], true)
            : collect();

        try {
            $locations = WmsLocation::query()->limit(500)->get()->map(fn (WmsLocation $location) => $location->toArray())->values();
        } catch (\Throwable) {
            $locations = collect();
        }

        return Inertia::render('CutBoard/Scan', [
            'jobs' => $jobs,
            'selectedJob' => $selectedJob,
            'headers' => $headers,
            'employeeGroups' => $employeeGroups,
            'storeUrl' => route('insert-check-csv-qr-code'),
            'locations' => $locations,
        ]);
    }

    public function store(ScanTagRequest $request, ScanTagService $service, WmsExportService $wmsExport): RedirectResponse
    {
        $job = CutJob::query()->findOrFail($request->integer('job_id'));
        $header = CutJobHeader::query()->findOrFail($request->integer('header_id'));
        $line = $service->scan($job, $header, $request->validated(), $request->user()->getAuthIdentifier());
        $wmsExport->queue($job->refresh(), $line->refresh());

        return redirect()
            ->route('checker-qc-scan', ['job' => $job->id])
            ->with('success', 'สแกน QC และบันทึก Spec เรียบร้อยแล้ว');
    }


}
