<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterCatalogSeeder extends Seeder
{
    public function run()
    {
        // ----------------------------
        // 1) master_product_groups
        // ----------------------------
        $productGroups = [
            ['code' => 'B1', 'name' => 'แผ่นยิบซั่ม L1'],
            ['code' => 'B2', 'name' => 'แผ่นยิบซั่ม L2'],
            ['code' => 'B3', 'name' => 'แผ่นยิบซั่ม L3'],
            ['code' => 'B4', 'name' => 'แผ่นยิบซั่ม L4'],
        ];
        $this->upsert('master_product_groups', $productGroups, ['code']);

        // ----------------------------
        // 2) master_faces (หน้า/สีหน้า)
        // ----------------------------
        $faces = [
            ['code' => 'W', 'name' => 'White', 'is_default' => true],
            ['code' => 'G', 'name' => 'Grey (Special)', 'is_default' => false],
        ];
        $this->upsert('master_faces', $faces, ['code']);

        // ----------------------------
        // 3) master_edges (ชนิดขอบของแผ่น)
        //     จากคอลัมน์ชมพู: A/B/C/0
        // ----------------------------
        $edges = [
            ['code' => 'A', 'name' => 'ขอบนอก'],
            ['code' => 'B', 'name' => 'ขอบเรียบ'],
            ['code' => 'C', 'name' => 'ไร้ขอบ'],
            ['code' => '0', 'name' => '-'],
        ];
        $this->upsert('master_edges', $edges, ['code']);

        // ----------------------------
        // 4) master_sizes (ขนาด) — จากคอลัมน์เทา code 01..33
        // ----------------------------
        $sizes = [
            ['code' => '01', 'label' => '1.2 x 2.4 m',   'width_mm' => 1200, 'length_mm' => 2400],
            ['code' => '02', 'label' => '1.22 x 2.44 m', 'width_mm' => 1220, 'length_mm' => 2440],
            ['code' => '03', 'label' => '1.20 x 2.42 cm', 'width_mm' => 1200, 'length_mm' => 2420], // ตามภาพมี "cm" (คงพิมพ์ผิดในชีต)
            ['code' => '04', 'label' => '1.2 x 3.05 m',  'width_mm' => 1200, 'length_mm' => 3050],
            ['code' => '05', 'label' => '1.22 x 3.05 m', 'width_mm' => 1220, 'length_mm' => 3050],
            ['code' => '06', 'label' => '1.05 x 2.25 m', 'width_mm' => 1050, 'length_mm' => 2250],
            ['code' => '07', 'label' => '1.2 x 2.3 m',   'width_mm' => 1200, 'length_mm' => 2300],
            ['code' => '08', 'label' => '1.2 x 2.5 m',   'width_mm' => 1200, 'length_mm' => 2500],
            ['code' => '09', 'label' => '1.2 x 2.7 m',   'width_mm' => 1200, 'length_mm' => 2700],
            ['code' => '10', 'label' => '1.22 x 2.74 m', 'width_mm' => 1220, 'length_mm' => 2740],
            ['code' => '11', 'label' => '1.22 x 1.83 m', 'width_mm' => 1220, 'length_mm' => 1830],
            ['code' => '12', 'label' => '0.9 x 2.1 m',   'width_mm' => 900,  'length_mm' => 2100],
            ['code' => '13', 'label' => '0.9 x 2.4 m',   'width_mm' => 900,  'length_mm' => 2400],
            ['code' => '14', 'label' => '1.09 x 2.44 m', 'width_mm' => 1090, 'length_mm' => 2440],
            ['code' => '15', 'label' => '1.2 x 2.8 m',   'width_mm' => 1200, 'length_mm' => 2800],
            ['code' => '16', 'label' => '0.9 x 2.81 m',  'width_mm' => 900,  'length_mm' => 2810],
            ['code' => '17', 'label' => '1.2 x 3.01 m',  'width_mm' => 1200, 'length_mm' => 3010],
            ['code' => '18', 'label' => '1.2 x 2.1 m',   'width_mm' => 1200, 'length_mm' => 2100],
            ['code' => '19', 'label' => '1.2 x 2.75 m',  'width_mm' => 1200, 'length_mm' => 2750],
            ['code' => '20', 'label' => '1.2 x 1.5 m',   'width_mm' => 1200, 'length_mm' => 1500],
            ['code' => '21', 'label' => '1.05 x 2.25 m', 'width_mm' => 1050, 'length_mm' => 2250],
            ['code' => '22', 'label' => '1.21 x 2.43 m', 'width_mm' => 1210, 'length_mm' => 2430],
            ['code' => '23', 'label' => '1.22 x 2.745 m', 'width_mm' => 1220, 'length_mm' => 2745],
            ['code' => '24', 'label' => '1.22 x 1.829 m', 'width_mm' => 1220, 'length_mm' => 1829],
            ['code' => '25', 'label' => '0.988 x 2.264 m', 'width_mm' => 988,  'length_mm' => 2264],
            ['code' => '26', 'label' => '0.9 x 2.5 m',   'width_mm' => 900,  'length_mm' => 2500],
            ['code' => '27', 'label' => '1.22 x 2.427 m', 'width_mm' => 1220, 'length_mm' => 2427],
            ['code' => '28', 'label' => '1.2 x 2.9 m',   'width_mm' => 1200, 'length_mm' => 2900],
            ['code' => '29', 'label' => '1.2 x 2.6 m',   'width_mm' => 1200, 'length_mm' => 2600],
            ['code' => '30', 'label' => '1.2 x 1.52 m',  'width_mm' => 1200, 'length_mm' => 1520],
            ['code' => '31', 'label' => '1.2 x 1.51 m',  'width_mm' => 1200, 'length_mm' => 1510],
            ['code' => '32', 'label' => '1.2 x 3.6 m',   'width_mm' => 1200, 'length_mm' => 3600],
            ['code' => '33', 'label' => '1.2 x 3.045 m', 'width_mm' => 1200, 'length_mm' => 3045],
        ];
        $this->upsert('master_sizes', $sizes, ['code']);

        // ----------------------------
        // 5) master_thicknesses (ความหนา) — จากคอลัมน์ส้ม
        // ----------------------------
        $thicknesses = [
            ['code' => '9',   'mm' => 9.0,   'label' => '9 mm.'],
            ['code' => '12',  'mm' => 12.0,  'label' => '12 mm.'],
            ['code' => '12.5', 'mm' => 12.5,  'label' => '12.5 mm.'],
            ['code' => '15',  'mm' => 15.0,  'label' => '15 mm.'],
            ['code' => '16',  'mm' => 16.0,  'label' => '16 mm.'],
            ['code' => '10',  'mm' => 10.0,  'label' => '10 mm.'],
            ['code' => '13',  'mm' => 13.0,  'label' => '13 mm.'],
            ['code' => '17',  'mm' => 15.5,  'label' => '15.5mm.'], // ในภาพมี 15.5mm
            ['code' => '14',  'mm' => 14.0,  'label' => '14 mm.'],
            ['code' => '7',   'mm' => 7.0,   'label' => '7 mm.'],
            ['code' => '6',   'mm' => 6.0,   'label' => '6 mm.'],
            ['code' => '11',  'mm' => 11.0,  'label' => '11 mm.'],
            // หากมีค่าอื่นในชีตของคุณ ให้เติมเพิ่มได้เลย
        ];
        $this->upsert('master_thicknesses', $thicknesses, ['code']);

        // ----------------------------
        // 6) master_sheet_types (ชนิดแผ่น) — สีเขียวฝั่งซ้าย
        //     รหัสตัวอักษร (G) + ชื่อ (H)
        //     *ชื่อบางตัวจากรูปอ่านได้ไม่ชัดนัก แก้ไขได้ตามจริง
        // ----------------------------
        $sheetTypes = [
            ['code' => '-', 'name' => 'เกรด B'],
            ['code' => 'A', 'name' => 'ทนเย็น'],
            ['code' => 'Z', 'name' => 'กันร้อน'],
            ['code' => 'X', 'name' => 'ตลาดล่าง'],
            ['code' => 'W', 'name' => 'ฟอกอากาศ'],
            ['code' => 'T', 'name' => 'ทนไฟ'],
            ['code' => 'H', 'name' => 'งานแก้'],
            ['code' => 'O', 'name' => 'เคลือบWAX'],
            ['code' => 'P', 'name' => 'หน้าเคลือบ+ฟิล์ม (กรุณาตรวจชื่อจากชีต)'],
            ['code' => 'Y', 'name' => 'เหนี่ยว เคลือบ WAX เงาคู่ (ตรวจ)'],
            ['code' => 'G', 'name' => 'เหนี่ยว เคลือบ WAX เงาเดี่ยว (ตรวจ)'],
            ['code' => 'V', 'name' => 'เกรดพิเศษ'],
            ['code' => 'C', 'name' => 'ยางพ้อง'],
            ['code' => '8', 'name' => 'PVC'],
            ['code' => 'E', 'name' => 'อุตสาหกรรม'],
            ['code' => 'R', 'name' => 'แผ่นกันเสียงเจียร'],
            ['code' => 'S', 'name' => 'Sblock รุ่น CR1'],
            ['code' => 'N', 'name' => 'Sblock รุ่น CR2'],
            ['code' => 'I', 'name' => 'Sblock รุ่น CR8'],
            ['code' => 'J', 'name' => 'Sblock รุ่น SR4'],
            ['code' => 'L', 'name' => '4 ด้าน(เจียร)'],
            ['code' => 'K', 'name' => 'ธรณีฯ สูตรเฉพาะแผ่น'],
            ['code' => 'M', 'name' => 'ยาวน่องสูตรพาแผ่น'],
            ['code' => 'D', 'name' => 'PVC Lemon'],
            ['code' => 'T', 'name' => 'PVC Orange'],
            ['code' => 'V', 'name' => 'PVC Lemon+ฟองมิ'],
            ['code' => 'X', 'name' => 'ตัดโค้ง'],
            ['code' => 'F', 'name' => '4 ด้านหน้าแผ่น'],
            ['code' => 'U', 'name' => 'FIBER CORE'],
            ['code' => 'B', 'name' => 'กันร้อนติดเทปAUTO'],
            ['code' => 'Q', 'name' => 'เหนี่ยว เคลือบ WAX เงาคู่(ตัดตาข่าย)'],
            ['code' => '0', 'name' => 'Green Shield / Sblock รุ่น SR8 / กันเชื้อรา / ทาสีฉลาย (รวมกลุ่มจากภาพ)'],
        ];
        $this->upsert('master_sheet_types', $sheetTypes, ['code']);

        // ----------------------------
        // 7) master_brands (ยี่ห้อ)
        //     ใส่ code ตามคอลัมน์ C ให้ครบ; ชื่อหลายตัวอ่านจากภาพไม่ชัด
        //     คุณสามารถแก้ไขชื่อให้ตรงได้เลยในอาร์เรย์นี้
        // ----------------------------
        $brands = [
            ['code' => '00', 'name' => 'แผ่นเปลือย'],
            ['code' => '01', 'name' => 'VIP (มอก.)'],
            ['code' => '04', 'name' => 'GM'],
            ['code' => '05', 'name' => 'SHOGUN'],
            ['code' => '06', 'name' => 'เพชร5ดาว'],
            ['code' => '09', 'name' => 'เกรดC'],
            ['code' => '10', 'name' => 'ต้นไม้'],
            ['code' => '11', 'name' => 'SCL'],
            ['code' => '12', 'name' => 'RPG'],
            ['code' => '13', 'name' => 'SUPER'],
            ['code' => '14', 'name' => 'NOLOGO'],
            ['code' => '15', 'name' => 'ตราเพชร'],
            ['code' => '16', 'name' => 'ST'],
            ['code' => '17', 'name' => '3G'],
            ['code' => '18', 'name' => 'Maxum'],
            ['code' => '19', 'name' => 'City bord'],
            ['code' => '20', 'name' => 'สีธรรมดา'],
            ['code' => '21', 'name' => 'ยาวน่อง'],           // ← กรุณาตรวจชื่อจากชีตจริง
            ['code' => '22', 'name' => 'ออกงาน'],
            ['code' => '23', 'name' => 'นาเคลีย'],         // ← ตรวจชื่อ
            ['code' => '24', 'name' => 'บึก-บอน'],         // ← ตรวจชื่อ
            ['code' => '25', 'name' => 'ปืนเหล็ก'],
            ['code' => '26', 'name' => 'TRUSUS'],
            ['code' => '27', 'name' => 'OPSKY'],
            ['code' => '28', 'name' => 'DD bord'],
            ['code' => '29', 'name' => 'CC'],
            ['code' => '30', 'name' => 'GM กัมพูชา'],
            ['code' => '31', 'name' => 'TOA'],
            ['code' => '32', 'name' => 'HOFF GM'],
            ['code' => '33', 'name' => 'แผ่นรองตัด'],
            ['code' => '99', 'name' => 'รอติดFOIL'],
            ['code' => '94', 'name' => 'รอติดWAX'],
            ['code' => '9W', 'name' => 'DIC'],
            ['code' => '34', 'name' => 'GM เบาๆ'],
            ['code' => '44', 'name' => 'รอเคลือบ WAX เงาคู่'],
            ['code' => '9H', 'name' => 'รอเคลือบ WAX เงาเดี่ยว'],
            ['code' => '9O', 'name' => 'รอติดPVC'],
            ['code' => '9P', 'name' => 'GM พฤกษา'],
            ['code' => '0P', 'name' => 'GM EASTBERG'],
            ['code' => '35', 'name' => 'VIP (มอก.)หน้าขาว'],
            ['code' => '36', 'name' => 'GMหน้าขาว'],
            ['code' => '37', 'name' => 'TOAหน้าขาว'],
            ['code' => '38', 'name' => 'SUPERหน้าขาว'],
            ['code' => '39', 'name' => 'ชั้นรองตัด'],
            ['code' => '98', 'name' => 'รอเจียร์'],
            ['code' => '91', 'name' => 'กัปตัน'],
            ['code' => '40', 'name' => 'รอตัดเทปออโต้'],
            ['code' => '92', 'name' => 'แผ่นรองตัด (ชุด 2)'], // ← หากชื่อจริงต่างให้แก้
            ['code' => '93', 'name' => 'แผ่นรองตัด (ชุด 3)'],
        ];
        $this->upsert('master_brands', $brands, ['code']);

        $this->command->info('Master catalog seeded.');
    }

    /**
     * Upsert helper สำหรับ Laravel 5.8 ที่ยังไม่มี ->upsert()
     */
    private function upsert(string $table, array $rows, array $uniqueBy): void
    {
        foreach ($rows as $row) {
            DB::table($table)->insertOrIgnore($row);
        }
    }
}

