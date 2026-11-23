<?php

namespace App\Http\Controllers\CutBoard;

use App\Http\Controllers\Controller;
use App\Http\Requests\CutBoard\CreateCutJobHeaderRequest;
use App\Models\CutJob;
use App\Models\CutJobHeader;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class CutJobHeaderController extends Controller
{
    public function store(CreateCutJobHeaderRequest $request, CutJob $cutJob): RedirectResponse
    {
        $header = DB::connection(config('cutboard.connection'))
            ->transaction(function () use ($request, $cutJob): CutJobHeader {
                $job = CutJob::query()->lockForUpdate()->findOrFail($cutJob->id);
                abort_unless(in_array($job->status, ['draft', 'scanning'], true), 422, 'ไม่สามารถเพิ่ม Header ใน Job ที่จบการคัดหรือปิดแล้ว');
                $nextNumber = $job->headers()->lockForUpdate()->count() + 1;

                return $job->headers()->create([
                    ...$request->validated(),
                    'header_no' => $request->string('header_no')->trim()->toString() ?: 'H-'.str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT),
                    'status' => 'draft',
                ]);
            });

        return redirect()
            ->route('cut-board.jobs.show', $cutJob)
            ->with('success', 'เพิ่ม Header '.$header->header_no.' เรียบร้อยแล้ว');
    }
}
