# แผน Refactor + อัปเกรด Laravel 5.8 → 13

> ใช้ไฟล์นี้เป็น "สัญญา" ระหว่างเรากับ AI ตอน prompt ทีละ module
> ทุก module ต้องทำตามกติกาในหัวข้อ 4 และเช็ค Definition of Done ในหัวข้อ 9 ก่อนปิดงาน

---

## สารบัญ

1. [สภาพโปรเจคปัจจุบัน](#1-สภาพโปรเจคปัจจุบัน)
2. [ปัญหาที่เจอ (ต้องแก้ระหว่าง refactor)](#2-ปัญหาที่เจอ-ต้องแก้ระหว่าง-refactor)
3. [โครงสร้างเป้าหมาย Laravel 13](#3-โครงสร้างเป้าหมาย-laravel-13)
4. [กติกาการเขียนโค้ด + ตัวอย่างก่อน/หลัง](#4-กติกาการเขียนโค้ด--ตัวอย่างก่อนหลัง)
5. [Package: ของเดิม → ของใหม่](#5-package-ของเดิม--ของใหม่)
6. [Breaking changes 5.8 → 13 ที่กระทบโปรเจคนี้](#6-breaking-changes-58--13-ที่กระทบโปรเจคนี้)
7. [แผนที่ Module (ลำดับการทำ)](#7-แผนที่-module-ลำดับการทำ)
8. [Model mapping: เดิม → ใหม่](#8-model-mapping-เดิม--ใหม่)
9. [Prompt template + Definition of Done](#9-prompt-template--definition-of-done)
10. [โค้ดที่ไม่ต้องย้าย (dead code)](#10-โค้ดที่ไม่ต้องย้าย-dead-code)
11. [เรื่องที่ต้องตัดสินใจ/ตรวจสอบก่อนเริ่ม](#11-เรื่องที่ต้องตัดสินใจตรวจสอบก่อนเริ่ม)

---

## 1. สภาพโปรเจคปัจจุบัน

| รายการ | ค่า |
|---|---|
| Laravel | 5.8.38 |
| PHP (composer.json) | ^7.1.3 (Laragon มี 7.4.33 และ 8.3.16) |
| Database | SQL Server หลายตัว (`sqlsrv`, `sqlsrv_bplus`, `sqlsrv_pklocal`, `sqlsrv_ps`, `sqlsrv_bplus_2`) |
| Controllers | 30 ไฟล์ — 4 ไฟล์ใหญ่รวมกัน ~10,800 บรรทัด |
| Models | 80 ไฟล์ อยู่ที่ `app/` ตรงๆ (ไม่มี `app/Models`) |
| Routes | 1 ไฟล์ `routes/route.php` ~290 route |
| Views | 166 ไฟล์ blade (~25,000 บรรทัด) |
| Tests | ไม่มี |
| Document root | **ตัวโปรเจคเลย** (`index.php` อยู่ root, ไม่มีโฟลเดอร์ `public/`) |

### Controller ขนาดใหญ่ (ตัวที่ต้องแตก)

| Controller | บรรทัด | public fn | `DB::` | raw SQL | หมายเหตุ |
|---|---:|---:|---:|---:|---|
| `QualityControlController` | 3,547 | 68 | 57 | 49 | WIP/FG/NG/พนักงาน/Tag/CSV รวมกันหมด |
| `WarehouseController` | 3,385 | 71 | 248 | 158 | โอนย้าย/ตัดสต็อก/WPS/forklift/CSV |
| `PruksaController` | 1,967 | 39 | 61 | 67 | C-line/rim/parcel/BOQ/report/LINE |
| `WipController` | 1,630 | 44 | 25 | 19 | **ไม่มี route ชี้ → dead code** |
| `BillofladingController` | 979 | 12 | 52 | 32 | |
| `TransferCalenderController` | 956 | 16 | 17 | 26 | |
| `EndLineQcController` | 745 | 13 | 3 | 0 | เขียนค่อนข้างดีแล้ว |

---

## 2. ปัญหาที่เจอ (ต้องแก้ระหว่าง refactor)

### 2.1 โค้ดซ้ำ

- `WarehouseController::selectbp()` กับ `selectby()` **เหมือนกันเกือบทุกบรรทัด**
- query BPLUS แบบ `DOCINFO JOIN TRANSTKH JOIN TRANSTKD JOIN SKUMASTER + whereDate(-60 วัน) + DI_REF LIKE 'XX%' + DI_ACTIVE = 0` copy-paste หลายสิบครั้ง
- `checkdbproductionpath()`, `checkqueryproductionpath()`, `checkconnectionproduction()` ซ้ำใน 4 controller (Warehouse, Pruksa, Billoflading, TransferCalender)
- lookup array (`productsize()`, `thickness()`, `typeofproduct()`, `boarder()`, `thaimonth()`, `namecolor()`) ซ้ำใน `QualityControlController`, `WipController`, `WarehouseController`
- `checkline('L1')` → `'ไลน์ 1'` ด้วย if/elseif
- `date_default_timezone_set("Asia/Bangkok")` กระจายอยู่ ~20 ที่ (ควรตั้งครั้งเดียวที่ `config/app.php`)
- ตัวแปร `$countxx = 1` ส่งไป view เพื่อทำเลขลำดับ → ใช้ `$loop->iteration` ใน Blade แทนได้

### 2.2 ความปลอดภัย (ต้องแก้แน่นอน)

| ปัญหา | ที่เจอ | วิธีแก้ |
|---|---|---|
| **SQL Injection** — เอาตัวแปรจาก URL ใส่ SQL ตรงๆ เช่น `"... where DI_KEY = '$DI_KEY'"` | ~56 จุด (Warehouse, QC, Pruksa ฯลฯ) | ใช้ Eloquent/Query Builder หรือ binding `?` |
| LINE token เขียนตรงในโค้ด | `app/Console/Commands/*`, `PruksaController.php:635` | ย้ายไป `.env` |
| รหัสผ่าน DB เป็นค่า default ใน `config/database.php` | connection `sqlsrv_bplus` ฯลฯ | ลบ default ออก ให้อ่านจาก `.env` อย่างเดียว |
| สิทธิ์เช็คด้วยชื่อ user ตรงๆ (`Auth::user()->name == 'Manager'`) | controller + view หลายสิบจุด | Gate/Policy (หัวข้อ 4.6) |
| **Bug**: `select_function.blade.php:78` ใช้ `=` แทน `==` (`Auth::user()->username = 'adminr'`) | เงื่อนไขเป็นจริงเสมอ → เมนูโชว์ให้ทุกคน | แก้ตอนย้าย module Core |
| route ไม่มี middleware `auth` | `/usersetting`, `/adduser`, `/adjustuser`, `/bplusdb`, `/testso/*`, `/checkconnection`, `/gencode`, `showname/{id}` | ใส่ใน group `auth` |
| Document root = root โปรเจค | `.env`, `composer.lock`, `csvbackup/*.csv`, `phpinfo.php` อาจเปิดผ่านเว็บได้ | Laravel 13 ใช้ `public/` เป็น docroot |
| `env()` เรียกใน controller | 4 controller | หลัง `config:cache` จะได้ `null` → ใช้ `config()` |

### 2.3 Raw SQL ที่จะพังใน Laravel 10+

- `DB::select(DB::raw($sql))` — 22 จุด (Warehouse 16, QC 6) → Laravel 10+ ต้องเป็น `DB::select($sql, $bindings)`

### 2.4 Model ไม่ได้ใช้ความสามารถของ Eloquent

- 80 model มี relationship รวมกันแค่ ~30 ตัว, scope แทบไม่มี (มีแค่ `EndLineQcHeader`)
- ไม่มี `$casts` (วันที่เป็น string), ไม่ระบุ `$timestamps = false` ทั้งที่หลายตารางไม่มี `created_at`
- model บางตัวผิด connection: `AllOrder`, `SOTABLE` (ตาราง `DOCINFO`) และ `WareLocation` (`WARELOCATION`) ใช้ connection default ทั้งที่เป็นตาราง BPLUS
- controller join เองทุกครั้งแทนที่จะใช้ relationship

### 2.5 อื่นๆ

- **LINE Notify ปิดให้บริการแล้ว (31 มี.ค. 2025)** → `notification:pruksa`, `notification:pruksasp`, `CheckFgtoWh` และ `PruksaController::pklinenotification()` ส่งไม่ได้แล้ว ต้องเปลี่ยนเป็น LINE Messaging API หรือช่องทางอื่น
- `EndCsvExport` เขียนแบบ Excel v3 แต่ติดตั้ง `maatwebsite/excel` 2.1 → route `/csvexport` ใช้ไม่ได้อยู่แล้ว
- route `pruksahomeframerim`, `pruksaaddrim` ชี้ไป `FramerimController` ที่**ไม่มีไฟล์**
- `Route::resource('/search', 'QualityControlController@search')` ใช้ resource ผิดวิธี
- route `/qc-end-line/{line}/{id}/export` เป็น stub คืนค่าว่าง
- comment code ทิ้งไว้ ~1,200 บรรทัด (Warehouse 728, QC 405)
- เขียน CSV ด้วย `fopen/fputcsv` ลงโฟลเดอร์ `csvbackup/` ใน docroot

---

## 3. โครงสร้างเป้าหมาย Laravel 13

**หลักการ:** แยกตาม module ให้หาไฟล์ง่าย, ไม่ใช้ Repository pattern (เกินความจำเป็นสำหรับโปรเจคนี้), logic ที่ซ้ำย้ายไป Model (scope/relationship) ก่อน ถ้าซับซ้อนข้ามหลาย model ค่อยย้ายไป Service

```
app/
├── Enums/                         # ค่าคงที่ที่มีชุดจำกัด
│   ├── ProductionLine.php         # L1..L4 + label 'ไลน์ 1'
│   ├── WorkStatus.php             # W / E ...
│   └── QcStatus.php               # draft / in_process / success / cancel
├── Http/
│   ├── Controllers/
│   │   ├── Controller.php
│   │   ├── Auth/LoginController.php
│   │   ├── HomeController.php
│   │   ├── Master/                # 8 master CRUD
│   │   ├── Production/            # เดิม QualityControlController
│   │   │   ├── WorkShiftController.php
│   │   │   ├── EmployeeController.php
│   │   │   ├── WipController.php
│   │   │   ├── FgOutController.php
│   │   │   ├── NgController.php
│   │   │   ├── TagController.php
│   │   │   └── ExportController.php
│   │   ├── QcProcess/             # QcProcessGoods
│   │   ├── EndLineQc/             # EndLineQc + Reports
│   │   ├── Warehouse/             # เดิม WarehouseController
│   │   │   ├── DocumentController.php
│   │   │   ├── CutStockController.php
│   │   │   ├── TransferController.php
│   │   │   ├── ReceiveController.php
│   │   │   ├── CsvCheckController.php
│   │   │   └── Wps/ (LocationController, ForkliftController, ...)
│   │   ├── BillOfLading/
│   │   ├── TransferCalendar/
│   │   ├── Pruksa/
│   │   └── Api/
│   └── Requests/                  # Form Request (validation) แยกตาม module
├── Models/
│   ├── User.php
│   ├── Bplus/                     # ตาราง ERP BPLUS (read-only เป็นหลัก)
│   │   ├── BplusModel.php         # base: connection + $timestamps = false
│   │   ├── Docinfo.php
│   │   ├── SkuMaster.php
│   │   └── ...
│   ├── Master/
│   ├── Production/
│   ├── QcProcess/
│   ├── EndLineQc/
│   ├── Warehouse/
│   ├── Wps/
│   ├── BillOfLading/
│   └── Pruksa/
├── Services/                      # เฉพาะ logic ที่ซับซ้อน/ข้าม model
│   ├── Csv/CsvExporter.php        # แทน fopen/fputcsv ที่ซ้ำ ~100 จุด
│   ├── Notification/LineMessenger.php
│   └── Bplus/BplusTable.php       # จัดการ prefix linked server
├── Policies/ (ถ้าจำเป็น)
├── Providers/AppServiceProvider.php   # Gate, Paginator::useBootstrapFour()
└── Support/ThaiDate.php           # เดิม app/Helpers/ThaiDate.php

config/
├── bplus.php        # connection + table prefix (แทน checkdbproductionpath)
├── product.php      # size/thickness/type/border code → label
├── permissions.php  # role → รายชื่อ username (ระยะแรก)
└── line.php         # LINE token/channel

routes/
├── web.php          # require ไฟล์ด้านล่าง
├── web/
│   ├── core.php
│   ├── master.php
│   ├── production.php
│   ├── qc-process.php
│   ├── end-line-qc.php
│   ├── warehouse.php
│   ├── bill-of-lading.php
│   ├── transfer-calendar.php
│   └── pruksa.php
├── api.php
└── console.php      # schedule (แทน app/Console/Kernel.php)

resources/views/
├── layouts/         # layout เดียว (รวม layout/, layouts/, frontend/)
├── components/      # blade component ที่ใช้ซ้ำ (table, modal, tag)
├── master/
├── production/
├── warehouse/
├── pruksa/
└── ...              # โฟลเดอร์ตรงกับชื่อ module

public/              # docroot ใหม่ — ย้าย css/ js/ img/ fonts/ มาที่นี่
```

---

## 4. กติกาการเขียนโค้ด + ตัวอย่างก่อน/หลัง

### 4.1 กติกาหลัก (ทุก module)

1. **ผลลัพธ์ต้องเหมือนเดิม** — refactor ห้ามเปลี่ยน behavior ถ้าเจอ bug ให้จดไว้ใน PR/สรุป แล้วแก้แยกเป็นข้อชัดเจน
2. **ระยะแรกคง URI และชื่อ route เดิม** (`->name('transfer_input')` ฯลฯ) เพื่อให้ blade/JS ที่เรียก `route('...')` ยังใช้ได้ ค่อยเปลี่ยนชื่อทีหลัง
3. Controller ละไม่เกิน ~10 method, method ละไม่ควรเกิน ~40 บรรทัด
4. ห้ามต่อ string SQL ด้วยตัวแปร — ใช้ Eloquent/Query Builder หรือ binding
5. ห้ามเรียก `env()` นอก `config/`
6. ห้ามเช็คสิทธิ์ด้วย `Auth::user()->name == '...'` — ใช้ `Gate`/`@can`
7. Validation ใช้ `$request->validate()` หรือ Form Request
8. ลบ comment code เก่าทิ้ง (อยู่ใน git history แล้ว)
9. ใช้ `use Illuminate\Support\Facades\DB;` แทน alias `use DB;`
10. ชื่อ class/method ภาษาอังกฤษ camelCase, comment ภาษาไทยได้

### 4.2 Model — ใส่ข้อมูลตารางให้ครบ

```php
// ก่อน: app/WipWorktime.php
class WipWorktime extends Model
{
    protected $table = 'wip_worktime';
    protected $primaryKey = 'wwt_id';
}

// หลัง: app/Models/Production/WipWorktime.php
namespace App\Models\Production;

use App\Enums\ProductionLine;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WipWorktime extends Model
{
    protected $table = 'wip_worktime';
    protected $primaryKey = 'wwt_id';
    protected $guarded = ['wwt_id'];

    protected function casts(): array
    {
        return [
            'wwt_date' => 'datetime',
            'wwt_line' => ProductionLine::class,
        ];
    }

    public function workings(): HasMany
    {
        return $this->hasMany(WipWorking::class, 'ww_wwt_index', 'wwt_index');
    }

    public function scopeLine(Builder $query, ProductionLine|string $line): Builder
    {
        return $query->where('wwt_line', $line instanceof ProductionLine ? $line->value : $line);
    }
}
```

### 4.3 BPLUS — รวม query ที่ซ้ำไว้ใน scope

```php
// app/Models/Bplus/BplusModel.php
namespace App\Models\Bplus;

use Illuminate\Database\Eloquent\Model;

abstract class BplusModel extends Model
{
    public $timestamps = false;
    protected $guarded = [];

    public function getConnectionName()
    {
        return config('bplus.connection');
    }
}
```

```php
// app/Models/Bplus/Docinfo.php
namespace App\Models\Bplus;

use Illuminate\Database\Eloquent\Builder;

class Docinfo extends BplusModel
{
    protected $table = 'DOCINFO';
    protected $primaryKey = 'DI_KEY';

    public function scopeRecent(Builder $q, int $days = 60): Builder
    {
        return $q->whereDate('DI_CRE_DATE', '>=', now()->subDays($days));
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('DI_ACTIVE', 0);
    }

    public function scopeRefPrefix(Builder $q, string $prefix): Builder
    {
        return $q->where('DI_REF', 'like', $prefix.'%');
    }

    /** เอกสารขาย (SO/SB/SE) join ลูกค้า */
    public function scopeSalesDocs(Builder $q, string $prefix): Builder
    {
        return $q->join('AROE', 'DOCINFO.DI_KEY', '=', 'AROE.AROE_DI')
            ->join('ARFILE', 'AROE.AROE_AR', '=', 'ARFILE.AR_KEY')
            ->recent()->refPrefix($prefix)
            ->orderByDesc('DI_CRE_DATE');
    }

    /** เอกสารโอนสินค้าระหว่างคลัง (RC/RN/FN/FC/RA) */
    public function scopeTransferDocs(Builder $q, string $prefix, ?int $fromWl = null, ?int $toWl = null): Builder
    {
        return $q->select('DOCINFO.DI_REF', 'DOCINFO.DI_KEY', 'DOCINFO.DI_REMARK')
            ->join('TRANSTKH', 'DOCINFO.DI_KEY', '=', 'TRANSTKH.TRH_DI')
            ->join('TRANSTKD', 'TRANSTKD.TRD_TRH', '=', 'TRANSTKH.TRH_KEY')
            ->join('SKUMASTER', 'TRANSTKD.TRD_SKU', '=', 'SKUMASTER.SKU_KEY')
            ->recent()->active()->refPrefix($prefix)
            ->when($fromWl, fn ($q) => $q->where('TRANSTKD.TRD_WL', $fromWl))
            ->when($toWl, fn ($q) => $q->where('TRANSTKD.TRD_TO_WL', $toWl))
            ->groupBy('DOCINFO.DI_REF', 'DOCINFO.DI_KEY', 'DOCINFO.DI_REMARK');
    }
}
```

```php
// ก่อน: WarehouseController::selectbp() ~95 บรรทัด และ selectby() ซ้ำอีกชุด
// หลัง: app/Http/Controllers/Warehouse/DocumentController.php
public function selectBp()
{
    return view('warehouse.select-bp', [
        'sb'    => Docinfo::salesDocs('SB')->active()->get(),
        'rctoa' => Docinfo::transferDocs('RC', WarehouseLocation::BP, WarehouseLocation::TOA)->get(),
        'rcnw'  => Docinfo::transferDocs('RC', WarehouseLocation::BP, WarehouseLocation::NW)->get(),
        'fn'    => Docinfo::transferDocs('FN', toWl: 2034)->get(),
        'ra'    => Docinfo::transferDocs('RA')->get(),
    ]);
}
```

> ⚠️ query บางตัวเดิม**ไม่มี** `groupBy`/`select` (เช่น `$rcnw`, `$rntotoa`) → view อาจใช้คอลัมน์อื่น ต้องเช็ค view ก่อนใช้ scope ร่วม
> รหัสคลัง (1, 101, 2511, 2033, 2034) ใส่ใน `config/bplus.php` หรือ Enum พร้อมชื่อ — ชื่อ NW/BP/TOA ข้างบนเดาจากชื่อตัวแปร ต้องยืนยันอีกครั้ง

### 4.4 Linked server prefix — รวมไว้ที่ config เดียว

```php
// config/bplus.php
return [
    // production: sqlsrv_bplus, dev: sqlsrv (ผ่าน linked server)
    'connection'   => env('BPLUS_CONNECTION', 'sqlsrv_bplus'),
    // dev: 'BPLUS.GypmantechNew3.dbo.'  production: ''
    'table_prefix' => env('BPLUS_TABLE_PREFIX', ''),
    // production: 'gypman-tech.dbo.'  dev: ''
    'local_prefix' => env('LOCAL_DB_PREFIX', ''),
];
```

ใช้แทน `checkdbproductionpath()` / `checkqueryproductionpath()` / `checkconnectionproduction()` ทั้ง 4 controller

### 4.5 Raw SQL → Eloquent / binding

```php
// ก่อน (SQL injection + พังใน Laravel 10+)
$sql = "select * from fg_barcode_action where DI_KEY = '$DI_KEY'";
$barcodecutstock = DB::select(DB::raw($sql));

// หลัง
$barcodeCutStock = FgBarcodeAction::where('DI_KEY', $diKey)->get();

// ถ้า SQL ซับซ้อนจริงๆ ยังเขียน raw ได้ แต่ต้อง binding
$rows = DB::select('SELECT ... WHERE wwt_id = ? AND ww_line = ?', [$wwtId, $line]);
```

### 4.6 สิทธิ์ผู้ใช้ — Gate จาก config (ไม่ต้องแก้ DB)

```php
// config/permissions.php
return [
    'manager'     => ['Manager', 'arnut', 'r'],
    'pruksa_out'  => ['PLAE', 'PKEY', 'PDIA', 'PICE', 'PBRD', 'PTOP', 'PKOO'],
    'pruksa_admin'=> ['AdminPK', 'PKOO', 'PNAADMIN', 'PRUNGADMIN'],
    'warehouse'   => ['warehouse01', 'warehouse02', 'warehouse03', 'warehouse04', 'Manager'],
    'forklift'    => ['forklift01', 'forklift02', 'forklift03', 'forklift04', 'forklift05'],
];

// AppServiceProvider::boot()
foreach (config('permissions') as $ability => $names) {
    Gate::define($ability, fn (User $user) => in_array($user->name, $names, true));
}
```

```blade
{{-- ก่อน --}}
@if (Auth::user()->name == 'Manager' || Auth::user()->name == 'arnut' || Auth::user()->name == 'r')
{{-- หลัง --}}
@can('manager')
```

ระยะยาวค่อยเพิ่มคอลัมน์ `role` ในตาราง `users`

### 4.7 Lookup / ค่าคงที่ → Enum หรือ config

```php
// app/Enums/ProductionLine.php — แทน checkline()
enum ProductionLine: string
{
    case L1 = 'L1';
    case L2 = 'L2';
    case L3 = 'L3';
    case L4 = 'L4';

    public function label(): string
    {
        return 'ไลน์ '.substr($this->value, 1);
    }
}

// route: Route::get('/selectemp/{line}', ...) + type-hint ProductionLine $line
// → ถ้าส่ง L9 มาจะได้ 404 อัตโนมัติ
```

```php
// config/product.php — ย้าย array จาก productsize()/thickness()/typeofproduct()/boarder() มาตรงๆ
return [
    'sizes'      => ['01' => '1.2 x 2.4 m.', '02' => '1.22 x 2.44 m.', /* ... */],
    'thicknesses'=> ['09' => '9 mm.', /* ... */],
    'types'      => ['1' => '-', '2' => 'เกรด B', /* ... */],
    'borders'    => ['A' => 'ขอบลาด', 'B' => 'ขอบเรียบ', '0' => '-'],
];
// ใช้: config('product.sizes')[$code] ?? $code
```

> มีตาราง `master_sizes`, `master_thicknesses` อยู่แล้ว (module End-line QC) — ระยะยาวควรรวมให้ใช้ตาราง master ชุดเดียว แต่รหัสอาจไม่ตรงกัน ต้องเช็คก่อน

### 4.8 Routes — แยกไฟล์ + group

```php
// ก่อน
Route::get('/transfer', 'WarehouseController@transfer')->name('transfer');
Route::post('/transfer_input', 'WarehouseController@transfer_input')->name('transfer_input');
Route::delete('/transfer_delete/{id}', 'WarehouseController@transfer_delete')->name('transfer_delete');

// หลัง: routes/web/warehouse.php
use App\Http\Controllers\Warehouse\TransferController;

Route::middleware('auth')->group(function () {
    Route::controller(TransferController::class)->group(function () {
        Route::get('/transfer', 'index')->name('transfer');
        Route::post('/transfer_input', 'store')->name('transfer_input');
        Route::delete('/transfer_delete/{id}', 'destroy')->name('transfer_delete');
    });
});
```

### 4.9 CSV export — helper ตัวเดียว

```php
// app/Services/Csv/CsvExporter.php
class CsvExporter
{
    public static function download(string $filename, array $header, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM ให้ Excel อ่านภาษาไทย
            fputcsv($out, $header);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
```

> ⚠️ CSV บางตัวถูก import เข้า BPLUS — format (encoding, ลำดับคอลัมน์, มี/ไม่มี BOM, header) ต้องเหมือนเดิมเป๊ะ เทียบไฟล์เก่า/ใหม่ด้วย diff ก่อนปิดงาน
> ไฟล์ backup (`csvbackup/`) ให้เขียนลง `storage/app/csvbackup` แทน docroot

### 4.10 Master CRUD — ลดโค้ดซ้ำ 8 controller

controller master 8 ตัวโครงเหมือนกันหมด (index/create/store/edit/update/destroy) ต่างกันแค่ model, view, rules →
ใช้ `Route::resource()` + abstract `MasterCrudController` ที่รับ `$model`, `$viewPath`, `rules()` แล้ว controller ลูกเหลือ ~15 บรรทัด
(view ก็รวมเป็นชุดเดียวที่รับ field list ได้ ถ้าหน้าตาเหมือนกัน)

---

## 5. Package: ของเดิม → ของใหม่

| Package เดิม (version ที่ติดตั้ง) | ใช้ที่ไหน | Laravel 13 |
|---|---|---|
| `laravel/framework` 5.8.38 | — | `^13.0` |
| `fideloper/proxy` | TrustProxies | **ลบ** — มีใน core แล้ว (`bootstrap/app.php`) |
| `laravelcollective/html` 5.8 | ไม่พบการใช้ `Form::`/`Html::` ใน view | **ลบ** |
| `maatwebsite/excel` 2.1.30 | `EndCsvExport` (พังอยู่แล้ว) | **ลบ** ใช้ `CsvExporter` / ถ้าต้องการ xlsx ใช้ `^3.1` |
| `phpoffice/phpspreadsheet` 1.18 | ไม่พบการใช้ตรงๆ | **ลบ** (มากับ excel ถ้าติดตั้ง) |
| `yajra/laravel-datatables-oracle` 9.x | ไม่พบการใช้ใน controller | **ลบ** (DataTables ฝั่ง JS ใช้ต่อได้) |
| `uxweb/sweet-alert` 2.0 | Warehouse, QC, Home, Billoflading, TransferCalendar | **ลบ** → session flash + SweetAlert2 (JS) ใน layout |
| `maddhatter/laravel-fullcalendar` 1.3 | Pruksa, TransferCalendar (`Calendar::`) | **ลบ** → FullCalendar JS + endpoint คืน JSON |
| `royalmar/laravel-line-notify` 1.0 | config เท่านั้น (โค้ดจริงใช้ curl) | **ลบ** — LINE Notify ปิดแล้ว → LINE Messaging API ผ่าน `Http::` |
| `alfa6661/laravel-autonumber` 1.2 | `LotNumber` model | **ลบ** → เขียนเลข running เอง (ดู logic เดิมก่อน) |
| `jenssegers/agent` 2.6 | `PruksaController` (`isMobile`) | ตรวจ compat L13; ถ้าไม่ได้ใช้ `mobiledetect/mobiledetectlib` ตรง หรือ responsive CSS |
| `milon/barcode` 6.0 | 7 view (`DNS1D`/`DNS2D`) | อัปเป็นเวอร์ชันที่รองรับ L13 (เช็คบน Packagist) |
| `picqer/php-barcode-generator` 0.3 | `BarcodeGenController` | `^3.x` (ไม่ผูก Laravel) หรือใช้ milon ตัวเดียว |
| `simplesoftwareio/simple-qrcode` 4.2 | 6 view | เช็คเวอร์ชันที่รองรับ L13 |
| `snapappointments/bootstrap-select` | เป็น JS lib แต่ติดตั้งผ่าน composer | ย้ายไป `public/` หรือ npm |
| `laravel/tinker` ^1.0 | — | `^2.x` |
| `beyondcode/laravel-dump-server`, `fzaninotto/faker`, `phpunit ^7.5`, `nunomaduro/collision ^3` | dev | ใช้ของที่มากับ skeleton L13 (`fakerphp/faker`, Pest/PHPUnit ใหม่) |
| `laravel-mix` 4 (npm) | assets | Vite (หรือเก็บ static ไฟล์ไว้ใน `public/` ไปก่อนก็ได้) |

---

## 6. Breaking changes 5.8 → 13 ที่กระทบโปรเจคนี้

> รายการนี้เฉพาะที่เจอในโค้ดจริง — อ่าน upgrade guide แต่ละเวอร์ชัน (6→…→13) ประกอบ

| เรื่อง | ต้องทำอะไร |
|---|---|
| PHP ขั้นต่ำ 8.3 | ใช้ PHP 8.3.16 ของ Laragon + ติดตั้ง `pdo_sqlsrv`/`sqlsrv` ที่ตรงกับ PHP 8.3 |
| SQL Server ขั้นต่ำ 2017 (ตั้งแต่ L11) | **เช็คเวอร์ชัน SQL Server ทุกตัว** โดยเฉพาะ BPLUS (`103.40.144.248`) และ `10.8.240.231` |
| โครงสร้างแบบ slim (L11+) | ไม่มี `Http/Kernel.php`, `Console/Kernel.php`, `RouteServiceProvider` → ตั้งค่าใน `bootstrap/app.php`, schedule ไป `routes/console.php` |
| Model อยู่ `App\Models` | ย้าย + แก้ namespace ทุก `use App\Xxx;` |
| Route แบบ string `'Controller@method'` (namespace prefix ถูกเอาออกตั้งแต่ L8) | ใช้ `[Controller::class, 'method']` หรือ `Route::controller()` |
| `DB::select(DB::raw($sql))` (L10) | ส่ง string ตรง + bindings |
| `Input` facade ถูกลบ (L6) | `routes/route.php` import อยู่ → ลบ |
| `str_*` / `array_*` helper ถูกลบ (L6) | ใช้ `Str::` / `Arr::` (เจอแค่ `str_starts_with` ซึ่งเป็น native PHP 8 ใช้ได้) |
| `Auth::routes()` ไม่อยู่ใน core (L6+) | เขียน `LoginController` เองแบบสั้น (login ด้วย `username` ไม่ใช่ email) |
| Pagination default เป็น Tailwind (L8) | `Paginator::useBootstrapFour()` ใน `AppServiceProvider` (view ใช้ Bootstrap 4) |
| `$dates` property ถูกลบ (L10) | ใช้ `casts()` |
| Carbon 3 (L11+) | `diffInDays()` ฯลฯ คืนค่า float และติดลบได้ → เช็คทุกจุดที่คำนวณวัน/เวลา |
| ค่า default ของ skeleton ใหม่: `SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database` | ถ้าไม่อยากสร้างตารางใหม่ใน DB production ให้ตั้งเป็น `file` / `sync` |
| ตาราง `password_resets` → `password_reset_tokens` (L10) | ถ้าใช้ reset password ต้องสร้างตารางใหม่หรือตั้งชื่อใน `config/auth.php` |
| Timezone | `config/app.php` → `'timezone' => 'Asia/Bangkok'` แล้วลบ `date_default_timezone_set` ทั้งหมด |

---

## 7. แผนที่ Module (ลำดับการทำ)

ลำดับ: ง่าย → ยาก และ module ที่เป็นเจ้าของตารางทำก่อน module ที่อ่านตารางนั้น

| # | Module | Controller เดิม | Route | ความยาก |
|---|---|---|---:|---|
| M0 | Core / Foundation | Home, Index, User, Auth | ~13 | ⭐⭐ |
| M1 | Master Data | Master* (8 ตัว) | 48 | ⭐ |
| M2 | End-line QC | EndLineQc, EndLineReports | 19 | ⭐⭐ |
| M3 | QC Process Goods | QcProcessGoods | 10 | ⭐⭐ |
| M4 | Production (WIP/FG) | QualityControl | 55 | ⭐⭐⭐⭐⭐ |
| M5 | Warehouse + WPS | Warehouse | 62 | ⭐⭐⭐⭐⭐ |
| M6 | Bill of Lading | Billoflading | 9 | ⭐⭐⭐ |
| M7 | Transfer Calendar | TransferCalender | 9 | ⭐⭐⭐ |
| M8 | Pruksa | Pruksa, PruksaPo | 40 | ⭐⭐⭐⭐ |
| M9 | Console + Notification | Commands/* (3 ตัว) | — | ⭐⭐ |
| M10 | API | APIController | 2 | ⭐ |

---

### M0 — Core / Foundation

**ทำก่อนทุกอย่าง:** สร้างโปรเจค L13, ตั้งค่า DB, layout, login, เมนูหลัก, Gate

- **ไฟล์เดิม:** `HomeController`, `IndexController`, `UserController`, `Auth/*`, `app/Helpers/ThaiDate.php`, `config/database.php`, `resources/views/layout*`, `frontend/`, `select_function.blade.php`, `select_line.blade.php`
- **งาน:**
  - `composer create-project laravel/laravel` ในโฟลเดอร์ใหม่
  - copy connection ทั้ง 6 ตัวไป `config/database.php` (เอา default รหัสผ่านออก)
  - สร้าง `config/bplus.php`, `config/permissions.php`, `config/product.php`
  - Login ด้วย `username` (ตาราง `users` เดิม — ห้าม migrate ทับ)
  - ย้าย static assets (`css/ js/ img/ fonts/ AdminLTE-master/`) ไป `public/`
  - รวม layout เป็นชุดเดียว + SweetAlert2 แสดง flash message
  - `HomeController::selectfunction()` → redirect ตามสิทธิ์ด้วย Gate
  - แก้ bug `select_function.blade.php:78`
- **ไม่ต้องย้าย:** `testso`, `bplusdb`, `checkconnection`, `connectdbplus`, `bplustestview`, `printwipl1a` (ใช้ model `Wipline1` ที่ไม่มีอยู่จริง)

### M1 — Master Data

- **ไฟล์เดิม:** `MasterBrandController`, `MasterEdgeController`, `MasterFaceController`, `MasterProductGroupController`, `MasterProductSpecController`, `MasterSheetTypeController`, `MasterSizeController`, `MasterThicknessController`
- **Model:** `MasterBrand`, `MasterEdge`, `MasterFace`, `MasterProductGroup`, `MasterProductSpec`, `MasterSheetType`, `MasterSize`, `MasterThickness`
- **Views:** `master-*/`
- **เป้าหมาย:** `Route::resource` + `MasterCrudController` (หัวข้อ 4.10) — ใช้เป็น module วอร์มอัพเพื่อลองโครงสร้างใหม่

### M2 — End-line QC

- **ไฟล์เดิม:** `EndLineQcController` (745), `EndLineReportsController` (251), `app/Exports/WipFgReportExport.php`
- **Model:** `EndLineQcHeader` (มี scope/relationship แล้ว), `EndLineQcLine`, `SkuMaster` (`sqlsrv_bplus_2`), `EmpGroup`, master ทั้งหมด
- **Views:** `end-line-qc/`, `end-line-qc-reports/`
- **ระวัง:** route `/qc-end-line/{line}/{id}/export` เป็น stub, มี route update 2 แบบ (POST/PUT legacy) — ถามก่อนว่ายังใช้ PUT อยู่ไหม

### M3 — QC Process Goods

- **ไฟล์เดิม:** `QcProcessGoodsController` (334)
- **Model:** `QcProcessGoods`, `QcProcessTran`, `WipWasteDetail`, `EmpGroup`, `Brandlist`, `GoodsMaster` (`sqlsrv_bplus_2`)
- **Views:** `qc-process-goods/`
- **งาน:** array `$productType/$fullBrand/$fullBrandSize` ใน constructor → `config/product.php` หรือ Enum, แก้ `wipWasteDetail::` (ตัวพิมพ์เล็กผิด)

### M4 — Production (WIP / FG / NG) ⚠️ ใหญ่สุด แตกเป็น sub-module

- **ไฟล์เดิม:** `QualityControlController` (3,547 บรรทัด, 68 method), `app/Exports/EndCsvExport.php`
- **Model:** `WipWorking`, `WipWorktime`, `Wipbarcode`, `WipSummary`, `WipHolding`, `WipProductDate`, `WipColordate`, `WipZiptape`, `AmountNg`, `ListNg`, `Brands`, `Brandlist`, `EmpGroup`, `EmpInOut`, `UserEmployee`, `ProductTypeEmp`, `ProductColors`, `LotNumber`, `EndCsvDetail`, `CheckCsvWh`
- **Views:** `selectemp`, `workedprevious`, `wip`, `addem`, `employeegroup`, `endtimeinterface`, `wpsaddbrands`, `linenotifyqc`, `mainside/qcfn/*`, `template/tag*`

| Sub | method เดิม | Controller ใหม่ |
|---|---|---|
| M4a กะทำงาน/กลุ่ม | `selectemp`, `workedprevious`, `inputworking`, `endworktime`, `endprocess`, `deletework`, `deleteWorkManager` | `WorkShiftController` |
| M4b พนักงาน | `addemployee`, `inputem`, `egstatus`, `addempgroup`, `deleteemp`, `employeegroup` | `EmployeeController` |
| M4c WIP barcode | `wipdetail`, `insertwip`, `editempwip`, `deletewipline1`, `deleteW4WipManager`, `updatewipscap` | `WipController` |
| M4d FG out | `outfgcode`, `deleteoutfg`, `deleteFgManager`, `edittypecolor`, `editbrand` | `FgOutController` |
| M4e NG | `addng`, `modalng`, `ngeachid`, `editwipamg`, `deleteng`, `resultng`, `addlistng`, `inputlistng`, `lngstatus` | `NgController` |
| M4f Brand list / ตั้งค่า | `addbrandslist`, `inputbrandslist`, `blstatus`, `changeStatusBl`, `wpsaddbrands`, `datecolor`, `productcolor` | `BrandListController`, `SettingController` |
| M4g พิมพ์ Tag | `tagfg`, `tagfn`, `tagwipnn`, `tagwipqc`, `taghd`, `tagc` | `TagController` |
| M4h CSV / Dashboard | `csvwip`, `csvendtime`, `csvbackup`, `csvexport`, `csvemployeegroup`, `csvemployeegroupWIP`, `endtimeinterface`, `Dashboard`, `search` | `ExportController`, `DashboardController` |
| helper | `checkline`, `namecolor`, `thaimonth`, `typeofproduct`, `thickness`, `productsize`, `boarder`, `conditioncolor`, `colorline`, `sumioperday`, `wipcolordatecon` | Enum / `config/product.php` / Model accessor |

- **ระวัง:**
  - `outfgcode` ใช้ `sp_getapplock` กันการกดซ้ำ — ต้องคง transaction + lock ไว้เหมือนเดิม
  - `deleteFgManager` เช็คทั้ง user `Manager` และ host `localhost` — คงเงื่อนไข host ไว้
  - `namecolor()` สุ่มสีจาก list ~900 สี (มีค่าเพี้ยน เช่น `#8a8a0fm`, ค่าว่าง `,,`) — ย้ายไป config แล้วทำความสะอาด
  - query ใช้ `TOP 10000` subquery (เฉพาะ SQL Server)

### M5 — Warehouse + WPS ⚠️ ใหญ่ แตกเป็น sub-module

- **ไฟล์เดิม:** `WarehouseController` (3,385 บรรทัด, 71 method, `DB::` 248 ครั้ง)
- **Model:** `Fgbarcode`, `FgbarcodeAction`, `FgbarcodeRef`, `FgTransfer`, `FgTransferAll`, `Itemdecode`, `CheckCsvWh`, `CheckCsvWhIndex`, `WarehouseReturnToQc`, `WpsLocation`, `WpsZone`, `CheckFlindex`, `WpsCheckFl`, `WpsHistoryFl`, `WpsOutCheckFl`, `WpsCheckbpFlbp`, `QcProcessTran`, `Brands`, `WipZiptape`, `ProductTypeEmp` + BPLUS (`DOCINFO`, `TRANSTKH`, `TRANSTKD`, `SKUMASTER`, `SKUMOVE`, `WARELOCATION`, `WAREHOUSE`, `AROE`, `ARFILE`)
- **Views:** `select`, `selectbp`, `tabcutout*`, `transfer*`, `warehouse`, `checkcsvtobplus`, `csvwhsaved`, `qrcodeinterface`, `forklift*`, `checkfl*`

| Sub | method เดิม | Controller ใหม่ |
|---|---|---|
| M5a เลือกเอกสาร | `select`, `selectbp`, `selectby` (ซ้ำกับ selectbp) | `DocumentController` |
| M5b ตัดสต็อก | `cutstocks`, `cutstockstore`, `cutstocks_result`, `cutstocks_input`, `cutstocks_update`, `cutstocktable`, `cutstockhidden`, `deletefg`, `deletefgall`, `secsv` | `CutStockController` |
| M5c โอนย้าย | `transfer*`, `transferbangphli*`, `transfertotoa*`, `transfernwtotoa_input`, `tranafernwtobp`, `transfertops`, `deletefg_trans*`, `deletefgall_trans*`, `deletetgall_transfertops` | `TransferController` (รวมด้วย parameter ปลายทาง แทนการแยก method ต่อคลัง) |
| M5d รับเข้าคลัง | `warehouse`, `warehouse_input`, `deletefg_transwh` | `ReceiveController` |
| M5e เช็ค CSV → BPLUS | `checkcsvtobplus`, `insertcheckcsv*`, `outcheckcsvwh`, `csvwhsaved`, `csvdetailrealtime`, `deleteccw`, `checkcsvqctowh` | `CsvCheckController` |
| M5f WPS / Forklift | `qrcodeinterface`, `interfaceqrcode`, `warehouselocationcreate`, `warehousezonecreate`, `forkliftinterface*`, `update`, `forkliftshow`, `editwpszone`, `checkfl`, `flrealtime`, `insertcheckfl`, `savehistoryfl`, `deletefl`, `checkflupdate`, `flupdaterealtime` | `Wps/LocationController`, `Wps/ForkliftController`, `Wps/CheckController` |

- **ระวัง:**
  - query zone A–F (`$sqlzonea` … `$sqlzonef`) น่าจะรวมเป็น query เดียว + `groupBy` zone
  - route `forkliftinterface` มีทั้ง POST และ PUT ชื่อเดียวกัน
  - `TESTinsertcheckcsvindex` เป็น route ทดสอบ — ไม่ต้องย้าย

### M6 — Bill of Lading

- **ไฟล์เดิม:** `BillofladingController` (979)
- **Model:** `Billoflading` (PK `DI_KEY`), `BillofladingRef` (PK `bor_di_key`) + BPLUS
- **Views:** `selectbilloflading`, `billlading`, `billofladingresult`, `billofladingstore`, `debug/connection-tests`
- **ระวัง:** parameter `{getru}` / `{rudirect}` = รหัสหน่วยงาน (`78`, `1005`…`1086`, `store` จาก `HomeController`) → Enum; `testconnections` ไม่ต้องย้าย

### M7 — Transfer Calendar

- **ไฟล์เดิม:** `TransferCalenderController` (956)
- **Model:** `CalendarColorStatus`, `CalendarStatusDetail`, `EditCalendarDetail`, `EditeCalendarDate` (ทั้งหมด `sqlsrv_pklocal`) + BPLUS
- **Views:** `transcalendar`
- **งาน:** เลิกใช้ `Calendar::` (maddhatter) → FullCalendar JS ดึง JSON จาก `calendarresponse`; method คู่ `xxx` / `xxxcar` (`transcalendarcar`, `calendarresponsecar`, `checkandrescalendarcar`) ไม่มี route — เช็คว่ายังใช้ไหม

### M8 — Pruksa

- **ไฟล์เดิม:** `PruksaController` (1,967, 39 method), `PruksaPoController` (226)
- **Model:** `PruksaInOut`, `PruksaOutCline`, `PruksaOutrim`, `PruksaOutparcel`, `PruksaConfirmBoq`, `PruksaPo`, `PruksaNotificationLog`, `Pruksauploadimg`, `PsBlk`, `PsDrw` (`sqlsrv_pklocal`)
- **Views:** `pruksa/`, `pruksa/mobile/`

| Sub | method เดิม | Controller ใหม่ |
|---|---|---|
| M8a เมนู/แผนบ้าน | `main`, `addhomeplan`, `inputhomeplan` | `MenuController` |
| M8b Upload | `pruksaupload`, `pkupload`, `upsqlnoti`, `pruksauploadimg`, `pkuploadimg`, `csimg`, `clinepic`, `parcelpic` | `UploadController` |
| M8c C-line เข้า/ออก | `clineinout`, `clinedetail`, `pkoutinsert`, `pkoutcline`, `pkoutrim`, `pkoutparcel`, `calendardetail` (helper query ของ clineinout) | `ClineController` |
| M8d BOQ / modal | `modalpkcalheader*`, `pkboqdetail*`, `pkareadetail*`, `modalpkconboq`, `insertconboq` | `BoqController` |
| M8e CSV | `dycsv`, `dycsvout` | `ExportController` |
| M8f Report | `clinereport`, `pdfclinereport`, `chartsclinereport`, `clinereportupdate`, `rimreport`, `pdfrimreport`, `chartsrimreport` | `ReportController` |
| M8g PO | `PruksaPoController` ทั้งหมด | `PoController` |
| M8h แจ้งเตือน | `pklinenotification` (`testurlline` ไม่ต้องย้าย) | ย้ายไป M9 |

- **ระวัง:**
  - method คู่ `xxx` / `xxxstat` (เช่น `pkboqdetail` / `pkboqdetailstat`) SQL เกือบเหมือนกัน แต่ต่างกันบางจุด (ตัวหนึ่งใช้ prefix `$dbpath` อีกตัวไม่ใช้, มีเงื่อนไข `TRH_KEY` hardcode) → diff ทีละคู่ก่อนรวมเป็น method เดียว
  - SQL ยาวมี `CASE WHEN TRH_KEY = 78123 ...` hardcode — คงไว้ตามเดิม แต่ย้ายไป query class/scope พร้อม comment ว่าทำไม
  - route `pruksahomeframerim`, `pruksaaddrim` ชี้ controller ที่ไม่มี (หัวข้อ 11)

### M9 — Console + Notification

- **ไฟล์เดิม:** `app/Console/Commands/CheckFgtoWh.php`, `PruksaCheckSP.php`, `PruksaNotification.php`, `app/Console/Kernel.php`, `config/LineNotify.php`, `*.bat`/`*.cmd` ที่ root
- **งาน:**
  - สร้าง `App\Services\Notification\LineMessenger` (ใช้ `Http::withToken()`) แทน curl ที่ซ้ำ 4 จุด
  - token ไป `.env` → `config/line.php`
  - schedule ย้ายไป `routes/console.php`
  - query SQL ยาวใน `handle()` ย้ายไป model/scope หรือ query class

### M10 — API

- **ไฟล์เดิม:** `APIController`, `routes/api.php` (`/api/v1/se`, `/api/v1/so`)
- **Model:** `SETABLE`, `SOTABLE` → ตั้งชื่อใหม่
- **ระวัง:** prefix เดิมกลายเป็น `/api/api/v1/...` (เพราะ `RouteServiceProvider` ใส่ `api` ซ้ำ) — เช็คว่ามีระบบภายนอกเรียกอยู่ไหม ถ้ามีต้องคง URL เดิม; API ยังไม่มี auth

---

## 8. Model mapping: เดิม → ใหม่

> ชื่อ class ใหม่ใช้ PascalCase เอกพจน์ ตารางเดิมคงไว้ (ห้ามเปลี่ยนชื่อตาราง)
> `default` = connection `sqlsrv` (DB `gypman-tech`)

| เดิม `App\` | ใหม่ `App\Models\` | ตาราง | PK | Connection |
|---|---|---|---|---|
| `User` | `User` | users | id | default |
| **Master** | | | | |
| `MasterBrand` | `Master\MasterBrand` | master_brands | id | default |
| `MasterEdge` | `Master\MasterEdge` | master_edges | id | default |
| `MasterFace` | `Master\MasterFace` | master_faces | id | default |
| `MasterProductGroup` | `Master\MasterProductGroup` | master_product_groups | id | default |
| `MasterProductSpec` | `Master\MasterProductSpec` | master_product_specs | id | default |
| `MasterSheetType` | `Master\MasterSheetType` | master_sheet_types | id | default |
| `MasterSize` | `Master\MasterSize` | master_sizes | id | default |
| `MasterThickness` | `Master\MasterThickness` | master_thicknesses | id | default |
| **End-line QC / QC Process** | | | | |
| `EndLineQcHeader` | `EndLineQc\EndLineQcHeader` | end_line_qc_headers | id | default |
| `EndLineQcLine` | `EndLineQc\EndLineQcLine` | end_line_qc_lines | id | default |
| `QcProcessGoods` | `QcProcess\QcProcessGoods` | qc_process_goods | id | default |
| `QcProcessTran` | `QcProcess\QcProcessTran` | qc_process_trans | id | default |
| `WipWasteDetail` | `QcProcess\WipWasteDetail` | wip_waste_detail | wwd_id | default |
| **Production** | | | | |
| `WipWorking` | `Production\WipWorking` | wip_working | ww_id | default |
| `WipWorktime` | `Production\WipWorktime` | wip_worktime | wwt_id | default |
| `Wipbarcode` | `Production\WipBarcode` | wipbarcode | wip_id | default |
| `WipSummary` | `Production\WipSummary` | wip_summary | ws_id | default |
| `WipHolding` | `Production\WipHolding` | wip_holding | wh_id | default |
| `WipProductDate` | `Production\WipProductDate` | wip_productdate | wp_id | default |
| `WipColordate` | `Production\WipColorDate` | wip_colordate | wcd_id | default |
| `WipZiptape` | `Production\WipZiptape` | wip_ziptape | wz_id | default |
| `AmountNg` | `Production\NgAmount` | amount_ng | amg_id | default |
| `ListNg` | `Production\NgType` | list_ng | lng_id | default |
| `Brands` | `Production\FgBrand` | brands | brd_id | default |
| `Brandlist` | `Production\BrandList` | brandlist | bl_id | default |
| `EmpGroup` | `Production\EmployeeGroup` | emp_group | eg_id | default |
| `EmpInOut` | `Production\EmployeeInOut` | emp_inout | eio_id | default |
| `UserEmployee` | `Production\Employee` | user_employee | ue_id | default |
| `ProductTypeEmp` | `Production\ProductTypeEmployee` | producttype_emp | pe_id | default |
| `ProductColors` | `Production\ProductColor` | production_colors | pcs_id | default |
| `LotNumber` | `Production\LotNumber` | lotnumber | lot_id | default |
| `EndCsvDetail` | `Production\EndCsvDetail` | (ไม่ระบุ — เช็ค) | id | default |
| **Warehouse** | | | | |
| `Fgbarcode` | `Warehouse\FgBarcode` | fg_barcode | id | default |
| `FgbarcodeAction` | `Warehouse\FgBarcodeAction` | fg_barcode_action | fga_id | default |
| `FgbarcodeRef` | `Warehouse\FgBarcodeRef` | fg_barcode_ref | id | default |
| `FgTransfer` | `Warehouse\FgTransfer` | fg_transfer | id | default |
| `FgTransferAll` | `Warehouse\FgTransferAll` | fg_transfer_all | id | default |
| `Itemdecode` | `Warehouse\ItemDecode` | item_decode | id | default |
| `CheckCsvWh` | `Warehouse\CsvCheck` | check_csv_wh | ccw_id | default |
| `CheckCsvWhIndex` | `Warehouse\CsvCheckIndex` | check_csv_wh_index | cswi_id | default |
| `WarehouseReturnToQc` | `Warehouse\ReturnToQc` | warehouse_return_to_qc | wrtc_id | default |
| **WPS** | | | | |
| `WpsLocation` | `Wps\Location` | wps_location | wl_id | default |
| `WpsZone` | `Wps\Zone` | wps_zone | wzone_id | default |
| `CheckFlindex` | `Wps\ForkliftCheckIndex` | wps_check_fl_index | cfl_id | default |
| `WpsCheckFl` | `Wps\ForkliftCheck` | wps_check_fl | fl_id | default |
| `WpsHistoryFl` | `Wps\ForkliftHistory` | wps_history_fl | hfl_id | default |
| `WpsOutCheckFl` | `Wps\ForkliftOutCheck` | wps_out_check | oc_id | default |
| `WpsCheckbpFlbp` | `Wps\ForkliftCheckBp` | wps_checkbp_flbp | bp_id | default |
| **Bill of Lading** | | | | |
| `Billoflading` | `BillOfLading\BillOfLading` | billof_lading | DI_KEY | default |
| `BillofladingRef` | `BillOfLading\BillOfLadingRef` | billoflading_ref | bor_di_key | default |
| **Transfer Calendar** | | | | |
| `CalendarColorStatus` | `TransferCalendar\ColorStatus` | calendar_color_status | ccs_id | sqlsrv_pklocal |
| `CalendarStatusDetail` | `TransferCalendar\StatusDetail` | calendar_status_detail | csd_id | sqlsrv_pklocal |
| `EditCalendarDetail` | `TransferCalendar\EditDetail` | edit_calendar_detail | ecd_id | sqlsrv_pklocal |
| `EditeCalendarDate` | `TransferCalendar\EditDate` | editdate_calendar | edc_id | sqlsrv_pklocal |
| **Pruksa** | | | | |
| `PruksaInOut` | `Pruksa\InOut` | pruksa_inout | pkio_id | default |
| `PruksaOutCline` | `Pruksa\OutCline` | pruksa_outcline | poc_id | default |
| `PruksaOutrim` | `Pruksa\OutRim` | pruksa_outrim | id | default |
| `PruksaOutparcel` | `Pruksa\OutParcel` | pruksa_outparcel | id | default |
| `PruksaConfirmBoq` | `Pruksa\ConfirmBoq` | pruksa_confirm_boq | pcb_id | default |
| `PruksaPo` | `Pruksa\Po` | pruksa_po | id | default |
| `PruksaNotificationLog` | `Pruksa\NotificationLog` | pruksa_notification_log | pnl_id | default |
| `Pruksauploadimg` | `Pruksa\UploadImage` | pruksauploadimg | id | default |
| `PsBlk` | `Pruksa\PsBlock` | BLK | PRJ | sqlsrv_pklocal |
| `PsDrw` | `Pruksa\PsDrawing` | DRW | id | sqlsrv_pklocal |
| **BPLUS (ERP)** | | | | |
| `AllOrder`, `SOTABLE` | `Bplus\Docinfo` | DOCINFO | DI_KEY | config('bplus.connection') — เดิมใช้ default ⚠️ |
| `SkuMaster` | `Bplus\SkuMaster` | SKUMASTER | (เช็ค — เดิมตั้ง id) | sqlsrv_bplus_2 |
| `GoodsMaster` | `Bplus\GoodsMaster` | GOODSMASTER | (เช็ค) | sqlsrv_bplus_2 |
| — (ใหม่) | `Bplus\TransferHeader` / `TransferDetail` | TRANSTKH / TRANSTKD | TRH_KEY / (เช็ค) | config('bplus.connection') |
| **API** | | | | |
| `SETABLE` | `Api\SeTable` (เช็คว่ายังใช้) | se_table | id | default |

---

## 9. Prompt template + Definition of Done

### 9.1 Prompt สำหรับแต่ละ module

```
อ่าน REFACTOR_LARAVEL13.md ก่อน แล้ว refactor module M<เลข> — <ชื่อ module>
(ถ้าเป็น sub-module: เฉพาะ M<เลข><ตัวอักษร>)

โปรเจคเดิม (Laravel 5.8): c:\laragon\www\production
โปรเจคใหม่ (Laravel 13):  c:\laragon\www\<ชื่อโปรเจคใหม่>

ขั้นตอน:
1. อ่าน controller / model / view / route ของ module นี้ในโปรเจคเดิม
2. สรุปให้ดูก่อน: method ที่จะย้าย, model ที่ต้องสร้าง/ใช้ซ้ำ, route ที่จะได้, bug/จุดเสี่ยงที่เจอ
3. รอฉันยืนยัน แล้วค่อยเขียนโค้ดในโปรเจคใหม่ตามกติกาหัวข้อ 4
4. ปิดงานด้วย checklist หัวข้อ 9.2
```

### 9.2 Definition of Done (ทุก module)

- [ ] ทุก route เดิมของ module มีใน `routes/web/<module>.php` — URI + ชื่อ route เหมือนเดิม (ยกเว้นที่ตกลงว่าไม่ย้าย)
- [ ] ไม่มี `DB::raw($var)`, ไม่มีต่อ string SQL ด้วยตัวแปร
- [ ] ไม่มี `env()` นอก `config/`, ไม่มี `date_default_timezone_set`
- [ ] ไม่มี `Auth::user()->name == '...'` — ใช้ Gate/`@can`
- [ ] model อยู่ `app/Models/<Module>/` มี `$table`, `$primaryKey`, `$timestamps`, `casts()`, relationship ที่ใช้
- [ ] controller ไม่เกิน ~10 method, ไม่มี comment code เก่า
- [ ] validation ใน `validate()`/Form Request
- [ ] view อยู่ `resources/views/<module>/` และใช้ layout กลาง
- [ ] CSV ที่ export: เทียบไฟล์กับระบบเดิมแล้วตรงกัน (ถ้ามี)
- [ ] เปิดทุกหน้าของ module ใน browser แล้วไม่ error, ข้อมูลตรงกับระบบเดิม
- [ ] `php artisan route:list --path=<prefix>` ไม่มี error
- [ ] จด bug ที่เจอแต่ยังไม่แก้ ไว้ในหัวข้อ "บันทึก" ท้ายไฟล์นี้

---

## 10. โค้ดที่ไม่ต้องย้าย (dead code)

ตรวจแล้วไม่มี route หรือที่ไหนเรียกใช้ — **ยืนยันอีกครั้งก่อนลบในระบบเดิม**

| ประเภท | รายการ |
|---|---|
| Controller ไม่มี route | `WipController` (1,630 บรรทัด — น่าจะเป็นสำเนาเก่าของ QC), `Wipline1Controller`, `Wipline2Controller`, `CreateSoController`, `ScanbarcodeController`, `QcCToFgController`, `DatabaseController` (ว่าง) |
| Model ไม่มีที่ใช้ | `BplusDB`, `BrandlotRelation`, `EndlineQc`, `ItemSe`, `GetSeTable`, `Pruksastatforman`, `USERSAVEDETAIL`, `WareHouse`, `WareLocation`, `Item` (ใช้แค่ใน CreateSo) |
| import model ที่ไม่มีอยู่จริง | `use App\Wipline1;` ใน Home, QC, Warehouse |
| Route ทดสอบ | `/testso/{x}`, `/TESTinsertcheckcsvindex`, `/testconnections`, `/checkconnection`, `/bplusdb`, `testurlline()` |
| View ทดสอบ | `testview`, `test_wip`, `testoutputfg`, `gencodetest`, `bplustestview`, `debug/` |
| ไฟล์ที่ root | `phpinfo.php` (**ลบด่วน — เปิดข้อมูล server**), `file2.csv`, `favicon2.ico`, `production85/`, `Login_v1/` (เช็คก่อน) |

---

## 11. เรื่องที่ต้องตัดสินใจ/ตรวจสอบก่อนเริ่ม

- [ ] **ชื่อ/ที่อยู่โปรเจคใหม่** (เช่น `c:\laragon\www\production13`) และจะรันคู่กับระบบเดิมช่วงย้ายไหม
- [ ] **DB สำหรับทดสอบ** — แนะนำ copy DB `gypman-tech` มาทดสอบ ห้ามชี้ production ตอนพัฒนา และ**ห้ามรัน `migrate`/`migrate:fresh` กับ DB เดิม**
- [ ] **เวอร์ชัน SQL Server** ทุกตัว ≥ 2017 หรือไม่ (L11+ ต้องการ)
- [ ] **pdo_sqlsrv สำหรับ PHP 8.3** ติดตั้งใน Laragon แล้วหรือยัง
- [ ] **LINE Notify** ปิดแล้ว — จะใช้ LINE Messaging API (ต้องมี LINE OA + channel token) หรือช่องทางอื่น
- [ ] **`FramerimController`** ไม่มีไฟล์ แต่มี view `pruksahomeframerim` (777 บรรทัด), `pruksaaddrim` — ทิ้งหรือเขียนใหม่
- [ ] **API `/api/api/v1/se|so`** มีระบบภายนอกเรียกอยู่ไหม
- [ ] **รหัสคลัง BPLUS** (1, 101, 2033, 2034, 2511) ชื่ออะไรบ้าง → ใส่ Enum/config
- [ ] **Front-end**: เก็บ Bootstrap 4 + jQuery + AdminLTE เดิมไว้ (แนะนำระยะแรก) หรือเปลี่ยน
- [ ] **ย้าย secret**: เปลี่ยนรหัสผ่าน DB และ LINE token ที่เคยอยู่ใน git (เพราะอยู่ใน history แล้ว)

---

## บันทึก (อัปเดตระหว่างทำ)

| วันที่ | Module | สถานะ | หมายเหตุ / bug ที่เจอ |
|---|---|---|---|
| | M0 | ⬜ | |
| | M1 | ⬜ | |
| | M2 | ⬜ | |
| | M3 | ⬜ | |
| | M4 | ⬜ | |
| | M5 | ⬜ | |
| | M6 | ⬜ | |
| | M7 | ⬜ | |
| | M8 | ⬜ | |
| | M9 | ⬜ | |
| | M10 | ⬜ | |
