<?php

namespace App\Http\Resources\CutBoard;

use App\Models\CutJobLine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CutJobLine */
class CutJobLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $snapshot = $this->spec_snapshot ?? [];

        return [
            'id' => $this->id,
            'header_id' => $this->cut_job_header_id,
            'source_type' => $this->source_type,
            'source_id' => $this->source_id,
            'source_line_digit' => (string) ($snapshot['source_line_digit'] ?? $snapshot['line'] ?? ''),
            'barcode' => $this->raw_scan_code ?: ($this->wip_code ?: ($this->fg_code ?: $this->source_id)),
            'employee_group_name' => $this->employee_group_name,
            'employee_group_id' => $this->employee_group_id,
            'spec_code' => $this->spec_code,
            'spec_name' => $this->spec_name,
            'sku_code' => $this->sku_code,
            'lot_no' => $this->lot_no,
            'brand_name' => $snapshot['brand_name'] ?? null,
            'brand_id' => $snapshot['brand_id'] ?? null,
            'brand_code' => $snapshot['brand_code'] ?? null,
            'checker' => $snapshot['checker'] ?? null,
            'backboard_no' => $snapshot['backboard_no'] ?? null,
            'remark' => $snapshot['remark'] ?? null,
            'out_code' => $this->interface_qr ?: ($this->tag ?: $this->raw_scan_code),
            'qty' => $this->qty,
            'qc_status' => $this->qc_status,
            'receive_status' => $this->receive_status,
            'print_status' => $this->print_status,
            'wms_status' => $this->wms_status,
            'rejection_reason' => $this->rejection_reason,
            'scanned_at' => $this->scanned_at?->toIso8601String(),
            'ng_qty' => $snapshot['ng_qty'] ?? 0,
            'ng_reason' => $snapshot['ng_reason'] ?? null,
            'print_url' => route('wms.bx-print-working', [
                'brd_working_id' => $snapshot['legacy_working_id'] ?? $this->id,
                'sku_code' => $this->sku_code ?: 'unknown',
            ]),
        ];
    }
}
