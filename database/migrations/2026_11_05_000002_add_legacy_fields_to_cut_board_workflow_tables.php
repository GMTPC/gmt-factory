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
            $table->unsignedBigInteger('legacy_scan_index')->nullable();
        });

        Schema::connection($this->connection)->table('cut_job_lines', function (Blueprint $table): void {
            $table->unsignedBigInteger('legacy_ccw_id')->nullable();
            $table->unsignedBigInteger('legacy_scan_index')->nullable();
            $table->string('wms_status', 30)->default('pending');
            $table->string('wms_reference', 255)->nullable();
            $table->index(['wms_status', 'receive_status']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('cut_job_lines', function (Blueprint $table): void {
            $table->dropIndex(['wms_status', 'receive_status']);
            $table->dropColumn(['legacy_ccw_id', 'legacy_scan_index', 'wms_status', 'wms_reference']);
        });

        Schema::connection($this->connection)->table('cut_jobs', function (Blueprint $table): void {
            $table->dropColumn('legacy_scan_index');
        });
    }
};
