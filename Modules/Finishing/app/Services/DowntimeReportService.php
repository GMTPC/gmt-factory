<?php

namespace Modules\Finishing\App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Finishing\App\Models\FinishingStationStatusEvent;

/**
 * Machine downtime built from the Start / Stop events operators record at each station.
 * A stop period runs from a "stop" event to the next "start" event of the same station
 * (or until now when the machine is still stopped). Durations are clipped to the date range.
 */
class DowntimeReportService
{
    public function build(?string $dateFrom, ?string $dateTo, ?int $stationId): array
    {
        $empty = ['periods' => [], 'byStation' => [], 'byReason' => [], 'total_minutes' => 0.0, 'stop_count' => 0, 'ongoing_count' => 0];
        if (! Schema::hasTable('finishing_station_status_events')) {
            return $empty;
        }

        $tz = config('finishing.timezone', 'Asia/Bangkok');
        $from = $dateFrom ? CarbonImmutable::parse($dateFrom, $tz)->startOfDay()->utc() : null;
        $to = $dateTo ? CarbonImmutable::parse($dateTo, $tz)->endOfDay()->utc() : null;
        $now = CarbonImmutable::now('UTC');
        $windowEnd = $to && $to->lt($now) ? $to : $now;

        $events = $this->events($from, $stationId);
        $periods = [];
        foreach ($events->groupBy('station_id') as $stationEvents) {
            $stationEvents = $stationEvents->values();
            foreach ($stationEvents as $index => $event) {
                if ($event->is_running) {
                    continue;
                }
                $resume = $stationEvents->slice($index + 1)->first(fn ($next) => $next->is_running);
                $stoppedAt = CarbonImmutable::parse($event->created_at)->utc();
                $resumedAt = $resume ? CarbonImmutable::parse($resume->created_at)->utc() : null;
                $start = $from && $stoppedAt->lt($from) ? $from : $stoppedAt;
                $end = $resumedAt && $resumedAt->lt($windowEnd) ? $resumedAt : $windowEnd;
                if ($end->lte($start)) {
                    continue;
                }

                $periods[] = [
                    'id' => $event->id,
                    'station_id' => $event->station_id,
                    'station_code' => $event->station?->code,
                    'station_name' => $event->station?->name,
                    'stopped_at' => $stoppedAt->toIso8601String(),
                    'stopped_at_text' => $stoppedAt->setTimezone($tz)->format('d/m/Y H:i'),
                    'resumed_at' => $resumedAt?->toIso8601String(),
                    'resumed_at_text' => $resumedAt?->setTimezone($tz)->format('d/m/Y H:i'),
                    'ongoing' => $resumedAt === null,
                    'minutes' => round($start->diffInSeconds($end) / 60, 1),
                    'reason' => $event->reason,
                    'stopped_by' => $event->user?->name,
                    'resume_reason' => $resume?->reason,
                    'resumed_by' => $resume?->user?->name,
                ];
            }
        }

        $periods = collect($periods)->sortByDesc('stopped_at')->values();

        return [
            'periods' => $periods->all(),
            'byStation' => $periods->groupBy('station_id')->map(fn (Collection $rows) => [
                'station_code' => $rows->first()['station_code'],
                'station_name' => $rows->first()['station_name'],
                'stop_count' => $rows->count(),
                'minutes' => round($rows->sum('minutes'), 1),
                'longest_minutes' => round($rows->max('minutes'), 1),
                'ongoing' => $rows->contains('ongoing', true),
            ])->sortBy('station_code')->values()->all(),
            'byReason' => $periods->groupBy(fn ($row) => trim((string) $row['reason']))->map(fn (Collection $rows, string $reason) => [
                'reason' => $reason,
                'stop_count' => $rows->count(),
                'minutes' => round($rows->sum('minutes'), 1),
            ])->sortByDesc('minutes')->values()->all(),
            'total_minutes' => round($periods->sum('minutes'), 1),
            'stop_count' => $periods->count(),
            'ongoing_count' => $periods->where('ongoing', true)->count(),
        ];
    }

    /** Events from the range start onward, plus each station's last event before it (a stop already in progress). */
    private function events(?CarbonImmutable $from, ?int $stationId): Collection
    {
        $query = fn () => FinishingStationStatusEvent::query()->with(['station:id,code,name', 'user:id,name'])
            ->when($stationId, fn ($q, $id) => $q->where('station_id', $id));

        $events = $query()->when($from, fn ($q) => $q->where('created_at', '>=', $from))->get();
        if ($from) {
            $before = $query()->where('created_at', '<', $from)
                ->whereIn('id', FinishingStationStatusEvent::query()->selectRaw('MAX(id)')
                    ->where('created_at', '<', $from)
                    ->when($stationId, fn ($q, $id) => $q->where('station_id', $id))
                    ->groupBy('station_id'))
                ->get();
            $events = $before->concat($events);
        }

        return $events->sortBy([['station_id', 'asc'], ['created_at', 'asc'], ['id', 'asc']])->values();
    }
}
