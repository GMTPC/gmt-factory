{{-- Finishing WIP tag as print-ready HTML (same layout as the PDF). Injected into the current page and printed
     with window.print(), so phones go straight to the print dialog without downloading a PDF. --}}
<style>
    #fin-print-root { display: none; }
    @media print {
        @page { size: A4; margin: 8mm 14mm; }
        body.fin-printing > *:not(#fin-print-root) { display: none !important; }
        body.fin-printing #fin-print-root { display: block !important; }
        body.fin-printing { background: #fff !important; }
    }
    #fin-print-root, #fin-print-root * { box-sizing: border-box; color: #111; font-family: 'Sarabun', 'Noto Sans Thai', Tahoma, sans-serif; }
    #fin-print-root .tag { height: 136mm; break-inside: avoid; page-break-inside: avoid; }
    #fin-print-root .head { display: flex; justify-content: space-between; font-size: 10pt; }
    #fin-print-root .codes { display: flex; align-items: flex-end; padding: 2mm 0; }
    #fin-print-root .codes > div { width: 50%; text-align: center; }
    #fin-print-root .qr svg { width: 36mm; height: 36mm; display: inline-block; }
    #fin-print-root .bar svg { width: 62mm; height: 14mm; display: inline-block; }
    #fin-print-root .code-text { font-size: 10pt; font-weight: 700; letter-spacing: .5px; margin-top: 1mm; }
    #fin-print-root table { width: 100%; border-collapse: collapse; font-size: 10pt; }
    #fin-print-root td { border: .6px solid #111; padding: .6mm 1.5mm; height: 6.2mm; text-align: center; }
    #fin-print-root td.label { width: 18%; font-weight: 700; }
    #fin-print-root td.label2 { width: 17%; font-weight: 700; }
    #fin-print-root td.value { width: 33%; }
    #fin-print-root .cut { border-top: .6px dashed #555; margin: 4mm 0; }
</style>
@foreach ([1, 2] as $copy)
    <div class="tag">
        <div class="head"><span>{{ $tag['sequence'] }}</span><span>ฝ่าย ฟินิชชิ่ง</span></div>
        <div class="codes">
            <div class="qr">{!! $qrSvg !!}<div class="code-text">{{ $tag['qr'] }}</div></div>
            <div class="bar">{!! $barcodeSvg !!}<div class="code-text">{{ $tag['barcode'] }}</div></div>
        </div>
        <table>
            <tr><td class="label">รหัสสินค้า</td><td colspan="3">{{ $tag['product_code'] }}</td></tr>
            <tr><td class="label">ชื่อสินค้า</td><td colspan="3">{{ $tag['product_name'] }}</td></tr>
            <tr><td class="label">วันที่คัด</td><td colspan="3">{{ $tag['sorted_date'] }}</td></tr>
            <tr><td class="label">ชนิดแผ่น</td><td class="value">{{ $tag['sheet_type'] }}</td><td class="label2" rowspan="2">ขนาดสินค้า</td><td rowspan="2">{{ $tag['size'] }}</td></tr>
            <tr><td class="label">ขอบ</td><td class="value">{{ $tag['edge'] }}</td></tr>
            <tr><td class="label">หมวดสินค้า</td><td class="value">{{ $tag['category'] }}</td><td class="label2">ไลน์ผลิต</td><td>{{ $tag['production_line'] }}</td></tr>
            <tr><td class="label">Lot</td><td class="value">{{ $tag['lot'] }}</td><td class="label2">ไลน์ FN</td><td>{{ $tag['fn_line'] }}</td></tr>
            <tr><td class="label">จำนวน</td><td class="value">{{ $tag['quantity'] }}</td><td class="label2">ผู้ตรวจสอบ</td><td>{{ $tag['inspector'] }}</td></tr>
            <tr><td class="label">เวลา</td><td class="value">{{ $tag['time'] }}</td><td class="label2">ผู้บันทึก</td><td>{{ $tag['recorder'] }}</td></tr>
            <tr><td class="label">น้ำหนัก</td><td class="value">{{ $tag['weight'] }}</td><td class="label2">วันที่ผลิต WIP</td><td>{{ $tag['wip_date'] }}</td></tr>
        </table>
    </div>
    @if ($copy === 1)
        <div class="cut"></div>
    @endif
@endforeach
