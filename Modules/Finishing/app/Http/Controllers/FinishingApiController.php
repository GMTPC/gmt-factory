<?php

namespace Modules\Finishing\App\Http\Controllers;

use App\Services\CutBoard\TagCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;
use App\Models\Master\ProductTypeEmployee;
use App\Models\User;
use Modules\Finishing\App\Models\FinishingEmployee;
use Modules\Finishing\App\Models\FinishingJob;
use Modules\Finishing\App\Models\FinishingJobEmployee;
use Modules\Finishing\App\Models\FinishingMaterial;
use Modules\Finishing\App\Models\FinishingStation;
use Modules\Finishing\App\Models\ProductionLog;
use Modules\Finishing\App\Services\ProductionService;
use Modules\Finishing\App\Services\DowntimeReportService;
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
        $request->validate(['station_id' => ['required', 'integer', 'exists:finishing_stations,id']]);
        $station = FinishingStation::query()->findOrFail($request->integer('station_id'));
        $this->assertStationEnabled($station);
        $data = $request->validate([
            'station_id' => ['required', 'exists:finishing_stations,id'],
            'employee_names' => ['required', 'array', 'min:1'],
            'employee_names.*' => ['required', 'string', 'max:255', 'distinct', Rule::in(FinishingEmployee::options())],
            'machine_name' => ['required', 'string', 'max:120', Rule::in($station->jobMachineOptions())],
            'pvc_type' => ['required', 'string', 'max:100', Rule::in($station->jobPvcTypes())],
            'raw_material_name' => ['nullable', 'string', 'max:255', Rule::in(config('finishing.raw_materials', []))],
            'recorder_user_id' => ['required', 'integer', 'exists:users,id'],
            'inspector_name' => ['required', 'string', 'max:120', Rule::in(config('finishing.job_inspectors', []))],
            'wip_code' => ['nullable', 'string', 'max:100'],
            'shift_date' => ['required', 'date'], 'shift' => ['required', Rule::in(['day', 'night'])],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $employeeNames = array_values($data['employee_names']);
        $recorder = User::query()->findOrFail($data['recorder_user_id']);
        $date = $data['shift_date'];
        $shift = $data['shift'];

        $job = DB::transaction(function () use ($data, $station, $date, $shift, $request, $employeeNames, $recorder) {
            $material = $this->createJobWip($station, [
                'wip_code' => $data['wip_code'] ?? null,
                'wip_name' => $data['pvc_type'],
                'wip_type' => $station->input_material,
            ]);

            $job = FinishingJob::query()->create([
                'job_no' => sprintf('FN-%s-%s-%s', str_replace('-', '', $date), $station->code, Str::upper(Str::random(4))),
                'station_id' => $station->id,
                'material_id' => $material->id,
                'user_id' => $request->user()->id,
                'recorder_user_id' => $recorder->id,
                'employee_names' => $employeeNames,
                'machine_name' => $data['machine_name'],
                'pvc_type' => $data['pvc_type'],
                'raw_material_name' => $data['raw_material_name'] ?? null,
                'inspector_name' => $data['inspector_name'],
                'shift_date' => $date,
                'shift' => $shift,
                'status' => 'active',
                'notes' => $data['notes'] ?? null,
            ]);
            FinishingJobEmployee::startForJob($job, $employeeNames);

            return $job->load(['user', 'recorder', 'material'])->loadCount('productionLogs');
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
        $this->assertStationEnabled(FinishingStation::query()->findOrFail($job->station_id));
        $data = $request->validate([
            'raw_scan_code' => ['required', 'string', 'max:255'],
        ]);
        $scanCode = Str::upper(trim($data['raw_scan_code']));
        if ($scanCode === '') {
            throw ValidationException::withMessages(['raw_scan_code' => 'กรุณาสแกนบาร์โค้ด WIP']);
        }
        $quantity = $this->wipBarcodeQuantity($scanCode);

        return DB::transaction(function () use ($job, $request, $scanCode, $quantity) {
            $job = FinishingJob::query()->lockForUpdate()->findOrFail($job->id);
            if ($job->status !== 'active' || ! $job->material_id) {
                throw ValidationException::withMessages(['job' => 'Job ต้องเปิดอยู่และระบุประเภท WIP ก่อนรับเข้า']);
            }
            if (DB::table('finishing_wip_receipts')->where('scan_code', $scanCode)->exists()) {
                throw ValidationException::withMessages(['raw_scan_code' => 'บาร์โค้ด WIP นี้รับเข้าแล้ว']);
            }
            $material = FinishingMaterial::query()->lockForUpdate()->findOrFail($job->material_id);
            DB::table('finishing_wip_receipts')->insert([
                'job_id' => $job->id,
                'material_id' => $material->id,
                'user_id' => $request->user()->id,
                'scan_code' => $scanCode,
                'quantity' => $quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $material->increment('quantity', $quantity);
            $material->update(['status' => 'pending']);

            return response()->json($job->fresh(['user', 'material'])->loadCount('productionLogs'));
        });
    }

    public function previewJobScan(Request $request, FinishingJob $job): JsonResponse
    {
        $data = $request->validate(['raw_scan_code' => ['required', 'string', 'max:255']]);
        $scanCode = Str::upper(trim($data['raw_scan_code']));
        $this->assertStationEnabled(FinishingStation::query()->findOrFail($job->station_id));
        if ($job->status !== 'active' || ! $job->material_id) {
            throw ValidationException::withMessages(['job' => 'Job ต้องเปิดอยู่และกำหนด WIP ก่อนสแกนแปรรูป']);
        }
        $quantity = $this->wipBarcodeQuantity($scanCode);
        $receipt = DB::table('finishing_wip_receipts')->where('scan_code', $scanCode)->first();
        if ($receipt) {
            $this->assertReceiptUsable($receipt, $job);
        }
        $productType = $this->productType($scanCode);
        $this->assertJobProductType($job, $productType);

        return response()->json([
            'scan_code' => $scanCode,
            'quantity_in' => $quantity,
            'quantity_out' => $quantity,
            ...$productType,
            'sets_job_product_type' => ! $job->product_type_code,
        ]);
    }

    public function scanJobWip(Request $request, FinishingJob $job, ProductionService $service): JsonResponse
    {
        $data = $request->validate([
            'raw_scan_code' => ['required', 'string', 'max:255'],
            'weight_kg' => ['required', 'numeric', 'gt:0', 'max:999999'],
            'ft_count' => ['nullable', 'numeric', 'gte:0'],
            'sub_type' => ['nullable', Rule::in(['single', 'double'])],
            'has_labeling' => ['sometimes', 'boolean'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'weight_kg.required' => 'กรุณากรอกน้ำหนัก WIP ออก (กก.)',
            'weight_kg.numeric' => 'น้ำหนักต้องเป็นตัวเลข',
            'weight_kg.gt' => 'น้ำหนักต้องมากกว่า 0',
            'weight_kg.max' => 'น้ำหนักเกินค่าที่รับได้',
        ]);
        $scanCode = Str::upper(trim($data['raw_scan_code']));
        if ($scanCode === '') {
            throw ValidationException::withMessages(['raw_scan_code' => 'กรุณาสแกนบาร์โค้ด WIP']);
        }
        $quantity = $this->wipBarcodeQuantity($scanCode);
        $productType = $this->productType($scanCode);

        $log = DB::transaction(function () use ($job, $request, $scanCode, $quantity, $productType, $data, $service): ProductionLog {
            $job = FinishingJob::query()->lockForUpdate()->findOrFail($job->id);
            if ($job->status !== 'active' || ! $job->material_id) {
                throw ValidationException::withMessages(['job' => 'Job ต้องเปิดอยู่และกำหนด WIP ก่อนสแกนแปรรูป']);
            }
            $station = FinishingStation::query()->findOrFail($job->station_id);
            $this->assertStationEnabled($station);
            $this->validateLogRules($station, $data + ['quantity_in' => $quantity, 'quantity_out' => $quantity, 'defect_count' => 0]);
            $this->assertJobProductType($job, $productType);
            if (! $job->product_type_code && $productType['product_type_code']) {
                $job->update($productType);
            }

            $material = FinishingMaterial::query()->lockForUpdate()->findOrFail($job->material_id);
            $receipt = DB::table('finishing_wip_receipts')->where('scan_code', $scanCode)->lockForUpdate()->first();
            if ($receipt) {
                $this->assertReceiptUsable($receipt, $job);
                if ((float) $material->quantity < $quantity) {
                    throw ValidationException::withMessages(['raw_scan_code' => 'ยอด WIP ของบาร์โค้ดนี้ไม่พอสำหรับบันทึกผลผลิต']);
                }
            } else {
                DB::table('finishing_wip_receipts')->insert([
                    'job_id' => $job->id,
                    'material_id' => $material->id,
                    'user_id' => $request->user()->id,
                    'scan_code' => $scanCode,
                    'quantity' => $quantity,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $material->increment('quantity', $quantity);
                $material->update(['status' => 'pending']);
            }

            $log = ProductionLog::query()->create([
                'job_id' => $job->id,
                'station_id' => $job->station_id,
                'material_id' => $material->id,
                'user_id' => $request->user()->id,
                'shift_date' => $job->shift_date,
                'shift' => $job->shift,
                'quantity_in' => $quantity,
                'quantity_out' => $quantity,
                'defect_count' => 0,
                'ft_count' => $data['ft_count'] ?? null,
                'sub_type' => $data['sub_type'] ?? null,
                'has_labeling' => $data['has_labeling'] ?? false,
                'notes' => $data['notes'] ?? null,
                'weight_kg' => $data['weight_kg'],
                'product_type_code' => $productType['product_type_code'],
                'product_type_name' => $productType['product_type_name'],
                'status' => 'draft',
            ]);
            DB::table('finishing_wip_receipts')->where('scan_code', $scanCode)
                ->update(['production_log_id' => $log->id, 'updated_at' => now()]);

            return $service->confirm($log);
        });

        $log->setAttribute('scan_code', $scanCode);
        $log->setAttribute('tag_url', route('finishing.production.tag', ['productionLog' => $log->id]));
        return response()->json($log->load(['job', 'station', 'material', 'user']), 201);
    }

    /** Staff master (พนักงาน Finishing): add / rename / activate. Jobs keep the names they were saved with. */
    public function storeEmployee(Request $request): JsonResponse
    {
        $data = $this->validateEmployee($request);
        $employee = FinishingEmployee::query()->create($data + ['is_active' => true]);

        return response()->json($employee, 201);
    }

    public function updateEmployee(Request $request, FinishingEmployee $employee): JsonResponse
    {
        $data = $request->has('name') ? $this->validateEmployee($request, $employee) : [];
        $data += $request->validate(['is_active' => ['sometimes', 'boolean']]);
        $employee->update($data);

        return response()->json($employee->fresh());
    }

    public function destroyEmployee(FinishingEmployee $employee): JsonResponse
    {
        $employee->delete();

        return response()->json(['deleted' => true]);
    }

    private function validateEmployee(Request $request, ?FinishingEmployee $current = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'nickname' => ['nullable', 'string', 'max:50'],
        ], [
            'name.required' => 'กรุณากรอกชื่อ-นามสกุล',
            'name.max' => 'ชื่อยาวเกิน 150 ตัวอักษร',
            'nickname.max' => 'ชื่อเล่นยาวเกิน 50 ตัวอักษร',
        ]);
        $data['name'] = trim(preg_replace('/\s+/u', ' ', $data['name']) ?? '');
        $data['nickname'] = isset($data['nickname']) && trim($data['nickname']) !== '' ? trim($data['nickname']) : null;
        $display = FinishingEmployee::displayName($data['name'], $data['nickname']);
        $taken = FinishingEmployee::query()->where('display_name', $display)
            ->when($current, fn ($q) => $q->whereKeyNot($current->id))->exists();
        if ($taken) {
            throw ValidationException::withMessages(['name' => "มีพนักงาน \"{$display}\" อยู่แล้ว"]);
        }

        return $data;
    }

    /** After a tag is issued only its weight may be corrected; reprinting the tag shows the new weight. */
    public function updateLogWeight(Request $request, ProductionLog $productionLog): JsonResponse
    {
        $data = $request->validate(['weight_kg' => ['required', 'numeric', 'gt:0', 'max:999999']], [
            'weight_kg.required' => 'กรุณากรอกน้ำหนัก (กก.)',
            'weight_kg.numeric' => 'น้ำหนักต้องเป็นตัวเลข',
            'weight_kg.gt' => 'น้ำหนักต้องมากกว่า 0',
        ]);
        if ($productionLog->status === 'cancelled') {
            throw ValidationException::withMessages(['weight_kg' => 'รายการนี้ถูกยกเลิกแล้ว แก้ไขไม่ได้']);
        }
        $this->assertJobOpen($productionLog->job_id, 'weight_kg');
        $this->assertStationEnabled(FinishingStation::query()->findOrFail($productionLog->station_id));
        $productionLog->update(['weight_kg' => $data['weight_kg']]);

        return response()->json($productionLog->fresh());
    }

    /** Raw materials are recorded for the whole Job (not per pallet) and can be changed at any time. */
    public function updateJobRawMaterials(Request $request, FinishingJob $job): JsonResponse
    {
        $this->assertStationEnabled(FinishingStation::query()->findOrFail($job->station_id));
        $this->assertJobOpen($job->id, 'raw_materials');
        $data = $request->validate([
            'raw_materials' => ['present', 'array'],
            'raw_materials.*.name' => ['required', 'string', 'distinct', Rule::in(config('finishing.raw_materials', []))],
            'raw_materials.*.quantity' => ['nullable', 'numeric', 'gte:0', 'max:99999999'],
            'raw_materials.*.unit' => ['nullable', 'string', Rule::in(config('finishing.raw_material_units', []))],
        ], [
            'raw_materials.*.name.in' => 'วัตถุดิบไม่อยู่ในรายการ',
            'raw_materials.*.name.distinct' => 'เลือกวัตถุดิบซ้ำ',
            'raw_materials.*.quantity.numeric' => 'จำนวนวัตถุดิบต้องเป็นตัวเลข',
            'raw_materials.*.quantity.gte' => 'จำนวนวัตถุดิบต้องไม่ติดลบ',
            'raw_materials.*.unit.in' => 'หน่วยไม่อยู่ในรายการ',
        ]);
        $items = collect($data['raw_materials'])->map(fn (array $item) => [
            'name' => $item['name'],
            'quantity' => isset($item['quantity']) && $item['quantity'] !== '' ? (float) $item['quantity'] : null,
            'unit' => $item['unit'] ?? null,
        ])->values()->all();
        $job->update(['raw_materials' => $items]);

        return response()->json($job->fresh());
    }

    public function completeJob(FinishingJob $job): JsonResponse
    {
        if ($job->status !== 'active') {
            throw ValidationException::withMessages(['status' => 'Job นี้ปิดไปแล้ว']);
        }
        if ($job->productionLogs()->where('status', 'draft')->exists()) {
            throw ValidationException::withMessages(['status' => 'ยืนยันหรือยกเลิกรายการ draft ก่อนปิด Job']);
        }
        $completedAt = now();
        DB::transaction(function () use ($job, $completedAt): void {
            $job->update(['status' => 'completed', 'completed_at' => $completedAt]);
            FinishingJobEmployee::endForJob($job, $completedAt);
        });

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
        $this->assertJobOpen($productionLog->job_id, 'status');
        $data = $request->validate([
            'material_id' => ['sometimes', 'exists:finishing_materials,id'],
            'quantity_in' => ['sometimes', 'numeric', 'gt:0'],
            'quantity_out' => ['sometimes', 'numeric', 'gte:0'], 'defect_count' => ['sometimes', 'numeric', 'gte:0'],
            'ft_count' => ['nullable', 'numeric', 'gte:0'], 'sub_type' => ['nullable', Rule::in(['single', 'double'])],
            'has_labeling' => ['sometimes', 'boolean'], 'notes' => ['nullable', 'string', 'max:2000'],
        ]);
        if (! $productionLog->job || $productionLog->job->status !== 'active' || (int) $productionLog->job->material_id !== (int) ($data['material_id'] ?? $productionLog->material_id)) {
            throw ValidationException::withMessages(['job_id' => 'ต้องมี Job ที่ยังเปิดอยู่ก่อนแก้ไขรายการ']);
        }
        $receipt = Schema::hasColumn('finishing_wip_receipts', 'production_log_id')
            ? DB::table('finishing_wip_receipts')->where('production_log_id', $productionLog->id)->first()
            : null;
        if ($receipt && abs((float) ($data['quantity_in'] ?? $productionLog->quantity_in) - (float) $receipt->quantity) > 0.0001) {
            throw ValidationException::withMessages(['quantity_in' => 'จำนวนรับเข้าของรายการสแกนต้องตรงกับบาร์โค้ด']);
        }
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
        $this->assertJobOpen($productionLog->job_id, 'status');

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
        abort_unless(Schema::hasTable('finishing_station_status_events'), 503, 'ระบบสถานะสถานียังไม่พร้อมใช้งาน');
        $this->assertStationEnabled($station);
        $data = $request->validate([
            'is_machine_on' => ['required', 'boolean'],
            'reason' => ['required', 'string', 'min:2', 'max:500'],
        ]);
        $reason = trim($data['reason']);
        if ($reason === '') {
            throw ValidationException::withMessages(['reason' => 'กรุณาระบุเหตุผล']);
        }

        $updated = DB::transaction(function () use ($station, $data, $reason, $request): FinishingStation {
            $lockedStation = FinishingStation::query()->lockForUpdate()->findOrFail($station->id);
            $isRunning = (bool) $data['is_machine_on'];
            if ($lockedStation->is_machine_on === $isRunning) {
                throw ValidationException::withMessages(['is_machine_on' => 'สถานีอยู่ในสถานะนี้แล้ว']);
            }

            $lockedStation->update(['is_machine_on' => $isRunning]);
            $lockedStation->statusEvents()->create([
                'is_running' => $isRunning,
                'reason' => $reason,
                'user_id' => $request->user()->id,
            ]);

            return $lockedStation;
        });

        return response()->json($updated->fresh()->load('latestStatusEvent.user'));
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

        return $this->xlsxDownload($rows, 'Finishing', 'finishing-production.xlsx');
    }

    public function exportDowntimeReport(Request $request, DowntimeReportService $downtime)
    {
        $filters = $request->validate(['date_from' => ['nullable', 'date'], 'date_to' => ['nullable', 'date'], 'station_id' => ['nullable', 'integer']]);
        $report = $downtime->build($filters['date_from'] ?? null, $filters['date_to'] ?? null, isset($filters['station_id']) ? (int) $filters['station_id'] : null);
        $rows = [['สถานี', 'ชื่อสถานี', 'หยุดเมื่อ', 'เดินต่อเมื่อ', 'เวลาหยุด (นาที)', 'เหตุผลหยุด', 'ผู้กดหยุด', 'เหตุผลเริ่ม', 'ผู้กดเริ่ม']];
        foreach ($report['periods'] as $period) {
            $rows[] = [$period['station_code'], $period['station_name'], $period['stopped_at_text'], $period['ongoing'] ? 'ยังหยุดอยู่' : $period['resumed_at_text'], $period['minutes'], $period['reason'], $period['stopped_by'], $period['resume_reason'], $period['resumed_by']];
        }

        return $this->xlsxDownload($rows, 'Downtime', 'finishing-downtime.xlsx');
    }

    /** @param list<list<scalar|null>> $rows first row is the header */
    private function xlsxDownload(array $rows, string $sheetName, string $fileName)
    {
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
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><dimension ref="A1:'.$lastColumn.count($rows).'"/><sheetViews><sheetView workbookViewId="0"/></sheetViews><sheetFormatPr defaultRowHeight="18"/><cols><col min="1" max="'.count($rows[0]).'" width="20" customWidth="1"/></cols><sheetData>'.$sheetRows.'</sheetData></worksheet>';
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="2"><font><sz val="11"/><name val="Arial"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Arial"/></font></fonts><fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF00855A"/><bgColor indexed="64"/></patternFill></fill></fills><borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders><cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/></cellXfs></styleSheet>';
        $path = tempnam(sys_get_temp_dir(), 'finishing-');
        $zip = new \ZipArchive();
        if ($path === false || $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'ไม่สามารถสร้างไฟล์ Excel ได้');
        }
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="'.e($sheetName).'" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>');
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
        $zip->addFromString('xl/styles.xml', $styles);
        $zip->close();

        return response()->download($path, $fileName, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])->deleteFileAfterSend(true);
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
    }

    private function wipBarcodeQuantity(string $scanCode): float
    {
        try {
            $tagCode = TagCode::parse($scanCode);
        } catch (InvalidArgumentException $exception) {
            throw ValidationException::withMessages(['raw_scan_code' => $exception->getMessage()]);
        }
        if (preg_match('/^\d{1,15}(?:\.\d{1,3})?$/', $tagCode->qty) !== 1 || (float) $tagCode->qty <= 0) {
            throw ValidationException::withMessages(['raw_scan_code' => 'บาร์โค้ด WIP นี้ไม่มีจำนวนที่ถูกต้อง']);
        }

        return (float) $tagCode->qty;
    }

    /** A finished Job (จบงาน) is locked: its tags, weights and raw materials can no longer change. */
    private function assertJobOpen(?int $jobId, string $field): void
    {
        if ($jobId && FinishingJob::query()->whereKey($jobId)->where('status', 'completed')->exists()) {
            throw ValidationException::withMessages([$field => 'Job นี้จบงานแล้ว แก้ไขไม่ได้']);
        }
    }

    private function assertStationEnabled(FinishingStation $station): void
    {
        if (! $station->is_enabled) {
            throw ValidationException::withMessages(['station_id' => "สถานี {$station->code} ยังไม่เปิดใช้งาน"]);
        }
    }

    /** One Job = one product type: every tag must match the type set by the Job's first tag. */
    private function assertJobProductType(FinishingJob $job, array $productType): void
    {
        $jobType = $job->product_type_code;
        $tagType = $productType['product_type_code'] ?? null;
        if (! $jobType || ! $tagType || strtoupper($jobType) === strtoupper($tagType)) {
            return;
        }
        $label = fn (string $code, ?string $name) => $name ? "{$code} ({$name})" : $code;
        throw ValidationException::withMessages(['raw_scan_code' => 'Tag นี้เป็นสินค้า '.$label($tagType, $productType['product_type_name'] ?? null)
            .' แต่ Job นี้เป็นสินค้า '.$label($jobType, $job->product_type_name).' · 1 Job ได้ 1 ประเภทสินค้า กรุณาเปิด Job ใหม่']);
    }

    private function assertReceiptUsable(object $receipt, FinishingJob $job): void
    {
        if ((int) $receipt->job_id !== (int) $job->id) {
            throw ValidationException::withMessages(['raw_scan_code' => 'บาร์โค้ด WIP นี้ถูกใช้กับ Job อื่นแล้ว']);
        }
        if ($receipt->production_log_id) {
            $previousLog = ProductionLog::withTrashed()->find($receipt->production_log_id);
            if ($previousLog && $previousLog->status !== 'cancelled') {
                throw ValidationException::withMessages(['raw_scan_code' => 'บาร์โค้ด WIP นี้บันทึกผลการผลิตแล้ว']);
            }
        }
    }

    /** @return array{product_type_code: ?string, product_type_name: ?string} */
    private function productType(string $scanCode): array
    {
        $code = TagCode::parse($scanCode)->productTypeCode();
        if ($code === '') {
            return ['product_type_code' => null, 'product_type_name' => null];
        }
        try {
            // Product type master lives in the legacy database; Finishing still works without it.
            $name = ProductTypeEmployee::query()->where('pe_type_code', $code)->orderByDesc('pe_id')->value('pe_type_name');
        } catch (Throwable $exception) {
            report($exception);
            $name = null;
        }

        return ['product_type_code' => $code, 'product_type_name' => $name ? trim((string) $name) : null];
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
