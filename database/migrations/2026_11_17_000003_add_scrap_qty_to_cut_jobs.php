<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'bplus';

    public function up(): void
    {
        Schema::connection($this->connection)->table('cut_jobs', function (Blueprint $table): void {
            $table->unsignedInteger('scrap_qty')->default(0);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('cut_jobs', function (Blueprint $table): void {
            $table->dropColumn('scrap_qty');
        });
    }
};
