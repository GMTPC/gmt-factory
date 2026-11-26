<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'bplus';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        if (! $schema->hasColumn('cut_jobs', 'legacy_scan_index')) {
            $schema->table('cut_jobs', fn (Blueprint $table) => $table->unsignedBigInteger('legacy_scan_index')->nullable());
        }

        if (! $schema->hasColumn('cut_job_lines', 'legacy_ccw_id')) {
            $schema->table('cut_job_lines', fn (Blueprint $table) => $table->unsignedBigInteger('legacy_ccw_id')->nullable());
        }
        if (! $schema->hasColumn('cut_job_lines', 'legacy_scan_index')) {
            $schema->table('cut_job_lines', fn (Blueprint $table) => $table->unsignedBigInteger('legacy_scan_index')->nullable());
        }
        if (! $schema->hasColumn('cut_job_lines', 'wms_status')) {
            $schema->table('cut_job_lines', fn (Blueprint $table) => $table->string('wms_status', 30)->default('pending'));
        }
        if (! $schema->hasColumn('cut_job_lines', 'wms_reference')) {
            $schema->table('cut_job_lines', fn (Blueprint $table) => $table->string('wms_reference', 255)->nullable());
        }
        if (! $schema->hasIndex('cut_job_lines', 'cut_job_lines_wms_status_receive_status_index')) {
            $schema->table('cut_job_lines', fn (Blueprint $table) => $table->index(['wms_status', 'receive_status']));
        }
    }

    public function down(): void
    {
        // Imported columns can predate the migration ledger. Preserve them.
    }
};
