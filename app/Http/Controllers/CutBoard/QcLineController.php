<?php

namespace App\Http\Controllers\CutBoard;

use App\Http\Controllers\Controller;
use App\Models\CutJob;
use App\Models\Master\LineEmployee;
use App\Services\CutBoard\LineJobWorkflow;
use App\Services\CutBoard\LineMasterDataService;
use App\Services\CutBoard\LineShiftWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class QcLineController extends Controller
{
    /**
     * แยกยอด FG (ผ่าน QC) ตามเกรด: C = แบรนด์เกรด C, B = รหัสชนิดสินค้าตำแหน่งที่กำหนดเป็นเกรด B, ที่เหลือ = A
     *
     * @param  iterable<\App\Models\CutJobLine>  $fgLines
     * @return array{grade_a: float, grade_b: float, grade_c: float}
     */
    private function gradeTotals(iterable $fgLines): array
    {
        $gradeCCode = (string) config('cutboard.grades.c_brand_code', '09');
        $gradeBCode = (string) config('cutboard.grades.b_product_type', '2');
        $typePosition = (int) config('cutboard.grades.product_type_position', 2) - 1;
        $grades = ['grade_a' => 0.0, 'grade_b' => 0.0, 'grade_c' => 0.0];
        foreach ($fgLines as $fgLine) {
            $snapshot = $fgLine->spec_snapshot ?? [];
            if (($snapshot['brand_code'] ?? '') === $gradeCCode) {
                $grades['grade_c'] += (float) $fgLine->qty;
            } elseif (substr((string) ($snapshot['product_type_code'] ?? ''), $typePosition, 1) === $gradeBCode) {
                $grades['grade_b'] += (float) $fgLine->qty;
            } else {
                $grades['grade_a'] += (float) $fgLine->qty;
            }
        }

        return $grades;
    }

    private function assertLine(string $line): void
    {
        abort_unless(in_array($line, ['L1', 'L2', 'L3', 'L4'], true), 404);
    }

    public function index(string $line): Response
    {
        $this->assertLine($line);
        $shiftWorkflow = app(LineShiftWorkflow::class);
        $worktime = $shiftWorkflow->currentWorktime($line);

        $jobs = function () use ($line, $worktime, $shiftWorkflow) {
            // งานของวันนี้ + งานที่ยังไม่จบจากวันก่อน (หน้าจอแสดงการ์ด "งานที่กำลังคัด" จากรายการนี้)
            return $shiftWorkflow->jobsForShift($line, $worktime)
                ->with(['headers.lines'])
                ->latest('id')
                ->get()
                ->map(function (CutJob $job) {
                    $header = $job->headers->first();
                    $lines = $job->headers->flatMap(fn ($jobHeader) => $jobHeader->lines);
                    $fgLines = $lines->where('source_type', 'FG')->where('qc_status', 'passed');
                    $grades = $this->gradeTotals($fgLines);

                    return (object) [
                        'id' => $job->id,
                        'job_no' => $job->job_no,
                        'group_code' => $header?->group_code ?? '-',
                        'status' => in_array($job->status, ['draft', 'scanning'], true) ? 'W' : 'E',
                        'can_delete' => in_array($job->status, ['draft', 'scanning'], true)
                            && $lines->every(fn ($item) => $item->receive_status !== 'received' && $item->print_status !== 'printed' && $item->wms_status !== 'received'),
                        'started_at' => $job->started_at,
                        'work_date' => $job->work_date?->toDateString(),
                        'product_name' => $header?->product_type_name,
                        'wip_count' => $lines->count(),
                        'wip_qty' => (float) $lines->sum('qty'),
                        'fg_qty' => (float) $fgLines->sum('qty'),
                        'ng_qty' => 0,
                        ...$grades,
                    ];
                });
        };

        $masterData = app(LineMasterDataService::class);
        $activeEmployees = fn () => $masterData->employees($line, true);
        $groups = fn () => $masterData->groups($line, true);

        // รายการที่คัดผ่านมา = งานที่คัดจบแล้ว (ทุกวัน รวมวันนี้) — แบ่งหน้าฝั่ง server ทีละ 15, ใช้ ?past_page= แยกจาก pagination อื่น
        // ค้นหา (?past_q=) ด้วย เลข Job / ชนิดสินค้า / SKU / กลุ่ม หรือวันที่ (dd-mm-yyyy หรือ yyyy-mm-dd)
        $pastSearch = trim((string) request()->query('past_q', ''));
        $pastSearchDate = null;
        foreach (['d-m-Y', 'Y-m-d', 'd/m/Y'] as $format) {
            $parsed = \DateTimeImmutable::createFromFormat('!'.$format, $pastSearch);
            if ($parsed && $parsed->format($format) === $pastSearch) {
                $pastSearchDate = $parsed->format('Y-m-d');
                break;
            }
        }
        $pastJobs = fn () => CutJob::query()
            ->where('production_line', $line)
            ->whereIn('status', ['qc_completed', 'receiving', 'received', 'completed'])
            ->when($pastSearch !== '', function ($query) use ($pastSearch, $pastSearchDate) {
                $like = '%'.$pastSearch.'%';
                $query->where(function ($match) use ($like, $pastSearchDate) {
                    $match->where('job_no', 'like', $like)
                        ->orWhereHas('headers', fn ($header) => $header
                            ->where('product_type_name', 'like', $like)
                            ->orWhere('sku_code', 'like', $like)
                            ->orWhere('group_code', 'like', $like));
                    if ($pastSearchDate) {
                        $match->orWhereDate('work_date', $pastSearchDate);
                    }
                });
            })
            ->with([
                'headers:id,cut_job_id,group_code,product_type_name',
                'lines:id,cut_job_id,source_type,qc_status,qty,spec_snapshot',
            ])
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->paginate(15, ['id', 'job_no', 'work_date', 'status'], 'past_page')
            ->through(function (CutJob $job): array {
                $header = $job->headers->first();
                $fgLines = $job->lines->where('source_type', 'FG')->where('qc_status', 'passed');

                return [
                    'id' => $job->id,
                    'job_no' => $job->job_no,
                    'work_date' => $job->work_date?->toDateString(),
                    'status' => $job->status,
                    'group_code' => $header?->group_code ?? '-',
                    'product_name' => $header?->product_type_name,
                    'wip_qty' => (float) $job->lines->where('source_type', 'WIP')->sum('qty'),
                    'fg_qty' => (float) $fgLines->sum('qty'),
                    ...$this->gradeTotals($fgLines),
                ];
            });

        $history = fn () => CutJob::query()->where('production_line', $line)
            ->whereDate('work_date', '<', now()->toDateString())->latest('id')->limit(100)
            ->get(['id', 'job_no', 'work_date', 'status']);

        $totals = function () use ($line, $worktime, $shiftWorkflow): array {
            $lines = \App\Models\CutJobLine::query()
                ->whereIn('cut_job_id', $shiftWorkflow->jobsForShift($line, $worktime)->select('id'))
                ->get();
            $wip = (float) $lines->where('source_type', 'WIP')->sum('qty');
            $fg = (float) $lines->where('source_type', 'FG')->where('qc_status', 'passed')->sum('qty');
            $ng = (float) $lines->where('qc_status', 'rejected')->sum('qty');

            return ['wip' => $wip, 'fg' => $fg, 'ng' => $ng, 'hd' => $wip - $fg - $ng];
        };

        $daily = fn () => $this->dailySummary($line);

        return Inertia::render('CutBoard/LineOverview', [
            'line' => $line,
            'lineLabel' => 'ไลน์ '.substr($line, 1),
            'worktime' => $worktime,
            'workDate' => now()->toDateString(),
            'jobs' => $jobs,
            'employeeCount' => fn () => LineEmployee::query()->where('ue_line', $line)->where('ue_status', 1)->count(),
            'employees' => Inertia::optional($activeEmployees),
            'groups' => Inertia::optional($groups),
            'history' => Inertia::optional($history),
            'pastJobs' => $pastJobs,
            'daily' => Inertia::optional($daily),
            'totals' => $totals,
            'canEndShift' => fn () => ! $shiftWorkflow->jobsForShift($line, $worktime)
                ->whereIn('status', ['draft', 'scanning'])->exists(),
            'canDeleteOpenJobs' => fn () => $this->isCutBoardManager(),
            'urls' => [
                'home' => url('/'),
                'linePrefix' => url('/cut-boards/lines'),
                'qcC' => url('/checker-qc-scan'),
                'gradeC' => url('/cut-boards/lines/'.$line.'/grade-c'),
                'endLineQc' => url('/end-line-qc/'.$line),
                'endLine' => url('/bx-print'),
                'storeJob' => url('/cut-boards/lines/'.$line.'/jobs'),
                'jobPrefix' => url('/cut-boards/lines/'.$line.'/jobs'),
                'storeEmployees' => url('/cut-boards/lines/'.$line.'/employees'),
                'employeePrefix' => url('/cut-boards/lines/'.$line.'/employees'),
                'storeGroups' => url('/cut-boards/lines/'.$line.'/groups'),
                'groupPrefix' => url('/cut-boards/lines/'.$line.'/groups'),
                'endShift' => url('/cut-boards/lines/'.$line.'/end-shift'),
                'workPrefix' => url('/cut-boards/lines/'.$line.'/wip'),
                'historyPrefix' => url('/cut-boards/'.$line),
            ],
        ]);
    }

    private function dailySummary(string $line)
    {
        return \App\Models\CutJobLine::query()
            ->join('cut_jobs as jobs', 'jobs.id', '=', 'cut_job_lines.cut_job_id')
            ->where('jobs.production_line', $line)
            ->whereDate('jobs.work_date', '>=', '2020-01-01')
            ->whereDate('jobs.work_date', '<=', now()->toDateString())
            ->where('cut_job_lines.source_type', 'WIP')
            ->selectRaw('CAST(jobs.work_date AS DATE) AS work_date, SUM(cut_job_lines.qty) AS qty')
            ->groupByRaw('CAST(jobs.work_date AS DATE)')
            ->orderByDesc('work_date')
            ->get();
    }

    public function master(string $line, string $section): Response
    {
        $this->assertLine($line);
        abort_unless(in_array($section, ['history', 'employees', 'groups', 'daily-summary'], true), 404);

        $data = [];

        if ($section === 'history') {
            $data['history'] = CutJob::query()->where('production_line', $line)
                ->whereDate('work_date', '<', now()->toDateString())->latest('id')->limit(100)
                ->get(['id', 'job_no', 'work_date', 'status']);
        }

        if ($section === 'employees') {
            $data['employees'] = app(LineMasterDataService::class)->employees($line);
        }

        if ($section === 'groups') {
            $service = app(LineMasterDataService::class);
            $data['employees'] = $service->employees($line, true);
            $data['groups'] = $service->groups($line);
        }

        if ($section === 'daily-summary') {
            $data['daily'] = $this->dailySummary($line);
        }

        return Inertia::render('CutBoard/LineMaster', [
            'line' => $line,
            'lineLabel' => 'ไลน์ '.substr($line, 1),
            'section' => $section,
            'data' => $data,
            'urls' => [
                'overview' => url('/cut-boards/lines/'.$line),
                'employees' => url('/cut-boards/lines/'.$line.'/employees'),
                'employeeStore' => url('/cut-boards/lines/'.$line.'/employees'),
                'employeePrefix' => url('/cut-boards/lines/'.$line.'/employees'),
                'groups' => url('/cut-boards/lines/'.$line.'/groups'),
                'groupStore' => url('/cut-boards/lines/'.$line.'/groups'),
                'groupPrefix' => url('/cut-boards/lines/'.$line.'/groups'),
                'history' => url('/cut-boards/lines/'.$line.'/history'),
                'jobShowPrefix' => url('/cut-boards/'.$line),
                'workPrefix' => url('/cut-boards/lines/'.$line.'/wip'),
            ],
        ]);
    }

    public function storeJob(Request $request, string $line): RedirectResponse
    {
        $this->assertLine($line);
        $data = $request->validate([
            'group_code' => ['required', Rule::in(['A', 'B', 'T'])],
            'work_date' => ['required', 'date'],
        ]);

        app(LineJobWorkflow::class)->create($line, $data, $request->user()?->getAuthIdentifier());

        return back()->with('success', 'เริ่มงานใหม่เรียบร้อยแล้ว');
    }

    public function deleteJob(string $line, int $jobId): RedirectResponse
    {
        $this->assertLine($line);
        abort_unless($this->isCutBoardManager(), 403, 'ลบงานที่ยังไม่จบได้เฉพาะ Manager');
        app(LineJobWorkflow::class)->deleteOpen($line, $jobId);

        return back()->with('success', 'ลบงานเรียบร้อยแล้ว');
    }

    private function isCutBoardManager(): bool
    {
        $username = strtolower((string) request()->user()?->username);

        return $username !== '' && in_array($username, config('cutboard.manager_usernames', ['manager']), true);
    }

    public function storeEmployees(Request $request, string $line): RedirectResponse
    {
        $this->assertLine($line);
        $data = $request->validate([
            'employees' => ['required', 'array', 'min:1'],
            'employees.*.name' => ['required', 'string', 'max:150'],
            'employees.*.remark' => ['nullable', 'string', 'max:255'],
        ]);
        app(LineMasterDataService::class)->createEmployees($line, $data['employees']);

        return back()->with('success', 'บันทึกรายชื่อพนักงานแล้ว');
    }

    public function deleteEmployee(string $line, int $employeeId): RedirectResponse
    {
        $this->assertLine($line);
        app(LineMasterDataService::class)->deactivateEmployee($line, $employeeId);

        return back()->with('success', 'ปิดการใช้งานพนักงานแล้ว');
    }

    public function storeGroups(Request $request, string $line): RedirectResponse
    {
        $this->assertLine($line);
        $data = $request->validate([
            'groups' => ['required', 'array', 'min:1'],
            'groups.*.employee1' => ['required', 'integer'],
            'groups.*.employee2' => ['required', 'integer'],
            'groups.*.division' => ['required', 'string', 'max:30'],
        ]);
        foreach ($data['groups'] as $group) {
            abort_if((int) $group['employee1'] === (int) $group['employee2'], 422, 'พนักงานในกลุ่มต้องเป็นคนละคน');
        }
        app(LineMasterDataService::class)->createGroups($line, $data['groups']);

        return back()->with('success', 'บันทึกกลุ่มพนักงานแล้ว');
    }

    public function toggleGroup(Request $request, string $line, int $groupId): RedirectResponse
    {
        $this->assertLine($line);
        $data = $request->validate(['status' => ['required', 'boolean']]);
        app(LineMasterDataService::class)->setGroupStatus($line, $groupId, (bool) $data['status']);

        return back()->with('success', 'ปรับสถานะกลุ่มพนักงานแล้ว');
    }

    public function endShift(Request $request, string $line): RedirectResponse
    {
        $this->assertLine($line);
        $data = $request->validate([
            'end_tape' => ['required', 'numeric', 'min:1'],
            'waste_qty' => ['required', 'numeric', 'min:0'],
        ]);
        app(LineShiftWorkflow::class)->close($line, (float) $data['end_tape'], (float) $data['waste_qty']);

        return redirect('/cut-boards/lines/'.$line)->with('success', 'ปิดกะและบันทึก END TAPE แล้ว');
    }

    public function showWork(string $line, int $jobId): RedirectResponse
    {
        $this->assertLine($line);
        $job = CutJob::query()->where('production_line', $line)->findOrFail($jobId);

        return redirect()->route('cut-board.lines.jobs.show', ['line' => $line, 'cutJob' => $job]);
    }

    public function finishQuality(string $line, int $jobId): RedirectResponse
    {
        $this->assertLine($line);
        app(LineJobWorkflow::class)->finishQuality($line, $jobId);

        return redirect()->route('cut-board.lines.jobs.show', ['line' => $line, 'cutJob' => $jobId])
            ->with('success', 'จบการคัดแล้ว งานพร้อมส่งต่อขั้นตอนคลังและพิมพ์ Tag');
    }

}
