<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // ST1 = ขาวผ่อง, ST2 = เคลือบ PVC (was "PVC ติดฟอยล์").
    public function up(): void
    {
        DB::table('finishing_stations')->where('code', 'ST2')->where('name', 'PVC ติดฟอยล์')->update(['name' => 'เคลือบ PVC', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('finishing_stations')->where('code', 'ST2')->where('name', 'เคลือบ PVC')->update(['name' => 'PVC ติดฟอยล์', 'updated_at' => now()]);
    }
};
