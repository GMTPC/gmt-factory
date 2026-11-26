<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('finishing_jobs', 'raw_materials')) {
            Schema::table('finishing_jobs', function (Blueprint $table): void {
                // Raw materials used by the whole Job: [{name, quantity, unit}], quantity may be filled in later.
                $table->json('raw_materials')->nullable();
            });
        }

        if (Schema::hasColumn('finishing_jobs', 'raw_material_names')) {
            DB::table('finishing_jobs')->whereNotNull('raw_material_names')->whereNull('raw_materials')->get(['id', 'raw_material_names'])
                ->each(function ($job): void {
                    $items = collect(json_decode((string) $job->raw_material_names, true) ?: [])
                        ->map(fn ($name) => ['name' => (string) $name, 'quantity' => null, 'unit' => null])->values();
                    DB::table('finishing_jobs')->where('id', $job->id)->update(['raw_materials' => $items->toJson(JSON_UNESCAPED_UNICODE)]);
                });
            Schema::table('finishing_jobs', function (Blueprint $table): void {
                $table->dropColumn('raw_material_names');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('finishing_jobs', 'raw_material_names')) {
            Schema::table('finishing_jobs', function (Blueprint $table): void {
                $table->json('raw_material_names')->nullable();
            });
        }
        DB::table('finishing_jobs')->whereNotNull('raw_materials')->get(['id', 'raw_materials'])->each(function ($job): void {
            $names = collect(json_decode((string) $job->raw_materials, true) ?: [])->pluck('name')->values();
            DB::table('finishing_jobs')->where('id', $job->id)->update(['raw_material_names' => $names->toJson(JSON_UNESCAPED_UNICODE)]);
        });
        if (Schema::hasColumn('finishing_jobs', 'raw_materials')) {
            Schema::table('finishing_jobs', function (Blueprint $table): void {
                $table->dropColumn('raw_materials');
            });
        }
    }
};
