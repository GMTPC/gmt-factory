<?php

namespace App\Http\Controllers\CutBoard;

use App\Http\Controllers\Controller;
use App\Http\Resources\CutBoard\CutJobLineResource;
use App\Models\CutJobLine;
use App\Models\CutJobWmsExport;
use App\Services\CutBoard\WmsExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CutJobWmsExportController extends Controller
{
    public function retry(Request $request, CutJobLine $cutJobLine, WmsExportService $service): JsonResponse
    {
        $export = CutJobWmsExport::query()
            ->where('cut_job_line_id', $cutJobLine->id)
            ->firstOrFail();
        abort_unless($export->sync_status === 'failed', 422, 'รายการนี้ไม่มีข้อผิดพลาดที่ต้องส่งซ้ำ');

        $service->queue($cutJobLine->job, $cutJobLine);
        $cutJobLine->refresh();

        return response()->json([
            'message' => $cutJobLine->wms_status === 'queued'
                ? 'ส่งข้อมูลเข้า WMS เดิมสำเร็จแล้ว'
                : 'ยังส่งข้อมูลเข้า WMS เดิมไม่สำเร็จ ตรวจสอบรายละเอียดใน cut_job_wms_exports',
            'line' => (new CutJobLineResource($cutJobLine))->resolve($request),
        ], $cutJobLine->wms_status === 'queued' ? 200 : 422);
    }
}
