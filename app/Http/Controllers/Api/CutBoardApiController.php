<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CutBoard\EditCutJobLineRequest;
use App\Http\Requests\CutBoard\ScanTagRequest;
use App\Http\Resources\CutBoard\CutJobLineResource;
use App\Models\CutJob;
use App\Models\CutJobHeader;
use App\Models\CutJobLine;
use App\Models\Master\BrandList;
use App\Services\CutBoard\LineMasterDataService;
use App\Services\CutBoard\ScanTagService;
use App\Services\CutBoard\TagCode;
use App\Services\CutBoard\WmsExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CutBoardApiController extends Controller
{
    public function scanWip(ScanTagRequest $request, ScanTagService $service, WmsExportService $wmsExport, string $line, CutJob $cutJob): JsonResponse
    {
        abort_unless($cutJob->production_line === $line && (int) $request->integer('job_id') === (int) $cutJob->id, 404);
        $header = CutJobHeader::query()->findOrFail($request->integer('header_id'));
        $lineRecord = $service->scan($cutJob, $header, $request->validated(), $request->user()->getAuthIdentifier());
        $wmsExport->queue($cutJob->refresh(), $lineRecord->refresh());
        $lineRecord->refresh();

        return response()->json([
            'message' => 'บันทึก WIP แล้ว',
            'line' => (new CutJobLineResource($lineRecord))->resolve($request),
        ], 201);
    }

    public function issueFg(Request $request, string $line, CutJob $cutJob): JsonResponse
    {
        abort_unless($cutJob->production_line === $line, 404);
        $data = $request->validate([
            'header_id' => ['required', 'integer'],
            'employee_group_id' => ['required', 'integer', 'min:1'],
            'brand_id' => ['required', 'integer', 'min:1'],
            'qty' => ['required', 'integer', 'min:1'],
            'ng_qty' => ['nullable', 'integer', 'min:0'],
            'ng_reason' => ['nullable', 'string', 'max:100'],
            'source_line_digit' => ['nullable', 'in:3,4'],
            'checker' => ['nullable', 'string', 'max:100'],
            'backboard_no' => ['nullable', 'string', 'max:100'],
            'remark' => ['nullable', 'string', 'max:500'],
        ]);
        $connection = config('cutboard.connection');
        $fgLine = DB::connection($connection)->transaction(function () use ($request, $line, $cutJob, $data, $connection): CutJobLine {
            $job = CutJob::on($connection)->lockForUpdate()->findOrFail($cutJob->id);
            if (! in_array($job->status, ['draft', 'scanning'], true)) {
                throw ValidationException::withMessages(['qty' => 'Job นี้ปิดรับรายการแล้ว']);
            }
            $header = CutJobHeader::on($connection)->where('cut_job_id', $job->id)->lockForUpdate()->findOrFail($data['header_id']);
            $group = app(LineMasterDataService::class)->activeGroup($line, (int) $data['employee_group_id']);
            $brand = BrandList::query()->where('bl_status', 1)->findOrFail($data['brand_id']);
            $jobLines = CutJobLine::on($connection)->where('cut_job_id', $job->id)->get();
            $origin = static function (CutJobLine $record) use ($job): string {
                $snapshot = $record->spec_snapshot ?? [];
                $digit = (string) ($snapshot['source_line_digit'] ?? $snapshot['line'] ?? '');
                if (in_array($digit, ['3', '4'], true)) return $digit;
                if ($record->source_type === 'FG' && $job->production_line === 'L3') return (int) substr((string) $record->lot_no, -3) > 500 ? '4' : '3';
                return (string) (int) preg_replace('/\D+/', '', $job->production_line);
            };
            $wipOrigins = $jobLines->where('source_type', 'WIP')->where('qc_status', 'passed')->map(fn (CutJobLine $record) => $origin($record))->unique()->values();
            $lineDigit = (string) ($data['source_line_digit'] ?? '');
            if ($job->production_line === 'L3' && $wipOrigins->isNotEmpty()) {
                if ($lineDigit === '' && $wipOrigins->count() === 1) $lineDigit = (string) $wipOrigins->first();
                if (! $wipOrigins->contains($lineDigit)) throw ValidationException::withMessages(['source_line_digit' => 'เลือก WIP ต้นทางไลน์ 3 หรือ 4 ก่อนออก FG']);
            } else {
                $lineDigit = (string) (int) preg_replace('/\D+/', '', $job->production_line);
            }
            $sourceLines = $jobLines->filter(fn (CutJobLine $record) => $origin($record) === $lineDigit);
            $wipQty = (float) $sourceLines->where('source_type', 'WIP')->where('qc_status', 'passed')->sum('qty');
            $usedQty = (float) $sourceLines->where('source_type', 'FG')->where('qc_status', 'passed')->sum('qty');
            $ngQty = (float) $sourceLines->where('qc_status', 'rejected')->sum('qty');
            $ngQty += (float) $sourceLines->where('source_type', 'FG')->sum(fn (CutJobLine $record) => (float) data_get($record->spec_snapshot, 'ng_qty', 0));
            $available = $wipQty - $usedQty - $ngQty;
            if (! $group || (int) $header->cut_job_id !== (int) $job->id) {
                throw ValidationException::withMessages(['employee_group_id' => 'เลือกกลุ่มพนักงานที่เปิดใช้งานในไลน์นี้']);
            }
            $issuedNgQty = (int) ($data['ng_qty'] ?? 0);
            if ($issuedNgQty > 0 && empty($data['ng_reason'])) {
                throw ValidationException::withMessages(['ng_reason' => 'กรุณาระบุสาเหตุ NG']);
            }
            if ((float) $data['qty'] + $issuedNgQty > $available) {
                throw ValidationException::withMessages(['qty' => 'จำนวน FG มากกว่ายอดคงเหลือ '.number_format(max(0, $available), 3)]);
            }
            $typeCode = (string) ($header->product_type_code ?: $header->sku_code ?: CutJobLine::on($connection)
                ->where('cut_job_header_id', $header->id)->where('source_type', 'WIP')->value('spec_code'));
            if ($typeCode === '') {
                throw ValidationException::withMessages(['header_id' => 'Header นี้ยังไม่มี Product Type Code']);
            }

            $prefix = $job->work_date->format('ymd').($group->eg_division ?: 'A');
            $crossLineFour = $job->production_line === 'L3' && $lineDigit === '4';
            $sequences = CutJobLine::on($connection)->where('source_type', 'FG')->where('lot_no', 'like', $prefix.'%')->pluck('lot_no')
                ->map(fn ($lot) => (int) substr((string) $lot, -3))
                ->filter(fn (int $value) => $crossLineFour ? $value > 500 : $value <= 500);
            $sequence = max($crossLineFour ? 500 : 0, (int) $sequences->max()) + 1;
            if ($sequence > ($crossLineFour ? 999 : 500)) throw ValidationException::withMessages(['qty' => 'เลข LOT FG ของ WIP ต้นทางไลน์นี้เต็มช่วงแล้ว']);
            $lot = $prefix.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
            $qty = (string) (int) $data['qty'];
            $tagCode = new TagCode('BX'.$brand->bl_code.'-'.$typeCode, $lot, $qty, $lineDigit, '');
            $interfaceQr = $tagCode->interfaceQr((string) $brand->bl_code, $typeCode, $lot, $qty);
            $nextLine = ((int) CutJobLine::on($connection)->where('cut_job_header_id', $header->id)->lockForUpdate()->max('line_no')) + 1;
            $created = CutJobLine::on($connection)->create([
                'cut_job_id' => $job->id,
                'cut_job_header_id' => $header->id,
                'line_no' => $nextLine,
                'source_key' => 'FG:'.$lot,
                'source_type' => 'FG',
                'source_id' => $lot,
                'fg_code' => $interfaceQr,
                'spec_code' => $typeCode,
                'spec_name' => $header->product_type_name,
                'sku_code' => 'BX'.$brand->bl_code.'-'.$typeCode,
                'lot_no' => $lot,
                'qty' => $data['qty'],
                'employee_group_id' => $group->eg_id,
                'employee_group_name' => trim(($group->employeeOne?->ue_name ?? '').' - '.($group->employeeTwo?->ue_name ?? '')),
                'interface_qr' => $interfaceQr,
                'tag' => $tagCode->tag((string) $brand->bl_code, $typeCode),
                'tag_date' => $tagCode->tagDate((string) $brand->bl_code, $typeCode),
                'spec_snapshot' => [
                    'brand_id' => $brand->bl_id,
                    'brand_code' => $brand->bl_code,
                    'brand_name' => $brand->bl_name,
                    'checker' => $data['checker'] ?? ($request->user()->name ?? 'QC'),
                    'backboard_no' => $data['backboard_no'] ?? null,
                    'remark' => $data['remark'] ?? null,
                    'ng_qty' => $issuedNgQty,
                    'ng_reason' => $data['ng_reason'] ?? null,
                    'source_line_digit' => $lineDigit,
                    'cross_line_four' => $crossLineFour,
                ],
                'qc_status' => 'passed',
                'receive_status' => 'pending',
                'print_status' => 'pending',
                'wms_status' => 'pending',
                'scanned_by' => $request->user()->getAuthIdentifier(),
                'scanned_at' => now(),
            ]);
            $job->update(['status' => 'scanning', 'started_at' => $job->started_at ?? now()]);

            return $created->refresh();
        });

        app(WmsExportService::class)->queue($cutJob->refresh(), $fgLine->refresh());
        $fgLine->refresh();

        return response()->json([
            'message' => 'ออก FG เรียบร้อยแล้ว',
            'line' => (new CutJobLineResource($fgLine))->resolve($request),
        ], 201);
    }

    public function updateLine(EditCutJobLineRequest $request, CutJobLine $cutJobLine): JsonResponse
    {
        $data = $request->validated();
        $connection = config('cutboard.connection');
        $updatedLine = DB::connection($connection)->transaction(function () use ($cutJobLine, $data, $connection): CutJobLine {
            $job = CutJob::on($connection)->lockForUpdate()->findOrFail($cutJobLine->cut_job_id);
            $line = CutJobLine::on($connection)->where('cut_job_id', $job->id)->lockForUpdate()->findOrFail($cutJobLine->id);
            if (! in_array($job->status, ['draft', 'scanning'], true)) {
                throw ValidationException::withMessages(['qty' => 'Job นี้ปิดการแก้ไขแล้ว']);
            }
            if ($line->receive_status === 'received' || $line->print_status === 'printed') {
                throw ValidationException::withMessages(['qty' => 'รายการที่รับเข้าแล้วหรือพิมพ์ Tag แล้วแก้ไขไม่ได้']);
            }
            $group = app(LineMasterDataService::class)->activeGroup($job->production_line, (int) $data['employee_group_id']);
            if (! $group) {
                throw ValidationException::withMessages(['employee_group_id' => 'เลือกกลุ่มพนักงานที่ยังเปิดใช้งานของไลน์นี้']);
            }
            $groupName = trim(($group->employeeOne?->ue_name ?? '').' - '.($group->employeeTwo?->ue_name ?? ''));
            $snapshot = $line->spec_snapshot ?? [];
            if ($line->source_type === 'WIP') {
                $line->update([
                    'qty' => $data['qty'],
                    'employee_group_id' => $group->eg_id,
                    'employee_group_name' => $groupName,
                ]);

                return $line->refresh();
            }
            if ($line->source_type !== 'FG') {
                throw ValidationException::withMessages(['qty' => 'ไม่รองรับการแก้ไขรายการชนิดนี้']);
            }
            if (empty($data['brand_id']) || empty($data['checker'])) {
                throw ValidationException::withMessages(['brand_id' => 'กรุณาเลือกแบรนด์และระบุผู้ตรวจ']);
            }
            $lineDigit = (string) ($snapshot['source_line_digit'] ?? ((int) substr((string) $line->lot_no, -3) > 500 && $job->production_line === 'L3' ? '4' : (int) preg_replace('/\D+/', '', $job->production_line)));
            $origin = static function (CutJobLine $record) use ($job): string {
                $stored = (string) data_get($record->spec_snapshot, 'source_line_digit', data_get($record->spec_snapshot, 'line', ''));
                if (in_array($stored, ['3', '4'], true)) return $stored;
                if ($record->source_type === 'FG' && $job->production_line === 'L3') return (int) substr((string) $record->lot_no, -3) > 500 ? '4' : '3';
                return (string) (int) preg_replace('/\D+/', '', $job->production_line);
            };
            $sameOrigin = CutJobLine::on($connection)->where('cut_job_id', $job->id)->get()->filter(fn (CutJobLine $record) => $origin($record) === $lineDigit);
            $inputQty = (float) $sameOrigin->where('source_type', 'WIP')->where('qc_status', 'passed')->sum('qty');
            $otherFgQty = (float) $sameOrigin->where('source_type', 'FG')->where('qc_status', 'passed')->where('id', '!=', $line->id)->sum('qty');
            $ngQty = (float) $sameOrigin->where('qc_status', 'rejected')->sum('qty');
            $ngQty += (float) $sameOrigin->where('source_type', 'FG')->where('id', '!=', $line->id)->sum(fn (CutJobLine $record) => (float) data_get($record->spec_snapshot, 'ng_qty', 0));
            $available = $inputQty - $otherFgQty - $ngQty;
            if ((float) $data['qty'] > $available) {
                throw ValidationException::withMessages(['qty' => 'จำนวน FG มากกว่ายอดคงเหลือ '.number_format(max(0, $available), 3)]);
            }
            $brand = BrandList::query()->where('bl_status', 1)->find($data['brand_id']);
            if (! $brand) {
                throw ValidationException::withMessages(['brand_id' => 'ไม่พบแบรนด์ที่เปิดใช้งาน']);
            }
            $typeCode = (string) ($line->spec_code ?: $line->header?->product_type_code);
            if ($typeCode === '') {
                throw ValidationException::withMessages(['brand_id' => 'ไม่พบ Product Type Code สำหรับสร้าง OUT FG CODE']);
            }
            $qty = (string) (int) $data['qty'];
            $tagCode = new TagCode('BX'.$brand->bl_code.'-'.$typeCode, (string) $line->lot_no, $qty, $lineDigit, '');
            $skuCode = 'BX'.$brand->bl_code.'-'.$typeCode;
            $line->update([
                'qty' => $data['qty'],
                'sku_code' => $skuCode,
                'fg_code' => $skuCode,
                'employee_group_id' => $group->eg_id,
                'employee_group_name' => $groupName,
                'interface_qr' => $tagCode->interfaceQr((string) $brand->bl_code, $typeCode, (string) $line->lot_no, $qty),
                'tag' => $tagCode->tag((string) $brand->bl_code, $typeCode),
                'tag_date' => $tagCode->tagDate((string) $brand->bl_code, $typeCode),
                'spec_snapshot' => array_merge($snapshot, [
                    'brand_id' => $brand->bl_id,
                    'brand_code' => $brand->bl_code,
                    'brand_name' => $brand->bl_name,
                    'checker' => $data['checker'],
                    'backboard_no' => $data['backboard_no'] ?? null,
                    'remark' => $data['remark'] ?? null,
                    'source_line_digit' => $lineDigit,
                ]),
            ]);

            return $line->refresh();
        });

        return response()->json([
            'message' => 'แก้ไขรายการเรียบร้อยแล้ว',
            'line' => (new CutJobLineResource($updatedLine))->resolve($request),
        ]);
    }
}
