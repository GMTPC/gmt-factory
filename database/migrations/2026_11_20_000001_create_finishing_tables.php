<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finishing_stations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->string('input_material', 100);
            $table->string('output_material', 100);
            $table->foreignId('next_station_id')->nullable()->constrained('finishing_stations');
            $table->boolean('is_final')->default(false);
            $table->timestamps();
        });

        Schema::create('finishing_materials', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->nullable();
            $table->string('name', 255);
            $table->string('type', 50);
            $table->string('status', 30)->default('pending');
            $table->foreignId('station_id')->constrained('finishing_stations');
            $table->decimal('quantity', 18, 3)->default(0);
            $table->foreignId('parent_id')->nullable()->constrained('finishing_materials');
            $table->timestamps();

            $table->index(['station_id', 'status']);
            $table->index(['code', 'type']);
        });

        Schema::create('finishing_production_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained('finishing_stations');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('material_id')->constrained('finishing_materials');
            $table->foreignId('output_material_id')->nullable()->constrained('finishing_materials');
            $table->date('shift_date');
            $table->string('shift', 20);
            $table->decimal('quantity_in', 18, 3)->default(0);
            $table->decimal('quantity_out', 18, 3)->default(0);
            $table->decimal('defect_count', 18, 3)->default(0);
            $table->decimal('ft_count', 18, 3)->nullable();
            $table->string('sub_type', 100)->nullable();
            $table->boolean('has_labeling')->default(false);
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamps();

            $table->index(['station_id', 'shift_date', 'shift']);
            $table->index(['material_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finishing_production_logs');
        Schema::dropIfExists('finishing_materials');
        Schema::dropIfExists('finishing_stations');
    }
};
