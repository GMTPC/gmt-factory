<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('end_line_qc_headers')) {
            Schema::create('end_line_qc_headers', function (Blueprint $table): void {
                $table->id();
                $table->unsignedTinyInteger('line');
                $table->date('qc_date');
                $table->string('shift', 1);
                $table->string('qc_inspector')->nullable();
                $table->string('status', 20)->default('draft');
                $table->string('lot', 20)->nullable();
                $table->unsignedInteger('running_no')->default(1);
                $table->string('job_no', 30)->index();
                $table->foreignId('brand_id')->nullable();
                $table->foreignId('sheet_type_id')->nullable();
                $table->foreignId('edge_id')->nullable();
                $table->foreignId('size_id')->nullable();
                $table->foreignId('thickness_id')->nullable();
                $table->foreignId('face_id')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->index(['line', 'status', 'qc_date']);
            });
        }

        if (! Schema::hasTable('end_line_qc_lines')) {
            Schema::create('end_line_qc_lines', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('header_id')->constrained('end_line_qc_headers')->cascadeOnDelete();
                $table->string('running_no', 10)->nullable();
                $table->string('line_no', 10)->nullable();
                $table->foreignId('spec_id')->nullable();
                $table->string('product_type', 10)->nullable();
                $table->string('product_state', 10)->nullable();
                $table->string('brand')->nullable();
                $table->foreignId('brand_id')->nullable();
                $table->decimal('weight', 10, 2)->nullable();
                $table->dateTime('time_at')->nullable();
                $table->unsignedInteger('qty')->default(0);
                $table->string('unit', 10)->default('2/1');
                $table->string('status', 20)->default('draft');
                $table->text('remark')->nullable();
                $table->unsignedBigInteger('product_group_id')->nullable();
                $table->string('lot', 30)->nullable();
                $table->string('sku_code', 100)->nullable();
                $table->string('full_name')->nullable();
                $table->string('sku_name')->nullable();
                $table->string('tag', 255)->nullable();
                $table->string('full_tag', 255)->nullable();
                $table->string('wip_code', 100)->nullable();
                $table->string('wip_status_code', 10)->nullable();
                $table->string('wip_status_name', 100)->nullable();
                $table->unsignedInteger('tag_no')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->index(['header_id', 'product_state', 'line_no']);
            });
        }
    }

    public function down(): void
    {
        // These names may be tables from the legacy app and can contain
        // production history. Keep them when rolling back this adapter.
    }
};
