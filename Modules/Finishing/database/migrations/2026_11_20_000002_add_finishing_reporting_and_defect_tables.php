<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finishing_stations', function (Blueprint $table) {
            $table->boolean('is_machine_on')->default(false);
        });

        Schema::table('finishing_production_logs', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::create('finishing_defect_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->nullable()->constrained('finishing_stations');
            $table->string('name', 120);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['station_id', 'is_active']);
        });

        Schema::create('finishing_defect_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_log_id')->constrained('finishing_production_logs');
            $table->foreignId('defect_type_id')->constrained('finishing_defect_types');
            $table->decimal('quantity', 18, 3)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['production_log_id', 'defect_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finishing_defect_entries');
        Schema::dropIfExists('finishing_defect_types');

        Schema::table('finishing_production_logs', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('finishing_stations', function (Blueprint $table) {
            $table->dropColumn('is_machine_on');
        });
    }
};
