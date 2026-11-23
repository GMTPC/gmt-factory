<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'bplus';

    public function up(): void
    {
        Schema::connection($this->connection)->table('cut_jobs', function (Blueprint $table): void {
            $table->unsignedInteger('worktime')->nullable()->after('production_line');
            $table->index(['production_line', 'worktime'], 'cut_jobs_line_worktime_idx');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('cut_jobs', function (Blueprint $table): void {
            $table->dropIndex('cut_jobs_line_worktime_idx');
            $table->dropColumn('worktime');
        });
    }
};
