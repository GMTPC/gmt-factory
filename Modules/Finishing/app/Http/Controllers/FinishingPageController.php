<?php

namespace Modules\Finishing\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use App\Models\User;
use App\Services\CutBoard\TagCode;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Finishing\App\Models\FinishingEmployee;
use Modules\Finishing\App\Models\FinishingJob;
use Modules\Finishing\App\Models\FinishingJobEmployee;
use Modules\Finishing\App\Models\FinishingStation;
use Modules\Finishing\App\Models\ProductionLog;
use Modules\Finishing\App\Services\BplusExportService;
use Modules\Finishing\App\Services\DowntimeReportService;
use Modules\Finishing\App\Services\ShiftService;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

class FinishingPageController
{
    public function selectStation(): Response
    {
        return Inertia::render('Finishing/SelectStation', [
            // Jobs still open (status active) = Jobs currently being processed at each station.
            // Open Jobs with what they sort and who sorts it (the card shows a short list).
            'stations' => FinishingStation::query()->with(['jobs' => fn ($q) => $q->where('status', 'active')->orderByDesc('id')
                ->select(['id', 'station_id', 'product_type_code', 'product_type_name', 'employee_names'])])
                ->orderBy('id')->get(),
        ]);
    }

    public function stationJobs(string $station): Response
    {
        $statusReady = Schema::hasTable('finishing_station_status_events');
        $stationQuery = FinishingStation::query();
        if ($statusReady) {
            $stationQuery->with('latestStatusEvent.user');
        }
        $station = $stationQuery->where('code', $station)->firstOrFail();
        $this->abortIfDisabled($station);
        $shift = app(ShiftService::class)->current();
        $jobs = FinishingJob::query()->with(['user', 'recorder', 'material'])->withCount('productionLogs')
            ->where('station_id', $station->id)->orderByDesc('id')->limit(100)->get();
        return Inertia::render('Finishing/StationJobs', [
            'station' => $station,
            'statusEvents' => $statusReady ? $station->statusEvents()->with('user')->orderByDesc('id')->limit(10)->get() : [],
            'statusReady' => $statusReady,
            'jobs' => $jobs,
            'shift' => $shift,
            'employeeOptions' => FinishingEmployee::options(),
            'recorderOptions' => User::query()->orderBy('name')->get(['id', 'name']),
            'machineOptions' => $station->jobMachineOptions(),
            'pvcTypes' => $station->jobPvcTypes(),
            'inspectors' => config('finishing.job_inspectors', []),
            'stationSwitcher' => FinishingStation::query()->orderBy('id')->get(['id', 'code'])->map(fn (FinishingStation $item) => ['code' => $item->code, 'is_enabled' => $item->is_enabled])->values(),
            'currentUserId' => (int) request()->user()->id,
            'csrf' => csrf_token(),
        ]);
    }

    public function recordJob(string $station, FinishingJob $job): Response
    {
        $station = FinishingStation::query()->where('code', $station)->firstOrFail();
        $this->abortIfDisabled($station);
        abort_unless((int) $job->station_id === (int) $station->id, 404);
        $shift = app(ShiftService::class)->current();
        $job->load(['user', 'recorder', 'material'])->loadCount('productionLogs');
        $materials = $job->material && $job->status === 'active'
            ? collect([$job->material])
            : collect();
        $logs = ProductionLog::query()->with(['job', 'material', 'outputMaterial', 'user'])
            ->where('station_id', $station->id)->where('job_id', $job->id)
            ->orderByDesc('id')->limit(50)->get();
        $scanCodes = Schema::hasColumn('finishing_wip_receipts', 'production_log_id')
            ? DB::table('finishing_wip_receipts')->whereIn('production_log_id', $logs->pluck('id'))
                ->pluck('scan_code', 'production_log_id')
            : collect();
        // Running number inside this Job (1 = first tag of the Job), not the database id.
        $sequence = ProductionLog::query()->where('job_id', $job->id)->orderBy('id')->pluck('id')->flip();
        $logs->each(function (ProductionLog $log) use ($scanCodes, $sequence): void {
            $log->setAttribute('sequence', ($sequence[$log->id] ?? 0) + 1);
            $log->setAttribute('defect_details', DB::table('finishing_defect_entries')->where('production_log_id', $log->id)->get());
            $log->setAttribute('scan_code', $scanCodes->get($log->id));
            if ($log->status === 'confirmed' && $log->output_material_id) {
                $log->setAttribute('tag_url', route('finishing.production.tag', ['productionLog' => $log->id]));
            }
        });

        return Inertia::render('Finishing/StationRecord', [
            'station' => $station,
            'stations' => FinishingStation::query()->orderBy('id')->get(['id', 'name', 'code']),
            'job' => $job,
            'summary' => $this->jobSummary($job),
            'materials' => $materials,
            'logs' => $logs,
            'shift' => $shift,
            'isAdmin' => $this->isAdmin(request()),
            'rawMaterials' => config('finishing.raw_materials', []),
            'rawMaterialUnits' => config('finishing.raw_material_units', []),
            'defectTypes' => DB::table('finishing_defect_types')->where('is_active', true)->where(fn ($q) => $q->whereNull('station_id')->orWhere('station_id', $station->id))->orderBy('name')->get(),
            'csrf' => csrf_token(),
        ]);
    }

    public function printProductionTag(ProductionLog $productionLog)
    {
        abort_unless($productionLog->status === 'confirmed' && $productionLog->output_material_id, 404);
        $productionLog->load(['station', 'job.recorder', 'material', 'outputMaterial', 'user']);
        $fileName = $productionLog->output_lot ?: 'FIN-'.$productionLog->station->code.'-'.$productionLog->id;

        // Return the bytes as a normal response: ->stream() echoes the PDF itself and Laravel then fails with
        // "headers already sent" when it sends its own response.
        $pdf = PDF::loadView('finishing.production-tag', ['tag' => $this->tagFields($productionLog)], [], ['format' => 'A4', 'margin_top' => 8, 'margin_bottom' => 8])->output();

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$fileName.'.pdf"',
        ]);
    }

    /** Master: Finishing staff that can be picked when opening a Job. */
    public function employees(): Response
    {
        $tz = config('finishing.timezone', 'Asia/Bangkok');
        $today = now($tz)->startOfDay();
        $month = now($tz)->startOfMonth();
        $hasStints = Schema::hasTable('finishing_job_employees');
        $todayMinutes = $hasStints ? FinishingJobEmployee::minutesByEmployee($today->copy()->utc(), $today->copy()->addDay()->utc()) : collect();
        $monthMinutes = $hasStints ? FinishingJobEmployee::minutesByEmployee($month->copy()->utc(), $month->copy()->addMonth()->utc()) : collect();
        $working = $hasStints ? FinishingJobEmployee::query()->whereNull('ended_at')->whereNotNull('employee_id')->pluck('employee_id')->unique()->flip() : collect();

        return Inertia::render('Finishing/Employees', [
            'employees' => FinishingEmployee::query()->orderByDesc('is_active')->orderBy('name')
                ->get(['id', 'name', 'nickname', 'display_name', 'is_active', 'updated_at'])
                ->map(fn (FinishingEmployee $employee) => $employee->toArray() + [
                    'minutes_today' => (float) ($todayMinutes[$employee->id] ?? 0),
                    'minutes_month' => (float) ($monthMinutes[$employee->id] ?? 0),
                    'working_now' => $working->has($employee->id),
                ]),
            'monthLabel' => $month->locale('th')->translatedFormat('F').' '.($month->year + 543),
            'csrf' => csrf_token(),
        ]);
    }

    /** "สรุปการคัดตามวัน": every Job of one production date (Job shift date), with B+ CSV export per Job / per day. */
    public function dailySummary(Request $request): Response
    {
        $filters = $this->dailyFilters($request);
        $jobs = FinishingJob::query()->with(['station:id,code,name', 'recorder:id,name'])
            ->whereDate('shift_date', $filters['date'])
            ->when($filters['station_id'], fn ($q, $id) => $q->where('station_id', $id))
            ->orderBy('station_id')->orderBy('shift')->orderBy('id')->get();
        $totals = ProductionLog::query()->whereIn('job_id', $jobs->pluck('id'))->where('status', 'confirmed')
            ->groupBy('job_id')->select('job_id')
            ->selectRaw('COUNT(*) as tags, SUM(quantity_in) as quantity_in, SUM(quantity_out) as quantity_out, SUM(defect_count) as defects, SUM(weight_kg) as weight_kg')
            ->get()->keyBy('job_id');
        $rows = $jobs->map(function (FinishingJob $job) use ($totals) {
            $total = $totals->get($job->id);

            return [
                'id' => $job->id,
                'station_code' => $job->station?->code,
                'station_name' => $job->station?->name,
                'shift' => $job->shift,
                'status' => $job->status,
                'product_type_code' => $job->product_type_code,
                'product_type_name' => $job->product_type_name,
                'machine_name' => $job->machine_name,
                'pvc_type' => $job->pvc_type,
                'recorder' => $job->recorder?->name,
                'raw_materials' => $job->raw_materials ?? [],
                'tags' => (int) ($total->tags ?? 0),
                'quantity_in' => (float) ($total->quantity_in ?? 0),
                'quantity_out' => (float) ($total->quantity_out ?? 0),
                'defects' => (float) ($total->defects ?? 0),
                'weight_kg' => round((float) ($total->weight_kg ?? 0), 3),
            ];
        })->values();

        return Inertia::render('Finishing/DailySummary', [
            'filters' => ['date' => $filters['date'], 'station_id' => $filters['station_id']],
            'stations' => FinishingStation::query()->orderBy('id')->get(['id', 'code', 'name']),
            'jobs' => $rows,
            'totals' => [
                'jobs' => $rows->count(),
                'active_jobs' => $rows->where('status', 'active')->count(),
                'tags' => $rows->sum('tags'),
                'quantity_in' => $rows->sum('quantity_in'),
                'quantity_out' => $rows->sum('quantity_out'),
                'defects' => $rows->sum('defects'),
                'weight_kg' => round($rows->sum('weight_kg'), 3),
            ],
        ]);
    }

    public function exportDaily(Request $request, BplusExportService $export)
    {
        $filters = $this->dailyFilters($request);
        $station = $filters['station_id'] ? FinishingStation::query()->find($filters['station_id']) : null;
        $logs = $export->logs(fn ($q) => $q->whereHas('job', fn ($job) => $job->whereDate('shift_date', $filters['date']))
            ->when($filters['station_id'], fn ($q, $id) => $q->where('station_id', $id)));

        return $export->download($logs, 'FIN_'.str_replace('-', '', $filters['date']).'_'.($station?->code ?? 'ALL').'.csv');
    }

    public function exportJob(FinishingJob $job, BplusExportService $export)
    {
        $logs = $export->logs(fn ($q) => $q->where('job_id', $job->id));

        return $export->download($logs, 'FIN_JOB_'.$job->job_no.'.csv');
    }

    /** @return array{date: string, station_id: ?int} */
    private function dailyFilters(Request $request): array
    {
        $data = $request->validate(['date' => ['nullable', 'date'], 'station_id' => ['nullable', 'integer', 'exists:finishing_stations,id']]);

        return [
            'date' => isset($data['date']) ? \Carbon\Carbon::parse($data['date'])->toDateString() : now(config('finishing.timezone', 'Asia/Bangkok'))->toDateString(),
            'station_id' => isset($data['station_id']) ? (int) $data['station_id'] : null,
        ];
    }

    /** Full-page processing summary of one Job (desktop); phones use the in-page dialog. */
    public function jobSummaryPage(string $station, FinishingJob $job): Response
    {
        $station = FinishingStation::query()->where('code', $station)->firstOrFail();
        $this->abortIfDisabled($station);
        abort_unless((int) $job->station_id === (int) $station->id, 404);
        $job->load(['user', 'recorder', 'material'])->loadCount('productionLogs');

        return Inertia::render('Finishing/JobSummary', [
            'station' => $station,
            'job' => $job,
            'summary' => $this->jobSummary($job),
            'downtime' => $this->jobDowntime($job),
            'csrf' => csrf_token(),
        ]);
    }

    /** Machine stops at the Job's station while the Job was running (clipped to the Job's start/end). */
    private function jobDowntime(FinishingJob $job): array
    {
        $tz = config('finishing.timezone', 'Asia/Bangkok');
        $start = $job->created_at?->copy()->utc();
        $end = ($job->completed_at ?? ($job->status === 'completed' ? $job->updated_at : now()))?->copy()->utc();
        if (! $start || ! $end) {
            return ['periods' => [], 'minutes' => 0];
        }
        $report = app(DowntimeReportService::class)->build(
            $start->copy()->timezone($tz)->toDateString(), $end->copy()->timezone($tz)->toDateString(), (int) $job->station_id,
        );
        $periods = collect($report['periods'])->map(function (array $period) use ($start, $end) {
            $from = \Carbon\Carbon::parse($period['stopped_at'])->max($start);
            $to = ($period['resumed_at'] ? \Carbon\Carbon::parse($period['resumed_at']) : now())->min($end);
            $minutes = $to->gt($from) ? round($from->diffInSeconds($to) / 60, 1) : 0;

            return $minutes > 0 ? ['minutes' => $minutes] + $period : null;
        })->filter()->sortBy('stopped_at')->values();

        return ['periods' => $periods->all(), 'minutes' => round($periods->sum('minutes'), 1)];
    }

    /** "จบงาน" summary: what went in, what came out, and how long the Job took (all logs, not just the visible 50). */
    private function jobSummary(FinishingJob $job): array
    {
        $tz = config('finishing.timezone', 'Asia/Bangkok');
        $logs = ProductionLog::query()->with('user:id,name')->where('job_id', $job->id)->where('status', 'confirmed')->orderBy('id')
            ->get(['id', 'user_id', 'output_material_id', 'quantity_in', 'quantity_out', 'defect_count', 'weight_kg', 'created_at', 'output_lot', 'output_tag_code']);
        $receipts = DB::table('finishing_wip_receipts')->where('job_id', $job->id)->orderBy('id')->get(['scan_code', 'quantity', 'production_log_id', 'created_at']);
        $sequence = ProductionLog::query()->where('job_id', $job->id)->orderBy('id')->pluck('id')->flip();
        $scanByLog = $receipts->whereNotNull('production_log_id')->pluck('scan_code', 'production_log_id');
        $started = $job->created_at;
        $ended = $job->completed_at ?? ($job->status === 'completed' ? $job->updated_at : now());
        $firstScan = $logs->min('created_at');
        $lastScan = $logs->max('created_at');
        $format = fn ($time) => $time ? $time->copy()->timezone($tz)->format('d/m/Y H:i') : null;

        return [
            // One row per processed tag: WIP in (scanned) -> WIP out (issued).
            'items' => $logs->map(fn (ProductionLog $log) => [
                'sequence' => ($sequence[$log->id] ?? 0) + 1,
                'in_code' => $scanByLog->get($log->id),
                'in_quantity' => (float) $log->quantity_in,
                'out_code' => $log->output_tag_code,
                'out_lot' => $log->output_lot,
                'out_quantity' => (float) $log->quantity_out,
                'weight_kg' => $log->weight_kg !== null ? (float) $log->weight_kg : null,
                'time' => $log->created_at?->copy()->timezone($tz)->format('H:i'),
                'date_time' => $log->created_at?->copy()->timezone($tz)->format('d/m/Y H:i'),
                'defects' => (float) $log->defect_count,
                'recorded_by' => $log->user?->name,
                'tag_url' => $log->output_material_id ? route('finishing.production.tag', ['productionLog' => $log->id]) : null,
            ])->values()->all(),
            // Received into the Job but not (or no longer) turned into an output tag.
            'pending_items' => $receipts->filter(fn ($receipt) => ! $receipt->production_log_id || ! $logs->contains('id', (int) $receipt->production_log_id))
                ->map(fn ($receipt) => ['in_code' => $receipt->scan_code, 'in_quantity' => (float) $receipt->quantity])->values()->all(),
            'input_tags' => $receipts->count(),
            'input_quantity' => (float) $receipts->sum('quantity'),
            'output_tags' => $logs->count(),
            'quantity_in' => (float) $logs->sum('quantity_in'),
            'quantity_out' => (float) $logs->sum('quantity_out'),
            'defects' => (float) $logs->sum('defect_count'),
            'weight_kg' => round((float) $logs->sum('weight_kg'), 3),
            'pending_quantity' => (float) ($job->material?->quantity ?? 0),
            'draft_count' => ProductionLog::query()->where('job_id', $job->id)->where('status', 'draft')->count(),
            'started_at' => $format($started),
            'first_scan_at' => $format($firstScan),
            'last_scan_at' => $format($lastScan),
            'ended_at' => $format($ended),
            'is_completed' => $job->status === 'completed',
            'minutes' => $started ? (int) round($started->diffInSeconds($ended) / 60) : 0,
            'scan_minutes' => $firstScan && $lastScan ? (int) round($firstScan->diffInSeconds($lastScan) / 60) : 0,
        ];
    }

    /** The same tag as print-ready HTML (inline SVG codes) for phones: printed in-page, no PDF download. */
    public function printProductionTagHtml(ProductionLog $productionLog)
    {
        abort_unless($productionLog->status === 'confirmed' && $productionLog->output_material_id, 404);
        $productionLog->load(['station', 'job.recorder', 'material', 'outputMaterial', 'user']);
        $tag = $this->tagFields($productionLog);

        return response()->view('finishing.production-tag-print', [
            'tag' => $tag,
            'qrSvg' => $this->qrSvg((string) $tag['qr']),
            'barcodeSvg' => $this->code128Svg((string) $tag['barcode']),
        ])->header('Cache-Control', 'no-store');
    }

    private function qrSvg(string $code): string
    {
        if ($code === '') {
            return '';
        }
        $qr = new \Mpdf\QrCode\QrCode($code, 'M');
        $qr->disableBorder();
        $svg = (new \Mpdf\QrCode\Output\Svg())->output($qr, 200);

        // Scalable: swap the fixed 200x200 size for a viewBox so CSS can size it in mm.
        $svg = (string) preg_replace('/^<\?xml[^>]*>\s*/', '', $svg);

        return (string) preg_replace('/width="200" height="200"/', 'viewBox="0 0 200 200"', $svg, 1);
    }

    private function code128Svg(string $code): string
    {
        $data = $code !== '' ? (new \Mpdf\Barcode())->getBarcodeArray($code, 'C128B') : false;
        if (! $data) {
            return '';
        }
        $x = 0;
        $bars = '';
        foreach ($data['bcode'] as $bar) {
            if ($bar['t']) {
                $bars .= '<rect x="'.$x.'" y="0" width="'.$bar['w'].'" height="40"/>';
            }
            $x += $bar['w'];
        }

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$data['maxw'].' 40" preserveAspectRatio="none" shape-rendering="crispEdges" fill="#000">'.$bars.'</svg>';
    }

    /** Fields of the Finishing WIP tag, laid out like the AppSheet Finishing tag. */
    private function tagFields(ProductionLog $log): array
    {
        $tz = config('finishing.timezone', 'Asia/Bangkok');
        $job = $log->job;
        $scanCode = DB::table('finishing_wip_receipts')->where('production_log_id', $log->id)->value('scan_code');
        try {
            $source = $scanCode ? TagCode::parse((string) $scanCode) : null;
        } catch (InvalidArgumentException) {
            $source = null;
        }
        // Product names look like "ขอบเรียบ ธรรมดา 1.2 x 2.4 m. x9 mm." -> edge + size.
        $productName = (string) ($log->product_type_name ?: $job?->product_type_name);
        preg_match('/[\d.]+\s*x\s*[\d.]+\s*m\.?\s*x\s*[\d.]+\s*mm\.?/iu', $productName, $size);
        preg_match('/^(ขอบ\S+)/u', $productName, $edge);
        $qty = (float) $log->quantity_out;
        $lot = (string) $log->output_lot;

        return [
            'sequence' => $lot !== '' ? substr($lot, 6) : '#'.$log->id,
            'qr' => $log->output_tag_code ?: $log->outputMaterial?->code,
            'barcode' => $lot !== '' ? $lot : $log->outputMaterial?->code,
            'product_code' => $log->product_type_code ?: $job?->product_type_code,
            'product_name' => $productName,
            'sorted_date' => $log->shift_date ? $log->shift_date->format('d/m/').($log->shift_date->year + 543) : '',
            'sheet_type' => $job?->pvc_type,
            'size' => $size[0] ?? '',
            'edge' => $edge[1] ?? '',
            'category' => 'WIP แผ่นรอคัด',
            'production_line' => $source?->line ? 'L'.$source->line : '',
            'lot' => $lot,
            'fn_line' => $job?->machine_name,
            'quantity' => floor($qty) === $qty ? number_format($qty) : rtrim(rtrim(number_format($qty, 3), '0'), '.'),
            'inspector' => $job?->inspector_name,
            'time' => $log->created_at?->timezone($tz)->format('H:i'),
            'recorder' => $job?->recorder?->name ?? $log->user?->name,
            'weight' => $log->weight_kg !== null ? number_format((float) $log->weight_kg, 2) : '',
            'wip_date' => $source ? substr($source->lot, 0, 6) : '',
        ];
    }

    public function history(Request $request, string $station): Response
    {
        $stationModel = FinishingStation::query()->where('code', $station)->firstOrFail();
        $this->abortIfDisabled($stationModel);
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'], 'date_to' => ['nullable', 'date'],
            'shift' => ['nullable', 'in:day,night'], 'status' => ['nullable', 'in:draft,confirmed,cancelled'],
        ]);
        $logs = ProductionLog::query()->with(['material', 'outputMaterial', 'user'])
            ->where('station_id', $stationModel->id)
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('shift_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('shift_date', '<=', $date))
            ->when($filters['shift'] ?? null, fn ($q, $shift) => $q->where('shift', $shift))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('shift_date')->orderByDesc('id')->paginate(30)->withQueryString();

        return Inertia::render('Finishing/StationHistory', ['station' => $stationModel, 'logs' => $logs, 'filters' => $filters]);
    }

    /** Finishing home: today's production at a glance (by Job date, same as the daily summary). */
    public function overview(): Response
    {
        $tz = config('finishing.timezone', 'Asia/Bangkok');
        $today = now($tz)->toDateString();
        $statusReady = Schema::hasTable('finishing_station_status_events');
        $stationsQuery = FinishingStation::query()->with(['jobs' => fn ($q) => $q->where('status', 'active')->orderByDesc('id')
            ->select(['id', 'station_id', 'status', 'shift_date', 'shift', 'product_type_code', 'product_type_name', 'employee_names', 'inspector_name', 'created_at'])
            ->withCount(['productionLogs as tag_count' => fn ($logs) => $logs->where('status', 'confirmed')])])->orderBy('id');
        if ($statusReady) {
            $stationsQuery->with('latestStatusEvent.user');
        }
        $stations = $stationsQuery->get();

        $logs = ProductionLog::query()->with(['station:id,code', 'user:id,name'])
            ->where('status', 'confirmed')->whereDate('shift_date', $today)->orderByDesc('id')
            ->get(['id', 'station_id', 'job_id', 'user_id', 'shift', 'quantity_in', 'quantity_out', 'defect_count', 'weight_kg', 'output_lot', 'output_tag_code', 'output_material_id', 'product_type_code', 'product_type_name', 'created_at']);
        $downtime = app(DowntimeReportService::class)->build($today, $today, null);
        $sum = fn ($rows, string $field) => round((float) $rows->sum($field), 3);

        return Inertia::render('Finishing/Overview', [
            'date' => $today,
            'shift' => app(ShiftService::class)->current(),
            'kpis' => [
                'active_jobs' => $stations->sum(fn ($station) => $station->jobs->count()),
                'tags' => $logs->count(),
                'quantity_in' => $sum($logs, 'quantity_in'),
                'quantity_out' => $sum($logs, 'quantity_out'),
                'weight_kg' => $sum($logs, 'weight_kg'),
                'defects' => $sum($logs, 'defect_count'),
                'running' => $stations->where('is_enabled', true)->where('is_machine_on', true)->count(),
                'enabled' => $stations->where('is_enabled', true)->count(),
                'downtime_minutes' => $downtime['total_minutes'],
                'stops' => $downtime['stop_count'],
            ],
            'stations' => $stations->map(fn (FinishingStation $station) => [
                'id' => $station->id,
                'code' => $station->code,
                'name' => $station->name,
                'is_enabled' => $station->is_enabled,
                'is_machine_on' => (bool) $station->is_machine_on,
                'status_reason' => $statusReady ? $station->latestStatusEvent?->reason : null,
                'status_since' => $statusReady ? $station->latestStatusEvent?->created_at?->toIso8601String() : null,
                'jobs' => $station->jobs->map(fn (FinishingJob $job) => [
                    'id' => $job->id,
                    'shift' => $job->shift,
                    'shift_date' => $job->shift_date?->toDateString(),
                    'product_type_code' => $job->product_type_code,
                    'product_type_name' => $job->product_type_name,
                    'employees' => collect($job->employee_names ?? [])->map(fn ($item) => is_array($item) ? ($item['name'] ?? '') : (string) $item)->filter()->values(),
                    'inspector_name' => $job->inspector_name,
                    'tag_count' => (int) $job->tag_count,
                    'started_at' => $job->created_at?->copy()->timezone($tz)->format('H:i'),
                ])->values(),
                'today_tags' => $logs->where('station_id', $station->id)->count(),
                'today_quantity_out' => $sum($logs->where('station_id', $station->id), 'quantity_out'),
                'today_weight_kg' => $sum($logs->where('station_id', $station->id), 'weight_kg'),
                'today_downtime_minutes' => collect($downtime['byStation'])->firstWhere('station_code', $station->code)['minutes'] ?? 0,
            ])->values(),
            'byProduct' => $logs->groupBy(fn ($log) => $log->product_type_code ?: '-')->map(fn ($rows, $code) => [
                'code' => $code,
                'name' => $rows->first()->product_type_name,
                'tags' => $rows->count(),
                'quantity_out' => $sum($rows, 'quantity_out'),
                'weight_kg' => $sum($rows, 'weight_kg'),
            ])->sortByDesc('quantity_out')->values(),
            'byShift' => collect(['day', 'night'])->map(fn ($shift) => [
                'shift' => $shift,
                'tags' => $logs->where('shift', $shift)->count(),
                'quantity_out' => $sum($logs->where('shift', $shift), 'quantity_out'),
                'weight_kg' => $sum($logs->where('shift', $shift), 'weight_kg'),
            ]),
            'recent' => $logs->take(10)->map(fn (ProductionLog $log) => [
                'id' => $log->id,
                'job_id' => $log->job_id,
                'station_code' => $log->station?->code,
                'time' => $log->created_at?->copy()->timezone($tz)->format('H:i'),
                'product_type_code' => $log->product_type_code,
                'lot' => $log->output_lot,
                'tag_code' => $log->output_tag_code,
                'quantity_out' => (float) $log->quantity_out,
                'weight_kg' => $log->weight_kg !== null ? (float) $log->weight_kg : null,
                'recorded_by' => $log->user?->name,
                'tag_url' => $log->output_material_id ? route('finishing.production.tag', ['productionLog' => $log->id]) : null,
            ])->values(),
            'downtime' => array_slice($downtime['periods'], 0, 8),
        ]);
    }

    public function dashboard(): Response
    {
        $today = now(config('finishing.timezone', config('app.timezone')))->toDateString();
        $statusReady = Schema::hasTable('finishing_station_status_events');
        // Open Jobs per station: what is being processed right now and by whom.
        $stationsQuery = FinishingStation::query()->with(['jobs' => fn ($q) => $q->where('status', 'active')->orderByDesc('id')
            ->select(['id', 'station_id', 'status', 'shift_date', 'shift', 'product_type_code', 'product_type_name', 'employee_names', 'inspector_name'])
            ->withCount(['productionLogs as tag_count' => fn ($logs) => $logs->where('status', 'confirmed')])]);
        if ($statusReady) {
            $stationsQuery->with('latestStatusEvent.user');
        }
        $stations = $stationsQuery->withCount([
            'productionLogs as today_logs' => fn ($q) => $q->whereDate('shift_date', $today)->where('status', 'confirmed'),
        ])->withSum([
            'productionLogs as today_quantity_out' => fn ($q) => $q->whereDate('shift_date', $today)->where('status', 'confirmed'),
        ], 'quantity_out')->withSum([
            'productionLogs as today_defects' => fn ($q) => $q->whereDate('shift_date', $today)->where('status', 'confirmed'),
        ], 'defect_count')->orderBy('id')->get();
        $recent = ProductionLog::query()->with(['station', 'user', 'material'])
            ->whereDate('shift_date', $today)->where('status', 'confirmed')->orderByDesc('id')->limit(20)->get();
        $byShift = ProductionLog::query()->whereDate('shift_date', $today)->where('status', 'confirmed')
            ->select('shift')->selectRaw('SUM(quantity_out) as quantity_out, SUM(defect_count) as defects, COUNT(*) as log_count')
            ->groupBy('shift')->get();

        $threshold = (float) config('finishing.defect_alert_threshold', 5);
        $alerts = $stations->filter(function ($station) use ($threshold) {
            $output = (float) ($station->today_quantity_out ?? 0);
            return $output > 0 && ((float) ($station->today_defects ?? 0) / $output) * 100 >= $threshold;
        })->values();
        return Inertia::render('Finishing/Dashboard', ['stations' => $stations, 'recent' => $recent, 'byShift' => $byShift, 'date' => $today, 'alerts' => $alerts, 'csrf' => csrf_token(), 'statusReady' => $statusReady]);
    }

    public function reports(Request $request): Response
    {
        $filters = $request->validate(['date_from' => ['nullable', 'date'], 'date_to' => ['nullable', 'date'], 'station_id' => ['nullable', 'integer']]);
        $logs = ProductionLog::query()->with('station')
            ->where('status', 'confirmed')
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('shift_date', '>=', $d))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('shift_date', '<=', $d))
            ->when($filters['station_id'] ?? null, fn ($q, $id) => $q->where('station_id', $id))
            ->orderBy('shift_date')->get();
        $defects = DB::table('finishing_defect_entries as e')
            ->join('finishing_defect_types as t', 't.id', '=', 'e.defect_type_id')
            ->join('finishing_production_logs as l', 'l.id', '=', 'e.production_log_id')
            ->where('l.status', 'confirmed')
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('l.shift_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('l.shift_date', '<=', $date))
            ->when($filters['station_id'] ?? null, fn ($q, $id) => $q->where('l.station_id', $id))
            ->select('t.name')->selectRaw('SUM(e.quantity) as quantity')->groupBy('t.name')->orderByDesc('quantity')->get();

        return Inertia::render('Finishing/Reports', [
            'stations' => FinishingStation::query()->orderBy('id')->get(['id', 'code', 'name']),
            'filters' => $filters,
            'defects' => $defects,
            'downtime' => app(DowntimeReportService::class)->build($filters['date_from'] ?? null, $filters['date_to'] ?? null, isset($filters['station_id']) ? (int) $filters['station_id'] : null),
            'csrf' => csrf_token(),
            'isAdmin' => $this->isAdmin(request()),
            'summary' => [
                'quantity_in' => (float) $logs->sum('quantity_in'),
                'quantity_out' => (float) $logs->sum('quantity_out'),
                'defects' => (float) $logs->sum('defect_count'),
                'ft' => (float) $logs->sum('ft_count'),
                'byStation' => $logs->groupBy('station_id')->map(fn ($items) => ['station' => $items->first()->station?->name, 'quantity_out' => (float) $items->sum('quantity_out'), 'defects' => (float) $items->sum('defect_count'), 'logs' => $items->count()])->values(),
                'byShift' => $logs->groupBy('shift')->map(fn ($items, $shift) => ['shift' => $shift, 'quantity_out' => (float) $items->sum('quantity_out'), 'defects' => (float) $items->sum('defect_count'), 'logs' => $items->count()])->values(),
                'daily' => $logs->groupBy(fn ($item) => $item->shift_date->format('Y-m-d'))->map(fn ($items, $date) => ['date' => $date, 'quantity_out' => (float) $items->sum('quantity_out'), 'defects' => (float) $items->sum('defect_count')])->values(),
            ],
        ]);
    }

    private function abortIfDisabled(FinishingStation $station): void
    {
        abort_unless($station->is_enabled, 403, "สถานี {$station->code} ยังไม่เปิดใช้งาน");
    }

    private function isAdmin(Request $request): bool
    {
        return in_array(strtolower((string) ($request->user()->username ?? '')), config('finishing.admin_usernames', ['manager']), true);
    }
}
