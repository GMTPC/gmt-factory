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
        if (! $schema->hasColumn('cut_jobs', 'legacy_cswi_id')) {
            $schema->table('cut_jobs', fn (Blueprint $table) => $table->unsignedBigInteger('legacy_cswi_id')->nullable());
        }
        if (! $schema->hasColumn('cut_job_lines', 'legacy_cswi_id')) {
            $schema->table('cut_job_lines', fn (Blueprint $table) => $table->unsignedBigInteger('legacy_cswi_id')->nullable());
        }
    }

    public function down(): void
    {
        // Imported columns can predate the migration ledger. Preserve them.
    }
};
