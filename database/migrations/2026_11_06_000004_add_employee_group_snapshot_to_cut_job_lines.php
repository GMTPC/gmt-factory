<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'bplus';

    public function up(): void
    {
        Schema::connection($this->connection)->table('cut_job_lines', function (Blueprint $table): void {
            $table->unsignedBigInteger('employee_group_id')->nullable();
            $table->string('employee_group_name', 255)->nullable();
            $table->index('employee_group_id');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('cut_job_lines', function (Blueprint $table): void {
            $table->dropIndex(['employee_group_id']);
            $table->dropColumn(['employee_group_id', 'employee_group_name']);
        });
    }
};
