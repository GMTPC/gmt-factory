<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, nextTick, reactive, ref, watch } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import QrCameraScanner from '@/components/QrCameraScanner.vue'
import { errorText, finishingRequest } from '@/lib/finishing-api'
import { isTouchOnly, printFrame } from '@/lib/print-pdf'
import { toast } from '@/lib/toast'

type Station = { id: number; code: string; name: string; input_material: string; output_material: string; next_station_id: number | null; is_final: boolean }
type Material = { id: number; code: string | null; name: string; type: string; quantity: string; status: string }
type Job = { id: number; job_no: string; user?: { name: string } | null; employee_names?: (string | { name: string })[] | null; machine_name?: string | null; pvc_type?: string | null; inspector_name?: string | null; recorder?: { name: string } | null; raw_material_name?: string | null; raw_materials?: RawMaterialUse[] | null; product_type_code?: string | null; product_type_name?: string | null; station_id: number; material_id: number | null; shift_date: string; shift: string; status: string; notes: string | null; production_logs_count: number; material: Material | null }
type RawMaterialUse = { name: string; quantity: number | null; unit: string | null }
type SummaryItem = { sequence: number; in_code: string | null; in_quantity: number; out_code: string | null; out_lot: string | null; out_quantity: number; weight_kg: number | null; time: string | null }
type JobSummary = { items: SummaryItem[]; pending_items: { in_code: string; in_quantity: number }[]; input_tags: number; input_quantity: number; output_tags: number; quantity_in: number; quantity_out: number; defects: number; weight_kg: number; pending_quantity: number; draft_count: number; started_at: string | null; first_scan_at: string | null; last_scan_at: string | null; ended_at: string | null; is_completed: boolean; minutes: number; scan_minutes: number }
type Log = { id: number; job_id: number; created_at?: string; sequence?: number; shift_date: string; shift: string; quantity_in: string; quantity_out: string; defect_count: string; ft_count: string | null; sub_type: string | null; has_labeling: boolean; status: string; notes: string | null; material: Material; scan_code?: string | null; tag_url?: string | null; output_material?: Material | null; user?: { name: string }; weight_kg?: string | null; raw_material_name?: string | null; product_type_code?: string | null; product_type_name?: string | null; output_tag_code?: string | null; output_lot?: string | null; defect_details?: { defect_type_id: number; quantity: string; notes: string | null }[] }
const props = defineProps<{ station: Station; stations: { id: number; code: string; name: string }[]; job: Job; materials: Material[]; logs: Log[]; shift: { shift: string; shift_date: string }; isAdmin: boolean; rawMaterials: string[]; rawMaterialUnits: string[]; summary: JobSummary; defectTypes: { id: number; name: string }[]; csrf: string }>()
const busy = ref(false)
const message = ref('')
const error = ref('')
const scanCode = ref('')
const scanError = ref('')
const scanMessage = ref('')
const scanInput = ref<HTMLInputElement | null>(null)
const weightInput = ref<HTMLInputElement | null>(null)
type ScanPreview = { scan_code: string; quantity_in: number; quantity_out: number; product_type_code: string | null; product_type_name: string | null; sets_job_product_type?: boolean }
const preview = ref<ScanPreview | null>(null)
const previewing = ref(false)
const weightKg = ref('')
// Raw materials belong to the whole Job (not a pallet). Pick several, change any time; scanning never waits on it.
const jobRawMaterials = computed<RawMaterialUse[]>(() => props.job.raw_materials ?? (props.job.raw_material_name ? [{ name: props.job.raw_material_name, quantity: null, unit: null }] : []))
function rawMaterialText(item: RawMaterialUse) { return item.quantity != null ? `${item.name} · ${formatQty(item.quantity)} ${item.unit ?? ''}`.trim() : item.name }
const showRawMaterials = ref(false)
const rawMaterialSearch = ref('')
type RawMaterialDraft = { name: string; quantity: string; unit: string }
const rawMaterialDraft = ref<RawMaterialDraft[]>([])
const rawMaterialBusy = ref(false)
const rawMaterialError = ref('')
const filteredRawMaterials = computed(() => props.rawMaterials.filter((name) => name.toLocaleLowerCase().includes(rawMaterialSearch.value.trim().toLocaleLowerCase())))
const draftFor = (name: string) => rawMaterialDraft.value.find((item) => item.name === name)
// Rows shown on the page: one per raw material, quantity typed straight in and saved automatically.
const toRows = (items: RawMaterialUse[]): RawMaterialDraft[] => items.map((item) => ({ name: item.name, quantity: item.quantity != null ? String(item.quantity) : '', unit: item.unit ?? props.rawMaterialUnits[0] ?? '' }))
const rawRows = ref<RawMaterialDraft[]>(toRows(jobRawMaterials.value))
watch(jobRawMaterials, (items) => { rawRows.value = toRows(items) })
// What is on screen right now (includes quantities just typed), used by the summary and tag details.
const liveRawMaterials = computed<RawMaterialUse[]>(() => rawRows.value.map((row) => ({ name: row.name, quantity: row.quantity === '' ? null : Number(row.quantity), unit: row.quantity === '' ? null : row.unit })))
const rawRowsMissingQty = computed(() => rawRows.value.filter((row) => row.quantity === '').length)
const rawSaveState = ref<'idle' | 'saving' | 'saved' | 'error'>('idle')
const payloadOf = (rows: RawMaterialDraft[]) => rows.map((row) => ({ name: row.name, quantity: row.quantity === '' ? null : row.quantity, unit: row.quantity === '' ? null : row.unit || null }))
async function saveRawRows() {
    rawSaveState.value = 'saving'; rawMaterialError.value = ''
    try {
        await finishingRequest(`/api/finishing/jobs/${props.job.id}/raw-materials`, 'PUT', { raw_materials: payloadOf(rawRows.value) }, props.csrf)
        rawSaveState.value = 'saved'
        toast('บันทึกจำนวนวัตถุดิบแล้ว')
    } catch (e) { rawSaveState.value = 'error'; rawMaterialError.value = errorText(e); toast(rawMaterialError.value, 'error') }
}
function openRawMaterials() {
    rawMaterialDraft.value = rawRows.value.map((row) => ({ ...row }))
    rawMaterialSearch.value = ''; rawMaterialError.value = ''; showRawMaterials.value = true
}
function toggleRawMaterial(name: string) {
    rawMaterialDraft.value = draftFor(name)
        ? rawMaterialDraft.value.filter((item) => item.name !== name)
        : [...rawMaterialDraft.value, { name, quantity: '', unit: props.rawMaterialUnits[0] ?? '' }]
}
async function saveRawMaterials() {
    rawMaterialBusy.value = true; rawMaterialError.value = ''
    try {
        await finishingRequest(`/api/finishing/jobs/${props.job.id}/raw-materials`, 'PUT', { raw_materials: payloadOf(rawMaterialDraft.value) }, props.csrf)
        rawRows.value = rawMaterialDraft.value.map((row) => ({ ...row }))
        showRawMaterials.value = false
        toast(`บันทึกวัตถุดิบ ${rawRows.value.length} รายการแล้ว`)
        router.reload({ only: ['job', 'summary'], preserveScroll: true })
    } catch (e) { rawMaterialError.value = errorText(e) } finally { rawMaterialBusy.value = false }
}
const lastTag = ref<{ id: number; code: string | null; lot: string | null; url: string | null; productType: string; quantity: number; weight: string } | null>(null)
const editingLogId = ref<number | null>(null)
const editingScanCode = ref('')
const selectedJob = computed(() => props.job)
const form = reactive({ material_id: String(props.job.material_id ?? ''), shift_date: props.job.shift_date.slice(0, 10), shift: props.job.shift, quantity_in: '', quantity_out: '', defect_count: '0', ft_count: '', sub_type: '', has_labeling: false, notes: '' })
const defectRows = ref<{ defect_type_id: string; quantity: string; notes: string }[]>([])
const jobLogs = computed(() => props.logs)
const canConfirm = computed(() => !!preview.value && preview.value.scan_code === scanCode.value.trim().toUpperCase() && Number(weightKg.value) > 0)

function resetNotices() { message.value = ''; error.value = '' }
function formatQty(value: number | string) { return Number(value).toLocaleString('th-TH', { maximumFractionDigits: 3 }) }
function productTypeText(code?: string | null, name?: string | null) { return code ? (name ? `${code} · ${name}` : code) : '-' }
// Camera scan: the decoded text goes through exactly the same path as a hand-scanner (preview, then weight).
const showCamera = ref(false)
function onCameraDetected(text: string) {
    showCamera.value = false
    scanCode.value = text
    onScanInput()
    previewScan()
}
function onScanInput() { scanError.value = ''; preview.value = null }

async function previewScan() {
    if (busy.value || previewing.value) return
    const barcode = scanCode.value.trim()
    if (!barcode) { scanError.value = 'กรุณาสแกนบาร์โค้ด WIP'; scanInput.value?.focus(); return }
    if (preview.value?.scan_code === barcode.toUpperCase()) { weightInput.value?.focus(); return }
    scanError.value = ''; scanMessage.value = ''; previewing.value = true
    try {
        preview.value = await finishingRequest<ScanPreview>(`/api/finishing/jobs/${props.job.id}/scan-preview?raw_scan_code=${encodeURIComponent(barcode)}`, 'GET', undefined, props.csrf)
        await nextTick()
        weightInput.value?.focus()
    } catch (e) { preview.value = null; scanError.value = errorText(e); scanInput.value?.select() } finally { previewing.value = false }
}

async function scanAndConfirm() {
    if (busy.value) return
    if (!canConfirm.value) {
        if (!preview.value) return previewScan()
        scanError.value = 'กรุณากรอกน้ำหนัก WIP ออก (กก.)'; weightInput.value?.focus(); return
    }
    const current = preview.value!
    resetNotices(); scanError.value = ''; scanMessage.value = ''; busy.value = true
    try {
        const log = await finishingRequest<Log>(`/api/finishing/jobs/${props.job.id}/scan`, 'POST', {
            raw_scan_code: current.scan_code, weight_kg: weightKg.value,
            ft_count: form.ft_count || null, sub_type: form.sub_type || null,
            has_labeling: form.has_labeling, notes: form.notes || null,
        }, props.csrf)
        lastTag.value = { id: log.id, code: log.output_tag_code ?? null, lot: log.output_lot ?? null, url: log.tag_url ?? null, productType: productTypeText(current.product_type_code, current.product_type_name), quantity: Number(log.quantity_out), weight: weightKg.value }
        toast(`ออก Tag WIP ${log.output_tag_code ?? ''} แล้ว`.replace('  ', ' '))
        if (log.tag_url) openTag({ ...log, scan_code: current.scan_code })
        scanCode.value = ''; weightKg.value = ''; preview.value = null
        const subType = form.sub_type
        const hasLabeling = form.has_labeling
        resetForm()
        form.sub_type = subType
        form.has_labeling = hasLabeling
        router.reload({ preserveScroll: true, onSuccess: () => nextTick(() => scanInput.value?.focus()) })
    } catch (e) { scanError.value = errorText(e) } finally { busy.value = false }
}
function resetForm() {
    editingLogId.value = null
    editingScanCode.value = ''
    Object.assign(form, { material_id: String(props.job.material_id ?? ''), shift_date: props.job.shift_date.slice(0, 10), shift: props.job.shift, quantity_in: '', quantity_out: '', defect_count: '0', ft_count: '', sub_type: '', has_labeling: false, notes: '' })
    defectRows.value = []
}
async function saveDraft() {
    resetNotices(); busy.value = true
    if (props.job.status !== 'active' || !props.job.material_id) { error.value = 'Job นี้ยังเปิดไม่ได้ หรือยังไม่ได้กำหนด WIP'; busy.value = false; return }
    if (!editingLogId.value) { error.value = 'สแกนบาร์โค้ดเพื่อบันทึกรายการใหม่'; busy.value = false; return }
    try {
        const data = { station_id: props.station.id, ...form, material_id: String(props.job.material_id), shift_date: props.job.shift_date.slice(0, 10), shift: props.job.shift, ft_count: form.ft_count || null, sub_type: form.sub_type || null }
        const log = await finishingRequest<Log>(`/api/finishing/production-logs/${editingLogId.value}`, 'PATCH', data, props.csrf)
        const defects = defectRows.value.filter((row) => row.defect_type_id && Number(row.quantity) > 0)
        await finishingRequest(`/api/finishing/production-logs/${log.id}/defects`, 'PUT', { defects }, props.csrf)
        message.value = 'อัปเดตร่างแล้ว'
        resetForm()
        router.reload({ preserveScroll: true })
    } catch (e) { error.value = errorText(e) } finally { busy.value = false }
}
function editLog(log: Log) {
    editingLogId.value = log.id
    editingScanCode.value = log.scan_code ?? ''
    Object.assign(form, { material_id: String(log.material?.id ?? ''), shift_date: log.shift_date.slice(0, 10), shift: log.shift, quantity_in: String(log.quantity_in), quantity_out: String(log.quantity_out), defect_count: String(log.defect_count), ft_count: log.ft_count ?? '', sub_type: log.sub_type ?? '', has_labeling: log.has_labeling, notes: log.notes ?? '' })
    defectRows.value = (log.defect_details ?? []).map((item) => ({ defect_type_id: String(item.defect_type_id), quantity: String(item.quantity), notes: item.notes ?? '' }))
    window.scrollTo({ top: 0, behavior: 'smooth' })
}
function addDefectRow() { defectRows.value.push({ defect_type_id: '', quantity: '', notes: '' }) }
async function confirmLog(log: Log) {
    if (!window.confirm(`ยืนยันรายการ #${log.id}? หลังยืนยันจะปรับยอดวัตถุดิบ`)) return
    resetNotices(); busy.value = true
    try { await finishingRequest(`/api/finishing/production-logs/${log.id}/confirm`, 'PATCH', {}, props.csrf); message.value = 'ยืนยันรายการและปรับยอดวัตถุดิบแล้ว'; router.reload({ preserveScroll: true }) }
    catch (e) { error.value = errorText(e) } finally { busy.value = false }
}
async function cancelLog(log: Log) {
    if (!window.confirm(`ยกเลิกรายการ #${log.id}?`)) return
    resetNotices(); busy.value = true
    try { await finishingRequest(`/api/finishing/production-logs/${log.id}/cancel`, 'PATCH', {}, props.csrf); message.value = 'ยกเลิกรายการแล้ว'; router.reload({ preserveScroll: true }) }
    catch (e) { error.value = errorText(e) } finally { busy.value = false }
}
// "จบงาน": show the Job summary first, then close the Job.
const showSummary = ref(false)
const finishing = ref(false)
const finishError = ref('')
const employeesText = computed(() => (props.job.employee_names ?? []).map((item) => typeof item === 'string' ? item : item.name).join(', ') || '-')
function durationText(minutes: number) {
    const days = Math.floor(minutes / 1440)
    const hours = Math.floor((minutes % 1440) / 60)
    return [days ? `${days} วัน` : '', hours ? `${hours} ชม.` : '', `${minutes % 60} นาที`].filter(Boolean).join(' ')
}
function openSummary() {
    // PC: full page with every detail; phones: the in-page summary dialog.
    if (window.matchMedia('(min-width: 768px)').matches) {
        router.visit(`/finishing/station/${props.station.code}/jobs/${props.job.id}/summary`)
        return
    }
    finishError.value = ''; showSummary.value = true; router.reload({ only: ['summary', 'job'] })
}
async function finishJob() {
    if (finishing.value) return
    finishing.value = true; finishError.value = ''
    try {
        await finishingRequest(`/api/finishing/jobs/${props.job.id}/complete`, 'PATCH', {}, props.csrf)
        message.value = 'จบงานแล้ว · Job นี้ปิดแล้ว'
        toast('จบงานแล้ว')
        router.reload({ preserveScroll: true })
    } catch (e) { finishError.value = errorText(e); toast(finishError.value, 'error') } finally { finishing.value = false }
}

// Tag preview: details + the real PDF, print dialog opens automatically on desktop.
const tagPreview = ref<Log | null>(null)
const tagFrame = ref<HTMLIFrameElement | null>(null)
const touchOnly = isTouchOnly()
let tagAutoPrinted = false
function openTag(log: Log) { if (!log.tag_url) return; tagAutoPrinted = false; tagPreview.value = log }
function onTagFrameLoad() {
    if (tagAutoPrinted || !tagPreview.value?.tag_url) return
    tagAutoPrinted = true
    const url = tagPreview.value.tag_url
    window.setTimeout(() => printFrame(tagFrame.value, url), 300)
}
function printTag() { if (tagPreview.value?.tag_url) printFrame(tagFrame.value, tagPreview.value.tag_url) }
function formatTime(value?: string) { return value ? new Date(value).toLocaleString('th-TH', { timeZone: 'Asia/Bangkok', dateStyle: 'short', timeStyle: 'short' }) : '-' }

// Edit weight only (the rest of an issued tag is fixed).
const weightEdit = ref<Log | null>(null)
const weightEditValue = ref('')
const weightEditError = ref('')
const weightEditBusy = ref(false)
const weightEditInput = ref<HTMLInputElement | null>(null)
function openWeightEdit(log: Log) {
    weightEdit.value = log; weightEditValue.value = log.weight_kg != null ? Number(log.weight_kg).toFixed(2) : ''; weightEditError.value = ''
    nextTick(() => { weightEditInput.value?.focus(); weightEditInput.value?.select() })
}
async function saveWeightEdit() {
    if (!weightEdit.value || weightEditBusy.value) return
    if (!(Number(weightEditValue.value) > 0)) { weightEditError.value = 'น้ำหนักต้องมากกว่า 0'; return }
    weightEditBusy.value = true; weightEditError.value = ''
    try {
        await finishingRequest(`/api/finishing/production-logs/${weightEdit.value.id}/weight`, 'PATCH', { weight_kg: weightEditValue.value }, props.csrf)
        message.value = `แก้น้ำหนักรายการ #${weightEdit.value.id} เป็น ${Number(weightEditValue.value).toFixed(2)} กก. แล้ว · พิมพ์ Tag ใหม่เพื่อให้น้ำหนักตรง`
        toast(`แก้น้ำหนักเป็น ${Number(weightEditValue.value).toFixed(2)} กก. แล้ว`)
        weightEdit.value = null
        router.reload({ only: ['logs'], preserveScroll: true })
    } catch (e) { weightEditError.value = errorText(e) } finally { weightEditBusy.value = false }
}
function shiftLabel(value: string) { return value === 'day' ? 'กะเช้า' : 'กะดึก' }
</script>

<template>
    <Head :title="`${station.code} · Finishing`" />
    <AppLayout :title="`Finishing · ${station.code}`" full-width hide-header>
        <main class="hmi flex min-w-0 flex-col gap-3 pb-6">
            <header class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white shadow-sm">
                <div class="flex items-center gap-3 px-3 py-2.5">
                    <Link :href="`/finishing/station/${station.code}/jobs`" class="mono shrink-0 rounded bg-emerald-700 px-2.5 py-1.5 text-base font-extrabold text-white" :aria-label="`กลับรายการ Job ${station.code}`">{{ station.code }}</Link>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start gap-2"><h1 class="m-0 min-w-0 flex-1 break-words text-base font-bold leading-snug text-[var(--hmi-title)]"><template v-if="selectedJob.product_type_code"><span class="mono">{{ selectedJob.product_type_code }}</span>{{ selectedJob.product_type_name ? ` · ${selectedJob.product_type_name}` : '' }}</template><span v-else class="text-amber-700">สินค้า: กำหนดจาก Tag แรก</span></h1><span class="mt-0.5 shrink-0 rounded-full px-2 py-0.5 text-[11px] font-bold" :class="selectedJob.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'">{{ selectedJob.status === 'active' ? 'ACTIVE' : 'ปิดแล้ว' }}</span></div>
                        <p class="m-0 truncate text-xs text-[var(--hmi-mute)]">{{ selectedJob.shift_date.slice(0, 10) }} · {{ shiftLabel(selectedJob.shift) }} · WIP {{ selectedJob.material ? (selectedJob.material.code || selectedJob.material.name) : 'ยังไม่กำหนด' }}<template v-if="selectedJob.material"> · รอแปรรูป {{ formatQty(selectedJob.material.quantity) }}</template> · {{ selectedJob.production_logs_count }} รายการ</p>
                    </div>
                    <nav class="hidden shrink-0 gap-2 md:flex"><Link :href="`/finishing/station/${station.code}/jobs`" class="rounded border border-[var(--hmi-line)] bg-white px-3 py-1.5 text-sm font-semibold">รายการ Job</Link><Link :href="`/finishing/station/${station.code}/history`" class="rounded border border-[var(--hmi-line)] bg-white px-3 py-1.5 text-sm font-semibold">ประวัติ</Link><Link href="/finishing/dashboard" class="rounded border border-[var(--hmi-line)] bg-white px-3 py-1.5 text-sm font-semibold">ผังเครื่องจักร</Link></nav>
                </div>
            </header>
            <div v-if="message" role="status" class="rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ message }}</div>
            <div v-if="error" role="alert" class="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ error }}</div>

            <section v-if="selectedJob.status !== 'active'" class="overflow-hidden rounded-md border border-slate-300 bg-white shadow-sm">
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 border-b border-slate-200 bg-slate-100 px-4 py-2.5 text-sm">
                    <span class="inline-flex items-center gap-1.5 font-bold text-slate-800"><i class="ph-fill ph-lock-simple" aria-hidden="true"></i>จบงานแล้ว</span>
                    <span v-if="summary.ended_at" class="text-slate-600">{{ summary.ended_at }}</span>
                    <span class="text-slate-600">· ใช้เวลา <strong class="text-slate-800">{{ durationText(summary.minutes) }}</strong></span>
                    <span class="ml-auto text-xs text-slate-500">ดูข้อมูลและพิมพ์ Tag ซ้ำได้ แต่เพิ่มหรือแก้ไขไม่ได้</span>
                </div>
                <dl class="m-0 grid grid-cols-1 gap-x-6 gap-y-3 p-4 text-sm sm:grid-cols-2 xl:grid-cols-4">
                    <div class="sm:col-span-2"><dt class="text-xs font-bold text-slate-500">ผู้คัด / ผู้แปรรูป</dt><dd class="m-0 font-semibold">{{ employeesText }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-500">ผู้ควบคุม / ผู้ตรวจสอบ</dt><dd class="m-0 font-semibold">{{ selectedJob.inspector_name || '-' }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-500">ผู้บันทึก</dt><dd class="m-0 font-semibold">{{ selectedJob.recorder?.name || '-' }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-500">เครื่อง</dt><dd class="m-0 font-semibold">{{ selectedJob.machine_name || '-' }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-500">ชนิดแผ่น</dt><dd class="m-0 font-semibold">{{ selectedJob.pvc_type || '-' }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-500">ผู้สร้าง Job</dt><dd class="m-0 font-semibold">{{ selectedJob.user?.name || '-' }}</dd></div>
                    <div><dt class="text-xs font-bold text-slate-500">เริ่ม → จบ</dt><dd class="m-0 font-semibold">{{ summary.started_at || '-' }} → {{ summary.ended_at || '-' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs font-bold text-slate-500">วัตถุดิบที่ใช้</dt><dd class="m-0 font-semibold">{{ liveRawMaterials.length ? liveRawMaterials.map(rawMaterialText).join(', ') : '-' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs font-bold text-slate-500">WIP เข้า → WIP ออก</dt><dd class="m-0 font-semibold"><span class="mono">{{ formatQty(summary.input_quantity) }}</span> แผ่น ({{ summary.input_tags }} Tag) → <span class="mono text-emerald-800">{{ formatQty(summary.quantity_out) }}</span> แผ่น ({{ summary.output_tags }} Tag) · <span class="mono">{{ Number(summary.weight_kg).toFixed(2) }}</span> กก.<template v-if="summary.defects > 0"> · เสีย {{ formatQty(summary.defects) }}</template></dd></div>
                </dl>
            </section>
            <div v-else-if="!selectedJob.material_id" class="rounded border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">Job นี้ยังไม่ผูก WIP กลับไปเลือก WIP ที่หน้า <Link :href="`/finishing/station/${station.code}/jobs`" class="font-bold underline">รายการ Job</Link> ก่อนเริ่มผลิต</div>
            <section v-if="selectedJob.status === 'active' && selectedJob.material_id" class="min-w-0 overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white">
                <div class="flex items-center gap-2 border-b border-[var(--hmi-line)] bg-[var(--hmi-panel)] px-3 py-2"><span class="mono rounded-sm bg-[var(--hmi-yellow)] px-2 py-0.5 text-[11px] font-extrabold">{{ editingLogId ? `EDIT #${editingLogId}` : 'SCAN' }}</span><h2 class="m-0 text-sm font-bold">{{ editingLogId ? 'แก้ไขรายการร่าง' : 'สแกน WIP เข้า / ออก' }}</h2></div>
                <form class="grid grid-cols-1 gap-3 p-3 sm:grid-cols-2 xl:grid-cols-4" @submit.prevent="editingLogId ? saveDraft() : scanAndConfirm()">
                    <div v-if="!editingLogId" class="min-w-0 space-y-3 sm:col-span-2 xl:col-span-4">
                        <div class="overflow-hidden rounded border" :class="rawRows.length ? 'border-slate-200' : 'border-red-300 bg-red-50'">
                            <div class="flex items-center gap-2 border-b px-3 py-2" :class="rawRows.length ? 'border-slate-200 bg-slate-50' : 'border-red-200'">
                                <i class="ph ph-stack text-lg" :class="rawRows.length ? 'text-slate-500' : 'text-red-600'" aria-hidden="true"></i>
                                <span class="min-w-0 flex-1 text-sm font-bold" :class="rawRows.length ? 'text-slate-700' : 'text-red-700'">วัตถุดิบของ Job <span v-if="rawRows.length" class="font-normal text-slate-500">({{ rawRows.length }})</span></span>
                                <span v-if="rawSaveState === 'saving'" class="shrink-0 text-xs text-slate-500">กำลังบันทึก...</span>
                                <span v-else-if="rawSaveState === 'saved'" class="shrink-0 text-xs font-semibold text-emerald-700">✓ บันทึกแล้ว</span>
                                <button type="button" class="shrink-0 rounded border border-slate-300 bg-white px-2.5 py-1 text-xs font-bold text-emerald-800" aria-haspopup="dialog" @click="openRawMaterials">{{ rawRows.length ? 'เพิ่ม / ลบ' : 'เลือกวัตถุดิบ' }} ›</button>
                            </div>
                            <button v-if="!rawRows.length" type="button" class="w-full px-3 py-3 text-left text-sm font-semibold text-red-600" @click="openRawMaterials">ยังไม่ระบุ (เลือกเมื่อไรก็ได้)</button>
                            <ul v-else class="m-0 divide-y divide-slate-100 p-0">
                                <li v-for="row in rawRows" :key="row.name" class="m-0 list-none px-3 py-2">
                                    <p class="m-0 break-words text-sm font-semibold text-slate-900">{{ row.name }}</p>
                                    <div class="mt-1.5 flex items-center gap-2">
                                        <span class="shrink-0 text-xs text-slate-500">ใช้ไป</span>
                                        <input v-model="row.quantity" type="number" inputmode="decimal" min="0" step="0.01" class="mono min-h-10 w-0 min-w-0 flex-1 rounded text-base font-bold" :class="row.quantity === '' ? 'border-red-400 bg-red-50 placeholder:text-red-400' : 'border-slate-300'" placeholder="กรอกจำนวน" :aria-label="`จำนวนที่ใช้ ${row.name}`" @change="saveRawRows" @keydown.enter.prevent="($event.target as HTMLInputElement).blur()" />
                                        <select v-model="row.unit" class="min-h-10 w-24 shrink-0 rounded border-slate-300 text-sm" :aria-label="`หน่วย ${row.name}`" @change="row.quantity !== '' && saveRawRows()"><option v-for="unit in rawMaterialUnits" :key="unit" :value="unit">{{ unit }}</option></select>
                                    </div>
                                </li>
                            </ul>
                            <p v-if="rawMaterialError && !showRawMaterials" role="alert" class="m-0 border-t border-red-200 bg-red-50 px-3 py-2 text-xs text-red-800">{{ rawMaterialError }}</p>
                            <p v-else-if="rawRowsMissingQty" class="m-0 border-t border-red-100 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700">ยังไม่กรอกจำนวน {{ rawRowsMissingQty }} รายการ</p>
                        </div>

                        <div>
                            <label for="finishing-process-scan" class="mb-1 flex flex-wrap items-baseline justify-between gap-x-3 text-xs text-slate-500"><span>ยิง TAG WIP (รับเข้า)</span><span v-if="selectedJob.product_type_code">รับเฉพาะสินค้า <strong class="mono text-slate-800">{{ selectedJob.product_type_code }}</strong></span><span v-else class="text-amber-700">Tag แรกจะกำหนดประเภทสินค้าของ Job นี้ · 1 Job = 1 ประเภทสินค้า</span></label>
                            <div class="flex min-h-14 min-w-0 items-center gap-3 rounded border-2 border-[var(--hmi-line)] bg-[var(--hmi-panel)] px-3 focus-within:border-emerald-700 focus-within:bg-white"><i class="ph ph-barcode text-2xl text-emerald-700" aria-hidden="true"></i><input id="finishing-process-scan" ref="scanInput" v-model="scanCode" autofocus autocomplete="off" maxlength="255" spellcheck="false" class="mono min-w-0 flex-1 border-0 bg-transparent p-0 text-base font-bold focus:ring-0" placeholder="สแกนหรือพิมพ์รหัส แล้วกด Enter" @keydown.enter.prevent="previewScan" @input="onScanInput" /><span v-if="previewing" class="text-xs text-[var(--hmi-mute)]">กำลังตรวจ...</span><button type="button" class="-mr-1 flex min-h-11 shrink-0 items-center gap-1.5 rounded-md bg-emerald-700 px-3 text-sm font-bold text-white" aria-label="สแกนด้วยกล้อง" @click="showCamera = true"><i class="ph ph-camera text-xl" aria-hidden="true"></i><span class="hidden sm:inline">กล้อง</span></button></div>
                            <p v-if="scanError" role="alert" class="mb-0 mt-2 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">{{ scanError }}</p>
                        </div>

                        <template v-if="preview">
                            <dl class="m-0 grid grid-cols-2 gap-x-4 gap-y-3 border-y border-slate-200 py-3">
                                <div class="col-span-2"><dt class="text-xs text-slate-500">รหัสสินค้า</dt><dd class="m-0 text-base"><span class="mono font-bold">{{ preview.product_type_code || '-' }}</span><template v-if="preview.product_type_name"> · {{ preview.product_type_name }}</template></dd><dd v-if="preview.sets_job_product_type && preview.product_type_code" class="m-0 mt-1 inline-block rounded bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-900">Tag แรก · Job นี้จะเป็นสินค้า {{ preview.product_type_code }}</dd><dd v-else-if="preview.product_type_code" class="m-0 mt-1 inline-block rounded bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-800">✓ ตรงกับสินค้าของ Job</dd></div>
                                <div><dt class="text-xs text-slate-500">จำนวนแผ่น (WIP เข้า)</dt><dd class="mono m-0 text-xl font-bold">{{ formatQty(preview.quantity_in) }}</dd></div>
                                <div><dt class="text-xs text-slate-500">จำนวนแผ่น (WIP ออก)</dt><dd class="mono m-0 text-xl font-bold">{{ formatQty(preview.quantity_out) }}</dd></div>
                            </dl>
                            <div>
                                <label for="finishing-weight" class="mb-1 block text-sm font-bold">น้ำหนัก (กก.) <span class="text-red-600">*</span></label>
                                <input id="finishing-weight" ref="weightInput" v-model="weightKg" type="number" inputmode="decimal" min="0.01" step="0.01" required class="mono min-h-14 w-full rounded border-2 border-emerald-600 text-2xl font-bold focus:border-emerald-700 focus:ring-emerald-600" placeholder="0.00" @keydown.enter.prevent="scanAndConfirm" />
                            </div>
                        </template>

                        <div v-if="lastTag" role="status" class="rounded border border-emerald-300 bg-emerald-50 p-3 text-sm">
                            <p class="m-0 text-xs font-bold text-emerald-800">ออก Tag WIP แล้ว · รายการ #{{ lastTag.id }}</p>
                            <p class="mono m-0 mt-1 break-all font-bold">{{ lastTag.code || '-' }}</p>
                            <p class="m-0 text-slate-600">{{ lastTag.productType }} · {{ formatQty(lastTag.quantity) }} แผ่น · {{ lastTag.weight }} กก.</p>
                            <a v-if="lastTag.url" :href="lastTag.url" target="_blank" rel="noopener" @click.prevent="openTag(jobLogs.find((item) => item.id === lastTag?.id) ?? { id: lastTag.id, tag_url: lastTag.url } as Log)" class="mt-2 flex min-h-11 items-center justify-between gap-3 rounded border border-emerald-300 bg-white px-3 font-semibold text-slate-800">
                                <span class="min-w-0"><span class="block text-[11px] font-normal text-slate-500">TagPDF</span>{{ lastTag.lot || `FIN-${lastTag.id}` }}.pdf</span><i class="ph-fill ph-file-pdf text-2xl text-emerald-700" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                    <p v-else-if="editingScanCode" class="m-0 break-all rounded border border-[var(--hmi-line)] bg-[var(--hmi-panel)] p-3 text-sm sm:col-span-2 xl:col-span-4">บาร์โค้ด WIP: <strong class="mono">{{ editingScanCode }}</strong></p>
                    <template v-if="editingLogId"><label class="text-sm">จำนวนรับเข้า<input v-model="form.quantity_in" type="number" min="0.001" step="0.001" :readonly="!!editingScanCode" class="mt-1 w-full rounded border-slate-300 read-only:bg-slate-100" required /></label><label class="text-sm">จำนวนผลิตได้<input v-model="form.quantity_out" type="number" min="0" step="0.001" class="mt-1 w-full rounded border-slate-300" required /></label><label class="text-sm">ยอดเสีย<input v-model="form.defect_count" type="number" min="0" step="0.001" class="mt-1 w-full rounded border-slate-300" required /></label></template>
                    <label v-if="['ST3','ST4'].includes(station.code)" class="text-sm">ยอด FT<input v-model="form.ft_count" type="number" min="0" step="0.001" class="mt-1 w-full rounded border-slate-300" /></label>
                    <label v-if="station.code === 'ST2' && editingLogId" class="text-sm">ประเภทการเคลือบ<select v-model="form.sub_type" class="mt-1 w-full rounded border-slate-300"><option value="">ไม่ระบุ</option><option value="single">เคลือบครั้งเดียว</option><option value="double">ติดฟอยล์ 2 รอบ</option></select></label>
                    <label v-if="station.code === 'ST3'" class="flex items-center gap-2 self-end rounded bg-emerald-50 p-3 text-sm font-semibold"><input v-model="form.has_labeling" type="checkbox" class="rounded border-slate-300 text-emerald-700" /> แปะผักแล้ว</label>
                    <label v-if="editingLogId" class="text-sm sm:col-span-2 xl:col-span-4">หมายเหตุ<textarea v-model="form.notes" rows="1" class="mt-1 w-full rounded border-slate-300" maxlength="2000" /></label>
                    <div v-if="editingLogId && Number(form.defect_count) > 0 && defectTypes.length" class="space-y-2 rounded border border-amber-200 bg-amber-50 p-3 sm:col-span-2 xl:col-span-4"><div class="flex items-center justify-between"><strong class="text-sm">แยกประเภทของเสีย (ไม่บังคับ)</strong><button type="button" class="rounded border border-amber-400 px-3 py-1 text-xs font-bold" @click="addDefectRow">+ เพิ่มประเภท</button></div><div v-for="(row, index) in defectRows" :key="index" class="grid gap-2 sm:grid-cols-3"><select v-model="row.defect_type_id" class="rounded border-slate-300 text-sm"><option value="">เลือกประเภท</option><option v-for="type in defectTypes" :key="type.id" :value="String(type.id)">{{ type.name }}</option></select><input v-model="row.quantity" type="number" min="0.001" step="0.001" class="rounded border-slate-300 text-sm" placeholder="จำนวน" /><input v-model="row.notes" class="rounded border-slate-300 text-sm" placeholder="หมายเหตุ" /></div></div>
                    <div v-if="station.code === 'ST5'" class="rounded border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900 sm:col-span-2 xl:col-span-4">สถานีทาสี/ปุลาย: กรอกยอดเสียรวมเมื่อเช็กงานปลายกะ</div>
                    <div class="sm:col-span-2 xl:col-span-4"><button :disabled="busy || previewing || (!editingLogId && !canConfirm)" class="min-h-12 w-full rounded bg-[var(--hmi-ink)] px-5 font-bold text-white disabled:opacity-50 sm:w-auto">{{ busy ? 'กำลังบันทึก...' : editingLogId ? 'บันทึกการแก้ไข' : 'ยืนยันและออก Tag WIP' }}</button><button v-if="editingLogId" type="button" class="ml-2 min-h-11 rounded border border-slate-300 px-5 font-semibold" @click="resetForm">ยกเลิกแก้ไข</button></div>
                </form>
            </section>

            <section v-if="selectedJob" class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white">
                <div class="flex items-center justify-between bg-[var(--hmi-panel)] px-4 py-3"><div><h2 class="m-0 font-bold">รายการผลิต</h2><p class="m-0 break-words text-sm font-semibold text-slate-800"><template v-if="selectedJob.product_type_code"><span class="mono">{{ selectedJob.product_type_code }}</span>{{ selectedJob.product_type_name ? ` · ${selectedJob.product_type_name}` : '' }}</template><span v-else class="font-normal text-amber-700">สินค้า: กำหนดจาก Tag แรก</span></p><p class="m-0 text-xs text-[var(--hmi-mute)]">{{ jobLogs.length }} รายการ · ยืนยันแล้วออก Tag WIP และปรับ stock ทันที</p></div><span class="mono rounded-sm bg-[var(--hmi-yellow)] px-2 py-1 text-xs font-extrabold">{{ shiftLabel(selectedJob.shift) }}</span></div>
                <div class="overflow-x-auto"><table class="tbl min-w-[1000px]"><thead><tr><th class="w-px">Tag</th><th>ลำดับ</th><th>WIP เข้า</th><th>WIP ออก</th><th>น้ำหนัก (กก.)</th><th>Tag WIP ออก</th><th>เสีย</th><th>สถานะ</th><th>บันทึกโดย</th><th>จัดการ</th></tr></thead><tbody><tr v-for="log in jobLogs" :key="log.id"><td class="whitespace-nowrap"><div class="flex gap-1.5"><a v-if="log.tag_url" :href="log.tag_url" target="_blank" rel="noopener" @click.prevent="openTag(log)" class="inline-flex min-h-9 items-center gap-1.5 rounded bg-emerald-700 px-3 text-xs font-bold text-white" :aria-label="`พิมพ์ Tag WIP ${log.output_lot || log.id}`"><i class="ph ph-printer text-base" aria-hidden="true"></i>พิมพ์</a><button v-if="log.status !== 'cancelled' && selectedJob.status === 'active'" type="button" :disabled="busy" class="inline-flex min-h-9 items-center gap-1.5 rounded border border-slate-300 bg-white px-3 text-xs font-bold text-slate-700" :aria-label="`แก้น้ำหนักรายการ #${log.id}`" @click="openWeightEdit(log)"><i class="ph ph-pencil-simple text-base" aria-hidden="true"></i>แก้ไข</button><span v-if="!log.tag_url && log.status === 'cancelled'" class="text-xs text-slate-400">-</span></div></td><td class="mono font-bold">{{ log.sequence ?? '-' }}<span v-if="log.output_lot" class="block text-[11px] font-normal text-slate-500">{{ log.output_lot.slice(6) }}</span></td><td>{{ formatQty(log.quantity_in) }}</td><td>{{ formatQty(log.quantity_out) }}</td><td>{{ log.weight_kg != null ? Number(log.weight_kg).toFixed(2) : '-' }}</td><td class="mono text-xs">{{ log.output_tag_code || log.output_material?.code || '-' }}</td><td>{{ formatQty(log.defect_count) }}</td><td><span class="rounded-full px-3 py-1 text-xs font-bold" :class="log.status === 'confirmed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900'">{{ log.status }}</span></td><td>{{ log.user?.name || '-' }}</td><td class="space-x-2 whitespace-nowrap"><button v-if="log.status === 'draft' && selectedJob.status === 'active'" :disabled="busy" class="rounded border border-slate-300 px-3 py-2 text-xs font-bold" @click="editLog(log)">แก้ไขร่าง</button><button v-if="log.status === 'draft' && selectedJob.status === 'active'" :disabled="busy" class="rounded bg-emerald-700 px-3 py-2 text-xs font-bold text-white" @click="confirmLog(log)">ยืนยัน</button><button v-if="log.status !== 'cancelled' && selectedJob.status === 'active'" :disabled="busy" class="rounded border border-red-300 px-3 py-2 text-xs font-bold text-red-700" @click="cancelLog(log)">ยกเลิก</button><span v-if="selectedJob.status !== 'active'" class="inline-flex items-center gap-1 text-xs text-slate-500"><i class="ph ph-lock-simple" aria-hidden="true"></i>จบงานแล้ว</span></td></tr><tr v-if="!jobLogs.length"><td colspan="10" class="py-10 text-center text-slate-500">ยังไม่มีรายการใน Job นี้</td></tr></tbody></table></div>
            </section>
            <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                <button v-if="selectedJob.status === 'active'" type="button" class="flex min-h-14 items-center justify-center gap-2 rounded-md bg-red-600 px-8 text-lg font-extrabold text-white shadow-sm hover:bg-red-700" @click="openSummary"><i class="ph-fill ph-flag-checkered text-2xl" aria-hidden="true"></i>จบงาน</button>
                <button v-else type="button" class="flex min-h-12 items-center justify-center gap-2 rounded-md border border-slate-300 bg-white px-6 font-bold text-slate-800" @click="openSummary"><i class="ph ph-clipboard-text text-xl" aria-hidden="true"></i>ดูสรุปการแปรรูป</button>
            </div>
        </main>
    <QrCameraScanner v-if="showCamera" @detected="onCameraDetected" @close="showCamera = false; nextTick(() => scanInput?.focus())" />
    <Teleport to="body">
        <div v-if="showSummary" class="fixed inset-0 z-[10000] flex items-end justify-center bg-slate-950/60 sm:items-center sm:p-4" role="presentation" @click.self="showSummary = false" @keydown.esc="showSummary = false">
            <section role="dialog" aria-modal="true" aria-label="สรุปการแปรรูป" class="flex max-h-[95vh] w-full max-w-2xl flex-col overflow-hidden rounded-t-xl bg-white shadow-2xl sm:rounded-xl">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
                    <div class="min-w-0"><p class="m-0 text-xs text-slate-500">{{ station.code }} · {{ station.name }} · {{ selectedJob.shift_date.slice(0, 10) }} {{ shiftLabel(selectedJob.shift) }}</p><h2 class="m-0 text-lg font-bold">สรุปการแปรรูป</h2></div>
                    <button type="button" class="flex size-9 shrink-0 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100" aria-label="ปิด" @click="showSummary = false"><i class="ph ph-x text-xl" aria-hidden="true"></i></button>
                </div>
                <div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-4 text-sm">
                    <p class="m-0 rounded bg-slate-50 px-3 py-2"><span class="text-slate-500">สินค้า</span> <strong>{{ productTypeText(selectedJob.product_type_code, selectedJob.product_type_name) }}</strong></p>

                    <div class="grid grid-cols-[1fr_auto_1fr] items-stretch gap-2">
                        <div class="rounded-md border border-slate-200 p-3"><p class="m-0 text-xs font-bold text-slate-500">เอาเข้า (WIP เข้า)</p><p class="mono m-0 mt-1 text-2xl font-extrabold">{{ formatQty(summary.input_quantity) }} <span class="text-sm font-semibold">แผ่น</span></p><p class="m-0 text-xs text-slate-500">{{ summary.input_tags }} Tag</p></div>
                        <div class="flex items-center text-2xl text-emerald-700" aria-hidden="true">→</div>
                        <div class="rounded-md border border-emerald-300 bg-emerald-50 p-3"><p class="m-0 text-xs font-bold text-emerald-800">ได้ออกมา (WIP ออก)</p><p class="mono m-0 mt-1 text-2xl font-extrabold text-emerald-900">{{ formatQty(summary.quantity_out) }} <span class="text-sm font-semibold">แผ่น</span></p><p class="m-0 text-xs text-emerald-800">{{ summary.output_tags }} Tag · {{ Number(summary.weight_kg).toFixed(2) }} กก.</p></div>
                    </div>

                    <div>
                        <p class="m-0 mb-1 text-xs font-bold text-slate-500">รายการ WIP เข้า → WIP ออก ({{ summary.items.length }})</p>
                        <ol v-if="summary.items.length" class="m-0 divide-y divide-slate-100 overflow-hidden rounded border border-slate-200 p-0">
                            <li v-for="item in summary.items" :key="item.sequence" class="m-0 flex list-none gap-3 px-3 py-2">
                                <span class="mono w-6 shrink-0 pt-0.5 text-right font-bold text-slate-400">{{ item.sequence }}</span>
                                <div class="min-w-0 flex-1 space-y-1">
                                    <p class="m-0 flex items-baseline justify-between gap-2"><span class="min-w-0"><span class="mr-1 rounded bg-slate-100 px-1.5 text-[11px] font-bold text-slate-600">เข้า</span><span class="mono break-all text-xs">{{ item.in_code || '-' }}</span></span><span class="mono shrink-0 font-semibold">{{ formatQty(item.in_quantity) }}</span></p>
                                    <p class="m-0 flex items-baseline justify-between gap-2"><span class="min-w-0"><span class="mr-1 rounded bg-emerald-100 px-1.5 text-[11px] font-bold text-emerald-800">ออก</span><span class="mono break-all text-xs font-semibold text-emerald-900">{{ item.out_code || '-' }}</span></span><span class="mono shrink-0 font-bold text-emerald-900">{{ formatQty(item.out_quantity) }}</span></p>
                                    <p class="m-0 text-right text-[11px] text-slate-500">{{ item.weight_kg != null ? `${item.weight_kg.toFixed(2)} กก.` : 'ไม่มีน้ำหนัก' }}<template v-if="item.time"> · {{ item.time }}</template></p>
                                </div>
                            </li>
                        </ol>
                        <p v-else class="m-0 rounded border border-dashed border-slate-300 px-3 py-4 text-center text-slate-500">ยังไม่มีรายการ</p>
                    </div>
                    <div v-if="summary.pending_items.length">
                        <p class="m-0 mb-1 text-xs font-bold text-amber-800">รับเข้าแล้ว ยังไม่ได้แปรรูป ({{ summary.pending_items.length }})</p>
                        <ul class="m-0 divide-y divide-amber-100 rounded border border-amber-200 bg-amber-50 p-0">
                            <li v-for="item in summary.pending_items" :key="item.in_code" class="m-0 flex list-none items-baseline justify-between gap-2 px-3 py-1.5"><span class="mono min-w-0 break-all text-xs">{{ item.in_code }}</span><span class="mono shrink-0 font-semibold">{{ formatQty(item.in_quantity) }}</span></li>
                        </ul>
                    </div>
                    <div v-if="summary.defects > 0 || summary.pending_quantity > 0 || summary.draft_count > 0" class="space-y-1">
                        <p v-if="summary.defects > 0" class="m-0 rounded bg-amber-50 px-3 py-2 text-amber-900">ของเสีย {{ formatQty(summary.defects) }} แผ่น</p>
                        <p v-if="summary.pending_quantity > 0" class="m-0 rounded bg-amber-50 px-3 py-2 text-amber-900">⚠ รับ WIP เข้าแล้วแต่ยังไม่ได้แปรรูป {{ formatQty(summary.pending_quantity) }} แผ่น</p>
                        <p v-if="summary.draft_count > 0" class="m-0 rounded bg-red-50 px-3 py-2 font-semibold text-red-800">มีรายการร่าง {{ summary.draft_count }} รายการ · ต้องยืนยันหรือยกเลิกก่อนจบงาน</p>
                    </div>

                    <dl class="m-0 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2"><dt class="text-xs font-bold text-slate-500">วัตถุดิบที่ใช้</dt><dd class="m-0">
                            <ul v-if="liveRawMaterials.length" class="m-0 mt-1 divide-y divide-slate-100 rounded border border-slate-200 p-0"><li v-for="item in liveRawMaterials" :key="item.name" class="m-0 flex list-none items-baseline justify-between gap-3 px-3 py-1.5"><span class="font-semibold">{{ item.name }}</span><span v-if="item.quantity != null" class="mono shrink-0 font-bold">{{ formatQty(item.quantity) }} {{ item.unit }}</span><span v-else class="shrink-0 text-xs font-bold text-red-600">ยังไม่กรอกจำนวน</span></li></ul>
                            <span v-else class="font-semibold text-red-600">ยังไม่ระบุ</span>
                        </dd></div>
                        <div class="sm:col-span-2"><dt class="text-xs font-bold text-slate-500">ผู้แปรรูป</dt><dd class="m-0 font-semibold">{{ employeesText }}</dd></div>
                        <div><dt class="text-xs font-bold text-slate-500">ผู้ควบคุม / ผู้ตรวจสอบ</dt><dd class="m-0 font-semibold">{{ selectedJob.inspector_name || '-' }}</dd></div>
                        <div><dt class="text-xs font-bold text-slate-500">ผู้บันทึก</dt><dd class="m-0 font-semibold">{{ selectedJob.recorder?.name || '-' }}</dd></div>
                        <div><dt class="text-xs font-bold text-slate-500">เครื่อง</dt><dd class="m-0 font-semibold">{{ selectedJob.machine_name || '-' }}</dd></div>
                        <div><dt class="text-xs font-bold text-slate-500">ชนิดแผ่น</dt><dd class="m-0 font-semibold">{{ selectedJob.pvc_type || '-' }}</dd></div>
                    </dl>

                    <div class="rounded-md border border-slate-200 p-3">
                        <div class="flex items-baseline justify-between gap-3"><p class="m-0 text-xs font-bold text-slate-500">ใช้เวลา</p><p class="mono m-0 text-xl font-extrabold">{{ durationText(summary.minutes) }}</p></div>
                        <dl class="m-0 mt-2 grid grid-cols-2 gap-x-4 gap-y-1 text-xs">
                            <dt class="text-slate-500">เริ่ม Job</dt><dd class="m-0 text-right">{{ summary.started_at || '-' }}</dd>
                            <dt class="text-slate-500">Tag แรก</dt><dd class="m-0 text-right">{{ summary.first_scan_at || '-' }}</dd>
                            <dt class="text-slate-500">Tag สุดท้าย</dt><dd class="m-0 text-right">{{ summary.last_scan_at || '-' }}</dd>
                            <dt class="text-slate-500">{{ summary.is_completed ? 'จบงาน' : 'ถึงตอนนี้' }}</dt><dd class="m-0 text-right">{{ summary.ended_at || '-' }}</dd>
                            <dt class="text-slate-500">ช่วงยิง Tag (แรก → สุดท้าย)</dt><dd class="m-0 text-right">{{ summary.first_scan_at ? durationText(summary.scan_minutes) : '-' }}</dd>
                        </dl>
                    </div>
                    <p v-if="finishError" role="alert" class="m-0 rounded border border-red-200 bg-red-50 px-3 py-2 text-red-800">{{ finishError }}</p>
                </div>
                <div class="flex gap-2 border-t border-slate-200 p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
                    <button type="button" class="min-h-12 flex-1 rounded border border-slate-300 px-4 font-semibold" @click="showSummary = false">{{ selectedJob.status === 'active' ? 'กลับไปทำต่อ' : 'ปิด' }}</button>
                    <button v-if="selectedJob.status === 'active'" type="button" :disabled="finishing || summary.draft_count > 0" class="flex min-h-12 flex-[2] items-center justify-center gap-2 rounded bg-red-600 px-4 font-extrabold text-white disabled:opacity-50" @click="finishJob"><i class="ph-fill ph-flag-checkered text-xl" aria-hidden="true"></i>{{ finishing ? 'กำลังจบงาน...' : 'ยืนยันจบงาน' }}</button>
                </div>
            </section>
        </div>
        <div v-if="tagPreview" class="fixed inset-0 z-[10000] flex items-end justify-center bg-slate-950/60 sm:items-center sm:p-4" role="presentation" @click.self="tagPreview = null" @keydown.esc="tagPreview = null">
            <section role="dialog" aria-modal="true" aria-label="Tag WIP" class="flex max-h-[95vh] w-full max-w-5xl flex-col overflow-hidden rounded-t-xl bg-white shadow-2xl sm:rounded-xl">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
                    <div class="min-w-0"><p class="m-0 text-xs text-slate-500">Tag WIP · รายการ #{{ tagPreview.id }}</p><h2 class="mono m-0 truncate text-lg font-bold">{{ tagPreview.output_lot || tagPreview.output_tag_code || '-' }}</h2></div>
                    <button type="button" class="flex size-9 shrink-0 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100" aria-label="ปิด" @click="tagPreview = null"><i class="ph ph-x text-xl" aria-hidden="true"></i></button>
                </div>
                <div class="grid min-h-0 flex-1 overflow-y-auto md:grid-cols-[minmax(0,1fr)_320px] md:overflow-hidden">
                    <iframe v-if="!touchOnly" ref="tagFrame" :src="tagPreview.tag_url ?? undefined" title="ตัวอย่าง Tag WIP" class="hidden h-[75vh] w-full border-0 bg-slate-100 md:block" @load="onTagFrameLoad"></iframe>
                    <div class="flex min-h-0 flex-col md:border-l md:border-slate-200">
                        <dl class="m-0 grid flex-1 grid-cols-2 content-start gap-x-4 gap-y-3 overflow-y-auto p-4 text-sm">
                            <div class="col-span-2"><dt class="text-xs text-slate-500">Tag WIP</dt><dd class="mono m-0 break-all font-bold">{{ tagPreview.output_tag_code || '-' }}</dd></div>
                            <div class="col-span-2"><dt class="text-xs text-slate-500">สินค้า</dt><dd class="m-0 font-semibold">{{ productTypeText(tagPreview.product_type_code ?? selectedJob.product_type_code, tagPreview.product_type_name ?? selectedJob.product_type_name) }}</dd></div>
                            <div><dt class="text-xs text-slate-500">จำนวน</dt><dd class="mono m-0 text-xl font-bold">{{ tagPreview.quantity_out != null ? formatQty(tagPreview.quantity_out) : '-' }}</dd></div>
                            <div><dt class="text-xs text-slate-500">น้ำหนัก (กก.)</dt><dd class="mono m-0 text-xl font-bold">{{ tagPreview.weight_kg != null ? Number(tagPreview.weight_kg).toFixed(2) : '-' }}</dd></div>
                            <div><dt class="text-xs text-slate-500">Lot</dt><dd class="mono m-0 font-semibold">{{ tagPreview.output_lot || '-' }}</dd></div>
                            <div><dt class="text-xs text-slate-500">วันที่ / กะ</dt><dd class="m-0 font-semibold">{{ selectedJob.shift_date.slice(0, 10) }} · {{ shiftLabel(selectedJob.shift) }}</dd></div>
                            <div><dt class="text-xs text-slate-500">ชนิดแผ่น</dt><dd class="m-0 font-semibold">{{ selectedJob.pvc_type || '-' }}</dd></div>
                            <div><dt class="text-xs text-slate-500">ไลน์ FN</dt><dd class="m-0 font-semibold">{{ selectedJob.machine_name || '-' }}</dd></div>
                            <div><dt class="text-xs text-slate-500">ผู้ตรวจสอบ</dt><dd class="m-0 font-semibold">{{ selectedJob.inspector_name || '-' }}</dd></div>
                            <div><dt class="text-xs text-slate-500">ผู้บันทึก</dt><dd class="m-0 font-semibold">{{ selectedJob.recorder?.name || tagPreview.user?.name || '-' }}</dd></div>
                            <div class="col-span-2"><dt class="text-xs text-slate-500">Tag WIP ต้นทาง</dt><dd class="mono m-0 break-all">{{ tagPreview.scan_code || '-' }}</dd></div>
                            <div class="col-span-2"><dt class="text-xs text-slate-500">วัตถุดิบของ Job</dt><dd class="m-0">{{ liveRawMaterials.length ? liveRawMaterials.map(rawMaterialText).join(', ') : '-' }}</dd></div>
                            <div class="col-span-2"><dt class="text-xs text-slate-500">บันทึกเมื่อ</dt><dd class="m-0">{{ formatTime(tagPreview.created_at) }}</dd></div>
                        </dl>
                        <div class="flex gap-2 border-t border-slate-200 p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
                            <button type="button" class="min-h-11 flex-1 rounded border border-slate-300 px-4 font-semibold" @click="tagPreview = null">ปิด</button>
                            <button v-if="!touchOnly" type="button" class="flex min-h-11 flex-[2] items-center justify-center gap-2 rounded bg-emerald-700 px-4 font-bold text-white" @click="printTag"><i class="ph ph-printer text-lg" aria-hidden="true"></i>พิมพ์ Tag</button>
                            <a v-else :href="tagPreview.tag_url ?? '#'" target="_blank" rel="noopener" class="flex min-h-11 flex-[2] items-center justify-center gap-2 rounded bg-emerald-700 px-4 font-bold text-white"><i class="ph ph-file-pdf text-lg" aria-hidden="true"></i>เปิด PDF เพื่อพิมพ์</a>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <div v-if="weightEdit" class="fixed inset-0 z-[10000] flex items-end justify-center bg-slate-950/50 sm:items-center sm:p-4" role="presentation" @click.self="weightEdit = null" @keydown.esc="weightEdit = null">
            <section role="dialog" aria-modal="true" aria-label="แก้ไขน้ำหนัก" class="w-full max-w-sm overflow-hidden rounded-t-xl bg-white shadow-2xl sm:rounded-xl">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
                    <div class="min-w-0"><h2 class="m-0 text-base font-bold">แก้ไขน้ำหนัก</h2><p class="mono m-0 truncate text-xs text-slate-500">#{{ weightEdit.id }} · {{ weightEdit.output_tag_code || weightEdit.output_lot || '-' }}</p></div>
                    <button type="button" class="flex size-9 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100" aria-label="ปิด" @click="weightEdit = null"><i class="ph ph-x text-xl" aria-hidden="true"></i></button>
                </div>
                <form class="space-y-3 p-4 pb-[max(1rem,env(safe-area-inset-bottom))]" @submit.prevent="saveWeightEdit">
                    <label class="block text-sm font-bold" for="finishing-weight-edit">น้ำหนัก (กก.)</label>
                    <input id="finishing-weight-edit" ref="weightEditInput" v-model="weightEditValue" type="number" inputmode="decimal" min="0.01" step="0.01" required class="mono min-h-14 w-full rounded border-2 border-emerald-600 text-2xl font-bold focus:border-emerald-700 focus:ring-emerald-600" />
                    <p class="m-0 text-xs text-slate-500">แก้ได้เฉพาะน้ำหนัก · หลังบันทึกให้พิมพ์ Tag ใหม่</p>
                    <p v-if="weightEditError" role="alert" class="m-0 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">{{ weightEditError }}</p>
                    <div class="flex justify-end gap-2">
                        <button type="button" class="min-h-11 rounded border border-slate-300 px-4 font-semibold" @click="weightEdit = null">ยกเลิก</button>
                        <button :disabled="weightEditBusy" class="min-h-11 rounded bg-emerald-700 px-5 font-bold text-white disabled:opacity-50">{{ weightEditBusy ? 'กำลังบันทึก...' : 'บันทึก' }}</button>
                    </div>
                </form>
            </section>
        </div>
        <div v-if="showRawMaterials" class="fixed inset-0 z-[10000] flex items-end justify-center bg-slate-950/50 sm:items-center sm:p-4" role="presentation" @click.self="showRawMaterials = false" @keydown.esc="showRawMaterials = false">
            <section role="dialog" aria-modal="true" aria-label="วัตถุดิบของ Job" class="flex max-h-[85vh] w-full max-w-lg flex-col overflow-hidden rounded-t-xl bg-white shadow-2xl sm:rounded-xl">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
                    <div><h2 class="m-0 text-base font-bold">วัตถุดิบของ Job</h2><p class="m-0 text-xs text-slate-500">วัตถุดิบรวมที่ใช้ใน Job นี้ · เลือกได้หลายรายการ</p></div>
                    <button type="button" class="flex size-9 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100" aria-label="ปิด" @click="showRawMaterials = false"><i class="ph ph-x text-xl" aria-hidden="true"></i></button>
                </div>
                <div class="border-b border-slate-200 p-3"><input v-model="rawMaterialSearch" type="search" class="w-full rounded border-slate-300 text-sm" placeholder="ค้นหาวัตถุดิบ" aria-label="ค้นหาวัตถุดิบ" /></div>
                <div class="min-h-0 flex-1 overflow-y-auto p-1">
                    <div v-for="name in filteredRawMaterials" :key="name" class="rounded" :class="draftFor(name) ? 'bg-emerald-50' : ''">
                        <label class="flex min-h-12 w-full cursor-pointer items-center gap-3 rounded px-3 py-2 text-sm hover:bg-slate-50" :class="draftFor(name) ? 'font-bold text-emerald-900 hover:bg-emerald-50' : ''">
                            <input type="checkbox" class="size-5 rounded border-slate-400 text-emerald-700 focus:ring-emerald-600" :checked="!!draftFor(name)" @change="toggleRawMaterial(name)" /><span>{{ name }}</span>
                        </label>
                    </div>
                    <p v-if="!filteredRawMaterials.length" class="m-0 px-3 py-6 text-center text-sm text-slate-500">ไม่พบวัตถุดิบ</p>
                </div>
                <div class="space-y-2 border-t border-slate-200 p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
                    <p v-if="rawMaterialError" role="alert" class="m-0 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">{{ rawMaterialError }}</p>
                    <div class="flex items-center gap-2">
                        <span class="mr-auto text-sm text-slate-600">เลือกแล้ว {{ rawMaterialDraft.length }} รายการ <span class="block text-xs text-slate-400">กรอกจำนวนในหน้าหลัก</span></span>
                        <button type="button" class="min-h-11 rounded border border-slate-300 px-4 font-semibold" @click="showRawMaterials = false">ยกเลิก</button>
                        <button type="button" :disabled="rawMaterialBusy" class="min-h-11 rounded bg-emerald-700 px-5 font-bold text-white disabled:opacity-50" @click="saveRawMaterials">{{ rawMaterialBusy ? 'กำลังบันทึก...' : 'บันทึก' }}</button>
                    </div>
                </div>
            </section>
        </div>
    </Teleport>
    </AppLayout>
</template>
