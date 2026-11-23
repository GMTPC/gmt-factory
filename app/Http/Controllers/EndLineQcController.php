<?php

namespace App\Http\Controllers;

use App\Models\EndLineQcHeader;
use App\Models\EndLineQcLine;
use App\Models\Master\MasterBrand;
use App\Models\Master\MasterEdge;
use App\Models\Master\MasterFace;
use App\Models\Master\MasterProductSpec;
use App\Models\Master\MasterSheetType;
use App\Models\Master\MasterSize;
use App\Models\Master\MasterThickness;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;

class EndLineQcController extends Controller
{
    private function lineNumber(string $line): int
    {
        abort_unless(preg_match('/^L?[1-4]$/i', $line), 404);
        return (int) ltrim(strtoupper($line), 'L');
    }

    private function masterData(): array
    {
        return [
            'brands' => MasterBrand::query()->orderBy('code')->get(['id', 'code', 'name']),
            'sheetTypes' => MasterSheetType::query()->orderBy('code')->get(['id', 'code', 'name']),
            'edges' => MasterEdge::query()->orderBy('code')->get(['id', 'code', 'name']),
            'sizes' => MasterSize::query()->orderBy('code')->get(['id', 'code', 'label', 'width_mm', 'length_mm']),
            'thicknesses' => MasterThickness::query()->orderBy('code')->get(['id', 'code', 'label', 'mm']),
            'faces' => MasterFace::query()->orderBy('code')->get(['id', 'code', 'name', 'is_default']),
            'specs' => MasterProductSpec::query()->orderBy('name')->get(['id', 'code', 'name', 'grade', 'brand', 'sheet_type']),
        ];
    }

    public function index(Request $request, string $line)
    {
        $lineNo = $this->lineNumber($line);
        $filters = $request->only(['date_from', 'date_to', 'shift', 'qc_inspector', 'sheet_type_id', 'edge_id', 'size_id', 'thickness_id']);
        $base = EndLineQcHeader::query()->where('line', $lineNo)
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('qc_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('qc_date', '<=', $date))
            ->when($filters['shift'] ?? null, fn ($q, $shift) => $q->where('shift', $shift))
            ->when($filters['qc_inspector'] ?? null, fn ($q, $inspector) => $q->where('qc_inspector', 'like', '%'.$inspector.'%'))
            ->when($filters['sheet_type_id'] ?? null, fn ($q, $id) => $q->where('sheet_type_id', $id))
            ->when($filters['edge_id'] ?? null, fn ($q, $id) => $q->where('edge_id', $id))
            ->when($filters['size_id'] ?? null, fn ($q, $id) => $q->where('size_id', $id))
            ->when($filters['thickness_id'] ?? null, fn ($q, $id) => $q->where('thickness_id', $id));

        $headers = fn (array $statuses) => (clone $base)->whereIn('status', $statuses)
            ->with(['sheetType:id,code,name', 'edge:id,code,name', 'size:id,code,label', 'thickness:id,code,label', 'face:id,code,name', 'lines:id,header_id,product_state,qty,status'])
            ->latest('id')->get()->map(fn (EndLineQcHeader $header) => [
                'id' => $header->id, 'line' => 'L'.$header->line, 'qc_date' => $header->qc_date?->toDateString(),
                'shift' => $header->shift, 'qc_inspector' => $header->qc_inspector, 'status' => $header->status,
                'lot' => $header->lot, 'job_no' => $header->job_no, 'running_no' => $header->running_no,
                'product_name' => trim(implode(' ', array_filter([$header->sheetType?->name, $header->edge?->name, $header->size?->label, $header->thickness?->label]))),
                'lines_count' => $header->lines->count(),
                'wip_qty' => $header->lines->where('product_state', 'WIP')->sum('qty'),
                'fg_qty' => $header->lines->where('product_state', 'FG')->sum('qty'),
                'ng_count' => $header->lines->where('status', 'reject')->count(),
            ]);

        $inspectors = EndLineQcHeader::query()->where('line', $lineNo)->whereNotNull('qc_inspector')->distinct()->orderBy('qc_inspector')->pluck('qc_inspector');
        return Inertia::render('EndLineQc/Index', [
            'line' => 'L'.$lineNo,
            'today' => now()->toDateString(),
            'showCreate' => $request->boolean('create'),
            'inspector' => auth()->user()?->name ?? '',
            'processing' => $headers(['draft', 'in_process']),
            'completed' => $headers(['success', 'cancel']),
            'masters' => $this->masterData(), 'inspectors' => $inspectors, 'filters' => $filters,
            'urls' => ['store' => route('end-line-qc.store', ['line' => 'L'.$lineNo]), 'prefix' => url('/end-line-qc/L'.$lineNo)],
        ]);
    }

    public function store(Request $request, string $line)
    {
        $lineNo = $this->lineNumber($line);
        $data = $request->validate([
            'qc_date' => ['required', 'date'], 'shift' => ['required', Rule::in(['A', 'B', 'T'])],
            'qc_inspector' => ['required', 'string', 'max:255'],
            'sheet_type_id' => ['required', 'exists:master_sheet_types,id'], 'edge_id' => ['required', 'exists:master_edges,id'],
            'size_id' => ['required', 'exists:master_sizes,id'], 'thickness_id' => ['required', 'exists:master_thicknesses,id'],
            'face_id' => ['nullable', 'exists:master_faces,id'],
        ]);
        $date = \Illuminate\Support\Carbon::parse($data['qc_date']);
        $header = DB::transaction(function () use ($data, $lineNo, $date) {
            $running = (int) EndLineQcHeader::query()->where('line', $lineNo)->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month)->lockForUpdate()->max('running_no') + 1;
            return EndLineQcHeader::create(array_replace($data, [
                'line' => $lineNo, 'status' => 'draft', 'lot' => $date->format('ymd'), 'running_no' => $running,
                'job_no' => 'JB'.$date->format('ym').'/'.str_pad((string) $running, 3, '0', STR_PAD_LEFT),
                'face_id' => $data['face_id'] ?: MasterFace::query()->where('is_default', true)->value('id'),
                'created_by' => auth()->id(), 'updated_by' => auth()->id(),
            ]));
        });
        return back()->with('success', 'Created '.$header->job_no);
    }

    public function create(string $line)
    {
        return redirect()->route('end-line-qc.index', ['line' => 'L'.$this->lineNumber($line), 'create' => 1]);
    }

    public function edit(string $line, int $id)
    {
        return redirect()->route('end-line-qc.qc', ['line' => 'L'.$this->lineNumber($line), 'id' => $id]);
    }

    public function updateHeader(Request $request, string $line, int $id)
    {
        $header = EndLineQcHeader::where('line', $this->lineNumber($line))->findOrFail($id);
        abort_if($header->lines()->exists() || in_array($header->status, ['success', 'cancel'], true), 403, 'งานเริ่มคัดแล้ว แก้สเปกงานไม่ได้');
        $data = $request->validate([
            'qc_date' => ['required', 'date'], 'shift' => ['required', Rule::in(['A', 'B', 'T'])], 'qc_inspector' => ['required', 'string', 'max:255'],
            'sheet_type_id' => ['required', 'exists:master_sheet_types,id'], 'edge_id' => ['required', 'exists:master_edges,id'],
            'size_id' => ['required', 'exists:master_sizes,id'], 'thickness_id' => ['required', 'exists:master_thicknesses,id'], 'face_id' => ['nullable', 'exists:master_faces,id'],
        ]);
        $date = \Illuminate\Support\Carbon::parse($data['qc_date']);
        $header->update(array_replace($data, ['lot' => $date->format('ymd'), 'updated_by' => auth()->id()]));
        return back()->with('success', 'อัปเดตข้อมูลงานแล้ว');
    }

    public function show(string $line, int $id)
    {
        $lineNo = $this->lineNumber($line);
        $header = EndLineQcHeader::with(['brand', 'sheetType', 'edge', 'size', 'thickness', 'face', 'lines.spec', 'lines.brandMaster'])->where('line', $lineNo)->findOrFail($id);
        $lines = $header->lines->map(function (EndLineQcLine $record): array {
            $row = $record->toArray();
            $row['time_at'] = $this->formatTimeForDisplay($record->time_at);
            return $row;
        });
        return Inertia::render('EndLineQc/Show', [
            'line' => 'L'.$lineNo, 'header' => $header, 'lines' => $lines,
            'masters' => $this->masterData(),
            'urls' => ['prefix' => url('/end-line-qc/L'.$lineNo), 'save' => route('end-line-qc.measure', ['line' => 'L'.$lineNo, 'id' => $id]),
                'wipReport' => route('end-line-qc.wip-report', ['line' => 'L'.$lineNo, 'id' => $id]), 'fgReport' => route('end-line-qc.fg-report', ['line' => 'L'.$lineNo, 'id' => $id]),
                'wipExport' => route('end-line-qc.wip-export', ['id' => $id])],
        ]);
    }

    public function measure(Request $request, string $line, int $id)
    {
        $lineNo = $this->lineNumber($line);
        $header = EndLineQcHeader::where('line', $lineNo)->findOrFail($id);
        abort_if(in_array($header->status, ['success', 'cancel'], true), 403, 'This QC job is closed.');
        $data = $request->validate([
            'product_state' => ['required', Rule::in(['WIP', 'FG'])], 'weight' => ['nullable', 'numeric'],
            'time' => ['nullable', 'string', 'max:16'], 'qty' => ['required', 'integer', 'min:0'],
            'unit' => ['nullable', Rule::in(['2/1', '2/2'])], 'spec_id' => ['nullable', 'exists:master_product_specs,id'],
            'brand_id' => ['nullable', 'required_if:product_state,FG', 'exists:master_brands,id'],
            'wip_status_code' => ['nullable', 'required_if:product_state,WIP', Rule::in(['91', '98', '99'])],
            'wip_status_name' => ['nullable', 'string', 'max:100'], 'running_wip' => ['nullable', 'integer', 'min:1'], 'running_fg' => ['nullable', 'integer', 'min:1'],
        ]);
        $brand = !empty($data['brand_id']) ? MasterBrand::find($data['brand_id']) : null;
        $run = max(1, (int) ($data[$data['product_state'] === 'WIP' ? 'running_wip' : 'running_fg'] ?? 1));
        while (EndLineQcLine::where('header_id', $id)->where('product_state', $data['product_state'])->where('line_no', str_pad((string) $run, 3, '0', STR_PAD_LEFT))->exists()) $run++;
        $lineTagNo = (int) EndLineQcLine::where('header_id', $id)->max('tag_no') + 1;
        $lineNoTag = str_pad((string) $run, 3, '0', STR_PAD_LEFT);
        $spaceCode = ($header->edge()->value('code') ?? '').($header->sheetType()->value('code') ?? '').($header->size()->value('code') ?? '').($header->thickness()->value('code') ?? '');
        $statusName = $data['wip_status_name'] ?? ['91' => 'รอเจีย', '98' => 'ชิ้น', '99' => 'รอคัด'][$data['wip_status_code'] ?? ''] ?? null;
        $qty = (int) $data['qty']; $qtyPadded = sprintf('%03d', $qty); $state = $data['product_state'];
        $sku = $state === 'FG' ? 'BX'.($brand?->code ?? '').'-'.$spaceCode : null;
        $wip = $state === 'WIP' ? 'W'.$lineNo.$data['wip_status_code'].'-'.$spaceCode : null;
        $fullTag = $state === 'FG'
            ? 'B'.$lineNo.($brand?->code ?? '').'-'.$spaceCode.$header->lot.$header->shift.$lineNoTag.$qtyPadded
            : $wip.$header->lot.$header->shift.$lineNoTag.$qtyPadded;
        $saved = EndLineQcLine::create([
            'header_id' => $header->id, 'running_no' => (string) $run, 'line_no' => $lineNoTag, 'spec_id' => $data['spec_id'] ?? null,
            'product_type' => $state, 'product_state' => $state, 'brand' => $brand?->name, 'brand_id' => $brand?->id,
            'weight' => $data['weight'] ?? null, 'time_at' => $data['time'] ?? null, 'qty' => $qty, 'unit' => $data['unit'] ?? '2/1',
            'status' => 'draft', 'sku_code' => $sku, 'wip_code' => $wip, 'wip_status_code' => $state === 'WIP' ? $data['wip_status_code'] : null,
            'wip_status_name' => $state === 'WIP' ? $statusName : null, 'tag' => $fullTag, 'full_tag' => $fullTag,
            'tag_no' => $lineTagNo, 'created_by' => auth()->id(), 'updated_by' => auth()->id(),
        ]);
        $header->update(['status' => 'in_process', 'updated_by' => auth()->id()]);
        return back()->with('success', 'บันทึกรายการ '.$state.' สำเร็จ · '.$lineNoTag);
    }

    public function updateMeasure(Request $request, int $id)
    {
        $measure = EndLineQcLine::with('header')->findOrFail($id);
        abort_if(in_array($measure->header?->status, ['success', 'cancel'], true) || $measure->status === 'success', 403, 'Completed records cannot be edited.');
        $data = $request->validate([
            'weight' => ['nullable', 'numeric'], 'qty' => ['sometimes', 'integer', 'min:0'], 'time' => ['nullable', 'string', 'max:16'],
            'unit' => ['nullable', Rule::in(['2/1', '2/2'])], 'remark' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['draft', 'success', 'reject'])], 'product_state' => ['nullable', Rule::in(['WIP', 'FG'])],
            'brand_id' => ['nullable', 'required_if:product_state,FG', 'exists:master_brands,id'], 'wip_status_code' => ['nullable', 'required_if:product_state,WIP', Rule::in(['91', '98', '99'])],
        ]);
        $oldState = strtoupper((string) $measure->product_state);
        $newState = strtoupper((string) ($data['product_state'] ?? $oldState));
        abort_if($oldState === 'WIP' && $newState === 'FG', 403, 'ห้ามเปลี่ยน WIP เป็น FG');
        $measure->fill(['weight' => $data['weight'] ?? $measure->weight, 'qty' => $data['qty'] ?? $measure->qty, 'time_at' => $data['time'] ?? $measure->time_at, 'unit' => $data['unit'] ?? $measure->unit, 'remark' => $data['remark'] ?? $measure->remark, 'status' => $data['status'] ?? $measure->status, 'updated_by' => auth()->id()]);
        if ($newState !== $oldState) {
            $header = $measure->header->load(['edge', 'sheetType', 'size', 'thickness']);
            $space = ($header->edge?->code ?? '').($header->sheetType?->code ?? '').($header->size?->code ?? '').($header->thickness?->code ?? '');
            if ($newState === 'WIP') {
                $run = (int) EndLineQcLine::where('header_id', $header->id)->where('product_state', 'WIP')->max('line_no') + 1;
                $measure->line_no = str_pad((string) $run, 3, '0', STR_PAD_LEFT);
                $measure->running_no = (string) $run;
                $measure->brand = $measure->brand_id = $measure->sku_code = null;
                $measure->wip_status_code = $data['wip_status_code'] ?? null;
                $measure->wip_status_name = ['91' => 'รอเจีย', '98' => 'ชิ้น', '99' => 'รอคัด'][$measure->wip_status_code] ?? null;
                $measure->wip_code = 'W'.$header->line.$measure->wip_status_code.'-'.$space;
                $measure->tag = $measure->full_tag = $measure->wip_code.$header->lot.$header->shift.$measure->line_no.sprintf('%03d', (int) $measure->qty);
            } else {
                $brand = MasterBrand::findOrFail($data['brand_id']);
                $measure->brand = $brand->name; $measure->brand_id = $brand->id;
                $measure->product_state = $measure->product_type = 'FG';
                $measure->sku_code = 'BX'.$brand->code.'-'.$space; $measure->wip_code = null;
                $measure->tag = $measure->sku_code.$header->lot.$header->line.'0010000000';
                $measure->full_tag = 'B'.$header->line.$brand->code.'-'.$space.$header->lot.$header->shift.$measure->line_no.sprintf('%03d', (int) $measure->qty);
                $measure->wip_status_code = $measure->wip_status_name = null;
            }
            $measure->product_state = $measure->product_type = $newState;
        }
        $measure->save();
        return back()->with('success', 'อัปเดตรายการแล้ว');
    }

    public function destroyMeasure(int $id)
    {
        $measure = EndLineQcLine::with('header')->findOrFail($id);
        abort_if(in_array($measure->header?->status, ['success', 'cancel'], true) || $measure->status === 'success', 403, 'Completed records cannot be deleted.');
        $measure->delete();
        return back()->with('success', 'Record deleted.');
    }

    public function finish(string $line, int $id)
    {
        $header = EndLineQcHeader::where('line', $this->lineNumber($line))->findOrFail($id);
        abort_if($header->lines()->count() === 0, 422, 'Add at least one WIP or FG record before finishing.');
        $header->update(['status' => 'success', 'updated_by' => auth()->id()]);
        return back()->with('success', 'QC job completed.');
    }

    public function updateStatus(Request $request, string $line, int $id)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['draft', 'in_process', 'success', 'cancel'])]]);
        $header = EndLineQcHeader::where('line', $this->lineNumber($line))->findOrFail($id);
        $header->update(['status' => $data['status'], 'updated_by' => auth()->id()]);
        return back()->with('success', 'Status updated.');
    }

    public function destroy(string $line, int $id)
    {
        EndLineQcHeader::where('line', $this->lineNumber($line))->findOrFail($id)->delete();
        return back()->with('success', 'QC job deleted.');
    }

    public function specDetails(int $id)
    {
        return MasterProductSpec::query()->findOrFail($id, ['id', 'code', 'name', 'grade', 'brand', 'sheet_type']);
    }

    public function selectSpec(Request $request, string $line, int $id)
    {
        $data = $request->validate(['spec_id' => ['nullable', 'exists:master_product_specs,id'], 'product_state' => ['nullable', Rule::in(['WIP', 'FG'])]]);
        $request->session()->put('end_line_qc.'.$line.'.'.$id, $data);
        return back()->with('success', 'Selection saved.');
    }

    public function export(Request $request, string $line, int $id)
    {
        return $this->csvReport($line, $id, strtoupper((string) $request->query('type', 'WIP')));
    }

    public function wipReport(string $line, int $id) { return $this->csvReport($line, $id, 'WIP'); }
    public function fgReport(string $line, int $id) { return $this->csvReport($line, $id, 'FG'); }
    public function wipExport(int $id)
    {
        $header = EndLineQcHeader::findOrFail($id);
        return $this->csvReport('L'.$header->line, $id, 'WIP');
    }

    private function csvReport(string $line, int $id, string $state)
    {
        $header = EndLineQcHeader::where('line', $this->lineNumber($line))->findOrFail($id);
        abort_unless(in_array($state, ['WIP', 'FG'], true), 404);
        $items = $header->lines()->where('product_state', $state)->orderBy('line_no')->get();
        $headers = $state === 'WIP'
            ? ['วันที่ผลิตตามกะ', 'ชุดที่', 'เวลา', 'จำนวน', '-', 'หมายเหตุ', 'ตรา', 'การจัดการ ส่งคัด/จัดเก็บ', 'สถานะ', 'น้ำหนัก', 'ค่าที่ยกมา', 'วันที่ผลิตตามปฏิทิน', '-', '-', '-', 'YM']
            : ['วันที่คัด', 'ตั้งที่', 'วันที่ผลิต', 'ชุดที่', 'BRAND', 'เวลา', 'ยอดยกมา', 'ชิ้นรอคัด', 'คัดได้', 'เกรด', 'รวมเสีย', 'LOT', 'CODE', 'ยอดรวม', 'ตรวจชุด', 'ตรวจยอด'];
        return Response::streamDownload(function () use ($items, $header, $state, $headers): void {
            $out = fopen('php://output', 'w'); fwrite($out, "\xEF\xBB\xBF"); fputcsv($out, $headers);
            foreach ($items as $item) {
                $date = $header->qc_date?->format('j/n/Y') ?? '';
                $lot = $header->lot ?? '';
                fputcsv($out, $state === 'WIP'
                    ? [$date, 'O'.$header->shift.$item->line_no, $item->time_at, $item->qty, 'ตรวจผ่าน', $item->remark, 1, 'ส่งคัด', 'เสร็จสิ้น', $item->weight, 0, $date, '', 'O'.$header->shift.$item->line_no, '', $lot]
                    : [$date, $item->line_no, $date, 'O'.$header->shift.$item->line_no, $item->brand, $item->time_at, 0, '', $item->qty, 'A', 0, $lot, $lot, $lot, $item->qty, 'NG']);
            }
            fclose($out);
        }, $state.'_Report_'.$header->job_no.'_'.now()->format('Ymd_His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function printTag(string $state, int $id)
    {
        abort_unless(in_array($state, ['wip', 'fg'], true), 404);
        $item = EndLineQcLine::with(['header.sheetType', 'header.edge', 'header.size', 'header.thickness', 'header.face', 'spec'])->findOrFail($id);
        abort_unless(strtolower((string) $item->product_state) === $state, 404);
        return PDF::loadView('end-line-qc.tag', ['item' => $item, 'state' => strtoupper($state)], [], ['format' => 'A4'])->stream('QC-'.$state.'-'.$item->id.'.pdf');
    }

    private function formatTimeForDisplay($time): ?string
    {
        if ($time === null || $time === '') return null;
        $text = trim((string) $time);
        if (preg_match('/^\d+$/', $text)) {
            $length = strlen($text);
            $hours = $length <= 2 ? 0 : (int) substr($text, 0, $length - 2);
            $minutes = $length <= 2 ? (int) $text : (int) substr($text, -2);
            if ($hours < 24 && $minutes < 60) return sprintf('%02d:%02d', $hours, $minutes);
        }
        try { return \Illuminate\Support\Carbon::parse($text)->format('H:i'); }
        catch (\Throwable) { return null; }
    }
}
