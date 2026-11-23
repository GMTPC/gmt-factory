<?php

namespace Modules\Finishing\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinishingStation extends Model
{
    protected $table = 'finishing_stations';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['is_final' => 'boolean', 'is_machine_on' => 'boolean'];
    }

    public function nextStation(): BelongsTo
    {
        return $this->belongsTo(self::class, 'next_station_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(FinishingMaterial::class, 'station_id');
    }

    public function productionLogs(): HasMany
    {
        return $this->hasMany(ProductionLog::class, 'station_id');
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(FinishingJob::class, 'station_id');
    }
}
