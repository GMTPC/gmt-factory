<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('master_product_groups')) {
            Schema::create('master_product_groups', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 10)->unique();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('master_product_specs')) {
            Schema::create('master_product_specs', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name');
                $table->string('grade', 100)->nullable();
                $table->string('brand', 100)->nullable();
                $table->text('description')->nullable();
                $table->text('full_desc')->nullable();
                $table->string('status', 50)->nullable();
                $table->string('sheet_type', 50)->nullable();
                $table->string('edge_type', 50)->nullable();
                $table->decimal('width_mm', 10, 2)->nullable();
                $table->decimal('length_mm', 10, 2)->nullable();
                $table->decimal('thickness_mm', 8, 2)->nullable();
                $table->string('category', 100)->nullable();
                $table->timestamps();
            });
        }

        $lookups = [
            'master_brands' => fn (Blueprint $table) => $table->string('code', 10)->unique(),
            'master_edges' => fn (Blueprint $table) => $table->string('code', 10)->unique(),
            'master_sheet_types' => fn (Blueprint $table) => $table->string('code', 5)->unique(),
        ];

        foreach ($lookups as $tableName => $addCode) {
            if (! Schema::hasTable($tableName)) {
                Schema::create($tableName, function (Blueprint $table) use ($addCode): void {
                    $table->id();
                    $addCode($table);
                    $table->string('name');
                    $table->timestamps();
                });
            }
        }

        if (! Schema::hasTable('master_sizes')) {
            Schema::create('master_sizes', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 10)->unique();
                $table->string('label');
                $table->unsignedInteger('width_mm')->nullable();
                $table->unsignedInteger('length_mm')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('master_thicknesses')) {
            Schema::create('master_thicknesses', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 10)->unique();
                $table->decimal('mm', 5, 2)->nullable();
                $table->string('label')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('master_faces')) {
            Schema::create('master_faces', function (Blueprint $table): void {
                $table->id();
                $table->string('code', 10)->unique();
                $table->string('name');
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Keep product catalog data intact on rollback. The tables may have
        // existed before this migration and were intentionally left untouched.
    }
};
