<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CutJobLine extends Model
{
    use HasFactory;

    protected $connection = 'bplus';

    protected $fillable = [
        'cut_job_header_id',
        'cut_job_id',
        'line_no',
        'source_key',
        'source_type',
        'source_id',
        'wip_code',
        'fg_code',
        'spec_code',
        'spec_name',
        'spec_snapshot',
        'raw_scan_code',
        'sku_code',
        'lot_no',
        'qty',
        'employee_group_id',
        'employee_group_name',
        'interface_qr',
        'tag',
        'tag_date',
        'qc_status',
        'receive_status',
        'print_status',
        'rejection_reason',
        'scanned_by',
        'scanned_at',
        'received_by',
        'received_at',
        'printed_by',
        'printed_at',
        'legacy_ccw_id',
        'legacy_scan_index',
        'legacy_cswi_id',
        'wms_status',
        'wms_reference',
    ];

    protected function casts(): array
    {
        return [
            'spec_snapshot' => 'array',
            'qty' => 'decimal:3',
            'scanned_at' => 'datetime',
            'received_at' => 'datetime',
            'printed_at' => 'datetime',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(CutJob::class, 'cut_job_id');
    }

    public function header(): BelongsTo
    {
        return $this->belongsTo(CutJobHeader::class, 'cut_job_header_id');
    }

    public function wmsExport(): HasOne
    {
        return $this->hasOne(CutJobWmsExport::class, 'cut_job_line_id');
    }
}
