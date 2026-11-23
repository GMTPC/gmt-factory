<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: garuda, sans-serif; color: #111; font-size: 11pt; }
        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid #111; padding: 5px; text-align: center; }
        .green { background: #159653; color: #fff; font-weight: bold; }
        .code { font-size: 25pt; font-weight: bold; padding: 12px; }
        .name { font-size: 15pt; font-weight: bold; padding: 12px; }
        .qty { font-size: 28pt; font-weight: bold; }
        .small { font-size: 8pt; }
    </style>
</head>
<body>
@php($header = $item->header)
@php($productName = $item->spec?->name ?: trim(implode(' ', array_filter([$header?->sheetType?->name, $header?->edge?->name, $header?->size?->label, $header?->thickness?->label, $header?->face?->name]))))
<table>
    <tr><td class="green">Pallet No.</td><td><b>{{ str_pad((string) ($item->tag_no ?: $item->line_no), 3, '0', STR_PAD_LEFT) }}</b></td><td>วันที่คัด</td><td colspan="2"><b>{{ $header?->qc_date?->format('d/m/Y') }}</b></td><td class="green">{{ $state }}</td></tr>
    <tr><td colspan="2">สำหรับงานคลัง<br><barcode code="{{ $item->full_tag ?: $item->tag }}" type="QR" size="0.9" error="M" /><div class="small">{{ $item->full_tag ?: $item->tag }}</div></td><td colspan="4" class="code">{{ $item->sku_code ?: $item->wip_code }}</td></tr>
    <tr><td colspan="6" class="name">{{ $productName }}</td></tr>
    <tr><td>Line</td><td>{{ $header?->line }}</td><td colspan="2" class="qty">{{ $item->qty }}</td><td>หน่วย</td><td>{{ $item->unit }}</td></tr>
    <tr><td>วันที่ผลิต</td><td>{{ $header?->qc_date?->format('d/m/Y') }}</td><td>เวลา</td><td>{{ $item->time_at ?: '-' }}</td><td>ผู้คัด</td><td>{{ $header?->qc_inspector }}</td></tr>
    <tr><td>กะ</td><td>{{ $header?->shift }}</td><td>สถานะ WIP</td><td>{{ $item->wip_status_name ?: '-' }}</td><td>หมายเหตุ</td><td>{{ $item->remark ?: '-' }}</td></tr>
</table>
</body>
</html>
