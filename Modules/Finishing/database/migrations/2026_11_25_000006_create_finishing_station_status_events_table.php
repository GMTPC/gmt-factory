<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('finishing_station_status_events')) {
            return;
        }

        Schema::create('finishing_station_status_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('station_id')->constrained('finishing_stations')->cascadeOnDelete();
            $table->boolean('is_running');
            $table->string('reason', 500);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['station_id', 'id']);
        });
    }

    public function down(): void
    {
        // Preserve station status history in imported databases.
    }
};
