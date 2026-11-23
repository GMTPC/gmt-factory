<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import { computed, onMounted, ref, watch } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { lineStyle } from '@/lib/cut-board-line'

type JobLine = {
    id: number
    header_id: number
    source_type: 'WIP' | 'FG'
    source_id: string
    source_line_digit?: string
    barcode: string
    employee_group_name: string | null
    employee_group_id?: number | null
    spec_code: string | null
    spec_name: string | null
    sku_code: string | null
    lot_no: string | null
    brand_name?: string | null
    brand_id?: number | null
    brand_code?: string | null
    checker?: string | null
    backboard_no?: string | null
    remark?: string | null
    out_code: string | null
    qty: string | number
    qc_status: string
    receive_status: string
    print_status: string
    wms_status?: string
    rejection_reason: string | null
    scanned_at?: string | null
    ng_qty?: number | string | null
    ng_reason?: string | null
    print_url?: string | null
}
type DetailTab = 'home' | 'csv' | 'wip' | 'fg'

const props = defineProps<{
    job: {
        id: number
        job_no: string
        production_line: string
        work_date: string
        status: string
        scrap_qty: number
        group_code: string | null
        product_type_name: string | null
        headers: Array<{ id: number; header_no: string; group_code: string | null; sku_code: string | null; product_type_name?: string | null; lot_no: string | null; target_qty: string; status: string; lines_count: number }>
        lines: JobLine[]
    }
    summary: { input: number; output: number; holding: number; ng: number; wip_count: number; fg_count: number }
    lineCuttingCards: Array<{
        line: string
        job_no: string
        products: Array<{ header_no: string; group_code: string | null; name: string; sku_code: string | null; wip_qty: number }>
    }>
    createHeaderUrl: string
    backUrl: string
    completeUrl: string
    scrapUrl: string
    groupUrl: string
    finishQualityUrl?: string | null
    employeeGroups: Array<{ id: number; label: string; division: string | null }>
    brands: Array<{ id: number; code: string; name: string }>
    wipStoreUrl: string | null
    fgStoreUrl: string | null
}>()

const rejectForm = useForm({ reason: '' })
const completeForm = useForm({})
const finishQualityForm = useForm({})
const scrapForm = useForm({ scrap_qty: Number(props.job.scrap_qty ?? 0) })
const scrapSuccess = ref('')
const headerForm = useForm({
    header_no: 'H-001',
    group_code: '',
    sku_code: '',
    product_type_code: '',
    product_type_name: '',
    lot_no: '',
    lot_date: '',
    target_qty: 0,
})
const wipForm = useForm({ job_id: props.job.id, header_id: props.job.headers[0]?.id ?? 0, raw_scan_code: '', source_type: 'WIP', employee_group_id: '' as number | '' })
const fgForm = useForm({ header_id: props.job.headers[0]?.id ?? 0, employee_group_id: '' as number | '', brand_id: '' as number | '', source_line_digit: '' as string, qty: 100, ng_qty: 0, ng_reason: '', checker: '', backboard_no: '', remark: '' })
const editOpen = ref(false)
const editingLine = ref<JobLine | null>(null)
const editForm = useForm({ qty: 1, employee_group_id: '' as number | '', brand_id: '' as number | '', checker: '', backboard_no: '', remark: '' })
const allLines = ref<JobLine[]>([...props.job.lines])
const activeLineCards = ref(props.lineCuttingCards.map((card) => ({ ...card, products: card.products.map((product) => ({ ...product })) })))
const currentJobStatus = ref(props.job.status)
const activeTab = ref<DetailTab>('home')
const wipSubmitting = ref(false)
const fgSubmitting = ref(false)
const editSubmitting = ref(false)
const wipError = ref('')
const wipSuccess = ref('')
const fgError = ref('')
const editError = ref('')
const wipLines = computed(() => allLines.value.filter((line) => line.source_type === 'WIP'))
const fgLines = computed(() => allLines.value.filter((line) => line.source_type === 'FG'))
const wipOrigins = computed(() => [...new Set(wipLines.value.map((line) => line.source_line_digit || (line.barcode.match(/^[WB]([1-4])/i)?.[1] ?? '')).filter((digit) => ['3', '4'].includes(digit)))].sort())
const isCrossLineMix = computed(() => props.job.production_line === 'L3' && wipOrigins.value.length > 1)
const jobLineDigit = computed(() => props.job.production_line.replace(/\D/g, ''))
// มีเฉพาะ L3 ที่คัด WIP ไลน์ 4 ได้ (ตาม flow เก่า) — ไลน์อื่นใช้เลขไลน์ของ Job เสมอ เหมือนฝั่ง CutBoardApiController::issueFg
const selectedOrigin = computed(() => props.job.production_line !== 'L3'
    ? jobLineDigit.value
    : fgForm.source_line_digit || (wipOrigins.value.length === 1 ? wipOrigins.value[0] : '3'))
function lineOrigin(item: JobLine) {
    const fromSnapshot = item.source_line_digit || item.barcode.match(/^[WB]([1-4])/i)?.[1]
    if (fromSnapshot && ['3', '4'].includes(fromSnapshot)) return fromSnapshot
    if (item.source_type === 'FG' && props.job.production_line === 'L3') return Number(String(item.lot_no || '').slice(-3)) > 500 ? '4' : '3'
    return props.job.production_line.replace(/\D/g, '')
}
const csvPreviewRows = computed(() => [
    ...wipLines.value.map((line) => ({
        barcode: line.barcode || line.source_id,
        lot: line.lot_no || '—',
        qty: Number(line.qty),
        source: lineOrigin(line),
        type: 'WIP',
    })),
    ...fgLines.value.flatMap((line) => {
        const rows = [{
            barcode: line.out_code || line.barcode || line.source_id,
            lot: line.lot_no || '—',
            qty: line.qc_status === 'passed' ? Number(line.qty) : 0,
            source: lineOrigin(line),
            type: 'FG',
        }]
        const ngQty = Number(line.ng_qty || 0) + (line.qc_status === 'rejected' ? Number(line.qty) : 0)
        if (ngQty > 0) rows.push({
            barcode: line.out_code || line.barcode || line.source_id,
            lot: line.lot_no || '—',
            qty: ngQty,
            source: lineOrigin(line),
            type: 'NG',
        })
        return rows
    }),
    ...(summary.value.holding > 0 ? [{
        barcode: '—',
        lot: '—',
        qty: summary.value.holding,
        source: jobLineDigit.value,
        type: 'HD',
    }] : []),
])
const selectedWipQty = computed(() => wipLines.value.filter((item) => item.qc_status === 'passed' && lineOrigin(item) === selectedOrigin.value).reduce((sum, item) => sum + Number(item.qty), 0))
const selectedFgLines = computed(() => fgLines.value.filter((item) => lineOrigin(item) === selectedOrigin.value))
const selectedAvailable = computed(() => selectedWipQty.value - selectedFgLines.value.filter((item) => item.qc_status === 'passed').reduce((sum, item) => sum + Number(item.qty), 0) - allLines.value.filter((item) => lineOrigin(item) === selectedOrigin.value).reduce((sum, item) => sum + (item.qc_status === 'rejected' ? Number(item.qty) : 0) + Number(item.ng_qty || 0), 0))
const summary = computed(() => {
    const input = wipLines.value.reduce((total, line) => total + Number(line.qty), 0)
    const output = fgLines.value.filter((line) => line.qc_status === 'passed').reduce((total, line) => total + Number(line.qty), 0)
    const ng = allLines.value.reduce((total, line) => total + Number(line.ng_qty ?? 0) + (line.qc_status === 'rejected' ? Number(line.qty) : 0), 0)
    return {
        input,
        output,
        ng,
        holding: input - output - ng,
        yield_percent: input > 0 ? (output / input) * 100 : 0,
        wip_count: wipLines.value.length,
        fg_count: fgLines.value.length,
    }
})
const fgPreviewLot = computed(() => {
    const selectedGroup = props.employeeGroups.find((group) => group.id === Number(fgForm.employee_group_id))
    const datePart = props.job.work_date.slice(2, 10).replaceAll('-', '')
    const prefix = `${datePart}${selectedGroup?.division || 'A'}`
    const nextSequence = Math.max(selectedOrigin.value === '4' && props.job.production_line === 'L3' ? 500 : 0, ...fgLines.value.filter((line) => (line.lot_no || '').startsWith(prefix) && lineOrigin(line) === selectedOrigin.value).map((line) => Number(String(line.lot_no).slice(-3)))) + 1
    return `${prefix}${String(nextSequence).padStart(3, '0')}`
})
const canComplete = computed(() => allLines.value.length > 0 && allLines.value.every((line) => line.qc_status === 'rejected' || (line.receive_status === 'received' && line.print_status === 'printed')))
const canIssueFg = computed(() => currentJobStatus.value === 'scanning' && summary.value.holding > 0 && !!props.fgStoreUrl && props.job.headers.length > 0)
const canEditLines = computed(() => ['draft', 'scanning'].includes(currentJobStatus.value))
const canScanWip = computed(() => !!props.wipStoreUrl && props.job.headers.length > 0 && canEditLines.value)
const canOpenFgForm = computed(() => !!props.fgStoreUrl && props.job.headers.length > 0 && canEditLines.value)
const fgStarved = computed(() => selectedAvailable.value <= 0)
const fgSubmitBlocked = computed(() => fgSubmitting.value || !canIssueFg.value || !fgForm.employee_group_id || !fgForm.brand_id || Number(fgForm.qty) + Number(fgForm.ng_qty || 0) > selectedAvailable.value || (Number(fgForm.ng_qty) > 0 && !fgForm.ng_reason))
const lineNumber = computed(() => props.job.production_line.replace(/\D/g, '').padStart(2, '0'))
// สินค้าที่กำลังคัด: ใช้ WIP รายการแรก (อัปเดตทันทีหลังสแกน) แล้วค่อย fallback ไปที่ข้อมูล Header
const currentProduct = computed(() => {
    const firstWip = wipLines.value[0]
    return {
        name: firstWip?.spec_name || props.job.product_type_name || '',
        sku: firstWip?.sku_code || props.job.headers[0]?.sku_code || '',
    }
})
const statusLamp = computed(() => {
    const map: Record<string, { lamp: string; tag: string; color: string }> = {
        scanning: { lamp: 'lamp-green', tag: 'Run', color: 'text-[#047857]' },
        draft: { lamp: 'lamp-amber', tag: 'Standby', color: 'text-[#B45309]' },
        qc_completed: { lamp: 'lamp-amber', tag: 'Hold', color: 'text-[#B45309]' },
        receiving: { lamp: 'lamp-amber', tag: 'Hold', color: 'text-[#B45309]' },
        received: { lamp: 'lamp-off', tag: 'Done', color: 'text-[#5B6573]' },
        completed: { lamp: 'lamp-off', tag: 'Done', color: 'text-[#5B6573]' },
        cancelled: { lamp: 'lamp-red', tag: 'Stop', color: 'text-[#DC2626]' },
    }
    return map[currentJobStatus.value] ?? { lamp: 'lamp-off', tag: currentJobStatus.value, color: 'text-[#5B6573]' }
})
const readouts = computed(() => [
    { th: 'รับเข้า WIP', en: 'WIP In', value: qty(summary.value.input), unit: 'แผ่น', cls: 'led-blue' },
    { th: 'WIP คงเหลือ', en: 'Buffer', value: qty(summary.value.holding), unit: 'แผ่น · รอคัด', cls: summary.value.holding < 0 ? 'led-red' : 'led-white' },
    { th: 'FG ออกแล้ว', en: 'Good', value: qty(summary.value.output), unit: 'แผ่น', cls: 'led-green' },
    { th: 'NG ทั้งหมด', en: 'Reject', value: qty(summary.value.ng), unit: 'แผ่น', cls: 'led-red' },
    { th: 'จำนวนตัดทิ้ง', en: 'Scrap', value: qty(scrapForm.scrap_qty), unit: 'แผ่น', cls: 'led-amber' },
    { th: 'Yield', en: 'FG ÷ WIP', value: `${summary.value.yield_percent.toFixed(1)}%`, unit: 'FG ผ่าน ÷ รับเข้า WIP', cls: 'led-amber' },
])
const SEGMENT_COUNT = 20
const segments = computed(() => {
    const { input, output, ng } = summary.value
    if (input <= 0) return Array.from({ length: SEGMENT_COUNT }, () => '')
    const fgCount = Math.min(SEGMENT_COUNT, Math.round((output / input) * SEGMENT_COUNT))
    let ngCount = Math.round((ng / input) * SEGMENT_COUNT)
    if (ng > 0 && ngCount === 0) ngCount = 1
    return Array.from({ length: SEGMENT_COUNT }, (_, index) => index < fgCount ? 'seg-fg' : index < fgCount + ngCount ? 'seg-ng' : '')
})
const donePercent = computed(() => summary.value.input > 0 ? Math.round(((summary.value.output + summary.value.ng) / summary.value.input) * 100) : 0)

onMounted(() => {
    const tabByHash: Record<string, DetailTab> = {
        '#summary': 'home',
        '#barcode': 'home',
        '#csvtest': 'csv',
        '#wip-detail': 'wip',
        '#detail': 'wip',
        '#fg-detail': 'fg',
        '#fa-detail': 'fg',
        '#tofg': 'fg',
    }
    activeTab.value = tabByHash[window.location.hash] ?? 'home'
})

function selectTab(tab: DetailTab) {
    activeTab.value = tab
    const hash = ({ home: '#summary', csv: '#csvtest', wip: '#wip-detail', fg: '#fg-detail' } as Record<DetailTab, string>)[tab]
    window.history.replaceState(null, '', `${window.location.pathname}${window.location.search}${hash}`)
}

watch(() => props.job.headers, (headers) => {
    if (!headers.length) return
    if (!headers.some((header) => header.id === Number(wipForm.header_id))) wipForm.header_id = headers[0].id
    if (!headers.some((header) => header.id === Number(fgForm.header_id))) fgForm.header_id = headers[0].id
}, { immediate: true })
watch(() => wipForm.header_id, (headerId) => { fgForm.header_id = Number(headerId) })
watch(() => wipForm.employee_group_id, (groupId) => { fgForm.employee_group_id = groupId })
watch(wipLines, (lines) => {
    const latestBrandCode = [...lines].reverse().find((line) => line.brand_code)?.brand_code
    const brand = latestBrandCode ? props.brands.find((item) => item.code === latestBrandCode) : null
    if (brand) fgForm.brand_id = brand.id
    const origins = [...new Set(lines.map((item) => item.source_line_digit || item.barcode.match(/^[WB]([1-4])/i)?.[1]).filter((digit): digit is string => !!digit && ['3', '4'].includes(digit)))]
    if (!fgForm.source_line_digit || !origins.includes(fgForm.source_line_digit)) fgForm.source_line_digit = origins.length === 1 ? origins[0] : origins.includes('3') ? '3' : origins[0] || ''
}, { immediate: true })
watch(() => props.job.lines, (lines) => { allLines.value = [...lines] })
watch(() => props.lineCuttingCards, (cards) => { activeLineCards.value = cards.map((card) => ({ ...card, products: card.products.map((product) => ({ ...product })) })) })
watch(() => props.job.status, (status) => { currentJobStatus.value = status })

function updateActiveProductCard(line: JobLine) {
    if (line.source_type !== 'WIP') return
    const card = activeLineCards.value.find((item) => item.job_no === props.job.job_no)
    const header = props.job.headers.find((item) => item.id === line.header_id)
    const product = card?.products.find((item) => item.header_no === header?.header_no)
    if (!product) return
    product.name = line.spec_name || line.spec_code || 'ยังไม่ระบุชนิดสินค้า'
    product.sku_code = line.sku_code || product.sku_code
    product.wip_qty += Number(line.qty)
}

async function fetchJson(url: string, method: 'POST' | 'PATCH', data: Record<string, unknown>) {
    const csrf = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content
    const response = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}) },
        body: JSON.stringify(data),
    })
    const payload = await response.json().catch(() => ({})) as { message?: string; errors?: Record<string, string[]>; line?: JobLine }
    if (!response.ok) {
        const firstError = payload.errors ? Object.values(payload.errors).flat()[0] : undefined
        throw new Error(firstError || payload.message || `Request failed (${response.status})`)
    }
    if (!payload.line) throw new Error('เซิร์ฟเวอร์ไม่ส่งข้อมูลรายการกลับมา')
    return payload.line
}

function qty(value: number | string) {
    return Number(value).toLocaleString('th-TH', { maximumFractionDigits: 3 })
}

function timeText(value?: string | null) {
    return value ? new Date(value).toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' }) : '—'
}

function dateText(value: string) {
    const [year, month, day] = value.slice(0, 10).split('-')
    return `${day}-${month}-${year}`
}

function jobStatus(value: string) {
    return ({ draft: 'รอเริ่มงาน', scanning: 'กำลังคัด', qc_completed: 'คัดเสร็จ', receiving: 'กำลังรับเข้า', received: 'รับเข้าแล้ว', completed: 'ปิดงานแล้ว', cancelled: 'ยกเลิก' } as Record<string, string>)[value] ?? value
}

function qcStatus(value: string) {
    return ({ passed: 'ผ่าน', rejected: 'ไม่ผ่าน', pending: 'รอตรวจ' } as Record<string, string>)[value] ?? value
}

function receiveStatus(value: string) {
    return value === 'received' ? 'รับเข้าแล้ว' : value === 'rejected' ? 'ไม่รับเข้า' : 'รอรับเข้า'
}

function printStatus(value: string) {
    return value === 'printed' ? 'พิมพ์แล้ว' : value === 'rejected' ? 'ไม่ต้องพิมพ์' : 'รอพิมพ์'
}

function rejectLine(lineId: number) {
    const reason = window.prompt('ระบุเหตุผลที่ Reject รายการนี้')
    if (!reason) return
    rejectForm.reason = reason
    rejectForm.post(`/cut-board-lines/${lineId}/reject`, { preserveScroll: true })
}

function finishQuality() {
    if (!props.finishQualityUrl || summary.value.holding < 0) return
    if (window.confirm('ยืนยันจบงานคัด? ระบบจะสรุปยอดเข้า ออก คงค้าง และ NG')) {
        finishQualityForm.post(props.finishQualityUrl, { preserveScroll: true })
    }
}

function completeJob() {
    if (window.confirm('ยืนยันปิด Job หลังรับเข้าและพิมพ์ Tag ครบแล้ว?')) {
        completeForm.post(props.completeUrl)
    }
}

function updateScrap() {
    scrapSuccess.value = ''
    scrapForm.patch(props.scrapUrl, {
        preserveScroll: true,
        onSuccess: () => { scrapSuccess.value = 'อัปเดตจำนวนตัดทิ้งแล้ว' },
    })
}

function createHeader() {
    headerForm.post(props.createHeaderUrl, { preserveScroll: true })
}

function selectCutter(groupId: number) {
    wipForm.employee_group_id = groupId
    wipError.value = ''
    // คืนโฟกัสให้ช่องสแกน เพื่อให้ยิงบาร์โค้ดต่อได้ทันทีหลังแตะเลือกผู้คัด
    document.getElementById('wip-scan')?.focus()
}

function scanWip() {
    if (!props.wipStoreUrl) return
    wipError.value = ''
    wipSuccess.value = ''
    if (!wipForm.employee_group_id) {
        wipError.value = 'เลือกผู้คัดก่อนสแกน WIP'
        return
    }
    wipSubmitting.value = true
    fetchJson(props.wipStoreUrl, 'POST', wipForm.data())
        .then((line) => {
            allLines.value.push(line)
            updateActiveProductCard(line)
            currentJobStatus.value = 'scanning'
            wipSuccess.value = `รับเข้าแล้ว ${line.barcode || line.source_id} · ${qty(line.qty)} แผ่น · ${timeText(line.scanned_at)}`
            if (line.wms_status === 'bridge_failed') wipError.value = 'บันทึก WIP แล้ว แต่ส่งข้อมูลไป WMS เดิมไม่สำเร็จ'
            wipForm.raw_scan_code = ''
        })
        .catch((error: Error) => { wipError.value = error.message })
        .finally(() => { wipSubmitting.value = false })
}

function issueFg() {
    if (!props.fgStoreUrl) return
    fgError.value = ''
    fgSubmitting.value = true
    fetchJson(props.fgStoreUrl, 'POST', fgForm.data())
        .then((line) => {
            allLines.value.push(line)
            currentJobStatus.value = 'scanning'
            if (line.wms_status === 'bridge_failed') fgError.value = 'บันทึก FG แล้ว แต่ส่งข้อมูลไป WMS เดิมไม่สำเร็จ'
            fgForm.qty = 100; fgForm.ng_qty = 0; fgForm.ng_reason = ''; fgForm.checker = ''; fgForm.backboard_no = ''; fgForm.remark = ''
        })
        .catch((error: Error) => { fgError.value = error.message })
        .finally(() => { fgSubmitting.value = false })
}

function retryWms(line: JobLine) {
    fetchJson(`/cut-board-lines/${line.id}/wms-export/retry`, 'POST', {})
        .then((updatedLine) => {
            const index = allLines.value.findIndex((item) => item.id === updatedLine.id)
            if (index >= 0) allLines.value[index] = updatedLine
            wipError.value = ''
            fgError.value = ''
        })
        .catch((error: Error) => {
            const message = error.message || 'ส่งข้อมูลเข้า WMS เดิมไม่สำเร็จ'
            if (line.source_type === 'WIP') wipError.value = message
            else fgError.value = message
        })
}

function openLineEdit(line: JobLine) {
    editingLine.value = line
    editForm.defaults({
        qty: Number(line.qty),
        employee_group_id: line.employee_group_id ?? '',
        brand_id: line.brand_id ?? '',
        checker: line.checker ?? '',
        backboard_no: line.backboard_no ?? '',
        remark: line.remark ?? '',
    })
    editForm.reset()
    editForm.clearErrors()
    editOpen.value = true
}

function closeLineEdit() {
    editOpen.value = false
    editingLine.value = null
    editForm.clearErrors()
}

function saveLineEdit() {
    if (!editingLine.value) return
    editError.value = ''
    editSubmitting.value = true
    fetchJson(`/api/cut-board-lines/${editingLine.value.id}`, 'PATCH', editForm.data())
        .then((line) => {
            const index = allLines.value.findIndex((item) => item.id === line.id)
            if (index >= 0) allLines.value[index] = line
            closeLineEdit()
        })
        .catch((error: Error) => { editError.value = error.message })
        .finally(() => { editSubmitting.value = false })
}
</script>

<template>
    <Head :title="`ระบบคัดบอร์ด ${job.production_line} · ${job.job_no}`" />
    <AppLayout :title="`ระบบคัดบอร์ด · ${job.production_line}`" full-width hide-header>
        <div class="hmi flex min-h-[calc(100vh-8rem)] w-full min-w-0 flex-col gap-4 pb-2" :style="lineStyle(job.production_line)">

            <!-- ===================== MACHINE CONTROL PANEL ===================== -->
            <!-- ส่วนหัวติดด้านบนขณะเลื่อน (อยู่ใต้ topbar ของธีม) — ต้องเป็นลูกตรงของ .hmi เพื่อให้ sticky ได้ตลอดทั้งหน้า -->
            <header aria-label="แผงควบคุมไลน์" class="sticky top-14 z-40 overflow-hidden rounded-t-md bg-[var(--hmi-surface)] shadow-[0_6px_16px_-8px_rgba(0,0,0,.45)] ring-1 ring-inset ring-[var(--hmi-line)] lg:top-[78px]">
                <div class="hazard" aria-hidden="true"></div>

                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[var(--hmi-line)] px-5 py-4">
                    <div class="flex min-w-0 flex-[1_1_36rem] flex-wrap items-center gap-x-5 gap-y-3">
                        <!-- ไลน์ + สินค้าที่กำลังคัด (ชื่อยาวตัดบรรทัดได้ ไม่ทับกล่องข้างๆ) -->
                        <div class="flex min-w-0 flex-[1_1_20rem] items-center gap-4">
                            <div class="flex shrink-0 flex-col items-center rounded bg-[var(--line-color)] px-3 py-1.5 leading-none text-white ring-1 ring-inset ring-white/25">
                                <span class="tag text-[11px] font-semibold">Line</span>
                                <span class="mono text-[28px] font-extrabold">{{ lineNumber }}</span>
                            </div>
                            <div class="flex min-w-0 flex-1 flex-col gap-0.5">
                                <span class="text-xs text-[var(--hmi-mute)]">กำลังคัดสินค้า<template v-if="currentProduct.sku"> · <span class="mono">{{ currentProduct.sku }}</span></template></span>
                                <h1 class="m-0 text-[22px] font-bold leading-snug break-words text-[var(--hmi-title)]" :class="{ 'text-[var(--hmi-mute)]!': !currentProduct.name }">{{ currentProduct.name || 'ยังไม่ระบุสินค้า — สแกน WIP รายการแรก' }}</h1>
                                <span class="mono text-[11px] text-[var(--hmi-mute)]">{{ job.job_no }}</span>
                            </div>
                        </div>

                        <!-- กะการผลิต + สถานะ แยกเป็นกลุ่ม มีเส้นคั่น -->
                        <div class="flex shrink-0 items-stretch gap-2 sm:border-l sm:border-[var(--hmi-line)] sm:pl-5">
                            <div class="flex items-center gap-2.5 rounded border border-[var(--hmi-line)] bg-[var(--hmi-screen)] px-3 py-1.5">
                                <i class="ph ph-calendar-check text-[22px] text-[var(--hmi-ink)]" aria-hidden="true"></i>
                                <div class="flex flex-col leading-tight">
                                    <span class="text-[11px] text-[var(--hmi-mute)]">กะการผลิต</span>
                                    <span class="whitespace-nowrap text-[15px] font-extrabold text-[var(--hmi-title)]">กลุ่ม {{ job.group_code || '—' }}<span class="mono ml-1.5 text-xs font-medium text-[var(--hmi-mute)]">{{ dateText(job.work_date) }}</span></span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 rounded border border-[var(--hmi-line)] bg-[var(--hmi-screen)] px-3 py-1.5">
                                <span class="lamp" :class="statusLamp.lamp" aria-hidden="true"></span>
                                <div class="flex flex-col leading-tight">
                                    <span class="tag text-[15px] font-bold" :class="statusLamp.color">{{ statusLamp.tag }}</span>
                                    <span class="text-[11px] text-[var(--hmi-mute)]">{{ jobStatus(currentJobStatus) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Link :href="backUrl" class="inline-flex min-h-11 items-center gap-1.5 rounded border border-[#46515E] px-4 text-sm font-medium text-[var(--hmi-text)] hover:bg-[var(--hmi-panel)]">
                            <i class="ph ph-caret-left" aria-hidden="true"></i> กลับหน้าไลน์
                        </Link>
                        <Link :href="groupUrl" class="inline-flex min-h-11 items-center gap-1.5 rounded border border-[#46515E] px-4 text-sm font-medium text-[var(--hmi-text)] hover:bg-[var(--hmi-panel)]">
                            <i class="ph ph-users-three" aria-hidden="true"></i> จัดกลุ่มพนักงาน
                        </Link>
                        <button v-if="finishQualityUrl && currentJobStatus === 'scanning'" type="button" :disabled="finishQualityForm.processing || !allLines.length || summary.holding < 0"
                                class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded border-2 border-[var(--hmi-yellow)] bg-[#C81E1E] px-4.5 text-sm font-bold text-white hover:bg-[#A31717] disabled:cursor-not-allowed disabled:opacity-50"
                                @click="finishQuality">
                            <i class="ph-fill ph-stop" aria-hidden="true"></i> จบงานคัด (END)
                        </button>
                        <button v-if="['qc_completed', 'receiving', 'received'].includes(currentJobStatus)" type="button" :disabled="completeForm.processing || !canComplete"
                                class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded border-2 border-[var(--hmi-yellow)] bg-[var(--hmi-ink)] px-4.5 text-sm font-bold text-white hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50"
                                @click="completeJob">
                            <i class="ph ph-lock-key" aria-hidden="true"></i> ปิด Job
                        </button>
                    </div>
                </div>

                <p v-if="finishQualityUrl && currentJobStatus === 'scanning'" class="m-0 flex items-center gap-2 border-b border-[var(--hmi-line)] px-5 py-2 text-[13px] text-[#B45309]">
                    <i class="ph ph-warning" aria-hidden="true"></i> ต้องรอการตรวจสอบรับเข้าคลังสินค้าให้หมด จึงจะสามารถจบงานได้
                </p>
            </header>

            <nav role="tablist" aria-label="เมนูฟังก์ชันงานคัด" class="flex flex-wrap gap-2 rounded-md border border-[var(--hmi-line)] bg-white p-2 shadow-sm">
                <button type="button" role="tab" :aria-selected="activeTab === 'home'" class="inline-flex min-h-10 items-center gap-2 rounded border px-3 text-sm font-semibold transition-colors" :class="activeTab === 'home' ? 'border-[#46515E] bg-[#1B2129] text-white' : 'border-[#A9B1BB] bg-white text-[var(--hmi-ink)] hover:bg-[#F1F3F5]'" @click="selectTab('home')">ข้อมูลเข้า (WIP) / ข้อมูลออก (FG)</button>
                <button type="button" role="tab" :aria-selected="activeTab === 'csv'" class="inline-flex min-h-10 items-center gap-2 rounded border px-3 text-sm font-semibold transition-colors" :class="activeTab === 'csv' ? 'border-amber-600 bg-amber-500 text-white' : 'border-amber-300 bg-white text-amber-800 hover:bg-amber-50'" @click="selectTab('csv')">ทดสอบ CSV</button>
                <button type="button" role="tab" :aria-selected="activeTab === 'wip'" class="inline-flex min-h-10 items-center gap-2 rounded border px-3 text-sm font-semibold transition-colors" :class="activeTab === 'wip' ? 'border-sky-700 bg-sky-700 text-white' : 'border-sky-300 bg-white text-sky-800 hover:bg-sky-50'" @click="selectTab('wip')">ข้อมูลขาเข้าแบบละเอียด</button>
                <button type="button" role="tab" :aria-selected="activeTab === 'fg'" class="inline-flex min-h-10 items-center gap-2 rounded border px-3 text-sm font-semibold transition-colors" :class="activeTab === 'fg' ? 'border-emerald-700 bg-emerald-700 text-white' : 'border-emerald-300 bg-white text-emerald-800 hover:bg-emerald-50'" @click="selectTab('fg')">ข้อมูลขาออกแบบละเอียด</button>
            </nav>

            <!-- ตัวเลขสรุป — ต่อจากส่วนหัว (-mt-4 หักระยะ gap ของ flex ให้ติดกัน) -->
            <section v-if="activeTab === 'home'" id="summary" role="tabpanel" aria-label="ยอดสรุปไลน์" class="scroll-mt-36 overflow-hidden rounded-md bg-[var(--hmi-surface)] ring-1 ring-inset ring-[var(--hmi-line)]">
                <!-- LED readouts -->
                <div class="grid grid-cols-[repeat(auto-fit,minmax(180px,1fr))] gap-3 px-5 pb-5 pt-4">
                    <div v-for="readout in readouts" :key="readout.en" class="flex flex-col gap-1 rounded border border-[var(--hmi-line)] bg-[var(--hmi-screen)] px-3.5 py-3">
                        <span class="flex justify-between text-xs text-[var(--hmi-mute)]"><span>{{ readout.th }}</span><span class="tag">{{ readout.en }}</span></span>
                        <span class="led" :class="readout.cls">{{ readout.value }}</span>
                        <span class="text-[11px] text-[var(--hmi-mute)]">{{ readout.unit }}</span>
                    </div>
                </div>

                <!-- Throughput -->
                <div class="flex flex-wrap items-center gap-x-4 gap-y-2.5 px-5 pb-4.5">
                    <span class="tag text-[13px] text-[var(--hmi-mute)]">Throughput</span>
                    <div role="img" :aria-label="`FG ${summary.output}, NG ${summary.ng}, คงเหลือ ${summary.holding} จาก ${summary.input} แผ่น`" class="grid flex-[1_1_360px] grid-cols-20 gap-[3px]">
                        <div v-for="(segment, index) in segments" :key="index" class="seg" :class="segment"></div>
                    </div>
                    <span class="mono text-[13px] text-[var(--hmi-text)]">{{ donePercent }}% คัดแล้ว</span>
                </div>
            </section>

            <section v-if="activeTab === 'home'" class="flex flex-wrap items-end gap-3 rounded-md border border-[var(--hmi-line)] bg-white p-4 shadow-sm" aria-label="จำนวนตัดทิ้ง">
                <label for="scrap-qty" class="flex min-w-56 flex-col gap-1.5 text-sm font-semibold text-[var(--hmi-ink)]">จำนวนตัดทิ้ง
                    <input id="scrap-qty" v-model.number="scrapForm.scrap_qty" type="number" min="0" max="999999" step="1" inputmode="numeric" :disabled="currentJobStatus !== 'scanning' || scrapForm.processing" class="field mono h-10!" />
                    <span v-if="scrapForm.errors.scrap_qty" class="text-xs font-normal text-red-700">{{ scrapForm.errors.scrap_qty }}</span>
                </label>
                <button type="button" :disabled="currentJobStatus !== 'scanning' || scrapForm.processing" class="min-h-10 cursor-pointer rounded bg-[var(--hmi-yellow)] px-5 text-sm font-bold text-[var(--hmi-ink)] hover:bg-[#DCA500] disabled:cursor-not-allowed disabled:opacity-50" @click="updateScrap">
                    {{ scrapForm.processing ? 'กำลังอัปเดต…' : 'อัปเดตข้อมูล' }}
                </button>
                <p v-if="scrapSuccess" role="status" class="m-0 text-sm font-medium text-emerald-700">{{ scrapSuccess }}</p>
            </section>

            <!-- ===================== สินค้าที่กำลังคัดแยกตามไลน์ ===================== -->
            <section v-if="activeTab === 'home' && activeLineCards.length" class="grid grid-cols-[repeat(auto-fit,minmax(250px,1fr))] gap-3" aria-label="สินค้าที่กำลังคัดแยกตามไลน์">
                <article v-for="lineCard in activeLineCards" :key="`${lineCard.line}-${lineCard.job_no}`" class="line-themed overflow-hidden rounded-md border border-[var(--hmi-steel)] bg-white" :style="lineStyle(lineCard.line)">
                    <header class="flex items-center justify-between gap-3 bg-[var(--hmi-panel)] px-4 py-2.5">
                        <div class="flex min-w-0 items-center gap-2.5">
                            <span class="mono shrink-0 rounded-sm bg-[var(--line-color)] px-2 py-0.5 text-[13px] font-extrabold text-white ring-1 ring-inset ring-white/25">{{ lineCard.line }}</span>
                            <div class="min-w-0">
                                <h2 class="m-0 truncate text-[15px] font-bold text-[var(--hmi-title)]">กำลังคัดสินค้า</h2>
                                <p class="mono m-0 truncate text-[11px] text-[var(--hmi-mute)]">{{ lineCard.job_no }}</p>
                            </div>
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-1.5 text-[var(--hmi-text)]"><span class="lamp lamp-green size-2.5!" aria-hidden="true"></span><span class="tag text-xs font-semibold">Run</span></span>
                    </header>
                    <div v-if="lineCard.products.length" class="divide-y divide-dashed divide-[var(--hmi-steel)]">
                        <div v-for="product in lineCard.products" :key="`${lineCard.line}-${product.header_no}`" class="flex items-center justify-between gap-3 px-4 py-2.5">
                            <div class="min-w-0">
                                <p class="m-0 truncate text-sm font-semibold">{{ product.name }}</p>
                                <p class="mono m-0 truncate text-xs text-[#4A5563]">{{ product.sku_code || product.group_code || product.header_no }}</p>
                            </div>
                            <span class="mono shrink-0 text-sm font-extrabold">{{ qty(product.wip_qty) }} <span class="text-xs font-medium text-[#4A5563]">แผ่น</span></span>
                        </div>
                    </div>
                    <div v-else class="px-4 py-3 text-sm text-[#4A5563]">ยังไม่มีข้อมูลสินค้า</div>
                </article>
            </section>

            <!-- ===================== เพิ่ม Header ===================== -->
            <section v-if="activeTab === 'home' && job.headers.length === 0" class="overflow-hidden rounded-md border border-[var(--hmi-steel)] bg-white">
                <div class="flex items-center gap-3 bg-[var(--hmi-panel)] px-4 py-2.5">
                    <span class="mono rounded-sm bg-[var(--hmi-yellow)] px-2 py-0.5 text-[13px] font-extrabold text-[var(--hmi-ink)]">SETUP</span>
                    <h2 class="m-0 text-[17px] font-bold text-[var(--hmi-title)]">เพิ่ม Header เพื่อเริ่ม Scan</h2>
                </div>
                <form class="grid gap-3 bg-[#F7F8F9] p-4 sm:grid-cols-2 xl:grid-cols-4" @submit.prevent="createHeader">
                    <input v-model="headerForm.header_no" class="field" placeholder="Header No." required />
                    <input v-model="headerForm.group_code" class="field" placeholder="กลุ่ม" />
                    <input v-model="headerForm.sku_code" class="field" placeholder="SKU Code" />
                    <input v-model="headerForm.product_type_code" class="field" placeholder="Product Type Code" />
                    <input v-model="headerForm.product_type_name" class="field sm:col-span-2" placeholder="ชนิดสินค้า" />
                    <input v-model="headerForm.lot_no" class="field" placeholder="Lot No." />
                    <input v-model="headerForm.lot_date" type="date" class="field" />
                    <input v-model="headerForm.target_qty" type="number" min="0" step="0.001" class="field" placeholder="เป้าหมาย" />
                    <button type="submit" :disabled="headerForm.processing" class="min-h-11 cursor-pointer rounded bg-[var(--hmi-yellow)] px-4 text-[15px] font-bold text-[var(--hmi-ink)] hover:bg-[#DCA500] disabled:opacity-50">เพิ่ม Header</button>
                </form>
            </section>

            <p v-if="activeTab === 'home' && summary.holding < 0" role="alert" class="m-0 flex items-center gap-2.5 border-l-4 border-[#C81E1E] bg-[#FCECEA] px-3 py-2.5 text-sm font-medium text-[#B42318]">
                <i class="ph ph-warning" aria-hidden="true"></i> ยอดคงค้างติดลบ กรุณาตรวจสอบรายการ WIP, FG และ NG ก่อนจบงาน
            </p>

            <section v-if="activeTab === 'csv'" id="csvtest" role="tabpanel" class="scroll-mt-36 overflow-hidden rounded-md border border-[var(--hmi-steel)] bg-white" aria-labelledby="csvtest-title">
                <header class="flex flex-wrap items-center justify-between gap-2 bg-[var(--hmi-panel)] px-4 py-3">
                    <h2 id="csvtest-title" class="m-0 text-base font-bold text-[var(--hmi-title)]">ทดสอบ CSV</h2>
                    <span class="tag text-xs text-[var(--hmi-mute)]">ตัวอย่างข้อมูล · ไม่บันทึกหรือแก้ไขรายการ</span>
                </header>
                <div class="overflow-x-auto p-3">
                    <table class="tbl">
                        <thead><tr><th>#</th><th>ประเภท</th><th>Barcode</th><th>LOT</th><th class="text-right!">จำนวน</th><th>ไลน์ต้นทาง</th></tr></thead>
                        <tbody>
                            <tr v-for="(row, index) in csvPreviewRows" :key="`${row.type}-${index}`">
                                <td class="mono">{{ index + 1 }}</td><td>{{ row.type }}</td><td class="mono">{{ row.barcode || '—' }}</td><td class="mono">{{ row.lot }}</td><td class="mono text-right">{{ qty(row.qty) }}</td><td>{{ row.source }}</td>
                            </tr>
                            <tr v-if="csvPreviewRows.length === 0"><td colspan="6" class="py-8! text-center text-[#4A5563]">ยังไม่มีข้อมูลสำหรับแสดงตัวอย่าง CSV</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- ===================== STATIONS ===================== -->
            <div v-if="activeTab === 'home' || activeTab === 'wip' || activeTab === 'fg'" class="grid grid-cols-1 items-stretch gap-4" :class="activeTab === 'home' ? 'xl:grid-cols-2' : ''">

                <!-- ---------- ST-01 · รับเข้า WIP ---------- -->
                <section v-if="activeTab === 'home' || activeTab === 'wip'" id="wip-detail" role="tabpanel" aria-labelledby="st1" class="scroll-mt-36 flex min-w-0 flex-col overflow-hidden rounded-md border border-[var(--hmi-steel)] bg-white">
                    <div class="flex items-center justify-between gap-3 bg-[var(--hmi-panel)] px-4 py-2.5">
                        <div class="flex items-center gap-3">
                            <span class="mono rounded-sm bg-[var(--hmi-yellow)] px-2 py-0.5 text-[13px] font-extrabold text-[var(--hmi-ink)]">ST-01</span>
                            <div class="flex flex-col">
                                <h2 id="st1" class="m-0 text-[17px] font-bold text-[var(--hmi-title)]">รับเข้า WIP</h2>
                                <span class="tag text-xs text-[var(--hmi-mute)]">WIP Intake · Scan Station</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 text-[var(--hmi-text)]">
                            <template v-if="canScanWip"><span class="lamp lamp-green size-2.5!" aria-hidden="true"></span><span class="tag text-[13px] font-semibold">Ready</span></template>
                            <template v-else><span class="lamp lamp-off size-2.5!" aria-hidden="true"></span><span class="tag text-[13px] font-semibold">Locked</span></template>
                        </span>
                    </div>

                    <form v-if="activeTab === 'home' && canScanWip" class="flex flex-col gap-3.5 border-b border-[var(--hmi-steel)] bg-[#F7F8F9] p-4" @submit.prevent="scanWip">
                        <fieldset class="m-0 flex min-w-0 flex-col gap-1.5 border-0 p-0">
                            <legend class="mb-1.5 p-0 text-[13px] font-semibold">ผู้คัด <span class="text-[#C81E1E]" aria-hidden="true">*</span>
                                <span v-if="!wipForm.employee_group_id && employeeGroups.length" class="ml-1 font-normal text-[#B42318]">— แตะเลือกก่อนสแกน</span>
                            </legend>
                            <div v-if="employeeGroups.length" class="flex flex-wrap gap-2">
                                <button v-for="group in employeeGroups" :key="group.id" type="button" :aria-pressed="Number(wipForm.employee_group_id) === group.id"
                                        class="inline-flex min-h-10 cursor-pointer items-center justify-center gap-1.5 rounded border-2 px-4 text-sm font-bold transition-colors"
                                        :class="Number(wipForm.employee_group_id) === group.id
                                            ? 'border-[var(--hmi-yellow)] bg-[var(--hmi-ink)] text-[var(--hmi-yellow)]'
                                            : 'border-[#A9B1BB] bg-white text-[var(--hmi-ink)] hover:border-[var(--hmi-ink)] hover:bg-[#F1F3F5]'"
                                        @click="selectCutter(group.id)">
                                    <i v-if="Number(wipForm.employee_group_id) === group.id" class="ph ph-check-circle text-lg" aria-hidden="true"></i>
                                    <span class="truncate">{{ group.label }}</span>
                                </button>
                            </div>
                            <p v-else class="m-0 flex flex-wrap items-center gap-2 border-l-4 border-[var(--hmi-yellow)] bg-[#FFF8E1] px-3 py-2.5 text-[13px] text-[#6B4E00]">
                                <i class="ph ph-warning" aria-hidden="true"></i> ยังไม่มีกลุ่มพนักงานของไลน์นี้
                                <Link :href="groupUrl" class="font-bold underline">จัดกลุ่มพนักงาน</Link>
                            </p>
                        </fieldset>

                        <div class="flex flex-col gap-1.5">
                            <label for="wip-scan" class="text-[13px] font-semibold">สแกนบาร์โค้ด WIP</label>
                            <div class="flex gap-2">
                                <div class="flex min-h-14 min-w-0 flex-1 items-center gap-3 rounded border-2 border-[var(--line-color)] bg-[#EEF0F2] px-3.5 focus-within:bg-white focus-within:shadow-[0_0_0_3px_rgba(245,184,0,.35)]">
                                    <i class="ph ph-barcode text-2xl text-[var(--line-color)]" aria-hidden="true"></i>
                                    <input id="wip-scan" v-model="wipForm.raw_scan_code" autofocus required autocomplete="off" placeholder="> รอสแกน…"
                                           class="mono min-w-0 flex-1 border-0 bg-transparent p-0 text-[17px] font-bold text-[#1B2129] placeholder:font-medium placeholder:text-[#8A949F] focus:outline-none focus:ring-0" />
                                </div>
                                <button type="submit" :disabled="wipSubmitting || !employeeGroups.length"
                                        class="min-h-14 shrink-0 cursor-pointer rounded bg-[var(--hmi-yellow)] px-5.5 text-[15px] font-bold text-[var(--hmi-ink)] hover:bg-[#DCA500] disabled:cursor-not-allowed disabled:opacity-50">
                                    {{ wipSubmitting ? 'กำลังบันทึก…' : 'รับเข้า WIP' }}
                                </button>
                            </div>
                        </div>

                        <p v-if="wipError" role="alert" class="m-0 flex items-center gap-2.5 border-l-4 border-[#C81E1E] bg-[#FCECEA] px-3 py-2 text-[13px] font-semibold text-[#B42318]">
                            <i class="ph ph-x-circle" aria-hidden="true"></i>{{ wipError }}
                        </p>
                        <div v-if="wipSuccess" role="status" class="flex items-center gap-2.5 border-l-4 border-[#12A060] bg-[#E7F5EE] px-3 py-2 text-[13px] text-[#0B5E3C]">
                            <i class="ph ph-check" aria-hidden="true"></i><span class="mono">{{ wipSuccess }}</span>
                        </div>
                    </form>
                    <div v-else-if="activeTab === 'home'" class="border-b border-[var(--hmi-steel)] bg-[#F7F8F9] p-4 text-sm text-[#4A5563]">ไม่สามารถสแกน WIP ในสถานะงานปัจจุบัน</div>

                    <div class="flex items-center justify-between px-4 py-2.5">
                        <span class="tag text-sm font-bold">Intake Log</span>
                        <span class="mono text-xs text-[#4A5563]">{{ wipLines.length }} รายการ · รวม {{ qty(summary.input) }} แผ่น</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="tbl">
                            <thead><tr>
                                <th>#</th><th>เวลา</th><th>Barcode / สินค้า</th><th>ผู้คัด</th><th class="text-right!">จำนวน</th><th><span class="sr-only">จัดการ</span></th>
                            </tr></thead>
                            <tbody>
                                <tr v-for="(line, index) in wipLines" :key="line.id">
                                    <td class="mono text-[#4A5563]">{{ index + 1 }}</td>
                                    <td class="mono">{{ timeText(line.scanned_at) }}</td>
                                    <td>
                                        <div class="mono text-[13px] font-bold">{{ line.barcode || line.source_id }}</div>
                                        <div class="text-xs text-[#4A5563]">{{ line.spec_name || line.spec_code || '—' }}</div>
                                    </td>
                                    <td class="text-[13px]">{{ line.employee_group_name || '—' }}</td>
                                    <td class="mono text-right text-base font-extrabold tabular-nums">{{ qty(line.qty) }}</td>
                                    <td class="text-right">
                                        <button v-if="line.wms_status === 'bridge_failed'" type="button" class="mr-1 min-h-[34px] cursor-pointer rounded border border-[#C81E1E] bg-white px-2 text-[12px] font-semibold text-[#B42318]" @click="retryWms(line)">ส่ง WMS ซ้ำ</button>
                                        <button v-if="canEditLines && line.receive_status !== 'received' && line.print_status !== 'printed'" type="button" :aria-label="`แก้ไขรายการ ${index + 1}`"
                                                class="min-h-[34px] cursor-pointer rounded border border-[#A9B1BB] bg-white px-3 text-[13px] hover:bg-[#F1F3F5]" @click="openLineEdit(line)">แก้ไข</button>
                                    </td>
                                </tr>
                                <tr v-if="wipLines.length === 0"><td colspan="6" class="py-10! text-center text-[#4A5563]">ยังไม่มีรายการ WIP</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- ---------- ST-02 · ออกเป็น FG ---------- -->
                <section v-if="activeTab === 'home' || activeTab === 'fg'" id="fg-detail" role="tabpanel" aria-labelledby="st2" class="scroll-mt-36 flex min-w-0 flex-col overflow-hidden rounded-md border border-[var(--hmi-steel)] bg-white">
                    <div class="flex items-center justify-between gap-3 bg-[var(--hmi-panel)] px-4 py-2.5">
                        <div class="flex items-center gap-3">
                            <span class="mono rounded-sm bg-[var(--hmi-yellow)] px-2 py-0.5 text-[13px] font-extrabold text-[var(--hmi-ink)]">ST-02</span>
                            <div class="flex flex-col">
                                <h2 id="st2" class="m-0 text-[17px] font-bold text-[var(--hmi-title)]">ออกเป็น FG</h2>
                                <span class="tag text-xs text-[var(--hmi-mute)]">FG Output · Lot &amp; Label</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 text-xs text-[var(--hmi-text)]">
                            <template v-if="!canOpenFgForm"><span class="lamp lamp-off size-2.5!" aria-hidden="true"></span><span class="tag text-[13px] font-semibold">Locked</span></template>
                            <template v-else-if="fgStarved"><span class="lamp lamp-amber size-2.5!" aria-hidden="true"></span><span class="tag text-[13px] font-semibold">Starved</span><span class="text-[var(--hmi-mute)]">· ไม่มี WIP</span></template>
                            <template v-else><span class="lamp lamp-green size-2.5!" aria-hidden="true"></span><span class="tag text-[13px] font-semibold">Ready</span></template>
                        </span>
                    </div>

                    <form v-if="activeTab === 'home' && canOpenFgForm" class="flex flex-col gap-3.5 border-b border-[var(--hmi-steel)] bg-[#F7F8F9] p-4" @submit.prevent="issueFg">
                        <div class="flex items-center justify-between gap-3 rounded border border-[#D5DAE0] border-l-4 border-l-[var(--line-color)] bg-[#EEF0F2] px-3.5 py-2.5">
                            <span class="text-[13px] font-medium text-[#4A5563]">WIP คงเหลือให้คัด · ไลน์ {{ selectedOrigin }}</span>
                            <span class="mono text-[26px] font-extrabold text-[#1B2129]">{{ qty(Math.max(0, selectedAvailable)) }} <span class="text-xs font-medium text-[#4A5563]">แผ่น</span></span>
                        </div>

                        <div v-if="isCrossLineMix" class="flex flex-wrap items-center gap-2">
                            <span class="text-[13px] font-semibold">เลือก WIP ต้นทาง:</span>
                            <button v-for="origin in wipOrigins" :key="origin" type="button" class="mono min-h-9 cursor-pointer rounded border px-3 text-[13px] font-bold"
                                    :class="fgForm.source_line_digit === origin ? 'border-[var(--hmi-ink)] bg-[var(--hmi-ink)] text-[var(--hmi-yellow)]' : 'border-[#A9B1BB] bg-white'"
                                    :aria-pressed="fgForm.source_line_digit === origin" @click="fgForm.source_line_digit = origin">
                                ไลน์ {{ origin }} · {{ qty(wipLines.filter((item) => lineOrigin(item) === origin).reduce((sum, item) => sum + Number(item.qty), 0)) }} แผ่น
                            </button>
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                            <label class="flex flex-col gap-1.5 text-[13px] font-semibold">FG (แผ่น)
                                <input v-model.number="fgForm.qty" type="number" min="0" :max="Math.max(0, Math.floor(selectedAvailable - Number(fgForm.ng_qty || 0)))" inputmode="numeric" placeholder="0" :disabled="fgStarved" required
                                       class="field mono h-12! text-right text-xl! font-extrabold text-[#0B6A43]" />
                            </label>
                            <label class="flex flex-col gap-1.5 text-[13px] font-semibold">NG (แผ่น)
                                <input v-model.number="fgForm.ng_qty" type="number" min="0" :max="Math.max(0, Math.floor(selectedAvailable - Number(fgForm.qty || 0)))" inputmode="numeric" placeholder="0" :disabled="fgStarved"
                                       class="field mono h-12! text-right text-xl! font-extrabold text-[#B42318]" />
                            </label>
                            <label class="flex flex-col gap-1.5 text-[13px] font-semibold">สาเหตุ NG
                                <select v-model="fgForm.ng_reason" :disabled="fgStarved" class="field h-12!">
                                    <option value="">เลือกสาเหตุ</option>
                                    <option value="ขอบแตก">ขอบแตก</option>
                                    <option value="กระดาษลอก">กระดาษลอก</option>
                                    <option value="ขนาดไม่ได้">ขนาดไม่ได้</option>
                                    <option value="บิ่น">บิ่น</option>
                                    <option value="รอยแตก">รอยแตก</option>
                                    <option value="อื่น ๆ">อื่น ๆ</option>
                                </select>
                            </label>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 rounded border border-dashed border-[#A9B1BB] bg-white px-3 py-2.5">
                            <div class="flex flex-col gap-0.5">
                                <span class="text-xs text-[#4A5563]">LOT FG ที่จะสร้าง · รหัสไลน์ {{ selectedOrigin }}</span>
                                <span class="mono text-[13px] font-bold">FG-{{ job.production_line }}-{{ fgPreviewLot }}</span>
                            </div>
                            <span class="text-xs text-[#4A5563]">หลังบันทึก คงเหลือ <b class="mono text-[var(--hmi-ink)]">{{ qty(Math.max(0, selectedAvailable - Number(fgForm.qty || 0) - Number(fgForm.ng_qty || 0))) }}</b> แผ่น</span>
                        </div>

                        <div v-if="fgStarved" class="flex flex-wrap items-center gap-2.5 border-l-4 border-[var(--hmi-yellow)] bg-[#FFF8E1] px-3 py-2.5 text-[13px] text-[#6B4E00]">
                            <i class="ph ph-warning" aria-hidden="true"></i>
                            <span>WIP หมดแล้ว — สแกนรับเข้าเพิ่มที่ ST-01 หรือกด <b>จบงานคัด</b></span>
                        </div>
                        <p v-if="fgError" role="alert" class="m-0 flex items-center gap-2.5 border-l-4 border-[#C81E1E] bg-[#FCECEA] px-3 py-2 text-[13px] font-semibold text-[#B42318]">
                            <i class="ph ph-x-circle" aria-hidden="true"></i>{{ fgError }}
                        </p>

                        <button type="submit" :disabled="fgSubmitBlocked"
                                class="inline-flex min-h-12 cursor-pointer items-center justify-center gap-2 rounded bg-[#0F7B4F] text-[15px] font-bold text-white hover:bg-[#0B6A43] disabled:cursor-not-allowed disabled:bg-[var(--hmi-steel)] disabled:text-[#4A5563]">
                            <i class="ph ph-plus" aria-hidden="true"></i> {{ fgSubmitting ? 'กำลังออกรหัส…' : 'ออกรหัส FG + พิมพ์ฉลาก' }}
                        </button>
                        <p class="m-0 text-xs text-[#4A5563]">FG + NG ต้องไม่เกิน WIP คงเหลือ · ปุ่มจะปิดเมื่อคงเหลือเป็น 0</p>
                    </form>
                    <div v-else-if="activeTab === 'home'" class="border-b border-[var(--hmi-steel)] bg-[#F7F8F9] p-4 text-sm text-[#4A5563]">ไม่สามารถออก FG ในสถานะงานปัจจุบัน</div>

                    <div class="flex items-center justify-between px-4 py-2.5">
                        <span class="tag text-sm font-bold">Output Log</span>
                        <span class="mono text-xs text-[#4A5563]">FG {{ qty(summary.output) }} · NG {{ qty(summary.ng) }}</span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="tbl">
                            <thead><tr>
                                <th>#</th><th>เวลา</th><th>LOT FG / FG Code</th><th>สินค้า</th><th class="text-right!">FG</th><th class="text-right!">NG</th><th>QC</th><th><span class="sr-only">จัดการ</span></th>
                            </tr></thead>
                            <tbody>
                                <tr v-for="(line, index) in fgLines" :key="line.id">
                                    <td class="mono text-[#4A5563]">{{ index + 1 }}</td>
                                    <td class="mono">{{ timeText(line.scanned_at) }}</td>
                                    <td>
                                        <div class="mono text-[13px] font-bold">{{ line.lot_no || '—' }}</div>
                                        <div class="mono text-[11px] text-[#4A5563]">{{ line.out_code || '—' }}</div>
                                    </td>
                                    <td>{{ line.brand_name || line.sku_code || '—' }}</td>
                                    <td class="mono text-right text-base font-extrabold tabular-nums text-[#0B6A43]">{{ line.qc_status === 'passed' ? qty(line.qty) : '0' }}</td>
                                    <td class="mono text-right text-base font-extrabold tabular-nums text-[#B42318]">{{ qty(Number(line.ng_qty || 0) + (line.qc_status === 'rejected' ? Number(line.qty) : 0)) }}</td>
                                    <td>
                                        <span class="inline-flex items-center gap-1.5 rounded-sm border px-2 py-0.5 text-xs font-bold"
                                              :class="line.qc_status === 'passed' ? 'border-[#12A060] bg-[#E7F5EE] text-[#0B5E3C]' : line.qc_status === 'rejected' ? 'border-[#C81E1E] bg-[#FCECEA] text-[#B42318]' : 'border-[#A9B1BB] bg-[#F1F3F5] text-[#4A5563]'">
                                            <span class="size-2 rounded-full" :class="line.qc_status === 'passed' ? 'bg-[#12A060]' : line.qc_status === 'rejected' ? 'bg-[#C81E1E]' : 'bg-[#A9B1BB]'" aria-hidden="true"></span>{{ qcStatus(line.qc_status) }}
                                        </span>
                                        <span class="mt-1 block text-[11px] text-[#4A5563]">{{ receiveStatus(line.receive_status) }} · {{ printStatus(line.print_status) }}</span>
                                    </td>
                                    <td class="text-right">
                                        <div class="inline-flex gap-1.5">
                                            <button v-if="line.wms_status === 'bridge_failed'" type="button" class="min-h-[34px] cursor-pointer rounded border border-[#C81E1E] bg-white px-2 text-[12px] font-semibold text-[#B42318]" @click="retryWms(line)">ส่ง WMS ซ้ำ</button>
                                            <button v-if="line.qc_status === 'passed' && line.receive_status !== 'received'" type="button" :aria-label="`Reject ${line.lot_no || line.id}`"
                                                    class="min-h-[34px] cursor-pointer rounded border border-[#C81E1E] bg-white px-2.5 text-[13px] font-semibold text-[#B42318] hover:bg-[#FCECEA]" @click="rejectLine(line.id)">Reject</button>
                                            <button v-if="canEditLines && line.receive_status !== 'received' && line.print_status !== 'printed'" type="button" :aria-label="`แก้ไข ${line.lot_no || line.id}`"
                                                    class="min-h-[34px] cursor-pointer rounded border border-[#A9B1BB] bg-white px-2.5 text-[13px] hover:bg-[#F1F3F5]" @click="openLineEdit(line)">แก้ไข</button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="fgLines.length === 0"><td colspan="8" class="py-10! text-center text-[#4A5563]">ยังไม่มีรายการ FG</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- ===================== Edit dialog ===================== -->
            <div v-if="editOpen && editingLine" class="fixed inset-0 z-[110] flex items-center justify-center bg-[#0E1318]/70 p-4" role="dialog" aria-modal="true" aria-labelledby="edit-line-title" @click.self="closeLineEdit" @keydown.esc="closeLineEdit">
                <section class="w-full max-w-xl overflow-hidden rounded-md bg-white shadow-2xl">
                    <div class="hazard" aria-hidden="true"></div>
                    <header class="flex items-start justify-between gap-3 bg-[var(--hmi-surface)] px-5 py-4">
                        <div>
                            <h2 id="edit-line-title" class="m-0 text-lg font-bold text-[var(--hmi-title)]">แก้ไขรายการ {{ editingLine.source_type }}</h2>
                            <p class="mono m-0 mt-1 break-all text-xs text-[var(--hmi-mute)]">{{ editingLine.barcode || editingLine.lot_no || editingLine.source_id }}</p>
                        </div>
                        <button type="button" class="cursor-pointer rounded px-2 py-1 text-xl text-[var(--hmi-mute)] hover:bg-[var(--hmi-panel)] hover:text-[var(--hmi-title)]" aria-label="ปิด Dialog" @click="closeLineEdit">×</button>
                    </header>
                    <form class="grid gap-3 bg-[#F7F8F9] p-5 sm:grid-cols-2" @submit.prevent="saveLineEdit">
                        <label class="flex flex-col gap-1.5 text-[13px] font-semibold">จำนวนแผ่น<input v-model.number="editForm.qty" type="number" min="1" step="1" class="field mono" required /></label>
                        <label class="flex flex-col gap-1.5 text-[13px] font-semibold">กลุ่มพนักงาน<select v-model="editForm.employee_group_id" class="field" required><option value="">เลือกกลุ่มพนักงาน</option><option v-for="group in employeeGroups" :key="group.id" :value="group.id">{{ group.label }}</option></select></label>
                        <template v-if="editingLine.source_type === 'FG'">
                            <label class="flex flex-col gap-1.5 text-[13px] font-semibold">แบรนด์<select v-model="editForm.brand_id" class="field" required><option value="">เลือกแบรนด์</option><option v-for="brand in brands" :key="brand.id" :value="brand.id">{{ brand.code }} · {{ brand.name }}</option></select></label>
                            <label class="flex flex-col gap-1.5 text-[13px] font-semibold">ผู้ตรวจ<input v-model="editForm.checker" class="field" required /></label>
                            <label class="flex flex-col gap-1.5 text-[13px] font-semibold">เลขแผ่นรอง<input v-model="editForm.backboard_no" class="field" /></label>
                            <label class="flex flex-col gap-1.5 text-[13px] font-semibold">หมายเหตุ<input v-model="editForm.remark" class="field" /></label>
                        </template>
                        <p v-if="editError" role="alert" class="m-0 border-l-4 border-[#C81E1E] bg-[#FCECEA] px-3 py-2 text-[13px] text-[#B42318] sm:col-span-2">{{ editError }}</p>
                        <footer class="flex justify-end gap-2 border-t border-[var(--hmi-steel)] pt-4 sm:col-span-2">
                            <button type="button" class="min-h-11 cursor-pointer rounded border border-[#A9B1BB] bg-white px-4 text-sm font-semibold hover:bg-[#F1F3F5]" :disabled="editSubmitting" @click="closeLineEdit">ยกเลิก</button>
                            <button type="submit" class="min-h-11 cursor-pointer rounded bg-[var(--hmi-yellow)] px-5 text-sm font-bold text-[var(--hmi-ink)] hover:bg-[#DCA500] disabled:opacity-50" :disabled="editSubmitting">{{ editSubmitting ? 'กำลังบันทึก…' : 'บันทึกการแก้ไข' }}</button>
                        </footer>
                    </form>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

