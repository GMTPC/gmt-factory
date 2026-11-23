<?php

namespace Modules\Finishing\App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Modules\Finishing\App\Models\FinishingJob;
use Modules\Finishing\App\Models\FinishingMaterial;
use Modules\Finishing\App\Models\FinishingStation;
use Modules\Finishing\App\Models\ProductionLog;
use Modules\Finishing\App\Services\ProductionService;
use Modules\Finishing\App\Services\ShiftService;

class FinishingApiController
{
    public function jobs(Request $request): JsonResponse
    {
        $filters = $request->validate(['station_id' => ['required', 'exists:finishing_stations,id'], 'status' => ['nullable', Rule::in(['active', 'completed'])]]);
        $jobs = FinishingJob::query()->with(['user', 'material'])->withCount('productionLogs')
            ->where('station_id', $filters['station_id'])
            ->when($filters['status'] ?? 'active', fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('id')->limit(50)->get();

        return response()->json($jobs);
    }

    public function createJob(Request $request): JsonResponse
    {
        $data = $request->validate([
            'station_id' => ['required', 'exists:finishing_stations,id'],
            'wip_type' => ['required', 'string', 'max:50'],
            'wip_name' => ['required', 'string', 'max:255'], 'wip_code' => ['nullable', 'string', 'max:100'],
            'shift_date' => ['nullable', 'date'], 'shift' => ['nullable', Rule::in(['day', 'night'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $station = FinishingStation::query()->findOrFail($data['station_id']);
        $currentShift = app(ShiftService::class)->current();
        $date = $data['shift_date'] ?? $currentShift['shift_date'];
        $shift = $data['shift'] ?? $currentShift['shift'];

        $job = DB::transaction(function () use ($data, $station, $date, $shift, $request) {
            $material = $this->createJobWip($station, $data);

            return FinishingJob::query()->create([
                'job_no' => sprintf('FN-%s-%s-%s', str_replace('-', '', $date), $station->code, Str::upper(Str::random(4))),
                'station_id' => $station->id,
                'material_id' => $material->id,
                'user_id' => $request->user()->id,
                'shift_date' => $date,
                'shift' => $shift,
                'status' => 'active',
                'notes' => $data['notes'] ?? null,
            ])->load(['user', 'material'])->loadCount('productionLogs');
        });

        return response()->json($job, 201);
    }

    public function assignJobMaterial(Request $request, FinishingJob $job): JsonResponse
    {
        if ($job->status !== 'active' || $job->productionLogs()->exists()) {
            throw ValidationException::withMessages(['material_id' => 'เปลี่ยน WIP ได้เฉพาะ Job ที่ยังไม่มีรายการผลิต']);
        }
        $data = $request->validate([
            'wip_type' => ['required', 'string', 'max:50'],
            'wip_name' => ['required', 'string', 'max:255'], 'wip_code' => ['nullable', 'string', 'max:100'],
        ]);
        $job = DB::transaction(function () use ($job, $data) {
            $station = FinishingStation::query()->findOrFail($job->station_id);
            $material = $this->createJobWip($station, $data);
            $job->update(['material_id' => $material->id]);

            return $job->fresh(['user', 'material'])->loadCount('productionLogs');
        });

        return response()->json($job);
    }

    public function receiveJobWip(Request $request, FinishingJob $job): JsonResponse
    {
        if ($job->status !== 'active' || ! $job->material_id) {
            throw ValidationException::withMessages(['job' => 'Job ต้องเปิดอยู่และระบุประเภท WIP ก่อนรับเข้า']);
        }
        $data = $request->validate(['quantity' => ['required', 'numeric', 'gt:0']]);

        return DB::transaction(function () use ($job, $data) {
            $material = FinishingMaterial::query()->lockForUpdate()->findOrFail($job->material_id);
            $material->increment('quantity', $data['quantity']);
            $material->update(['status' => 'pending']);

            return response()->json($job->fresh(['user', 'material'])->loadCount('productionLogs'));
        });
    }

    public function completeJob(FinishingJob $job): JsonResponse
    {
        if ($job->status !== 'active') {
            throw ValidationException::withMessages(['status' => 'Job นี้ปิดไปแล้ว']);
        }
        if ($job->productionLogs()->where('status', 'draft')->exists()) {
            throw ValidationException::withMessages(['status' => 'ยืนยันหรือยกเลิกรายการ draft ก่อนปิด Job']);
        }
        $job->update(['status' => 'completed']);

        return response()->json($job->fresh()->loadCount('productionLogs'));
    }

    public function stations(): JsonResponse
    {
        return response()->json(FinishingStation::query()->orderBy('id')->get());
    }

    public function station(FinishingStation $station): JsonResponse
    {
        return response()->json($station->load('nextStation'));
    }

    public function materials(Request $request): JsonResponse
    {
        $filters = $request->validate(['station_id' => ['nullable', 'integer'], 'status' => ['nullable', 'string', 'max:30'], 'type' => ['nullable', 'string', 'max:50'], 'q' => ['nullable', 'string', 'max:120']]);
        $materials = FinishingMaterial::query()->with('station')
            ->when($filters['station_id'] ?? null, fn ($q, $value) => $q->where('station_id', $value))
            ->when($filters['status'] ?? null, fn ($q, $value) => $q->where('status', $value))
            ->when($filters['type'] ?? null, fn ($q, $value) => $q->where('type', $value))
            ->when($filters['q'] ?? null, fn ($q, $value) => $q->where(fn ($sq) => $sq->where('code', 'like', "%{$value}%")->orWhere('name', 'like', "%{$value}%")))
            ->orderByDesc('id')->paginate(30)->withQueryString();

        return response()->json($materials);
    }

    public function material(FinishingMaterial $material): JsonResponse
    {
        $material->load(['station', 'parent', 'children', 'productionLogs' => fn ($q) => $q->with('station', 'user')->orderByDesc('id')]);
        return response()->json($material);
    }

    public function createMaterial(Request $request): JsonResponse
    {
        $this->authorizeManager($request);
        $data = $request->validate([
            'station_id' => ['required', 'exists:finishing_stations,id'],
            'code' => ['nullable', 'string', 'max:100'], 'name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'gt:0'], 'status' => ['nullable', Rule::in(['pending', 'in_process'])],
        ]);
        $station = FinishingStation::query()->findOrFail($data['station_id']);
        $material = FinishingMaterial::query()->create([
            'station_id' => $station->id, 'code' => $data['code'] ?: null,
            'name' => $data['name'], 'type' => $station->input_material,
            'quantity' => $data['quantity'], 'status' => $data['status'] ?? 'pending',
        ]);

        return response()->json($material->load('station'), 201);
    }

    public function updateMaterialStatus(Request $request, FinishingMaterial $material): JsonResponse
    {
        $this->authorizeManager($request);
        $data = $request->validate(['status' => ['required', Rule::in(['pending', 'in_process', 'completed'])]]);
        $material->update(['status' => $data['status']]);
        return response()->json($material->fresh('station'));
    }

    public function logs(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'station_id' => ['nullable', 'integer'], 'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'], 'shift' => ['nullable', Rule::in(['day', 'night'])],
            'user_id' => ['nullable', 'integer'], 'status' => ['nullable', 'string', 'max:20'],
        ]);
        $logs = $this->logQuery($filters)->orderByDesc('shift_date')->orderByDesc('id')->paginate(30)->withQueryString();
        return response()->json($logs);
    }

    public function storeLog(Request $request): JsonResponse
    {
        $data = $request->validate([
            'job_id' => ['required', 'exists:finishing_jobs,id'],
            'station_id' => ['required', 'exists:finishing_stations,id'], 'material_id' => ['required', 'exists:finishing_materials,id'],
            'shift_date' => ['nullable', 'date'], 'shift' => ['nullable', Rule::in(['day', 'night'])],
            'quantity_in' => ['required', 'numeric', 'gt:0'], 'quantity_out' => ['required', 'numeric', 'gte:0'],
            'defect_count' => ['required', 'numeric', 'gte:0'], 'ft_count' => ['nullable', 'numeric', 'gte:0'],
            'sub_type' => ['nullable', Rule::in(['single', 'double'])], 'has_labeling' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $station = FinishingStation::query()->findOrFail($data['station_id']);
        $job = FinishingJob::query()->findOrFail($data['job_id']);
        if ((int) $job->station_id !== (int) $station->id || $job->status !== 'active' || ! $job->material_id || (int) $job->material_id !== (int) $data['material_id']) {
            throw ValidationException::withMessages(['job_id' => 'เลือก Job ที่ยังเปิดและใช้ WIP ที่ผูกกับ Job นี้']);
        }
        $material = FinishingMaterial::query()->findOrFail($data['material_id']);
        if ((int) $material->station_id !== (int) $station->id) {
            throw ValidationException::withMessages(['material_id' => 'วัตถุดิบนี้ไม่ได้อยู่ที่สถานีที่เลือก']);
        }
        $this->validateLogRules($station, $data);
        $data['shift_date'] = $job->shift_date;
        $data['shift'] = $job->shift;
        $log = ProductionLog::query()->create($data + [
            'user_id' => $request->user()->id,
            'status' => 'draft',
            'has_labeling' => false,
        ]);
        return response()->json($log->load(['job', 'station', 'material', 'user']), 201);
    }

    public function showLog(ProductionLog $productionLog): JsonResponse
    {
        return response()->json($productionLog->load(['station', 'material', 'outputMaterial', 'user']));
    }

    public function updateLog(Request $request, ProductionLog $productionLog): JsonResponse
    {
        if ($productionLog->status !== 'draft') {
            throw ValidationException::withMessages(['status' => 'แก้ไขได้เฉพาะรายการ draft']);
        }
        if (! $productionLog->job || $productionLog->job->status !== 'active' || (int) $productionLog->job->material_id !== (int) ($data['material_id'] ?? $productionLog->material_id)) {
            throw ValidationException::withMessages(['job_id' => 'ต้องมี Job ที่ยังเปิดอยู่ก่อนแก้ไขรายการ']);
        }
        $data = $request->validate([
            'material_id' => ['sometimes', 'exists:finishing_materials,id'],
            'quantity_in' => ['sometimes', 'numeric', 'gt:0'],
            'quantity_out' => ['sometimes', 'numeric', 'gte:0'], 'defect_count' => ['sometimes', 'numeric', 'gte:0'],
            'ft_count' => ['nullable', 'numeric', 'gte:0'], 'sub_type' => ['nullable', Rule::in(['single', 'double'])],
            'has_labeling' => ['sometimes', 'boolean'], 'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $station = $productionLog->station;
        $material = FinishingMaterial::query()->findOrFail($data['material_id'] ?? $productionLog->material_id);
        if ((int) $material->station_id !== (int) $station->id) {
            throw ValidationException::withMessages(['material_id' => 'วัตถุดิบนี้ไม่ได้อยู่ที่สถานีของรายการ']);
        }
        $this->validateLogRules($station, array_merge($productionLog->getAttributes(), $data));
        $productionLog->update($data);
        return response()->json($productionLog->fresh(['station', 'material', 'user']));
    }

    public function confirmLog(ProductionLog $productionLog, ProductionService $service): JsonResponse
    {
        return response()->json($service->confirm($productionLog));
    }

    public function cancelLog(ProductionLog $productionLog, ProductionService $service): JsonResponse
    {
        return response()->json($service->cancel($productionLog));
    }

    public function saveDefectEntries(Request $request, ProductionLog $productionLog): JsonResponse
    {
        if ($productionLog->status !== 'draft') {
            throw ValidationException::withMessages(['status' => 'เพิ่มรายละเอียดของเสียได้เฉพาะรายการ draft']);
        }
        $data = $request->validate(['defects' => ['present', 'array'], 'defects.*.defect_type_id' => ['required', 'exists:finishing_defect_types,id'], 'defects.*.quantity' => ['required', 'numeric', 'gt:0'], 'defects.*.notes' => ['nullable', 'string', 'max:500']]);
        if (array_sum(array_column($data['defects'], 'quantity')) > (float) $productionLog->defect_count) {
            throw ValidationException::withMessages(['defects' => 'จำนวนแยกประเภทของเสียเกินยอดเสียรวม']);
        }
        $validTypes = DB::table('finishing_defect_types')->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('station_id')->orWhere('station_id', $productionLog->station_id))
            ->pluck('id')->map(fn ($id) => (int) $id)->all();
        foreach ($data['defects'] as $defect) {
            if (! in_array((int) $defect['defect_type_id'], $validTypes, true)) {
                throw ValidationException::withMessages(['defects' => 'ประเภทของเสียนี้ไม่เปิดใช้กับสถานีดังกล่าว']);
            }
        }
        DB::transaction(function () use ($data, $productionLog) {
            DB::table('finishing_defect_entries')->where('production_log_id', $productionLog->id)->delete();
            foreach ($data['defects'] as $defect) {
                DB::table('finishing_defect_entries')->insert($defect + ['production_log_id' => $productionLog->id, 'created_at' => now(), 'updated_at' => now()]);
            }
        });
        return response()->json(DB::table('finishing_defect_entries')->where('production_log_id', $productionLog->id)->get());
    }

    public function createDefectType(Request $request): JsonResponse
    {
        $this->authorizeManager($request);
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'station_id' => ['nullable', 'exists:finishing_stations,id']]);
        $id = DB::table('finishing_defect_types')->insertGetId($data + ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]);
        return response()->json(DB::table('finishing_defect_types')->find($id), 201);
    }

    public function toggleMachine(Request $request, FinishingStation $station): JsonResponse
    {
        $this->authorizeManager($request);
        $data = $request->validate(['is_machine_on' => ['required', 'boolean']]);
        $station->update($data);
        return response()->json($station->fresh());
    }

    public function dashboard(): JsonResponse
    {
        $today = now(config('finishing.timezone', config('app.timezone')))->toDateString();
        $stations = FinishingStation::query()->withCount(['productionLogs as today_logs' => fn ($q) => $q->whereDate('shift_date', $today)->where('status', 'confirmed')])
            ->withSum(['productionLogs as today_quantity_out' => fn ($q) => $q->whereDate('shift_date', $today)->where('status', 'confirmed')], 'quantity_out')
            ->withSum(['productionLogs as today_defects' => fn ($q) => $q->whereDate('shift_date', $today)->where('status', 'confirmed')], 'defect_count')->orderBy('id')->get();
        return response()->json(['date' => $today, 'stations' => $stations]);
    }

    public function dailyReport(Request $request): JsonResponse
    {
        $filters = $request->validate(['date_from' => ['nullable', 'date'], 'date_to' => ['nullable', 'date'], 'station_id' => ['nullable', 'integer']]);
        $logs = $this->logQuery($filters)->where('status', 'confirmed')->orderBy('shift_date')->get();
        return response()->json(['totals' => ['in' => (float) $logs->sum('quantity_in'), 'out' => (float) $logs->sum('quantity_out'), 'defects' => (float) $logs->sum('defect_count'), 'ft' => (float) $logs->sum('ft_count')], 'by_station' => $logs->groupBy('station_id')->map(fn ($rows) => ['station' => $rows->first()->station?->name, 'out' => (float) $rows->sum('quantity_out'), 'defects' => (float) $rows->sum('defect_count'), 'logs' => $rows->count()])->values(), 'by_shift' => $logs->groupBy('shift')->map(fn ($rows, $shift) => ['shift' => $shift, 'out' => (float) $rows->sum('quantity_out'), 'defects' => (float) $rows->sum('defect_count'), 'logs' => $rows->count()])->values(), 'by_day' => $logs->groupBy(fn ($log) => $log->shift_date->format('Y-m-d'))->map(fn ($rows, $date) => ['date' => $date, 'out' => (float) $rows->sum('quantity_out'), 'defects' => (float) $rows->sum('defect_count')])->values()]);
    }

    public function exportReport(Request $request)
    {
        $filters = $request->validate(['date_from' => ['nullable', 'date'], 'date_to' => ['nullable', 'date'], 'station_id' => ['nullable', 'integer']]);
        $logs = $this->logQuery($filters)->where('status', 'confirmed')->orderBy('shift_date')->get();
        $rows = [['วันที่กะ', 'กะ', 'สถานี', 'ผู้บันทึก', 'วัตถุดิบเข้า', 'รับเข้า', 'ผลิตได้', 'ยอดเสีย', 'FT', 'ประเภท', 'สถานะ']];
        foreach ($logs as $log) {
            $rows[] = [$log->shift_date->format('Y-m-d'), $log->shift, $log->station?->name, $log->user?->name, $log->material?->code ?: $log->material?->name, $log->quantity_in, $log->quantity_out, $log->defect_count, $log->ft_count, $log->sub_type, $log->status];
        }

        $lastColumn = $this->excelColumn(count($rows[0]));
        $sheetRows = '';
        foreach ($rows as $rowIndex => $row) {
            $cells = '';
            foreach ($row as $columnIndex => $value) {
                $ref = $this->excelColumn($columnIndex + 1).($rowIndex + 1);
                $value = (string) ($value ?? '');
                if ($rowIndex === 0) {
                    $cells .= '<c r="'.$ref.'" s="1" t="inlineStr"><is><t>'.e($value).'</t></is></c>';
                } elseif ($value !== '' && is_numeric($value)) {
                    $cells .= '<c r="'.$ref.'"><v>'.e($value).'</v></c>';
                } else {
                    $cells .= '<c r="'.$ref.'" t="inlineStr"><is><t>'.e($value).'</t></is></c>';
                }
            }
            $sheetRows .= '<row r="'.($rowIndex + 1).'">'.$cells.'</row>';
        }

        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><dimension ref="A1:'.$lastColumn.count($rows).'"/><sheetViews><sheetView workbookViewId="0"/></sheetViews><sheetFormatPr defaultRowHeight="18"/><cols><col min="1" max="11" width="20" customWidth="1"/></cols><sheetData>'.$sheetRows.'</sheetData></worksheet>';
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Arial"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Arial"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF00855A"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs></styleSheet>';
        $path = tempnam(sys_get_temp_dir(), 'finishing-');
        $zip = new \ZipArchive();
        if ($path === false || $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'ไม่สามารถสร้างไฟล์ Excel ได้');
        }
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Finishing" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
        $zip->addFromString('xl/styles.xml', $styles);
        $zip->close();

        return response()->download($path, 'finishing-production.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])->deleteFileAfterSend(true);
    }

    private function excelColumn(int $number): string
    {
        $column = '';
        while ($number > 0) {
            $number--;
            $column = chr(65 + ($number % 26)).$column;
            $number = intdiv($number, 26);
        }

        return $column;
    }

    public function defectReport(Request $request): JsonResponse
    {
        $filters = $request->validate(['date_from' => ['nullable', 'date'], 'date_to' => ['nullable', 'date'], 'station_id' => ['nullable', 'integer']]);
        $base = $this->logQuery($filters)->where('status', 'confirmed');
        $totals = (clone $base)->selectRaw('COUNT(*) as log_count, SUM(defect_count) as defect_count, SUM(quantity_out) as quantity_out')->first();
        $details = DB::table('finishing_defect_entries as e')->join('finishing_defect_types as t', 't.id', '=', 'e.defect_type_id')->join('finishing_production_logs as l', 'l.id', '=', 'e.production_log_id')->when($filters['station_id'] ?? null, fn ($q, $id) => $q->where('l.station_id', $id))->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('l.shift_date', '>=', $d))->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('l.shift_date', '<=', $d))->select('t.name')->selectRaw('SUM(e.quantity) as quantity')->groupBy('t.name')->orderByDesc('quantity')->get();
        return response()->json(['totals' => $totals, 'by_type' => $details, 'defect_rate' => (float) ($totals->quantity_out ?? 0) > 0 ? round(((float) $totals->defect_count / (float) $totals->quantity_out) * 100, 2) : 0]);
    }

    private function logQuery(array $filters): Builder
    {
        return ProductionLog::query()->with(['station', 'material', 'outputMaterial', 'user'])
            ->when($filters['station_id'] ?? null, fn ($q, $v) => $q->where('station_id', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('shift_date', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('shift_date', '<=', $v))
            ->when($filters['shift'] ?? null, fn ($q, $v) => $q->where('shift', $v))
            ->when($filters['user_id'] ?? null, fn ($q, $v) => $q->where('user_id', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v));
    }

    private function validateLogRules(FinishingStation $station, array $data): void
    {
        if ((float) ($data['quantity_out'] ?? 0) + (float) ($data['defect_count'] ?? 0) > (float) ($data['quantity_in'] ?? 0)) {
            throw ValidationException::withMessages(['quantity_out' => 'ยอดผลิตรวมยอดเสียต้องไม่เกินยอดรับเข้า']);
        }
        if ($station->code === 'ST2' && empty($data['sub_type'])) {
            throw ValidationException::withMessages(['sub_type' => 'สถานี PVC ต้องเลือกประเภทการเคลือบ']);
        }
    }

    private function createJobWip(FinishingStation $station, array $data): FinishingMaterial
    {
        return FinishingMaterial::query()->create([
            'station_id' => $station->id,
            'code' => $data['wip_code'] ?? null,
            'name' => $data['wip_name'],
            'type' => $data['wip_type'],
            'quantity' => 0,
            'status' => 'pending',
        ]);
    }

    private function authorizeManager(Request $request): void
    {
        $username = strtolower((string) ($request->user()->username ?? ''));
        abort_unless(in_array($username, config('finishing.admin_usernames', ['manager']), true), 403, 'เฉพาะผู้ดูแล Finishing เท่านั้น');
    }
}
