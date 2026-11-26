<?php

namespace Modules\Finishing\App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinishingStationStatusEvent extends Model
{
    protected $table = 'finishing_station_status_events';

    protected $fillable = ['station_id', 'is_running', 'reason', 'user_id'];

    protected function casts(): array
    {
        return ['is_running' => 'boolean'];
    }

    public function station(): BelongsTo
    {
        return $this->belongsTo(FinishingStation::class, 'station_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
