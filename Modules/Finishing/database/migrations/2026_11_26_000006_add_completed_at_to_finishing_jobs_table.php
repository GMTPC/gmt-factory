<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('finishing_jobs', 'completed_at')) {
            Schema::table('finishing_jobs', function (Blueprint $table): void {
                // When "จบงาน" was pressed; used for the Job's processing time.
                $table->timestamp('completed_at')->nullable();
            });
        }

        // Jobs closed before this column existed: best guess is their last update.
        DB::table('finishing_jobs')->where('status', 'completed')->whereNull('completed_at')
            ->update(['completed_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('finishing_jobs', 'completed_at')) {
            Schema::table('finishing_jobs', function (Blueprint $table): void {
                $table->dropColumn('completed_at');
            });
        }
    }
};
