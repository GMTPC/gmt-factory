<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finishing_jobs', function (Blueprint $table) {
            $table->foreignId('material_id')->nullable()->after('station_id')
                ->constrained('finishing_materials')->noActionOnDelete();
            $table->index(['material_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('finishing_jobs', function (Blueprint $table) {
            $table->dropForeign(['material_id']);
            $table->dropIndex(['material_id', 'status']);
            $table->dropColumn('material_id');
        });
    }
};
