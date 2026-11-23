<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 8mm; }
        body { font-family: garuda, sans-serif; color: #000; font-size: 11pt; }
        .tag-pair { page-break-inside: avoid; }
        table.tag { width: 100%; border-collapse: collapse; table-layout: fixed; margin: 0 0 3mm; }
        table.tag th, table.tag td { border: 0.25mm solid #000; padding: 1.5mm; text-align: center; vertical-align: middle; }
        table.tag th { color: #fff; font-weight: bold; }
        .head-cell { background: {{ $lineColor }}; }
        .pallet-no { font-size: 15pt; font-weight: bold; text-decoration: underline; }
        .date { font-size: 20pt; font-weight: bold; }
        .large-text { font-size: 32pt; font-weight: bold; }
        .medium-text { font-size: 19pt; font-weight: bold; }
        .wms-text { font-size: 11pt; font-weight: bold; }
        .wms-text-fg { font-size: 10pt; font-weight: bold; }
        .qr { text-align: center; }
        .qr barcode { width: 36mm; }
        .qr-wms barcode { width: 28mm; }
        .small-code { font-size: 7pt; overflow-wrap: anywhere; }
        .separator { border-top: 0.3mm dashed #000; margin: 5mm 0; }
    </style>
</head>
<body>
@foreach ($transactions as $tran)
    <div class="tag-pair" @if (! $loop->last) style="page-break-after: always" @endif>
        <table class="tag">
            <tr>
                <th class="head-cell" rowspan="2">Pallet No.</th>
                <td class="pallet-no" rowspan="2">{{ sprintf('%03d', $tran['running_no']) }}</td>
                <td>วันที่คัด</td>
                <td class="date" colspan="3">{{ $tran['created_date_th'] }}</td>
                <th class="head-cell" rowspan="2" style="width: 15mm">TAG</th>
                <th class="head-cell" rowspan="2" style="width: 15mm">FG</th>
            </tr>
            <tr><td>เวลา</td><td>-</td><td colspan="2"></td></tr>
            <tr>
                <td colspan="2" rowspan="2" class="qr">
                    <b class="wms-text">สำหรับงานคลัง</b><br>
                    <barcode code="{{ $tran['wip_lot'] }}" type="QR" size="1.25" error="M" disableborder="1" />
                    <br><span class="small-code">{{ $tran['wip_lot'] }}</span>
                </td>
                <td colspan="6" class="large-text">{{ $tran['sku_code'] }}</td>
            </tr>
            <tr><td colspan="6" class="medium-text">{{ $tran['product_alias'] }}</td></tr>
            <tr>
                <td>น้ำหนัก/หน่วย</td><td>-</td><td colspan="2">จำนวน</td><td>หน่วย</td><td>แผ่น</td>
                    <td rowspan="5" colspan="2" class="qr qr-wms">
                    <b class="wms-text">สำหรับงาน WMS</b><br>
                    <barcode code="T06T:{{ $tran['wms_lot'] }}" type="QR" size="1.0" error="M" disableborder="1" />
                    <br><span class="small-code">T06T:{{ $tran['wms_lot'] }}</span>
                </td>
            </tr>
            <tr><td>Line</td><td>{{ $tran['line_number'] }}</td><td rowspan="2" colspan="4" class="large-text">{{ number_format($tran['amount']) }}</td></tr>
            <tr><td>วันที่ผลิต</td><td>{{ $tran['production_date'] }}</td></tr>
            <tr><td>ผู้คัด</td><td>{{ $tran['qc_group'] }}</td><td>ไลน์/กะผลิต</td><td>-</td><td>หน่วยย่อย</td><td>-</td></tr>
            <tr><td>ผู้ตรวจสอบ</td><td>{{ $tran['checker_name'] }}</td><td>ไลน์/กะคัด</td><td>-</td><td>หมายเหตุ</td><td></td></tr>
        </table>

        <div class="separator"></div>

        <table class="tag">
            <tr>
                <th class="head-cell" rowspan="2">Pallet No.</th>
                <td class="pallet-no" rowspan="2">{{ sprintf('%03d', $tran['running_no']) }}</td>
                <td>วันที่คัด</td>
                <td class="date" colspan="3">{{ $tran['created_date_th'] }}</td>
                <th class="head-cell" rowspan="2" colspan="2">ฝ่ายคัดบอร์ด</th>
            </tr>
            <tr><td>เวลา</td><td>-</td><td colspan="2"></td></tr>
            <tr><td colspan="8" class="medium-text">{{ $tran['sku_code'] }}</td></tr>
            <tr><td colspan="8" class="medium-text">{{ $tran['product_alias'] }}</td></tr>
            <tr><td>น้ำหนัก/หน่วย</td><td>-</td><td colspan="2">จำนวน</td><td>หน่วย</td><td>แผ่น</td><td colspan="2"></td></tr>
            <tr>
                <td>Line</td><td>{{ $tran['line_number'] }}</td>
                <td rowspan="2" colspan="4" class="large-text">{{ number_format($tran['amount']) }}</td>
                <td rowspan="2" colspan="2" class="qr qr-wms">
                    <b class="wms-text-fg">สำหรับงาน WMS</b><br>
                    <barcode code="T06T:{{ $tran['wms_lot'] }}" type="QR" size="1.0" error="M" disableborder="1" />
                    <br><span class="small-code">T06T:{{ $tran['wms_lot'] }}</span>
                </td>
            </tr>
            <tr><td>วันที่ผลิต</td><td>{{ $tran['production_date'] }}</td></tr>
            <tr><td>ผู้คัด</td><td>{{ $tran['qc_group'] }}</td><td>ไลน์/กะผลิต</td><td>-</td><td>หน่วยย่อย</td><td>-</td><td colspan="2"></td></tr>
            <tr><td>ผู้ตรวจสอบ</td><td>{{ $tran['checker_name'] }}</td><td>ไลน์/กะคัด</td><td>-</td><td>หมายเหตุ</td><td></td><td colspan="2"></td></tr>
        </table>
    </div>
@endforeach
</body>
</html>
