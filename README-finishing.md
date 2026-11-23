# ระบบฟินิชชิ่ง (Finishing Production Tracking)

> **Module** ในโปรเจกต์ Laravel 13 ที่มีอยู่แล้ว
> Rebuild จาก AppSheet เดิม → Web App (Laravel 13 + Vue)
> ใช้งานหลักบนมือถือ — 5 สถานี, 6 อุปกรณ์

---

## Tech Stack

| Layer      | Technology                              |
|------------|-----------------------------------------|
| Backend    | Laravel 13 (PHP 8.3+) — existing project |
| Frontend   | Vue 3 (via Laravel + Vite)              |
| CSS        | Tailwind CSS                            |
| Database   | SQL Server (existing DB)                |
| Auth       | Laravel Sanctum (ใช้ auth ที่มีอยู่แล้ว)  |
| Structure  | Module: `Modules/Finishing/`            |
| Deploy     | Mobile-first responsive web             |

---

## ภาพรวมระบบ (System Overview)

ระบบนี้ใช้บันทึกการผลิตในแผนก **ฟินิชชิ่ง** (Finishing Department)
แต่ละสถานีทำงานเป็น **Flow เข้า-ออก** ของวัสดุ:

```
คลัทช์บอร์ด (วิบ)
    │
    ├──► [สถานี 1] ขาวผ่อง (แปรรูป) ──► วิป
    │                                      │
    │                              [สถานี 3] ตัดขาวผ่อง ──► วิป → FG
    │
    ├──► [สถานี 5] ทาสี/ปุลาย ──► วิป
    │                                │
    │                        [สถานี 6] แพ็ค ──► FG
    │
แผ่น PVC
    │
    └──► [สถานี 2] PVC ติดฟอยล์ ──► เว็บ
                                      │
                              [สถานี 4] ตัด PVC ──► วิป → FG
```

---

## สถานี (Stations) — รายละเอียด

### สถานี 1 — ขาวผ่อง (แปรรูป)

| รายการ      | ค่า                                    |
|-------------|----------------------------------------|
| Input       | วิบ (จากคลัทช์บอร์ด)                   |
| Process     | แปรรูปขาวผ่อง                           |
| Output      | วิป                                    |
| บันทึก      | จำนวนผลิต, ยอดเสีย                     |

### สถานี 2 — PVC ติดฟอยล์

| รายการ      | ค่า                                    |
|-------------|----------------------------------------|
| Input       | แผ่น PVC                               |
| Process     | ติดฟอยล์                                |
| Sub-types   | เคลือบครั้งเดียว (single) / ติดฟอยล์ 2 รอบ (double) |
| Output      | เว็บ                                   |
| บันทึก      | จำนวนผลิต, ยอดเสีย, ประเภทการเคลือบ     |

### สถานี 3 — ตัดขาวผ่อง

| รายการ      | ค่า                                    |
|-------------|----------------------------------------|
| Input       | เว็บขาวผ่อง (จากสถานี 1)                |
| Process     | ตัดเป็นแผ่นเล็ก                         |
| Output      | วิป → FG (Finished Goods)              |
| บันทึก      | จำนวนผลิต, ยอดเสีย, ยอด FT             |
| หมายเหตุ    | ขั้นตอน **แปะผัก** (labeling) ตอนเข้า FG |

### สถานี 4 — ตัด PVC

| รายการ      | ค่า                                    |
|-------------|----------------------------------------|
| Input       | PVC เว็บ (จากสถานี 2)                   |
| Process     | ตัดเป็นแผ่นเล็ก (เหมือนขาวผ่อง)         |
| Output      | วิป → FG                              |
| บันทึก      | จำนวนผลิต, ยอดเสีย, ยอด FT             |

### สถานี 5 — ทาสี / ปุลาย

| รายการ      | ค่า                                    |
|-------------|----------------------------------------|
| Input       | วิบ (จากคลัทช์บอร์ด)                   |
| Process     | ทาสี → ปุลาย → ตัดเป็นแผ่นเล็ก          |
| Output      | วิป (กองปลายกะ → เช็คยอดเสีย)           |
| บันทึก      | จำนวนผลิต, ยอดเสีย                     |
| หมายเหตุ    | ของจะถูกกองไว้ปลายกะ แล้วค่อยเช็คยอดเสีย |

### สถานี 6 — แพ็ค (สำหรับสถานี 5)

| รายการ      | ค่า                                    |
|-------------|----------------------------------------|
| Input       | วิป (จากสถานี 5)                       |
| Process     | แพ็ค                                   |
| Output      | FG (Finished Goods)                    |
| บันทึก      | จำนวนแพ็ค, ยอดเสีย                     |

---

## Flow การทำงานหลัก (Core Process Flow)

ทุกสถานีใช้ flow เดียวกัน:

```
┌─────────────────────────────────────────────────────┐
│                   เข้ากะ / เปิดแอป                     │
│                        │                             │
│                 เลือกสถานี (หรือ auto จำ)              │
│                        │                             │
│              ┌─────────▼──────────┐                  │
│              │   หน้าบันทึกผลิต    │ ◄── หน้าหลัก      │
│              │                    │                  │
│              │  1. เลือก/สแกน     │                  │
│              │     วัตถุดิบเข้า     │                  │
│              │                    │                  │
│              │  2. ระบุจำนวนผลิต   │                  │
│              │                    │                  │
│              │  3. ระบุยอดเสีย     │                  │
│              │     (defect count) │                  │
│              │                    │                  │
│              │  4. ระบุยอด FT     │                  │
│              │     (ถ้ามี)         │                  │
│              │                    │                  │
│              │  5. กดบันทึก ✓     │                  │
│              └─────────┬──────────┘                  │
│                        │                             │
│                        ▼                             │
│              สถานะวัตถุดิบอัปเดต:                      │
│              input consumed → output created          │
│              (วิบ → วิป / เว็บ → วิป / วิป → FG)      │
│                        │                             │
│                 บันทึกถัดไป หรือ จบกะ                   │
└─────────────────────────────────────────────────────┘
```

### Flow พิเศษ — สถานี 5 (ทาสี/ปุลาย)

```
ทาสี → ปุลาย → ตัด → กองปลายกะ
                          │
                    ปลายกะ: เช็คยอดเสีย
                          │
                   ส่งไป สถานี 6 (แพ็ค)
                          │
                        FG ✓
```

### Flow พิเศษ — สถานี 3 (ตัดขาวผ่อง → FG)

```
ตัดเป็นแผ่นเล็ก → บันทึกยอดเสีย → แปะผัก (labeling) → FG ✓
```

---

## Data Model (ERD Overview)

### ตาราง Users (ใช้ตารางเดิมของโปรเจกต์หลัก)
```
users (existing — ไม่ต้องแก้)
└── เพิ่ม field ถ้าจำเป็น:
    ├── finishing_station_id (FK, nullable — default station ในฟินิชชิ่ง)
    └── finishing_role: operator | supervisor | admin (nullable)
    * หรือสร้างตาราง finishing_user_settings แยก
```

### ตาราง Finishing Stations
```
finishing_stations
├── id
├── name             — "ขาวผ่อง", "PVC ติดฟอยล์", etc.
├── code             — "ST1", "ST2", ... "ST6"
├── input_material   — ประเภทวัตถุดิบขาเข้า
├── output_material  — ประเภทผลผลิตขาออก
├── next_station_id  — FK → finishing_stations (nullable, ถ้ามีสถานีถัดไป)
├── is_final         — boolean (true = output เข้า FG โดยตรง)
└── timestamps
```

### ตาราง Finishing Materials (วัตถุดิบ / ชิ้นงาน)
```
finishing_materials
├── id
├── code             — รหัสวัตถุดิบ / lot
├── name             — ชื่อวัตถุดิบ
├── type             — วิบ | แผ่น PVC | เว็บ | วิป
├── status           — pending | in_process | completed | fg
├── station_id       — FK → finishing_stations (สถานีปัจจุบัน)
├── quantity         — จำนวนรวม
├── parent_id        — FK → finishing_materials (nullable, ต้นทางมาจากไหน)
└── timestamps
```

### ตาราง Production Logs (บันทึกการผลิต — หัวใจของระบบ)
```
finishing_production_logs
├── id
├── station_id       — FK → finishing_stations
├── user_id          — FK → users (คนบันทึก — ใช้ users เดิม)
├── material_id      — FK → finishing_materials (วัตถุดิบขาเข้า)
├── output_material_id — FK → finishing_materials (ผลผลิตขาออก, nullable)
├── shift_date       — วันที่กะ
├── shift            — morning | afternoon | night
├── quantity_in      — จำนวนรับเข้า
├── quantity_out     — จำนวนผลิตได้
├── defect_count     — ยอดเสีย
├── ft_count         — ยอด FT (nullable)
├── sub_type         — เคลือบครั้งเดียว | ติดฟอยล์ 2 รอบ (สถานี 2 เท่านั้น, nullable)
├── has_labeling     — boolean (แปะผัก — สถานี 3 เท่านั้น)
├── notes            — หมายเหตุ
├── status           — draft | confirmed | cancelled
└── timestamps
```

### ตาราง Defect Types (ประเภทของเสีย — optional, phase 2)
```
finishing_defect_types
├── id
├── station_id       — FK → finishing_stations (nullable = ใช้ได้ทุกสถานี)
├── name             — ชื่อประเภทของเสีย
└── timestamps
```

---

## API Endpoints (RESTful)

> ทุก route อยู่ภายใต้ prefix `/api/finishing/`
> Auth ใช้ระบบที่มีอยู่แล้วในโปรเจกต์หลัก (Sanctum)

### Stations
```
GET    /api/finishing/stations            — List all stations
GET    /api/finishing/stations/{id}       — Station detail
```

### Materials
```
GET    /api/finishing/materials                     — List materials (filter by station, status, type)
GET    /api/finishing/materials/{id}                — Material detail + history
POST   /api/finishing/materials                     — Create material (admin/supervisor)
PATCH  /api/finishing/materials/{id}/status         — Update status
```

### Production Logs (Core)
```
GET    /api/finishing/production-logs               — List logs (filter: station, date, shift, user)
POST   /api/finishing/production-logs               — Create new log entry ⭐ (main action)
GET    /api/finishing/production-logs/{id}          — Log detail
PATCH  /api/finishing/production-logs/{id}          — Update (draft only)
PATCH  /api/finishing/production-logs/{id}/confirm  — Confirm log entry
PATCH  /api/finishing/production-logs/{id}/cancel   — Cancel log entry
```

### Dashboard / Reports
```
GET    /api/finishing/dashboard/station/{id}        — Station summary (today/shift)
GET    /api/finishing/reports/daily                  — Daily production report
GET    /api/finishing/reports/defects               — Defect summary report
```

---

## หน้าจอ (Screens — Vue Pages)

### 1. Login
- ใช้ระบบ auth ที่มีอยู่แล้วในโปรเจกต์หลัก
- จำสถานีที่เคยเลือกไว้ (localStorage)

### 2. เลือกสถานี (`/finishing/select-station`)
- แสดง 6 สถานี เป็น cards
- กดเลือก → เข้าหน้าบันทึก
- ถ้ามี default station → skip ไปเลย

### 3. หน้าบันทึกผลิต (`/finishing/station/:id/record`) ⭐ หน้าหลัก
- **ส่วนบน:** ชื่อสถานี + กะปัจจุบัน + วันที่
- **ฟอร์ม:**
  - เลือกวัตถุดิบขาเข้า (dropdown / scan)
  - จำนวนรับเข้า
  - จำนวนผลิตได้
  - ยอดเสีย
  - ยอด FT (ถ้าสถานีนั้นมี)
  - ประเภทเคลือบ (สถานี 2 เท่านั้น)
  - แปะผัก checkbox (สถานี 3 เท่านั้น)
  - หมายเหตุ
- **ปุ่ม:** บันทึก (draft) → ยืนยัน (confirm)
- **ด้านล่าง:** รายการที่บันทึกไปแล้ววันนี้

### 4. ประวัติการบันทึก (`/finishing/station/:id/history`)
- Filter: วันที่, กะ, สถานะ
- แสดงรายการ production logs
- กดดูรายละเอียด / แก้ไข (ถ้ายัง draft)

### 5. Dashboard (`/finishing/dashboard`) — Phase 2
- สรุปยอดผลิตรายวัน/รายกะ ทุกสถานี
- ยอดเสียรวม
- Machine floor layout (แสดงสถานะเครื่อง on/off)

---

## Shift Logic (กะ)

| กะ        | ช่วงเวลา         | Code      |
|-----------|------------------|-----------|
| กะเช้า    | 08:00 - 17:00    | day       |
| กะดึก     | 17:00 - 08:00    | night     |

> กะจะถูกกำหนดอัตโนมัติจากเวลาที่บันทึก
> สามารถเปลี่ยนมือได้ถ้าจำเป็น

---

## Business Rules

1. **ยอดเสีย + ยอดผลิต ≤ ยอดรับเข้า** — validate ตอนบันทึก
2. **สถานะ flow:**
   - `draft` → operator บันทึกได้, แก้ไขได้
   - `confirmed` → ล็อค, แก้ไขไม่ได้ (ต้อง cancel แล้วสร้างใหม่)
   - `cancelled` → soft delete
3. **วัตถุดิบ auto-flow:**
   - เมื่อ confirm log → output material ถูกสร้างอัตโนมัติ
   - status ของ input material อัปเดตเป็น `completed`
   - output material มี `parent_id` ชี้กลับไปที่ input
4. **สถานี 2 (PVC):** ต้องเลือก sub_type (เคลือบครั้งเดียว/2 รอบ)
5. **สถานี 3 (ตัดขาวผ่อง):** ต้องติ๊ก has_labeling เมื่อแปะผักเสร็จ
6. **สถานี 5 (ทาสี):** บันทึกยอดเสียตอนปลายกะ (ไม่ใช่ real-time)
7. **FG:** สถานีที่ `is_final = true` → output status = `fg`

---

## Module Structure

> Module อยู่ที่ `Modules/Finishing/` ในโปรเจกต์ Laravel หลัก
> ใช้ pattern `nwidart/laravel-modules` หรือ manual module structure

```
Modules/Finishing/
├── app/
│   ├── Models/
│   │   ├── FinishingStation.php
│   │   ├── FinishingMaterial.php
│   │   └── ProductionLog.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── StationController.php
│   │   │   ├── MaterialController.php
│   │   │   ├── ProductionLogController.php
│   │   │   └── DashboardController.php
│   │   ├── Requests/
│   │   │   ├── StoreProductionLogRequest.php
│   │   │   └── UpdateProductionLogRequest.php
│   │   └── Resources/
│   │       ├── StationResource.php
│   │       ├── MaterialResource.php
│   │       └── ProductionLogResource.php
│   └── Services/
│       ├── ProductionService.php      — Core business logic
│       └── MaterialFlowService.php    — Auto-create output materials
├── config/
│   └── config.php                     — Module config (shifts, etc.)
├── database/
│   ├── migrations/
│   │   ├── create_finishing_stations_table.php
│   │   ├── create_finishing_materials_table.php
│   │   └── create_production_logs_table.php
│   └── seeders/
│       └── FinishingStationSeeder.php — Seed 6 stations
├── resources/
│   └── js/
│       ├── finishing-app.js           — Vue app entry point
│       ├── router/index.js
│       ├── stores/                    — Pinia stores
│       │   └── production.js
│       ├── pages/
│       │   ├── SelectStation.vue
│       │   ├── StationRecord.vue      — ⭐ Main recording page
│       │   ├── StationHistory.vue
│       │   └── Dashboard.vue
│       ├── components/
│       │   ├── ProductionForm.vue     — ⭐ Core form
│       │   ├── LogList.vue
│       │   ├── StationCard.vue
│       │   └── ShiftBadge.vue
│       └── composables/
│           ├── useShift.js            — Auto-detect shift
│           └── useStation.js          — Station config per type
├── routes/
│   ├── api.php                        — API routes (prefix: /api/finishing)
│   └── web.php                        — Web routes (prefix: /finishing)
├── Providers/
│   └── FinishingServiceProvider.php   — Register routes, migrations, config
└── module.json                        — Module metadata
```

### Integration กับโปรเจกต์หลัก

- **Auth:** ใช้ User model + Sanctum ที่มีอยู่แล้ว (ไม่ต้องสร้าง auth ใหม่)
- **DB:** ตาราง finishing_* เพิ่มเข้าไปใน DB เดิม (SQL Server)
- **Migration prefix:** `finishing_` เพื่อไม่ชนกับตารางอื่น
- **Route prefix:** `/api/finishing/` (API) และ `/finishing/` (Web/Vue SPA)
- **Vue:** Mount เป็น sub-app หรือ nested route ใน Vue router ของโปรเจกต์หลัก

---

## Phase Plan

### Phase 1 — Core Process (MVP) ✅ เป้าหมายแรก
- [ ] Module scaffolding (ServiceProvider, routes, config)
- [ ] DB migrations (finishing_stations, finishing_materials, production_logs)
- [ ] Station selection
- [ ] Production recording form (ทุกสถานี)
- [ ] Log list + history
- [ ] Material auto-flow (input → output)
- [ ] Shift auto-detection
- [ ] Mobile-responsive UI

### Phase 2 — Reports & Dashboard
- [ ] Daily production report
- [ ] Defect summary
- [ ] Station dashboard (today's numbers)

### Phase 3 — Advanced
- [ ] Machine floor layout (เครื่อง on/off)
- [ ] Defect type categorization
- [ ] Barcode/QR scanning for materials
- [ ] Notification (ยอดเสียสูง)
- [ ] Export to Excel

---

## วิธี Setup Module สำหรับ Agentic

```bash
# 1. Install laravel-modules (ถ้ายังไม่มี)
composer require nwidart/laravel-modules

# 2. Create module
php artisan module:make Finishing

# 3. สร้างไฟล์ตาม Module Structure ด้านบน
#    - Models, Controllers, Requests, Resources, Services
#    - Migrations, Seeders
#    - Vue pages + components

# 4. Run module migrations
php artisan module:migrate Finishing

# 5. Seed stations
php artisan module:seed Finishing

# 6. Register Vue routes ใน frontend
#    - import finishing routes ใน main router
#    - หรือ mount เป็น sub-app

# 7. Dev server (ใช้ของโปรเจกต์หลัก)
composer run dev
```

### หมายเหตุ
- ถ้าไม่ใช้ `nwidart/laravel-modules` → สร้าง `Modules/Finishing/` manual
  แล้ว register `FinishingServiceProvider` ใน `config/app.php`
- DB ใช้ SQL Server ที่มีอยู่แล้ว — ตารางใหม่ prefix ด้วย `finishing_`
- Auth ใช้ Sanctum + User model ของโปรเจกต์หลัก — ไม่ต้องสร้างใหม่
