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
        return ['shift_date' => 'date'];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(FinishingStation::class, 'station_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(FinishingMaterial::class, 'material_id');
    }

    public function productionLogs(): HasMany
    {
        return $this->hasMany(ProductionLog::class, 'job_id');
    }
}
