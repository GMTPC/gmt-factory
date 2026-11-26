<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('finishing_jobs', 'raw_material_names')) {
            Schema::table('finishing_jobs', function (Blueprint $table): void {
                // Raw materials used across the whole Job (not per pallet / tag), editable at any time.
                $table->json('raw_material_names')->nullable();
            });
        }

        // Carry over what was recorded before: the Job's single raw material + any per-scan raw materials.
        $perLog = DB::table('finishing_production_logs')->whereNotNull('raw_material_name')->whereNull('deleted_at')
            ->where('status', '!=', 'cancelled')->orderBy('id')->get(['job_id', 'raw_material_name'])->groupBy('job_id');
        DB::table('finishing_jobs')->whereNull('raw_material_names')->orderBy('id')->get(['id', 'raw_material_name'])
            ->each(function ($job) use ($perLog): void {
                $names = collect([$job->raw_material_name])
                    ->concat(($perLog->get($job->id) ?? collect())->pluck('raw_material_name'))
                    ->filter()->map(fn ($name) => trim((string) $name))->unique()->values();
                if ($names->isNotEmpty()) {
                    DB::table('finishing_jobs')->where('id', $job->id)->update(['raw_material_names' => $names->toJson(JSON_UNESCAPED_UNICODE)]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasColumn('finishing_jobs', 'raw_material_names')) {
            Schema::table('finishing_jobs', function (Blueprint $table): void {
                $table->dropColumn('raw_material_names');
            });
        }
    }
};
