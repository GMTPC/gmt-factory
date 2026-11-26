<?php

namespace Modules\Finishing\App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

/** One person's stint on a Finishing Job (started_at → ended_at); used to count processing hours. */
class FinishingJobEmployee extends Model
{
    protected $table = 'finishing_job_employees';

    protected $fillable = ['job_id', 'employee_id', 'employee_name', 'started_at', 'ended_at'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'ended_at' => 'datetime'];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(FinishingJob::class, 'job_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(FinishingEmployee::class, 'employee_id');
    }

    /** Record everyone named on a new Job as starting now. */
    public static function startForJob(FinishingJob $job, array $names): void
    {
        $ids = FinishingEmployee::query()->whereIn('display_name', $names)->pluck('id', 'display_name');
        foreach (array_unique($names) as $name) {
            self::query()->create([
                'job_id' => $job->id,
                'employee_id' => $ids[$name] ?? null,
                'employee_name' => $name,
                'started_at' => $job->created_at ?? now(),
            ]);
        }
    }

    /** Close every open stint of a Job (จบงาน). */
    public static function endForJob(FinishingJob $job, CarbonInterface $at): void
    {
        self::query()->where('job_id', $job->id)->whereNull('ended_at')->update(['ended_at' => $at, 'updated_at' => now()]);
    }

    /**
     * Minutes each employee worked inside [$from, $to) (open stints count up to now).
     *
     * @return Collection<int, float> employee_id => minutes
     */
    public static function minutesByEmployee(CarbonInterface $from, CarbonInterface $to): Collection
    {
        $now = now();

        return self::query()->whereNotNull('employee_id')
            ->where('started_at', '<', $to)
            ->where(fn ($q) => $q->whereNull('ended_at')->orWhere('ended_at', '>', $from))
            ->get(['employee_id', 'started_at', 'ended_at'])
            ->groupBy('employee_id')
            ->map(fn (Collection $stints) => round($stints->sum(function (self $stint) use ($from, $to, $now) {
                $start = $stint->started_at->max($from);
                $end = ($stint->ended_at ?? $now)->min($to);

                return $end->gt($start) ? $start->diffInSeconds($end) / 60 : 0;
            }), 1));
    }
}
