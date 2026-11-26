<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('finishing_wip_receipts') || Schema::hasColumn('finishing_wip_receipts', 'production_log_id')) {
            return;
        }

        Schema::table('finishing_wip_receipts', function (Blueprint $table): void {
            $table->foreignId('production_log_id')->nullable()
                ->constrained('finishing_production_logs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Preserve scan history in imported databases.
    }
};
