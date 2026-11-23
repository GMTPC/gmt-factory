<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CutJobWmsExport extends Model
{
    protected $connection = 'bplus';

    protected $fillable = [
        'cut_job_id',
        'cut_job_line_id',
        'batch_code',
        'barcode',
        'lot_no',
        'quantity',
        'source_type',
        'sync_status',
        'sync_error',
        'legacy_csv_index',
        'legacy_csv_index_id',
        'legacy_csv_row_id',
        'synced_at',
    ];

    protected function casts(): array
    {
        return ['quantity' => 'decimal:3', 'synced_at' => 'datetime'];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(CutJob::class, 'cut_job_id');
    }

    public function line(): BelongsTo
    {
        return $this->belongsTo(CutJobLine::class, 'cut_job_line_id');
    }
}
