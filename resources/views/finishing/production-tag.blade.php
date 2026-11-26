<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 8mm 14mm; }
        body { font-family: garuda, sans-serif; color: #111; font-size: 10pt; }
        .tag { height: 136mm; }
        .head td { border: 0; padding: 0; font-size: 10pt; }
        .codes td { border: 0; text-align: center; vertical-align: bottom; padding: 4px 0; }
        .code-text { font-size: 10pt; font-weight: bold; padding-top: 4px; }
        table { width: 100%; border-collapse: collapse; }
        .info td { border: 0.6px solid #111; padding: 1px 4px; height: 6.2mm; }
        .info .label { width: 18%; text-align: center; font-weight: bold; }
        .info .label2 { width: 17%; text-align: center; font-weight: bold; }
        .info .value { width: 33%; text-align: center; }
        .info .value2 { width: 32%; text-align: center; }
        .info .wide { text-align: center; }
        .cut { border-top: 0.6px dashed #555; margin: 4mm 0; }
    </style>
</head>
<body>
{{-- Finishing WIP tag, same layout as the AppSheet Finishing tag: two copies per A4 with a cut line. --}}
@foreach ([1, 2] as $copy)
    <div class="tag">
        <table class="head"><tr>
            <td style="text-align: left;">{{ $tag['sequence'] }}</td>
            <td style="text-align: right;">ฝ่าย ฟินิชชิ่ง</td>
        </tr></table>
        <table class="codes"><tr>
            <td style="width: 50%;">
                <barcode code="{{ $tag['qr'] }}" type="QR" size="1.5" error="M" disableborder="1" />
                <div class="code-text">{{ $tag['qr'] }}</div>
            </td>
            <td style="width: 50%;">
                <barcode code="{{ $tag['barcode'] }}" type="C128B" size="0.75" height="1.6" />
                <div class="code-text">{{ $tag['barcode'] }}</div>
            </td>
        </tr></table>
        <table class="info">
            <tr><td class="label">รหัสสินค้า</td><td class="wide" colspan="3">{{ $tag['product_code'] }}</td></tr>
            <tr><td class="label">ชื่อสินค้า</td><td class="wide" colspan="3">{{ $tag['product_name'] }}</td></tr>
            <tr><td class="label">วันที่คัด</td><td class="wide" colspan="3">{{ $tag['sorted_date'] }}</td></tr>
            <tr><td class="label">ชนิดแผ่น</td><td class="value">{{ $tag['sheet_type'] }}</td><td class="label2" rowspan="2">ขนาดสินค้า</td><td class="value2" rowspan="2">{{ $tag['size'] }}</td></tr>
            <tr><td class="label">ขอบ</td><td class="value">{{ $tag['edge'] }}</td></tr>
            <tr><td class="label">หมวดสินค้า</td><td class="value">{{ $tag['category'] }}</td><td class="label2">ไลน์ผลิต</td><td class="value2">{{ $tag['production_line'] }}</td></tr>
            <tr><td class="label">Lot</td><td class="value">{{ $tag['lot'] }}</td><td class="label2">ไลน์ FN</td><td class="value2">{{ $tag['fn_line'] }}</td></tr>
            <tr><td class="label">จำนวน</td><td class="value">{{ $tag['quantity'] }}</td><td class="label2">ผู้ตรวจสอบ</td><td class="value2">{{ $tag['inspector'] }}</td></tr>
            <tr><td class="label">เวลา</td><td class="value">{{ $tag['time'] }}</td><td class="label2">ผู้บันทึก</td><td class="value2">{{ $tag['recorder'] }}</td></tr>
            <tr><td class="label">น้ำหนัก</td><td class="value">{{ $tag['weight'] }}</td><td class="label2">วันที่ผลิต WIP</td><td class="value2">{{ $tag['wip_date'] }}</td></tr>
        </table>
    </div>
    @if ($copy === 1)
        <div class="cut"></div>
    @endif
@endforeach
</body>
</html>
