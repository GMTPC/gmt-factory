<?php

namespace Modules\Finishing\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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

    public function statusEvents(): HasMany
    {
        return $this->hasMany(FinishingStationStatusEvent::class, 'station_id');
    }

    public function latestStatusEvent(): HasOne
    {
        return $this->hasOne(FinishingStationStatusEvent::class, 'station_id')->latestOfMany();
    }

    /** Machines allowed when opening a Job at this station (falls back to every machine). */
    public function jobMachineOptions(): array
    {
        return $this->jobOptions('machines', 'finishing.job_machines');
    }

    /** PVC types allowed when opening a Job at this station (falls back to every type). */
    public function jobPvcTypes(): array
    {
        return $this->jobOptions('pvc_types', 'finishing.pvc_types');
    }

    private function jobOptions(string $key, string $fallbackConfig): array
    {
        $all = config($fallbackConfig, []);
        $allowed = config("finishing.station_job_options.{$this->code}.{$key}");
        if (! is_array($allowed) || $allowed === []) {
            return $all;
        }

        return array_values(array_intersect($all, $allowed)) ?: $all;
    }
}
