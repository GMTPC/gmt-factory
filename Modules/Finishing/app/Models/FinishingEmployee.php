<?php

namespace Modules\Finishing\App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/** Finishing staff master: who can be picked as ผู้คัด / ผู้แปรรูป when opening a Job. */
class FinishingEmployee extends Model
{
    protected $table = 'finishing_employees';

    protected $fillable = ['name', 'nickname', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $employee): void {
            $employee->display_name = self::displayName((string) $employee->name, $employee->nickname);
        });
    }

    public static function displayName(string $name, ?string $nickname): string
    {
        $name = trim(preg_replace('/\s+/u', ' ', $name) ?? '');
        $nickname = trim((string) $nickname);

        return $nickname !== '' ? "{$name} ({$nickname})" : $name;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Names offered in the Job form; falls back to the old config list until the table exists. */
    public static function options(): array
    {
        if (! Schema::hasTable('finishing_employees')) {
            return config('finishing.job_employees', []);
        }

        return self::query()->active()->orderBy('name')->pluck('display_name')->all();
    }
}
