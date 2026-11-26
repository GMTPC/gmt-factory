<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, nextTick, reactive, ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { errorText, finishingRequest } from '@/lib/finishing-api'

type Station = { id: number; code: string; name: string; input_material: string; output_material: string; next_station_id: number | null; is_final: boolean }
type Material = { id: number; code: string | null; name: string; type: string; quantity: string; status: string }
type Job = { id: number; job_no: string; raw_material_name?: string | null; station_id: number; material_id: number | null; shift_date: string; shift: string; status: string; notes: string | null; production_logs_count: number; material: Material | null }
type Log = { id: number; job_id: number; shift_date: string; shift: string; quantity_in: string; quantity_out: string; defect_count: string; ft_count: string | null; sub_type: string | null; has_labeling: boolean; status: string; notes: string | null; material: Material; scan_code?: string | null; tag_url?: string | null; output_material?: Material | null; user?: { name: string }; weight_kg?: string | null; raw_material_name?: string | null; product_type_code?: string | null; product_type_name?: string | null; output_tag_code?: string | null; output_lot?: string | null; defect_details?: { defect_type_id: number; quantity: string; notes: string | null }[] }
const props = defineProps<{ station: Station; stations: { id: number; code: string; name: string }[]; job: Job; materials: Material[]; logs: Log[]; shift: { shift: string; shift_date: string }; isAdmin: boolean; rawMaterials: string[]; defectTypes: { id: number; name: string }[]; csrf: string }>()
const busy = ref(false)
const message = ref('')
const error = ref('')
const scanCode = ref('')
const scanError = ref('')
const scanMessage = ref('')
const scanInput = ref<HTMLInputElement | null>(null)
const weightInput = ref<HTMLInputElement | null>(null)
type ScanPreview = { scan_code: string; quantity_in: number; quantity_out: number; product_type_code: string | null; product_type_name: string | null }
const preview = ref<ScanPreview | null>(null)
const previewing = ref(false)
const weightKg = ref('')
// Raw material is picked per scan and kept for the next scan (usually the same roll/batch for a whole Job).
const lastRawMaterial = props.logs.find((log) => log.raw_material_name)?.raw_material_name ?? props.job.raw_material_name ?? ''
const rawMaterial = ref(props.rawMaterials.includes(lastRawMaterial) ? lastRawMaterial : '')
const showRawMaterials = ref(false)
const rawMaterialSearch = ref('')
const filteredRawMaterials = computed(() => props.rawMaterials.filter((name) => name.toLocaleLowerCase().includes(rawMaterialSearch.value.trim().toLocaleLowerCase())))
function pickRawMaterial(name: string) { rawMaterial.value = name; showRawMaterials.value = false; rawMaterialSearch.value = ''; scanError.value = ''; nextTick(() => (preview.value ? weightInput.value : scanInput.value)?.focus()) }
const lastTag = ref<{ id: number; code: string | null; lot: string | null; url: string | null; productType: string; rawMaterial: string; quantity: number; weight: string } | null>(null)
const editingLogId = ref<number | null>(null)
const editingScanCode = ref('')
const selectedJob = computed(() => props.job)
const form = reactive({ material_id: String(props.job.material_id ?? ''), shift_date: props.job.shift_date.slice(0, 10), shift: props.job.shift, quantity_in: '', quantity_out: '', defect_count: '0', ft_count: '', sub_type: '', has_labeling: false, notes: '' })
const defectRows = ref<{ defect_type_id: string; quantity: string; notes: string }[]>([])
const jobLogs = computed(() => props.logs)
const canConfirm = computed(() => !!rawMaterial.value && !!preview.value && preview.value.scan_code === scanCode.value.trim().toUpperCase() && Number(weightKg.value) > 0)

function resetNotices() { message.value = ''; error.value = '' }
function formatQty(value: number | string) { return Number(value).toLocaleString('th-TH', { maximumFractionDigits: 3 }) }
function productTypeText(code?: string | null, name?: string | null) { return code ? (name ? `${code} · ${name}` : code) : '-' }
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
        if (!rawMaterial.value) { scanError.value = 'กรุณาเลือกวัตถุดิบ'; showRawMaterials.value = true; return }
        if (!preview.value) return previewScan()
        scanError.value = 'กรุณากรอกน้ำหนัก WIP ออก (กก.)'; weightInput.value?.focus(); return
    }
    const current = preview.value!
    resetNotices(); scanError.value = ''; scanMessage.value = ''; busy.value = true
    try {
        const log = await finishingRequest<Log>(`/api/finishing/jobs/${props.job.id}/scan`, 'POST', {
            raw_scan_code: current.scan_code, weight_kg: weightKg.value, raw_material_name: rawMaterial.value,
            ft_count: form.ft_count || null, sub_type: form.sub_type || null,
            has_labeling: form.has_labeling, notes: form.notes || null,
        }, props.csrf)
        lastTag.value = { id: log.id, code: log.output_tag_code ?? null, lot: log.output_lot ?? null, url: log.tag_url ?? null, productType: productTypeText(current.product_type_code, current.product_type_name), rawMaterial: rawMaterial.value, quantity: Number(log.quantity_out), weight: weightKg.value }
        if (log.tag_url) window.open(log.tag_url, '_blank', 'noopener')
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
function shiftLabel(value: string) { return value === 'day' ? 'กะเช้า' : 'กะดึก' }
</script>

<template>
    <Head :title="`${station.code} · Finishing`" />
    <AppLayout :title="`Finishing · ${station.code}`" full-width hide-header>
        <main class="hmi flex flex-col gap-3 pb-6">
            <header class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white shadow-sm">
                <div class="flex items-center gap-3 px-3 py-2.5">
                    <Link :href="`/finishing/station/${station.code}/jobs`" class="mono shrink-0 rounded bg-emerald-700 px-2.5 py-1.5 text-base font-extrabold text-white" :aria-label="`กลับรายการ Job ${station.code}`">{{ station.code }}</Link>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2"><h1 class="m-0 truncate text-base font-bold text-[var(--hmi-title)]">{{ selectedJob.job_no }}</h1><span class="shrink-0 rounded-full px-2 py-0.5 text-[11px] font-bold" :class="selectedJob.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'">{{ selectedJob.status === 'active' ? 'ACTIVE' : 'ปิดแล้ว' }}</span></div>
                        <p class="m-0 truncate text-xs text-[var(--hmi-mute)]">{{ selectedJob.shift_date.slice(0, 10) }} · {{ shiftLabel(selectedJob.shift) }} · WIP {{ selectedJob.material ? (selectedJob.material.code || selectedJob.material.name) : 'ยังไม่กำหนด' }}<template v-if="selectedJob.material"> · รอแปรรูป {{ formatQty(selectedJob.material.quantity) }}</template> · {{ selectedJob.production_logs_count }} รายการ</p>
                    </div>
                    <nav class="hidden shrink-0 gap-2 md:flex"><Link :href="`/finishing/station/${station.code}/jobs`" class="rounded border border-[var(--hmi-line)] bg-white px-3 py-1.5 text-sm font-semibold">รายการ Job</Link><Link :href="`/finishing/station/${station.code}/history`" class="rounded border border-[var(--hmi-line)] bg-white px-3 py-1.5 text-sm font-semibold">ประวัติ</Link><Link href="/finishing/dashboard" class="rounded border border-[var(--hmi-line)] bg-white px-3 py-1.5 text-sm font-semibold">ผังเครื่องจักร</Link></nav>
                </div>
            </header>
            <div v-if="message" role="status" class="rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ message }}</div>
            <div v-if="error" role="alert" class="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ error }}</div>

            <div v-if="selectedJob.status !== 'active'" class="rounded border border-slate-300 bg-slate-100 p-4 text-sm text-slate-700">Job นี้ปิดแล้ว เปิดดูประวัติได้ แต่เพิ่มผลผลิตไม่ได้</div>
            <div v-else-if="!selectedJob.material_id" class="rounded border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">Job นี้ยังไม่ผูก WIP กลับไปเลือก WIP ที่หน้า <Link :href="`/finishing/station/${station.code}/jobs`" class="font-bold underline">รายการ Job</Link> ก่อนเริ่มผลิต</div>
            <section v-if="selectedJob.status === 'active' && selectedJob.material_id" class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white">
                <div class="flex items-center gap-2 border-b border-[var(--hmi-line)] bg-[var(--hmi-panel)] px-3 py-2"><span class="mono rounded-sm bg-[var(--hmi-yellow)] px-2 py-0.5 text-[11px] font-extrabold">{{ editingLogId ? `EDIT #${editingLogId}` : 'SCAN' }}</span><h2 class="m-0 text-sm font-bold">{{ editingLogId ? 'แก้ไขรายการร่าง' : 'สแกน WIP เข้า / ออก' }}</h2></div>
                <form class="grid gap-3 p-3 sm:grid-cols-2 xl:grid-cols-4" @submit.prevent="editingLogId ? saveDraft() : scanAndConfirm()">
                    <div v-if="!editingLogId" class="space-y-3 sm:col-span-2 xl:col-span-4">
                        <button type="button" class="flex w-full items-center gap-2 rounded border px-3 py-2 text-left text-sm" :class="rawMaterial ? 'border-slate-200 bg-slate-50' : 'border-amber-400 bg-amber-50'" aria-haspopup="dialog" @click="showRawMaterials = true; rawMaterialSearch = ''">
                            <i class="ph ph-stack text-lg" :class="rawMaterial ? 'text-slate-500' : 'text-amber-700'" aria-hidden="true"></i>
                            <span class="shrink-0 text-slate-500">วัตถุดิบ</span>
                            <span class="min-w-0 flex-1 truncate font-semibold" :class="rawMaterial ? 'text-slate-900' : 'text-amber-900'">{{ rawMaterial || 'ยังไม่ได้เลือก' }}</span>
                            <span class="shrink-0 text-xs font-bold text-emerald-800">{{ rawMaterial ? 'เปลี่ยน' : 'เลือก' }} ›</span>
                        </button>

                        <div>
                            <label for="finishing-process-scan" class="mb-1 block text-xs text-slate-500">ยิง TAG WIP (รับเข้า)</label>
                            <div class="flex min-h-14 min-w-0 items-center gap-3 rounded border-2 border-[var(--hmi-line)] bg-[var(--hmi-panel)] px-3 focus-within:border-emerald-700 focus-within:bg-white"><i class="ph ph-barcode text-2xl text-emerald-700" aria-hidden="true"></i><input id="finishing-process-scan" ref="scanInput" v-model="scanCode" autofocus autocomplete="off" maxlength="255" spellcheck="false" class="mono min-w-0 flex-1 border-0 bg-transparent p-0 text-base font-bold focus:ring-0" placeholder="สแกนหรือพิมพ์รหัส แล้วกด Enter" @keydown.enter.prevent="previewScan" @input="onScanInput" /><span v-if="previewing" class="text-xs text-[var(--hmi-mute)]">กำลังตรวจ...</span></div>
                            <p v-if="scanError" role="alert" class="mb-0 mt-2 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">{{ scanError }}</p>
                        </div>

                        <template v-if="preview">
                            <dl class="m-0 grid grid-cols-2 gap-x-4 gap-y-3 border-y border-slate-200 py-3">
                                <div class="col-span-2"><dt class="text-xs text-slate-500">รหัสสินค้า</dt><dd class="m-0 text-base"><span class="mono font-bold">{{ preview.product_type_code || '-' }}</span><template v-if="preview.product_type_name"> · {{ preview.product_type_name }}</template></dd></div>
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
                            <a v-if="lastTag.url" :href="lastTag.url" target="_blank" rel="noopener" class="mt-2 flex min-h-11 items-center justify-between gap-3 rounded border border-emerald-300 bg-white px-3 font-semibold text-slate-800">
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
                <div class="flex items-center justify-between bg-[var(--hmi-panel)] px-4 py-3"><div><h2 class="m-0 font-bold">รายการผลิตใน Job {{ selectedJob.job_no }}</h2><p class="m-0 text-xs text-[var(--hmi-mute)]">{{ jobLogs.length }} รายการ · ยืนยันแล้วออก Tag WIP และปรับ stock ทันที</p></div><span class="mono rounded-sm bg-[var(--hmi-yellow)] px-2 py-1 text-xs font-extrabold">{{ shiftLabel(selectedJob.shift) }}</span></div>
                <div class="overflow-x-auto"><table class="tbl min-w-[1300px]"><thead><tr><th>#</th><th>ประเภทสินค้า</th><th>วัตถุดิบ</th><th>WIP เข้า</th><th>WIP ออก</th><th>น้ำหนัก (กก.)</th><th>Tag WIP ออก</th><th>เสีย</th><th>สถานะ</th><th>บันทึกโดย</th><th>จัดการ</th></tr></thead><tbody><tr v-for="log in jobLogs" :key="log.id"><td>{{ log.id }}</td><td>{{ productTypeText(log.product_type_code, log.product_type_name) }}</td><td>{{ log.raw_material_name || '-' }}</td><td>{{ formatQty(log.quantity_in) }}</td><td>{{ formatQty(log.quantity_out) }}</td><td>{{ log.weight_kg != null ? Number(log.weight_kg).toFixed(2) : '-' }}</td><td class="mono text-xs">{{ log.output_tag_code || log.output_material?.code || '-' }}</td><td>{{ formatQty(log.defect_count) }}</td><td><span class="rounded-full px-3 py-1 text-xs font-bold" :class="log.status === 'confirmed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900'">{{ log.status }}</span></td><td>{{ log.user?.name || '-' }}</td><td class="space-x-2 whitespace-nowrap"><a v-if="log.tag_url" :href="log.tag_url" target="_blank" rel="noopener" class="rounded border border-emerald-700 px-3 py-2 text-xs font-bold text-emerald-800">พิมพ์ Tag WIP</a><button v-if="log.status === 'draft'" :disabled="busy" class="rounded border border-slate-300 px-3 py-2 text-xs font-bold" @click="editLog(log)">แก้ไข</button><button v-if="log.status === 'draft'" :disabled="busy" class="rounded bg-emerald-700 px-3 py-2 text-xs font-bold text-white" @click="confirmLog(log)">ยืนยัน</button><button v-if="log.status !== 'cancelled'" :disabled="busy" class="rounded border border-red-300 px-3 py-2 text-xs font-bold text-red-700" @click="cancelLog(log)">ยกเลิก</button></td></tr><tr v-if="!jobLogs.length"><td colspan="11" class="py-10 text-center text-slate-500">ยังไม่มีรายการใน Job นี้</td></tr></tbody></table></div>
            </section>
        </main>
    <Teleport to="body">
        <div v-if="showRawMaterials" class="fixed inset-0 z-[10000] flex items-end justify-center bg-slate-950/50 sm:items-center sm:p-4" role="presentation" @click.self="showRawMaterials = false" @keydown.esc="showRawMaterials = false">
            <section role="dialog" aria-modal="true" aria-label="เลือกวัตถุดิบ" class="flex max-h-[80vh] w-full max-w-lg flex-col overflow-hidden rounded-t-xl bg-white shadow-2xl sm:rounded-xl">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
                    <h2 class="m-0 text-base font-bold">เลือกวัตถุดิบ</h2>
                    <button type="button" class="flex size-9 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100" aria-label="ปิด" @click="showRawMaterials = false"><i class="ph ph-x text-xl" aria-hidden="true"></i></button>
                </div>
                <div class="border-b border-slate-200 p-3"><input v-model="rawMaterialSearch" type="search" class="w-full rounded border-slate-300 text-sm" placeholder="ค้นหาวัตถุดิบ" aria-label="ค้นหาวัตถุดิบ" /></div>
                <div class="min-h-0 flex-1 overflow-y-auto p-1 pb-[max(0.25rem,env(safe-area-inset-bottom))]">
                    <button v-for="name in filteredRawMaterials" :key="name" type="button" class="flex min-h-12 w-full items-center gap-3 rounded px-3 py-2 text-left text-sm hover:bg-slate-50" :class="rawMaterial === name ? 'bg-emerald-50 font-bold text-emerald-900' : ''" @click="pickRawMaterial(name)">
                        <i class="text-xl" :class="rawMaterial === name ? 'ph-fill ph-check-circle text-emerald-700' : 'ph ph-circle text-slate-400'" aria-hidden="true"></i><span>{{ name }}</span>
                    </button>
                    <p v-if="!filteredRawMaterials.length" class="m-0 px-3 py-6 text-center text-sm text-slate-500">ไม่พบวัตถุดิบ</p>
                </div>
            </section>
        </div>
    </Teleport>
    </AppLayout>
</template>
