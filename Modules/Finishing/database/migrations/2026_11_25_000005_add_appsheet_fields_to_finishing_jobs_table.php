<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('finishing_jobs', function (Blueprint $table): void {
            $table->json('employee_names')->nullable();
            $table->string('machine_name', 120)->nullable();
            $table->string('pvc_type', 100)->nullable();
            $table->foreignId('recorder_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('inspector_name', 120)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('finishing_jobs', function (Blueprint $table): void {
            $table->dropForeign(['recorder_user_id']);
            $table->dropColumn(['employee_names', 'machine_name', 'pvc_type', 'recorder_user_id', 'inspector_name']);
        });
    }
};
