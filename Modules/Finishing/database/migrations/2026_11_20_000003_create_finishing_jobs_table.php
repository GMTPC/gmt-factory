<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finishing_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_no', 40)->unique();
            $table->foreignId('station_id')->constrained('finishing_stations')->noActionOnDelete();
            $table->foreignId('user_id')->constrained('users')->noActionOnDelete();
            $table->date('shift_date');
            $table->string('shift', 20);
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['station_id', 'status', 'shift_date']);
        });

        Schema::table('finishing_production_logs', function (Blueprint $table) {
            $table->foreignId('job_id')->nullable()->after('station_id')
                ->constrained('finishing_jobs')->noActionOnDelete();
            $table->index(['job_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('finishing_production_logs', function (Blueprint $table) {
            $table->dropForeign(['job_id']);
            $table->dropIndex(['job_id', 'status']);
            $table->dropColumn('job_id');
        });

        Schema::dropIfExists('finishing_jobs');
    }
};
