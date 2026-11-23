<?php

namespace Modules\Finishing\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Finishing\App\Models\FinishingStation;

class FinishingStationSeeder extends Seeder
{
    public function run(): void
    {
        $stations = [
            ['code' => 'ST1', 'name' => 'ขาวผ่อง (แปรรูป)', 'input_material' => 'วิบ', 'output_material' => 'เว็บขาวผ่อง', 'is_final' => false],
            ['code' => 'ST2', 'name' => 'PVC ติดฟอยล์', 'input_material' => 'แผ่น PVC', 'output_material' => 'PVC เว็บ', 'is_final' => false],
            ['code' => 'ST3', 'name' => 'ตัดขาวผ่อง', 'input_material' => 'เว็บขาวผ่อง', 'output_material' => 'วิป', 'is_final' => false],
            ['code' => 'ST4', 'name' => 'ตัด PVC', 'input_material' => 'PVC เว็บ', 'output_material' => 'วิป', 'is_final' => true],
            ['code' => 'ST5', 'name' => 'ทาสี / ปุลาย', 'input_material' => 'วิบ', 'output_material' => 'วิป', 'is_final' => false],
            ['code' => 'ST6', 'name' => 'แพ็ค', 'input_material' => 'วิป', 'output_material' => 'วิป', 'is_final' => true],
        ];

        foreach ($stations as $station) {
            FinishingStation::query()->updateOrCreate(['code' => $station['code']], $station);
        }

        $ids = FinishingStation::query()->pluck('id', 'code');
        foreach (['ST1' => 'ST3', 'ST2' => 'ST4', 'ST5' => 'ST6'] as $from => $to) {
            FinishingStation::query()->where('code', $from)->update(['next_station_id' => $ids[$to] ?? null]);
        }
    }
}
