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
        if (! $schema->hasColumn('cut_job_lines', 'employee_group_id')) {
            $schema->table('cut_job_lines', fn (Blueprint $table) => $table->unsignedBigInteger('employee_group_id')->nullable());
        }
        if (! $schema->hasColumn('cut_job_lines', 'employee_group_name')) {
            $schema->table('cut_job_lines', fn (Blueprint $table) => $table->string('employee_group_name', 255)->nullable());
        }
        if (! $schema->hasIndex('cut_job_lines', 'cut_job_lines_employee_group_id_index')) {
            $schema->table('cut_job_lines', fn (Blueprint $table) => $table->index('employee_group_id'));
        }
    }

    public function down(): void
    {
        // Imported columns can predate the migration ledger. Preserve them.
    }
};
