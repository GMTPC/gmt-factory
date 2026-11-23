<?php

namespace Modules\Finishing\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FinishingDefectTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['รอยขีดข่วน', 'แตก / บิ่น', 'สีหรือฟอยล์ผิดปกติ', 'ขนาดไม่ได้', 'อื่น ๆ'] as $name) {
            DB::table('finishing_defect_types')->updateOrInsert(
                ['station_id' => null, 'name' => $name],
                ['is_active' => true, 'updated_at' => now(), 'created_at' => now()],
            );
        }
    }
}
