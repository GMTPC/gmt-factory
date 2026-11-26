<?php

namespace Modules\Finishing\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionLog extends Model
{
    protected $table = 'finishing_production_logs';

    protected $guarded = ['id'];

    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'shift_date' => 'date',
            'quantity_in' => 'decimal:3',
            'quantity_out' => 'decimal:3',
            'defect_count' => 'decimal:3',
            'ft_count' => 'decimal:3',
            'weight_kg' => 'decimal:3',
            'has_labeling' => 'boolean',
        ];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(FinishingStation::class, 'station_id');
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(FinishingJob::class, 'job_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(FinishingMaterial::class, 'material_id');
    }

    public function outputMaterial(): BelongsTo
    {
        return $this->belongsTo(FinishingMaterial::class, 'output_material_id');
    }
}
