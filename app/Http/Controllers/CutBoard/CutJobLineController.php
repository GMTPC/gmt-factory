<?php

namespace App\Http\Controllers\CutBoard;

use App\Http\Controllers\Controller;
use App\Http\Requests\CutBoard\RejectCutJobLineRequest;
use App\Models\CutJobLine;
use Illuminate\Http\RedirectResponse;

class CutJobLineController extends Controller
{
    public function reject(RejectCutJobLineRequest $request, CutJobLine $cutJobLine): RedirectResponse
    {
        abort_if($cutJobLine->receive_status === 'received', 422, 'รายการนี้รับเข้า WMS แล้ว ไม่สามารถ Reject ได้');

        $cutJobLine->update([
            'qc_status' => 'rejected',
            'receive_status' => 'rejected',
            'print_status' => 'rejected',
            'wms_status' => 'rejected',
            'rejection_reason' => $request->string('reason')->toString(),
        ]);

        return back()->with('success', 'Reject รายการเรียบร้อยแล้ว');
    }
}
