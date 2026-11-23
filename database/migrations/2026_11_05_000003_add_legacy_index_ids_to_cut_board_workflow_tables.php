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
            $table->unsignedBigInteger('legacy_cswi_id')->nullable();
        });

        Schema::connection($this->connection)->table('cut_job_lines', function (Blueprint $table): void {
            $table->unsignedBigInteger('legacy_cswi_id')->nullable();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('cut_job_lines', function (Blueprint $table): void {
            $table->dropColumn('legacy_cswi_id');
        });

        Schema::connection($this->connection)->table('cut_jobs', function (Blueprint $table): void {
            $table->dropColumn('legacy_cswi_id');
        });
    }
};
