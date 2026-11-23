<?php

namespace App\Http\Controllers\CutBoard;

use App\Http\Controllers\Controller;
use App\Models\CutJobLine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ForkliftReceiveController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('CutBoard/ForkliftReceive', [
            'lines' => CutJobLine::query()
                ->where('qc_status', 'passed')
                ->where('receive_status', 'pending')
                ->with(['job:id,job_no,production_line', 'header:id,header_no,group_code'])
                ->latest('id')
                ->limit(100)
                ->get()
                ->map(fn (CutJobLine $line) => [
                    'id' => $line->id,
                    'job_no' => $line->job->job_no,
                    'production_line' => $line->job->production_line,
                    'header_no' => $line->header->header_no,
                    'source_type' => $line->source_type,
                    'source_id' => $line->source_id,
                    'spec_code' => $line->spec_code,
                    'qty' => $line->qty,
                ])->values(),
            'storeUrl' => route('forklift-receive.store'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['line_id' => ['required', 'integer']]);
        $connection = config('cutboard.connection');

        DB::connection($connection)->transaction(function () use ($connection, $data, $request): void {
            $line = CutJobLine::on($connection)->lockForUpdate()->with('job')->findOrFail($data['line_id']);

            if ($line->qc_status !== 'passed') {
                abort(422, 'รายการนี้ยังไม่ผ่าน QC');
            }

            if ($line->receive_status === 'received') {
                abort(422, 'รายการนี้รับเข้า WMS แล้ว');
            }

            $line->update([
                'receive_status' => 'received',
                'wms_status' => 'received',
                'received_by' => $request->user()->getAuthIdentifier(),
                'received_at' => now(),
            ]);

            if (in_array($line->job->status, ['qc_completed', 'receiving', 'received'], true)) {
                $hasPendingReceipts = $line->job->lines()
                    ->where('qc_status', 'passed')
                    ->where('receive_status', 'pending')
                    ->exists();
                $hasReceivedLines = $line->job->lines()
                    ->where('qc_status', 'passed')
                    ->where('receive_status', 'received')
                    ->exists();

                $line->job->update([
                    'status' => $hasPendingReceipts
                        ? ($hasReceivedLines ? 'receiving' : 'qc_completed')
                        : 'received',
                ]);
            }
        });

        return back()->with('success', 'รับเข้า WMS เรียบร้อยแล้ว');
    }
}
