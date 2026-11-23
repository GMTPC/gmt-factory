<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'bplus';

    public function up(): void
    {
        Schema::connection($this->connection)->create('cut_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_no', 40)->unique();
            $table->string('production_line', 10);
            $table->date('work_date');
            $table->string('status', 30)->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['production_line', 'work_date']);
            $table->index('status');
        });

        Schema::connection($this->connection)->create('cut_job_headers', function (Blueprint $table) {
            $table->id();
            // Kept as a denormalized lookup key for the job-level unique index.
            // The owning FK is cut_job_header_id -> cut_job_headers -> cut_jobs;
            // adding a second cascading FK creates a SQL Server cascade path.
            $table->unsignedBigInteger('cut_job_id');
            $table->string('header_no', 40);
            $table->string('group_code', 100)->nullable();
            $table->string('sku_code', 100)->nullable();
            $table->string('product_type_code', 100)->nullable();
            $table->string('product_type_name', 255)->nullable();
            $table->string('lot_no', 100)->nullable();
            $table->date('lot_date')->nullable();
            $table->decimal('target_qty', 18, 3)->default(0);
            $table->string('status', 30)->default('draft');
            $table->timestamps();

            $table->unique(['cut_job_id', 'header_no']);
            $table->index(['cut_job_id', 'status']);
            $table->index(['sku_code', 'lot_no']);
        });

        Schema::connection($this->connection)->create('cut_job_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cut_job_header_id')->constrained('cut_job_headers')->cascadeOnDelete();
            $table->foreignId('cut_job_id')->constrained('cut_jobs')->cascadeOnDelete();
            $table->unsignedInteger('line_no');

            // source_key is normalized by the scan service and prevents a duplicate
            // WIP/FG source from being added to the same job.
            $table->string('source_key', 255);
            $table->string('source_type', 10);
            $table->string('source_id', 100)->nullable();
            $table->string('wip_code', 150)->nullable();
            $table->string('fg_code', 150)->nullable();
            $table->string('spec_code', 150)->nullable();
            $table->string('spec_name', 255)->nullable();
            $table->json('spec_snapshot')->nullable();

            $table->string('raw_scan_code', 500)->nullable();
            $table->string('sku_code', 100)->nullable();
            $table->string('lot_no', 100)->nullable();
            $table->decimal('qty', 18, 3)->default(0);

            $table->string('interface_qr', 255)->nullable();
            $table->string('tag', 255)->nullable();
            $table->string('tag_date', 255)->nullable();

            $table->string('qc_status', 30)->default('pending');
            $table->string('receive_status', 30)->default('pending');
            $table->string('print_status', 30)->default('pending');
            $table->text('rejection_reason')->nullable();

            $table->unsignedBigInteger('scanned_by')->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->unsignedBigInteger('received_by')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->unsignedBigInteger('printed_by')->nullable();
            $table->timestamp('printed_at')->nullable();
            $table->timestamps();

            $table->unique(['cut_job_id', 'source_key']);
            $table->unique(['cut_job_header_id', 'line_no']);
            $table->index('cut_job_id');
            $table->index(['qc_status', 'receive_status', 'print_status']);
            $table->index(['source_type', 'source_id']);
            $table->index(['spec_code', 'sku_code']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('cut_job_lines');
        Schema::connection($this->connection)->dropIfExists('cut_job_headers');
        Schema::connection($this->connection)->dropIfExists('cut_jobs');
    }
};
