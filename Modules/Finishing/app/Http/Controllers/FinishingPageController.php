<?php

namespace Modules\Finishing\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Finishing\App\Models\FinishingJob;
use Modules\Finishing\App\Models\FinishingStation;
use Modules\Finishing\App\Models\ProductionLog;
use Modules\Finishing\App\Services\ShiftService;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

class FinishingPageController
{
    public function selectStation(): Response
    {
        return Inertia::render('Finishing/SelectStation', [
            'stations' => FinishingStation::query()->orderBy('id')->get(),
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
        $shift = app(ShiftService::class)->current();
        $jobs = FinishingJob::query()->with(['user', 'recorder', 'material'])->withCount('productionLogs')
            ->where('station_id', $station->id)->orderByDesc('id')->limit(100)->get();
        return Inertia::render('Finishing/StationJobs', [
            'station' => $station,
            'statusEvents' => $statusReady ? $station->statusEvents()->with('user')->orderByDesc('id')->limit(10)->get() : [],
            'statusReady' => $statusReady,
            'jobs' => $jobs,
            'shift' => $shift,
            'employeeOptions' => config('finishing.job_employees', []),
            'recorderOptions' => User::query()->orderBy('name')->get(['id', 'name']),
            'machineOptions' => $station->jobMachineOptions(),
            'pvcTypes' => $station->jobPvcTypes(),
            'inspectors' => config('finishing.job_inspectors', []),
            'currentUserId' => (int) request()->user()->id,
            'csrf' => csrf_token(),
        ]);
    }

    public function recordJob(string $station, FinishingJob $job): Response
    {
        $station = FinishingStation::query()->where('code', $station)->firstOrFail();
        abort_unless((int) $job->station_id === (int) $station->id, 404);
        $shift = app(ShiftService::class)->current();
        $job->load(['user', 'material'])->loadCount('productionLogs');
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
        $logs->each(function (ProductionLog $log) use ($scanCodes): void {
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
            'materials' => $materials,
            'logs' => $logs,
            'shift' => $shift,
            'isAdmin' => $this->isAdmin(request()),
            'rawMaterials' => config('finishing.raw_materials', []),
            'defectTypes' => DB::table('finishing_defect_types')->where('is_active', true)->where(fn ($q) => $q->whereNull('station_id')->orWhere('station_id', $station->id))->orderBy('name')->get(),
            'csrf' => csrf_token(),
        ]);
    }

    public function printProductionTag(ProductionLog $productionLog)
    {
        abort_unless($productionLog->status === 'confirmed' && $productionLog->output_material_id, 404);
        $productionLog->load(['station', 'job', 'material', 'outputMaterial', 'user']);
        $productionLog->setAttribute('scan_code', DB::table('finishing_wip_receipts')
            ->where('production_log_id', $productionLog->id)->value('scan_code'));
        $fileName = $productionLog->output_lot ?: 'FIN-'.$productionLog->station->code.'-'.$productionLog->id;

        return PDF::loadView('finishing.production-tag', ['log' => $productionLog], [], ['format' => 'A4'])
            ->stream($fileName.'.pdf');
    }

    public function history(Request $request, string $station): Response
    {
        $stationModel = FinishingStation::query()->where('code', $station)->firstOrFail();
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

    public function dashboard(): Response
    {
        $today = now(config('finishing.timezone', config('app.timezone')))->toDateString();
        $statusReady = Schema::hasTable('finishing_station_status_events');
        $stationsQuery = FinishingStation::query();
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

    private function isAdmin(Request $request): bool
    {
        return in_array(strtolower((string) ($request->user()->username ?? '')), config('finishing.admin_usernames', ['manager']), true);
    }
}
