<?php

namespace App\Http\Controllers\CutBoard;

use App\Http\Controllers\Controller;
use App\Models\Master\EmployeeGroup;
use App\Models\Master\WasteDetail;
use App\Models\Production\QcProcessGoods;
use App\Models\Production\QcProcessTran;
use App\Models\User;
use App\Services\CutBoard\GradeCWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

final class GradeCController extends Controller
{
    private array $goodsAliases = [];

    public function index(Request $request, string $line): Response
    {
        $this->assertLine($line);
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'product_type' => ['nullable', 'in:A1,A3,A4'],
        ]);

        $processQuery = QcProcessGoods::query()->where('line', $line);
        $open = (clone $processQuery)->where('status', 'in process')->with('brandList')
            ->orderByDesc('qc_date')->orderByDesc('id')->get()->map(fn (QcProcessGoods $process) => $this->processRow($process));
        $completed = (clone $processQuery)->where('status', 'complete')
            ->when($filters['product_type'] ?? null, fn ($query, $type) => $query->where('product_type', $type))
            ->when(($filters['date_from'] ?? null) && ! ($filters['date_to'] ?? null), fn ($query) => $query->whereDate('qc_date', $filters['date_from']))
            ->when(($filters['date_from'] ?? null) && ($filters['date_to'] ?? null), fn ($query) => $query->whereDate('qc_date', '>=', $filters['date_from'])->whereDate('qc_date', '<=', $filters['date_to']))
            ->when(! ($filters['date_from'] ?? null) && ($filters['date_to'] ?? null), fn ($query) => $query->whereDate('qc_date', $filters['date_to']))
            ->with(['brandList', 'transactions'])
            ->orderByDesc('qc_date')->orderByDesc('id')->paginate(20)->withQueryString()
            ->through(fn (QcProcessGoods $process) => $this->processRow($process));

        return Inertia::render('CutBoard/GradeC/Index', [
            'line' => $line,
            'openProcesses' => $open,
            'completedProcesses' => $completed,
            'productTypes' => app(GradeCWorkflow::class)->productTypes(),
            'filters' => [
                'date_from' => $filters['date_from'] ?? '',
                'date_to' => $filters['date_to'] ?? '',
                'product_type' => $filters['product_type'] ?? '',
            ],
            'canDelete' => $this->isManager($request),
            'urls' => [
                'line' => url('/cut-boards/lines/'.$line),
                'index' => url('/cut-boards/lines/'.$line.'/grade-c'),
                'create' => url('/cut-boards/lines/'.$line.'/grade-c/create'),
            ],
        ]);
    }

    public function create(string $line): Response
    {
        $this->assertLine($line);
        $groups = EmployeeGroup::query()->with(['employeeOne', 'employeeTwo'])
            ->where('eg_line', $line)->where('eg_status', 1)->orderBy('eg_id')->get()
            ->map(fn (EmployeeGroup $group) => [
                'id' => $group->eg_id,
                'employee1' => $group->employeeOne?->ue_name,
                'employee2' => $group->employeeTwo?->ue_name,
            ])->values();

        return Inertia::render('CutBoard/GradeC/Create', [
            'line' => $line,
            'employeeGroups' => $groups,
            'productTypes' => app(GradeCWorkflow::class)->productTypes(),
            'storeUrl' => url('/cut-boards/lines/'.$line.'/grade-c'),
            'backUrl' => url('/cut-boards/lines/'.$line.'/grade-c'),
        ]);
    }

    public function store(Request $request, string $line): RedirectResponse
    {
        $this->assertLine($line);
        $data = $request->validate([
            'shift_code' => ['required', 'in:A,B,T'],
            'employee_group_id' => ['required', 'integer'],
            'product_type' => ['required', 'in:A1,A3,A4'],
            'sheet_size' => ['required', 'in:standard,large'],
        ]);
        $group = EmployeeGroup::query()->with(['employeeOne', 'employeeTwo'])
            ->where('eg_line', $line)->where('eg_status', 1)->findOrFail($data['employee_group_id']);
        $employeeOne = trim((string) ($group->employeeOne?->ue_name ?? ''));
        $employeeTwo = trim((string) ($group->employeeTwo?->ue_name ?? ''));
        $employeeName = implode(' และ ', array_filter([$employeeOne, $employeeTwo], fn (string $name) => $name !== ''));
        abort_if($employeeName === '', 422, 'กลุ่มผู้คัดไม่มีรายชื่อพนักงาน');

        $process = app(GradeCWorkflow::class)->create(
            $line,
            $data,
            (int) $group->eg_id,
            $employeeName,
            $request->user()->getAuthIdentifier(),
        );

        return redirect()->route('cut-board.lines.grade-c.show', [$line, $process->id])
            ->with('success', 'เริ่มรอบคัดแผ่น C แล้ว');
    }

    public function show(string $line, int $processId): Response
    {
        $this->assertLine($line);
        $process = QcProcessGoods::query()->where('line', $line)->with(['brandList', 'transactions'])
            ->findOrFail($processId);
        $wasteQuery = WasteDetail::query()->where('wwd_line', $line);
        if ($process->status === 'complete' && $process->batch) {
            $wasteQuery->where('wwd_batch', $process->batch);
        } else {
            $wasteQuery->whereNull('wwd_qc_date');
        }
        $waste = $wasteQuery
            ->where('wwd_barcode', 'like', '%'.$process->product_type.'%')
            ->orderBy('wwd_date')->get(['wwd_id', 'wwd_barcode', 'wwd_lot', 'wwd_amount', 'wwd_date']);

        return Inertia::render('CutBoard/GradeC/Show', [
            'line' => $line,
            'process' => $this->processRow($process),
            'productTypeName' => app(GradeCWorkflow::class)->productTypes()[$process->product_type]['name'] ?? $process->product_type,
            'wasteRows' => $waste->map(fn (WasteDetail $row) => [
                'id' => $row->wwd_id,
                'barcode' => $row->wwd_barcode,
                'lot' => $row->wwd_lot,
                'amount' => (int) $row->wwd_amount,
                'date' => $row->wwd_date,
            ])->values(),
            'totalWaste' => (int) $waste->sum('wwd_amount'),
            'saveUrl' => url('/cut-boards/lines/'.$line.'/grade-c/'.$processId),
            'finishUrl' => url('/cut-boards/lines/'.$line.'/grade-c/'.$processId.'/finish'),
            'backUrl' => url('/cut-boards/lines/'.$line.'/grade-c'),
            'printAllUrl' => url('/cut-boards/lines/'.$line.'/grade-c/'.$processId.'/print-all'),
            'csvUrl' => url('/cut-boards/lines/'.$line.'/grade-c/'.$processId.'/export-csv'),
        ]);
    }

    public function update(Request $request, string $line, int $processId): RedirectResponse
    {
        $this->assertLine($line);
        $process = QcProcessGoods::query()->where('line', $line)->findOrFail($processId);
        abort_unless($process->status === 'in process', 422, 'รอบนี้จบการคัดแล้ว');
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:1000000'],
            'loss_amount' => ['nullable', 'integer', 'min:0', 'max:1000000'],
        ]);
        abort_if((int) ($data['loss_amount'] ?? 0) > (int) $data['amount'], 422, 'จำนวนเสียต้องไม่มากกว่ายอดคัด');
        $process->update(['amount' => $data['amount'], 'loss_amount' => $data['loss_amount'] ?? 0]);

        return back()->with('success', 'บันทึกยอดคัดแผ่น C แล้ว');
    }

    public function finish(string $line, int $processId): RedirectResponse
    {
        $this->assertLine($line);
        $process = QcProcessGoods::query()->where('line', $line)->findOrFail($processId);
        app(GradeCWorkflow::class)->finish((int) $process->id);

        return back()->with('success', 'จบรอบคัดและสร้างรายการ WIP แล้ว');
    }

    public function destroy(Request $request, string $line, int $processId): RedirectResponse
    {
        $this->assertLine($line);
        abort_unless($this->isManager($request), 403, 'ลบรายการคัดแผ่น C ได้เฉพาะ Manager');
        abort_unless(QcProcessGoods::query()->where('line', $line)->whereKey($processId)->exists(), 404);
        app(GradeCWorkflow::class)->delete($processId);

        return back()->with('success', 'ลบรายการคัดแผ่น C แล้ว');
    }

    public function exportCsv(string $line, int $processId)
    {
        $this->assertLine($line);
        $process = QcProcessGoods::query()->where('line', $line)->findOrFail($processId);
        abort_unless($process->status === 'complete', 404);
        $tran = $process->transactions()->orderBy('running_no')->firstOrFail();

        return response()->streamDownload(function () use ($tran): void {
            $file = fopen('php://output', 'wb');
            $row = [$tran->wip_code, $tran->qc_lot, $tran->total_amount, $tran->status, $tran->wip_state];
            fputcsv($file, array_map(fn ($value) => iconv('UTF-8', 'CP874//TRANSLIT', (string) $value), $row));
            fclose($file);
        }, 'QC_'.now()->format('Ymd_His').'.csv', ['Content-Type' => 'text/csv; charset=cp874']);
    }

    public function printOne(string $line, int $processId, int $tranId): SymfonyResponse
    {
        $this->assertLine($line);
        $process = QcProcessGoods::query()->where('line', $line)->findOrFail($processId);
        $tran = $process->transactions()->findOrFail($tranId);

        return PDF::loadView('cut-board.grade-c-labels', [
            'line' => $line,
            'lineColor' => $this->lineColor($line),
            'transactions' => [$this->printTransactionRow($tran, $process)],
        ], [], $this->pdfConfig())->stream($this->pdfFilename($process, 'tag'));
    }

    public function printAll(string $line, int $processId): SymfonyResponse
    {
        $this->assertLine($line);
        $process = QcProcessGoods::query()->where('line', $line)->with('transactions')->findOrFail($processId);

        return PDF::loadView('cut-board.grade-c-labels', [
            'line' => $line,
            'lineColor' => $this->lineColor($line),
            'transactions' => $process->transactions->sortBy('running_no')
                ->map(fn ($tran) => $this->printTransactionRow($tran, $process))->values(),
        ], [], $this->pdfConfig())->stream($this->pdfFilename($process, 'all-tags'));
    }

    private function printTransactionRow(QcProcessTran $tran, QcProcessGoods $process): array
    {
        $createdAt = $tran->created_at ?? $process->created_at;
        $months = [1 => 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        try {
            $checkerName = $tran->checker ? User::query()->whereKey($tran->checker)->value('name') : null;
        } catch (Throwable) {
            $checkerName = null;
        }
        $largeSizes = ['A10209', 'A30209', 'A40209'];
        $size = in_array((string) $process->product_type_full, $largeSizes, true)
            ? '1.22x2.44m.x9mm.'
            : '1.2x2.4m.x9mm.';
        $skuCode = (string) $tran->sku_code;
        if (! array_key_exists($skuCode, $this->goodsAliases)) {
            try {
                $this->goodsAliases[$skuCode] = DB::connection('bplus')->table('GOODSMASTER')
                    ->where('GOODS_CODE', $skuCode)->value('GOODS_ALIAS');
            } catch (Throwable) {
                // Keep printing available when the optional BPlus product master is offline.
                $this->goodsAliases[$skuCode] = null;
            }
        }

        return [
            'running_no' => (int) $tran->running_no,
            'sku_code' => $skuCode,
            'product_alias' => $this->goodsAliases[$skuCode] ?: 'ยิปซั่ม TOA (GMB) '.$this->productTypeName($process).' '.$size,
            'amount' => (int) $tran->amount,
            'wip_lot' => (string) $tran->wip_lot,
            'wms_lot' => (string) $tran->wms_lot,
            'line_number' => (string) $tran->line,
            'qc_group' => (string) $tran->qc_group,
            'checker_name' => (string) ($checkerName ?? ''),
            'created_date_th' => $createdAt
                ? ltrim($createdAt->format('d'), '0').' '.$months[(int) $createdAt->format('m')].' '.$createdAt->format('Y')
                : '-',
            'production_date' => $createdAt?->format('d/m/Y') ?? '-',
        ];
    }

    private function productTypeName(QcProcessGoods $process): string
    {
        return app(GradeCWorkflow::class)->productTypes()[$process->product_type]['name'] ?? (string) $process->product_type;
    }

    private function lineColor(string $line): string
    {
        return match ($line) {
            'L1' => '#229954',
            'L2' => '#F4D03F',
            'L3' => '#3555F7',
            'L4' => '#7030a0',
        };
    }

    private function pdfConfig(): array
    {
        return [
            'format' => 'A4',
            'orientation' => 'P',
            'default_font' => 'garuda',
            'margin_left' => 8,
            'margin_right' => 8,
            'margin_top' => 8,
            'margin_bottom' => 8,
        ];
    }

    private function pdfFilename(QcProcessGoods $process, string $kind): string
    {
        return sprintf('QC-%s-%s-%s.pdf', $process->line, $process->id, $kind);
    }

    private function processRow(QcProcessGoods $process): array
    {
        $types = app(GradeCWorkflow::class)->productTypes();

        return [
            'id' => $process->id,
            'line' => $process->line,
            'qc_date' => $process->qc_date?->toDateString(),
            'created_at' => $process->created_at?->toIso8601String(),
            'product_type' => $process->product_type,
            'product_type_name' => $types[$process->product_type]['name'] ?? $process->product_type,
            'product_type_full' => $process->product_type_full,
            'brand' => $process->brand,
            'brand_name' => $process->brandList?->bl_name ?? 'เกรด C',
            'shift_desc' => $process->shift_desc,
            'shift_code' => $process->shift_code,
            'employee_name' => $process->employee_name,
            'amount' => (int) ($process->amount ?? 0),
            'loss_amount' => (int) ($process->loss_amount ?? 0),
            'status' => $process->status,
            'running_no' => $process->running_no,
            'transactions' => $process->relationLoaded('transactions')
                ? $process->transactions->sortBy('running_no')->map(fn ($tran) => $this->transactionRow($tran))->values()
                : [],
        ];
    }

    private function transactionRow(QcProcessTran $tran): array
    {
        return [
            'id' => $tran->id,
            'running_no' => (int) $tran->running_no,
            'qc_date' => $tran->qc_date,
            'sku_code' => $tran->sku_code,
            'wip_code' => $tran->wip_code,
            'wip_lot' => $tran->wip_lot,
            'wms_lot' => $tran->wms_lot,
            'amount' => (int) $tran->amount,
            'total_amount' => (int) $tran->total_amount,
            'status' => $tran->status,
            'qc_group' => $tran->qc_group,
        ];
    }

    private function assertLine(string $line): void
    {
        abort_unless(in_array($line, ['L1', 'L2', 'L3', 'L4'], true), 404);
    }

    private function isManager(Request $request): bool
    {
        $username = strtolower((string) $request->user()?->username);

        return $username !== '' && in_array($username, config('cutboard.manager_usernames', ['manager']), true);
    }
}
