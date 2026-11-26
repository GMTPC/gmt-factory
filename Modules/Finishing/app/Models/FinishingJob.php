<?php

namespace Modules\Finishing\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinishingJob extends Model
{
    protected $table = 'finishing_jobs';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['shift_date' => 'date', 'employee_names' => 'array', 'raw_materials' => 'array', 'completed_at' => 'datetime'];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(FinishingStation::class, 'station_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorder_user_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(FinishingMaterial::class, 'material_id');
    }

    /** Who worked on this Job and when (for employee processing hours). */
    public function workers(): HasMany
    {
        return $this->hasMany(FinishingJobEmployee::class, 'job_id');
    }

    public function productionLogs(): HasMany
    {
        return $this->hasMany(ProductionLog::class, 'job_id');
    }
}
