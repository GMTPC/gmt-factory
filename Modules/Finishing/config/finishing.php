<?php

return [
    'timezone' => env('APP_TIMEZONE', 'Asia/Bangkok'),
    'shifts' => [
        'day' => ['starts_at' => '08:00', 'ends_at' => '17:00'],
        'night' => ['starts_at' => '17:00', 'ends_at' => '08:00'],
    ],
    // Users in these departments (users.department, case-insensitive) see only the Finishing menu and land on the Finishing dashboard.
    'departments' => array_values(array_filter(array_map(
        fn (string $name) => mb_strtolower(trim($name)),
        explode(',', (string) env('FINISHING_DEPARTMENTS', 'ฟินิชชิ่ง,Finishing')),
    ))),
    'admin_usernames' => array_values(array_filter(array_map(
        'strtolower',
        explode(',', (string) env('FINISHING_ADMIN_USERNAMES', 'manager')),
    ))),
    'job_employees' => [
        'สมโชตน์ แดงทรัพย์ (อาท)',
        'กฤษติยา พุฒิเพ็ญ (ไอซ์)',
        'นิชิตา แถวลึก (แพร)',
        'ชาญยุทธ สุพะสอน (บอม)',
        'จรรยา เอี่ยมยวง (อาย)',
        'ภรัณย์ พวงจำปา (แอ๊ด)',
        'ณัฐพร คำบุญมี (เหมียว)',
        'Mr.Tin win maung (มาว)',
        'Mr.mg shine that aung (ชาย)',
        'อรรถชัย แก้วนีร (จอม)',
        'สมพงษ์ เล็กกุล (หนึ่ง)',
        'จิรกิตติ์ อิ่มปุ่ย (เลิฟ)',
        'นิรวิทย์ มาดี (ไตต้น)',
        'ธิตาพร บุญเกิด (ปอนด์)',
        'ชัยวัฒน์ ดำขำ (นุ)',
        'เมธา อยู่ค่าย',
        'สุชาติ เกษา',
        'จันทร์เพ็ญ พรมนะ',
        'วงเดือน จันทร์งาม',
        'พิพรรัตน์ ทิวาพัฒน์',
        'วาสนา ก้อนจันทร์เทศ',
        'สำเภา รักรัตน์',
        'กฤตนัย ขุนภักดี',
        'สรศักดิ์ บุญรอด',
        'พิชณุ เหล่าส่าย',
        'นวพล คำปอง',
    ],
    'job_machines' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('FINISHING_JOB_MACHINES', 'เครื่องเคลือบขาวผ่อง,เครื่องเคลือบ PVC,เครื่องลามิเนต,ฟอยล์อบกันร้อน')),
    ))),
    'pvc_types' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('FINISHING_PVC_TYPES', 'ขาวผ่อง,ลามิเนต,ฟอยล์อบกันร้อน')),
    ))),
    // Stations shown as "ยังไม่เปิดใช้": visible on the map, but no Jobs / scans / start-stop.
    'disabled_stations' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('FINISHING_DISABLED_STATIONS', 'ST3,ST4,ST5,ST6')),
    ))),
    // Per-station limits for the "open Job" form. Stations not listed get every machine / PVC type.
    'station_job_options' => [
        'ST1' => [
            'machines' => ['เครื่องเคลือบขาวผ่อง'],
            'pvc_types' => ['ขาวผ่อง'],
        ],
        // ST1 = ขาวผ่อง only. ST2 = เคลือบ PVC: same flow as ST1 on the PVC coating machine, PVC finishes only.
        'ST2' => [
            'machines' => ['เครื่องเคลือบ PVC'],
            'pvc_types' => ['ลามิเนต', 'ฟอยล์อบกันร้อน'],
        ],
    ],
    'raw_materials' => [
        'MET-MPT 1195 แผ่นอลูมิเนียมฟอยล์(จีน)',
        'FIBER GLASS MAT',
        'กระดาษปิดผิว PVC จีน ( Lemon 154 )',
        'กระดาษปิดผิว PVC จีน ( Orange 608 )',
        'AIU 1/2*1/2 นิ้ว ชุบขาวบาง (12มิล)',
        'PW1121 30G AMN กระดาษปิดผิว',
        'กระดาษปิดผิว PVC จีน (238)',
        'กระดาษปิดผิว PVC จีน (975)',
        'กระดาษปิดผิว PVC จีน (996)',
        'กระดาษปิดผิว PVC จีน (567)',
        'กระดาษปิดผิว PVC จีน ลายไม้ (P56 TA11-2)',
        'กระดาษปิดผิว PVC จีน ลายไม้ (WE002-5)',
        'กระดาษปิดผิว PVC จีน ลายไม้ (P50 TA07-1)',
        'กระดาษปิดผิว PVC จีน (567) B',
        'กระดาษปิดผิวสีดำ VC-70498 Melamine(MA) - 30g',
        'กาว EI 8108/M6',
        'กาว PVCGLUE จีน',
    ],
    // Units offered when recording how much of a raw material a Job used.
    'raw_material_units' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('FINISHING_RAW_MATERIAL_UNITS', 'กก.,ม้วน,เมตร,แผ่น,ลิตร,ถุง,ถัง')),
    ))),
    'job_inspectors' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('FINISHING_JOB_INSPECTORS', 'คุณแบงค์,คุณเหน่ง')),
    ))),
    'defect_alert_threshold' => (float) env('FINISHING_DEFECT_ALERT_THRESHOLD', 5),
];
