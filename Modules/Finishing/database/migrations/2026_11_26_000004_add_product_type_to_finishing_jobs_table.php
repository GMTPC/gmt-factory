<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('finishing_jobs', 'product_type_code')) {
            Schema::table('finishing_jobs', function (Blueprint $table): void {
                // One Job = one product type, locked by the first WIP tag scanned into it.
                $table->string('product_type_code', 50)->nullable();
                $table->string('product_type_name', 255)->nullable();
            });
        }

        // Existing Jobs take the product type of their first recorded (not cancelled) scan.
        $firstLogs = DB::table('finishing_production_logs')
            ->whereNotNull('product_type_code')->where('status', '!=', 'cancelled')->whereNull('deleted_at')
            ->orderBy('id')->get(['job_id', 'product_type_code', 'product_type_name'])
            ->unique('job_id');
        foreach ($firstLogs as $log) {
            DB::table('finishing_jobs')->where('id', $log->job_id)->whereNull('product_type_code')
                ->update(['product_type_code' => $log->product_type_code, 'product_type_name' => $log->product_type_name]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('finishing_jobs', 'product_type_code')) {
            Schema::table('finishing_jobs', function (Blueprint $table): void {
                $table->dropColumn(['product_type_code', 'product_type_name']);
            });
        }
    }
};
