<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('finishing_wip_receipts')) {
            return;
        }

        Schema::create('finishing_wip_receipts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('job_id')->constrained('finishing_jobs');
            $table->foreignId('material_id')->constrained('finishing_materials');
            $table->foreignId('user_id')->constrained('users');
            $table->string('scan_code', 255)->unique();
            $table->decimal('quantity', 18, 3);
            $table->timestamps();

            $table->index(['job_id', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finishing_wip_receipts');
    }
};
