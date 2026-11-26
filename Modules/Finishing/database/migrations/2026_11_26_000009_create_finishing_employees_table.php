<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('finishing_employees')) {
            Schema::create('finishing_employees', function (Blueprint $table): void {
                $table->id();
                $table->string('name', 150);
                $table->string('nickname', 50)->nullable();
                // "ชื่อ นามสกุล (ชื่อเล่น)" — the text saved on Jobs (employee_names) and shown in the picker.
                $table->string('display_name', 210)->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Start from the list that used to live in config/finishing.php.
        if (DB::table('finishing_employees')->doesntExist()) {
            $now = now();
            $rows = collect(config('finishing.job_employees', []))->map(function (string $display) use ($now) {
                $display = trim($display);
                preg_match('/^(.*?)\s*\(([^()]+)\)\s*$/u', $display, $match);

                return [
                    'name' => $match ? trim($match[1]) : $display,
                    'nickname' => $match ? trim($match[2]) : null,
                    'display_name' => $display,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->unique('display_name')->values()->all();
            foreach (array_chunk($rows, 100) as $chunk) {
                DB::table('finishing_employees')->insert($chunk);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('finishing_employees');
    }
};
