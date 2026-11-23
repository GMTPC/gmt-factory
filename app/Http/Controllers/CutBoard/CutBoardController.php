<?php

namespace App\Http\Controllers\CutBoard;

use App\Enums\ProductionLine;
use App\Http\Controllers\Controller;
use App\Http\Requests\CutBoard\CreateCutJobRequest;
use App\Http\Resources\CutBoard\CutJobResource;
use App\Models\CutJob;
use App\Models\CutJobLine;
use App\Models\Master\BrandList;
use App\Services\CutBoard\LineMasterDataService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CutBoardController extends Controller
{
    public function index(): Response
    {
        $jobs = CutJob::query()
            ->withCount(['headers', 'lines'])
            ->latest('id')
            ->paginate(15)
            ->through(fn (CutJob $job) => (new CutJobResource($job))->resolve());

        return Inertia::render('CutBoard/Index', [
            'jobs' => $jobs,
            'productionLines' => array_map(
                fn (ProductionLine $line) => ['value' => $line->value, 'label' => $line->label()],
                ProductionLine::cases(),
            ),
            'createUrl' => route('cut-board.jobs.create'),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('CutBoard/CreateJob', [
            'productionLines' => array_map(
                fn (ProductionLine $line) => ['value' => $line->value, 'label' => $line->label()],
                ProductionLine::cases(),
            ),
            'storeUrl' => route('cut-board.jobs.store'),
            'backUrl' => route('wms.cut-boards'),
        ]);
    }

    public function store(CreateCutJobRequest $request): RedirectResponse
    {
        $job = DB::connection(config('cutboard.connection'))
            ->transaction(function () use ($request): CutJob {
                $date = $request->date('work_date');
                $line = $request->string('production_line')->toString();
                $prefix = 'JOB-'.$date->format('Ymd').'-'.$line;
                $sequence = CutJob::query()
                    ->whereDate('work_date', $date)
                    ->where('production_line', $line)
                    ->lockForUpdate()
                    ->count() + 1;

                return CutJob::create([
                    'job_no' => $prefix.'-'.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT),
                    'production_line' => $line,
                    'work_date' => $date,
                    'status' => 'draft',
                    'created_by' => $request->user()->getAuthIdentifier(),
                ]);
            });

        return redirect()
            ->route('cut-board.jobs.show', $job)
            ->with('success', 'สร้าง Job คัดบอร์ดเรียบร้อยแล้ว');
    }

    public function show(Request $request, CutJob $cutJob): Response
    {
        $lineReturn = $request->query('line');
        if ($lineReturn !== null) {
            abort_unless($lineReturn === $cutJob->production_line, 404);
        }
        return $this->renderShow($cutJob, $lineReturn);
    }

    public function showForLine(string $line, CutJob $cutJob): Response
    {
        abort_unless(in_array($line, ['L1', 'L2', 'L3', 'L4'], true) && $line === $cutJob->production_line, 404);

        return $this->renderShow($cutJob, $line);
    }

    private function renderShow(CutJob $cutJob, ?string $lineReturn): Response
    {
        $cutJob->load([
            'headers' => fn ($query) => $query->withCount('lines')->latest('id'),
        ]);
        $scanLines = CutJobLine::query()->where('cut_job_id', $cutJob->id)->oldest('id')->get();
        $wipLines = $scanLines->where('source_type', 'WIP')->values();
        $fgLines = $scanLines->where('source_type', 'FG')->values();
        $inputQty = (float) $wipLines->sum('qty');
        $fgQty = (float) $fgLines->where('qc_status', 'passed')->sum('qty');
        $ngQty = (float) $scanLines->where('qc_status', 'rejected')->sum('qty');
        $ngQty += (float) $fgLines->sum(fn ($line) => (float) data_get($line->spec_snapshot, 'ng_qty', 0));
        $lineCuttingCards = CutJob::query()
            ->whereDate('work_date', $cutJob->work_date)
            ->where('status', 'scanning')
            ->with([
                'headers:id,cut_job_id,header_no,group_code,sku_code,product_type_name,target_qty',
                'lines' => fn ($query) => $query
                    ->where('source_type', 'WIP')
                    ->select(['id', 'cut_job_id', 'cut_job_header_id', 'spec_name', 'sku_code', 'qty']),
            ])
            ->orderBy('production_line')
            ->orderByDesc('id')
            ->get()
            ->unique('production_line')
            ->values()
            ->map(fn (CutJob $lineJob) => [
                'line' => $lineJob->production_line,
                'job_no' => $lineJob->job_no,
                'products' => $lineJob->headers->map(function ($header) use ($lineJob) {
                    $wipLines = $lineJob->lines->where('cut_job_header_id', $header->id);
                    $firstLine = $wipLines->first();

                    return [
                        'header_no' => $header->header_no,
                        'group_code' => $header->group_code,
                        'name' => $header->product_type_name ?: ($firstLine?->spec_name ?: ($header->sku_code ?: 'ยังไม่ระบุสินค้า')),
                        'sku_code' => $header->sku_code ?: $firstLine?->sku_code,
                        'wip_qty' => (float) $wipLines->sum('qty'),
                    ];
                })->values(),
            ])
            ->values();

        return Inertia::render('CutBoard/Show', [
            'lineCuttingCards' => $lineCuttingCards,
            'job' => [
                'id' => $cutJob->id,
                'job_no' => $cutJob->job_no,
                'production_line' => $cutJob->production_line,
                'work_date' => $cutJob->work_date?->toDateString(),
                'status' => $cutJob->status,
                'scrap_qty' => (int) ($cutJob->scrap_qty ?? 0),
                'group_code' => $cutJob->headers->first()?->group_code,
                'product_type_name' => $cutJob->headers->first()?->product_type_name,
                'headers' => $cutJob->headers->map(fn ($header) => [
                    'id' => $header->id,
                    'header_no' => $header->header_no,
                    'group_code' => $header->group_code,
                    'sku_code' => $header->sku_code,
                    'lot_no' => $header->lot_no,
                    'target_qty' => $header->target_qty,
                    'status' => $header->status,
                    'lines_count' => $header->lines_count,
                ])->values(),
                'lines' => $scanLines->map(fn ($line) => [
                    'id' => $line->id,
                    'header_id' => $line->cut_job_header_id,
                    'source_type' => $line->source_type,
                    'source_id' => $line->source_id,
                    'source_line_digit' => (string) ($line->spec_snapshot['source_line_digit'] ?? $line->spec_snapshot['line'] ?? ''),
                    'barcode' => $line->raw_scan_code ?: ($line->wip_code ?: ($line->fg_code ?: $line->source_id)),
                    'employee_group_name' => $line->employee_group_name,
                    'employee_group_id' => $line->employee_group_id,
                    'spec_code' => $line->spec_code,
                    'spec_name' => $line->spec_name,
                    'sku_code' => $line->sku_code,
                    'lot_no' => $line->lot_no,
                    'brand_name' => $line->spec_snapshot['brand_name'] ?? null,
                    'brand_id' => $line->spec_snapshot['brand_id'] ?? null,
                    'brand_code' => $line->spec_snapshot['brand_code'] ?? null,
                    'checker' => $line->spec_snapshot['checker'] ?? null,
                    'backboard_no' => $line->spec_snapshot['backboard_no'] ?? null,
                    'remark' => $line->spec_snapshot['remark'] ?? null,
                    'out_code' => $line->interface_qr ?: ($line->tag ?: $line->raw_scan_code),
                    'qty' => $line->qty,
                    'qc_status' => $line->qc_status,
                    'receive_status' => $line->receive_status,
                    'print_status' => $line->print_status,
                    'wms_status' => $line->wms_status,
                    'rejection_reason' => $line->rejection_reason,
                    'scanned_at' => $line->scanned_at?->toIso8601String(),
                    'ng_qty' => $line->spec_snapshot['ng_qty'] ?? 0,
                    'ng_reason' => $line->spec_snapshot['ng_reason'] ?? null,
                    'print_url' => route('wms.bx-print-working', [
                        'brd_working_id' => $line->spec_snapshot['legacy_working_id'] ?? $line->id,
                        'sku_code' => $line->sku_code ?: 'unknown',
                    ]),
                ])->values(),
            ],
            'summary' => [
                'input' => $inputQty,
                'output' => $fgQty,
                'holding' => $inputQty - $fgQty - $ngQty,
                'ng' => $ngQty,
                'wip_count' => $wipLines->count(),
                'fg_count' => $fgLines->count(),
            ],
            'createHeaderUrl' => route('cut-board.headers.store', $cutJob),
            'scanUrl' => route('checker-qc-scan', ['job' => $cutJob->id]),
            'backUrl' => $lineReturn ? route('cut-board.lines.show', ['line' => $lineReturn]) : route('wms.cut-boards'),
            'completeUrl' => route('cut-board.jobs.complete', $cutJob),
            'scrapUrl' => route('cut-board.jobs.scrap', $cutJob),
            'groupUrl' => route('cut-board.lines.master', ['line' => $cutJob->production_line, 'section' => 'groups']),
            'finishQualityUrl' => $lineReturn
                ? route('cut-board.lines.jobs.finish-quality', ['line' => $lineReturn, 'jobId' => $cutJob->id])
                : null,
            'employeeGroups' => $lineReturn ? app(LineMasterDataService::class)->groups($lineReturn, true)->map(fn ($group) => [
                'id' => $group->id,
                'label' => trim(($group->employee1 ?? '').' - '.($group->employee2 ?? '')),
                'division' => $group->division,
            ])->values() : [],
            'brands' => BrandList::query()->where('bl_status', 1)->orderBy('bl_code')->get(['bl_id', 'bl_code', 'bl_name'])->map(fn ($brand) => [
                'id' => $brand->bl_id,
                'code' => $brand->bl_code,
                'name' => $brand->bl_name,
            ])->values(),
            'wipStoreUrl' => $lineReturn ? route('api.cut-board.wip.store', ['line' => $lineReturn, 'cutJob' => $cutJob->id]) : null,
            'fgStoreUrl' => $lineReturn ? route('api.cut-board.fg.store', ['line' => $lineReturn, 'cutJob' => $cutJob->id]) : null,
        ]);
    }

    public function complete(Request $request, CutJob $cutJob): RedirectResponse
    {
        DB::connection(config('cutboard.connection'))->transaction(function () use ($cutJob): void {
            $job = CutJob::query()->lockForUpdate()->findOrFail($cutJob->id);
            abort_unless(in_array($job->status, ['qc_completed', 'receiving', 'received'], true), 422, 'ต้องจบงานคัดก่อนปิด Job');
            abort_if($job->headers()->doesntExist(), 422, 'Job นี้ยังไม่มี Header');
            abort_if($job->lines()->doesntExist(), 422, 'Job นี้ยังไม่มีรายการ Scan');
            abort_if($job->lines()->where('qc_status', 'passed')->where('receive_status', '!=', 'received')->exists(), 422, 'ยังมีรายการที่รับเข้า WMS ไม่ครบ');
            abort_if($job->lines()->where('qc_status', 'passed')->where('print_status', '!=', 'printed')->exists(), 422, 'ยังมีรายการที่พิมพ์ Tag ไม่ครบ');

            $job->update(['status' => 'completed', 'completed_at' => now()]);
        });

        return redirect()->route('wms.cut-boards')->with('success', 'ปิด Job เรียบร้อยแล้ว');
    }

    public function updateScrap(Request $request, CutJob $cutJob): RedirectResponse
    {
        abort_unless($cutJob->status === 'scanning', 422, 'แก้ไขจำนวนตัดทิ้งได้เฉพาะงานที่กำลังคัด');

        $data = $request->validate([
            'scrap_qty' => ['required', 'integer', 'min:0', 'max:999999'],
        ]);

        $cutJob->update(['scrap_qty' => $data['scrap_qty']]);

        return back()->with('success', 'อัปเดตจำนวนตัดทิ้งแล้ว');
    }
}
