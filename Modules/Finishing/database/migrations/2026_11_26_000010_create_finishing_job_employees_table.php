<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('finishing_job_employees')) {
            // Who worked on a Job and from when to when: the basis for counting each employee's processing hours.
            // One row per person per stint, so people joining / leaving mid-Job can be recorded later.
            Schema::create('finishing_job_employees', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('job_id')->constrained('finishing_jobs');
                $table->foreignId('employee_id')->nullable()->constrained('finishing_employees')->nullOnDelete();
                $table->string('employee_name', 210); // snapshot, kept if the master row is renamed / deleted
                $table->timestamp('started_at');
                $table->timestamp('ended_at')->nullable(); // null = still working on the Job
                $table->timestamps();

                $table->index(['employee_id', 'started_at']);
                $table->index(['job_id', 'ended_at']);
            });
        }

        // Existing Jobs: everyone named on the Job worked from Job start to Job end.
        if (DB::table('finishing_job_employees')->doesntExist()) {
            $employeeIds = DB::table('finishing_employees')->pluck('id', 'display_name');
            $now = now();
            DB::table('finishing_jobs')->orderBy('id')->get(['id', 'employee_names', 'status', 'created_at', 'updated_at', 'completed_at'])
                ->each(function ($job) use ($employeeIds, $now): void {
                    $names = collect(json_decode((string) $job->employee_names, true) ?: [])
                        ->map(fn ($item) => is_array($item) ? ($item['name'] ?? '') : (string) $item)->map(fn ($name) => trim($name))->filter()->unique();
                    $ended = $job->completed_at ?? ($job->status === 'completed' ? $job->updated_at : null);
                    $rows = $names->map(fn ($name) => [
                        'job_id' => $job->id,
                        'employee_id' => $employeeIds[$name] ?? null,
                        'employee_name' => $name,
                        'started_at' => $job->created_at,
                        'ended_at' => $ended,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->values()->all();
                    if ($rows) {
                        DB::table('finishing_job_employees')->insert($rows);
                    }
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('finishing_job_employees');
    }
};
