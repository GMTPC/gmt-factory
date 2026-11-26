<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('finishing_jobs', 'raw_material_name')) {
            Schema::table('finishing_jobs', function (Blueprint $table): void {
                $table->string('raw_material_name', 255)->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('finishing_jobs', 'raw_material_name')) {
            Schema::table('finishing_jobs', function (Blueprint $table): void {
                $table->dropColumn('raw_material_name');
            });
        }
    }
};
