<?php

namespace App\Http\Controllers\CutBoard;

use App\Http\Controllers\Controller;
use App\Models\CutJobLine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class BxPrintTagController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('CutBoard/BxPrint/Index', [
            'lines' => CutJobLine::query()
                ->where('receive_status', 'received')
                ->with(['job:id,job_no,production_line', 'header:id,header_no,group_code'])
                ->latest('id')
                ->limit(100)
                ->get()
                ->map(fn (CutJobLine $line) => $this->lineResource($line))
                ->values(),
        ]);
    }

    public function show(int $brd_working_id, string $sku_code): Response
    {
        $lines = CutJobLine::query()
            ->where('sku_code', $sku_code)
            ->where('receive_status', 'received')
            ->latest('id')
            ->get()
            ->filter(fn (CutJobLine $line) => (string) ($line->spec_snapshot['legacy_working_id'] ?? $line->id) === (string) $brd_working_id)
            ->values();

        return Inertia::render('CutBoard/BxPrint/Show', [
            'skuCode' => $sku_code,
            'lines' => $lines
                ->map(fn (CutJobLine $line) => $this->lineResource($line))
                ->values(),
            'exportUrl' => route('wms.bx-print-working-export', ['brd_working_id' => $brd_working_id, 'sku_code' => $sku_code]),
        ]);
    }

    public function export(int $brd_working_id, string $sku_code)
    {
        $lines = CutJobLine::query()
            ->where('sku_code', $sku_code)
            ->where('receive_status', 'received')
            ->get()
            ->filter(fn (CutJobLine $line) => (string) ($line->spec_snapshot['legacy_working_id'] ?? $line->id) === (string) $brd_working_id)
            ->values();

        return response()->streamDownload(function () use ($lines): void {
            $handle = fopen('php://output', 'wb');
            fputcsv($handle, ['Job', 'Source Type', 'Source ID', 'Spec Code', 'SKU', 'Lot', 'Qty', 'Interface QR', 'Tag', 'Tag Date']);
            foreach ($lines as $line) {
                fputcsv($handle, [$line->job?->job_no, $line->source_type, $line->source_id, $line->spec_code, $line->sku_code, $line->lot_no, $line->qty, $line->interface_qr, $line->tag, $line->tag_date]);
            }
            fclose($handle);
        }, 'bx-tags-'.$sku_code.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function markPrinted(Request $request, CutJobLine $cutJobLine): RedirectResponse
    {
        DB::connection(config('cutboard.connection'))->transaction(function () use ($request, $cutJobLine): void {
            $line = CutJobLine::on(config('cutboard.connection'))->lockForUpdate()->findOrFail($cutJobLine->id);
            abort_if($line->receive_status !== 'received', 422, 'ต้องรับเข้า WMS ก่อนพิมพ์ Tag');
            $line->update(['print_status' => 'printed', 'printed_by' => $request->user()->getAuthIdentifier(), 'printed_at' => now()]);
        });

        return back()->with('success', 'บันทึกสถานะพิมพ์ Tag แล้ว');
    }

    private function lineResource(CutJobLine $line): array
    {
        return [
            'id' => $line->id,
            'job_no' => $line->job?->job_no,
            'production_line' => $line->job?->production_line,
            'header_no' => $line->header?->header_no,
            'source_type' => $line->source_type,
            'source_id' => $line->source_id,
            'spec_code' => $line->spec_code,
            'sku_code' => $line->sku_code,
            'lot_no' => $line->lot_no,
            'qty' => $line->qty,
            'interface_qr' => $line->interface_qr,
            'tag' => $line->tag,
            'tag_date' => $line->tag_date,
            'print_status' => $line->print_status,
            'show_url' => route('wms.bx-print-working', ['brd_working_id' => $line->spec_snapshot['legacy_working_id'] ?? $line->id, 'sku_code' => $line->sku_code ?: 'unknown']),
        ];
    }
}
