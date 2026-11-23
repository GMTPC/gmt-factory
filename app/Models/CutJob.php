<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CutJob extends Model
{
    use HasFactory;

    protected $connection = 'bplus';

    protected $fillable = [
        'job_no',
        'production_line',
        'worktime',
        'scrap_qty',
        'work_date',
        'status',
        'created_by',
        'started_at',
        'completed_at',
        'legacy_scan_index',
        'legacy_cswi_id',
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'scrap_qty' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function headers(): HasMany
    {
        return $this->hasMany(CutJobHeader::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(CutJobLine::class);
    }

    public function wmsExports(): HasMany
    {
        return $this->hasMany(CutJobWmsExport::class, 'cut_job_id');
    }
}
