<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 12mm; }
        body { font-family: garuda, sans-serif; color: #111; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #111; padding: 8px; }
        th { width: 26%; background: #e5e7eb; text-align: left; }
        .title { background: #087f5b; color: #fff; text-align: center; font-size: 20pt; }
        .code { text-align: center; font-size: 18pt; font-weight: bold; word-break: break-all; }
        .qr { text-align: center; padding: 16px; }
        .qty { text-align: center; font-size: 26pt; font-weight: bold; }
        .half { width: 24%; }
        .small { font-size: 9pt; word-break: break-all; }
    </style>
</head>
<body>
    @php($tagCode = $log->output_tag_code ?: $log->outputMaterial?->code)
    <table>
        <tr><td colspan="4" class="title">FINISHING · {{ $log->station->code }} · WIP TAG{{ $log->output_lot ? ' · '.$log->output_lot : '' }}</td></tr>
        <tr><th>Job</th><td colspan="3">{{ $log->job->job_no }}</td></tr>
        <tr><th>สถานี</th><td colspan="3">{{ $log->station->name }} ({{ $log->station->code }})</td></tr>
        <tr><th>วันที่ / กะ</th><td colspan="3">{{ $log->shift_date?->format('d/m/Y') }} · {{ $log->shift === 'day' ? 'กะเช้า' : 'กะดึก' }}</td></tr>
        <tr><th>ประเภทสินค้า</th><td colspan="3"><strong>{{ $log->product_type_code ?: '-' }}</strong>{{ $log->product_type_name ? ' · '.$log->product_type_name : '' }}</td></tr>
        <tr><th>วัตถุดิบ</th><td colspan="3">{{ $log->raw_material_name ?: ($log->job->raw_material_name ?: '-') }}</td></tr>
        <tr><th>Tag WIP</th><td colspan="3" class="code">{{ $tagCode }}</td></tr>
        <tr><td colspan="4" class="qr">
            <barcode code="{{ $tagCode }}" type="QR" size="1.2" error="M" />
            <div style="margin-top: 8px;"><barcode code="{{ $tagCode }}" type="C128B" size="0.9" height="1.4" /></div>
            <div class="small">{{ $tagCode }}</div>
        </td></tr>
        <tr><th>WIP เข้า</th><td class="qty half">{{ rtrim(rtrim(number_format((float) $log->quantity_in, 3), '0'), '.') }}</td><th class="half">WIP ออก</th><td class="qty half">{{ rtrim(rtrim(number_format((float) $log->quantity_out, 3), '0'), '.') }}</td></tr>
        <tr><th>น้ำหนัก (กก.)</th><td class="qty" colspan="3">{{ $log->weight_kg !== null ? number_format((float) $log->weight_kg, 2) : '-' }}</td></tr>
        @if ((float) $log->defect_count > 0)
            <tr><th>ยอดเสีย</th><td colspan="3">{{ number_format((float) $log->defect_count, 3) }}</td></tr>
        @endif
        <tr><th>Tag WIP ต้นทาง</th><td colspan="3" class="small">{{ $log->scan_code ?? $log->material?->code ?? '-' }}</td></tr>
        <tr><th>บันทึกโดย</th><td colspan="3" class="small">{{ $log->user?->name ?? '-' }} · {{ $log->updated_at?->timezone(config('finishing.timezone'))->format('d/m/Y H:i') }}</td></tr>
    </table>
</body>
</html>
