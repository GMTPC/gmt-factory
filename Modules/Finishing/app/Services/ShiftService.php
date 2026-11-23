<?php

namespace Modules\Finishing\App\Services;

use Carbon\CarbonImmutable;

class ShiftService
{
    /** @return array{shift:string, shift_date:string} */
    public function current(?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now(config('finishing.timezone', config('app.timezone')));
        $time = $now->format('H:i');
        $dayStart = config('finishing.shifts.day.starts_at', '08:00');
        $nightStart = config('finishing.shifts.night.starts_at', '17:00');

        if ($time >= $dayStart && $time < $nightStart) {
            return ['shift' => 'day', 'shift_date' => $now->toDateString()];
        }

        return [
            'shift' => 'night',
            'shift_date' => ($time < $dayStart ? $now->subDay() : $now)->toDateString(),
        ];
    }
}
