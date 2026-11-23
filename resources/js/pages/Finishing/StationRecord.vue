<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, reactive, ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { errorText, finishingRequest } from '@/lib/finishing-api'

type Station = { id: number; code: string; name: string; input_material: string; output_material: string; next_station_id: number | null; is_final: boolean }
type Material = { id: number; code: string | null; name: string; type: string; quantity: string; status: string }
type Job = { id: number; job_no: string; station_id: number; material_id: number | null; shift_date: string; shift: string; status: string; notes: string | null; production_logs_count: number; material: Material | null }
type Log = { id: number; job_id: number; shift_date: string; shift: string; quantity_in: string; quantity_out: string; defect_count: string; ft_count: string | null; sub_type: string | null; has_labeling: boolean; status: string; notes: string | null; material: Material; user?: { name: string }; defect_details?: { defect_type_id: number; quantity: string; notes: string | null }[] }
const props = defineProps<{ station: Station; stations: { id: number; code: string; name: string }[]; job: Job; materials: Material[]; logs: Log[]; shift: { shift: string; shift_date: string }; isAdmin: boolean; defectTypes: { id: number; name: string }[]; csrf: string }>()
const busy = ref(false)
const message = ref('')
const error = ref('')
const scanCode = ref('')
const editingLogId = ref<number | null>(null)
const selectedJob = computed(() => props.job)
const form = reactive({ material_id: String(props.job.material_id ?? ''), shift_date: props.job.shift_date.slice(0, 10), shift: props.job.shift, quantity_in: '', quantity_out: '', defect_count: '0', ft_count: '', sub_type: '', has_labeling: false, notes: '' })
const defectRows = ref<{ defect_type_id: string; quantity: string; notes: string }[]>([])
const jobLogs = computed(() => props.logs)

function resetNotices() { message.value = ''; error.value = '' }
function resetForm() {
    editingLogId.value = null
    Object.assign(form, { material_id: String(props.job.material_id ?? ''), shift_date: props.job.shift_date.slice(0, 10), shift: props.job.shift, quantity_in: '', quantity_out: '', defect_count: '0', ft_count: '', sub_type: '', has_labeling: false, notes: '' })
    defectRows.value = []
}
async function saveDraft() {
    resetNotices(); busy.value = true
    if (props.job.status !== 'active' || !props.job.material_id) { error.value = 'Job นี้ยังเปิดไม่ได้ หรือยังไม่ได้กำหนด WIP'; busy.value = false; return }
    let savedId: number | null = editingLogId.value
    try {
        const data = { station_id: props.station.id, ...form, material_id: String(props.job.material_id), shift_date: props.job.shift_date.slice(0, 10), shift: props.job.shift, ft_count: form.ft_count || null, sub_type: form.sub_type || null }
        const log = editingLogId.value
            ? await finishingRequest<Log>(`/api/finishing/production-logs/${editingLogId.value}`, 'PATCH', data, props.csrf)
            : await finishingRequest<Log>('/api/finishing/production-logs', 'POST', { ...data, job_id: props.job.id }, props.csrf)
        savedId = log.id
        const defects = defectRows.value.filter((row) => row.defect_type_id && Number(row.quantity) > 0)
        if (defects.length || editingLogId.value) await finishingRequest(`/api/finishing/production-logs/${log.id}/defects`, 'PUT', { defects }, props.csrf)
        message.value = editingLogId.value ? 'อัปเดตร่างแล้ว' : `บันทึกร่างใน Job ${props.job.job_no} แล้ว ตรวจข้อมูลก่อนกดยืนยัน`
        resetForm()
        router.reload({ preserveScroll: true })
    } catch (e) {
        error.value = errorText(e)
        if (savedId && !editingLogId.value) { editingLogId.value = savedId; router.reload({ preserveScroll: true }) }
    } finally { busy.value = false }
}
function editLog(log: Log) {
    editingLogId.value = log.id
    Object.assign(form, { material_id: String(log.material?.id ?? ''), shift_date: log.shift_date.slice(0, 10), shift: log.shift, quantity_in: String(log.quantity_in), quantity_out: String(log.quantity_out), defect_count: String(log.defect_count), ft_count: log.ft_count ?? '', sub_type: log.sub_type ?? '', has_labeling: log.has_labeling, notes: log.notes ?? '' })
    defectRows.value = (log.defect_details ?? []).map((item) => ({ defect_type_id: String(item.defect_type_id), quantity: String(item.quantity), notes: item.notes ?? '' }))
    window.scrollTo({ top: 0, behavior: 'smooth' })
}
function scanMaterial() {
    const needle = scanCode.value.trim().toLowerCase()
    const found = props.materials.find((item) => item.code?.toLowerCase() === needle || String(item.id) === needle)
    if (found) { form.material_id = String(found.id); message.value = `เลือกวัตถุดิบ ${found.code || found.name} แล้ว`; error.value = ''; scanCode.value = '' }
    else error.value = 'ไม่พบรหัสวัตถุดิบในสถานีนี้'
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
    <AppLayout :title="`Finishing · ${station.code}`" full-width>
        <main class="hmi flex flex-col gap-4 pb-6">
            <header class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white shadow-sm">
                <div class="hazard" aria-hidden="true"></div>
                <div class="flex flex-wrap items-center justify-between gap-4 bg-[var(--hmi-panel)] px-5 py-4">
                    <div class="flex items-center gap-3"><span class="mono rounded bg-emerald-700 px-3 py-2 text-xl font-extrabold text-white">{{ station.code }}</span><div><p class="m-0 text-xs text-[var(--hmi-mute)]">JOB · {{ selectedJob.job_no }}</p><h1 class="m-0 text-xl font-bold text-[var(--hmi-title)]">{{ station.name }}</h1><p class="m-0 mt-1 text-sm text-[var(--hmi-mute)]">WIP: {{ selectedJob.material?.code || selectedJob.material?.name || 'ยังไม่กำหนด' }}</p></div></div>
                    <div class="flex flex-wrap gap-2"><Link :href="`/finishing/station/${station.code}/jobs`" class="rounded border border-[var(--hmi-line)] bg-white px-4 py-2 text-sm font-semibold">รายการ Job</Link><Link :href="`/finishing/station/${station.code}/history`" class="rounded border border-[var(--hmi-line)] bg-white px-4 py-2 text-sm font-semibold">ประวัติ</Link><Link href="/finishing/dashboard" class="rounded border border-[var(--hmi-line)] bg-white px-4 py-2 text-sm font-semibold">ผังเครื่องจักร</Link></div>
                </div>
                <nav class="flex flex-wrap gap-2 border-t border-[var(--hmi-line)] px-5 py-3"><Link v-for="item in stations" :key="item.id" :href="`/finishing/station/${item.code}/jobs`" class="rounded border px-3 py-2 text-sm font-bold" :class="item.id === station.id ? 'border-emerald-700 bg-emerald-700 text-white' : 'border-[var(--hmi-line)] bg-white text-[var(--hmi-text)]'">{{ item.code }}</Link></nav>
            </header>
            <div v-if="message" role="status" class="rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ message }}</div>
            <div v-if="error" role="alert" class="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ error }}</div>

            <section class="rounded-md border border-[var(--hmi-line)] bg-white p-4 shadow-sm"><div class="flex flex-wrap items-center justify-between gap-3"><div><span class="mono rounded-sm bg-[var(--hmi-yellow)] px-2 py-1 text-xs font-extrabold">{{ selectedJob.status === 'active' ? 'ACTIVE JOB' : 'CLOSED JOB' }}</span><h2 class="mb-0 mt-2 font-bold">{{ selectedJob.job_no }}</h2><p class="mb-0 mt-1 text-sm text-slate-600">WIP: {{ selectedJob.material ? `${selectedJob.material.code || selectedJob.material.name} · ${selectedJob.material.type}` : 'ยังไม่กำหนด' }} · {{ selectedJob.shift_date.slice(0, 10) }} · {{ shiftLabel(selectedJob.shift) }}</p></div><span class="rounded bg-slate-100 px-3 py-2 text-sm">{{ selectedJob.production_logs_count }} รายการ</span></div></section>
            <div v-if="selectedJob.status !== 'active'" class="rounded border border-slate-300 bg-slate-100 p-4 text-sm text-slate-700">Job นี้ปิดแล้ว เปิดดูประวัติได้ แต่เพิ่มผลผลิตไม่ได้</div>
            <div v-else-if="!selectedJob.material_id" class="rounded border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">Job นี้ยังไม่ผูก WIP กลับไปเลือก WIP ที่หน้า <Link :href="`/finishing/station/${station.code}/jobs`" class="font-bold underline">รายการ Job</Link> ก่อนเริ่มผลิต</div>
            <div v-else-if="Number(selectedJob.material?.quantity || 0) <= 0" class="rounded border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900">ยังไม่ได้รับจำนวน WIP เข้า Job นี้ กลับไปหน้า <Link :href="`/finishing/station/${station.code}/jobs`" class="font-bold underline">รายการ Job</Link> เพื่อรับ WIP ก่อนเริ่มแปรรูป</div>

            <section v-if="selectedJob.status === 'active' && selectedJob.material_id && Number(selectedJob.material?.quantity || 0) > 0" class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white">
                <div class="flex items-center gap-3 bg-[var(--hmi-panel)] px-4 py-3"><span class="mono rounded-sm bg-[var(--hmi-yellow)] px-2 py-1 text-xs font-extrabold">{{ editingLogId ? `EDIT #${editingLogId}` : 'NEW' }}</span><div><h2 class="m-0 font-bold">บันทึกผลการผลิต · {{ selectedJob.job_no }}</h2><p class="m-0 text-xs text-[var(--hmi-mute)]">บันทึกเป็นร่างก่อน ตรวจสอบแล้วจึงยืนยันรายการ</p></div></div>
                <form class="grid gap-4 p-4 sm:grid-cols-2 xl:grid-cols-4" @submit.prevent="saveDraft">
                    <div class="rounded border border-slate-200 bg-slate-50 p-3 text-sm"><span class="text-slate-500">วันที่ Job</span><strong class="mt-1 block">{{ selectedJob.shift_date.slice(0, 10) }}</strong></div><div class="rounded border border-slate-200 bg-slate-50 p-3 text-sm"><span class="text-slate-500">กะ Job</span><strong class="mt-1 block">{{ selectedJob.shift === 'day' ? 'กะเช้า' : 'กะดึก' }}</strong></div>
                    <label class="text-sm xl:col-span-2">สแกน Barcode / QR<input v-model="scanCode" class="mt-1 w-full rounded border-slate-300" placeholder="สแกนหรือพิมพ์รหัส แล้วกด Enter" @keydown.enter.prevent="scanMaterial" /></label><div class="flex items-end"><button type="button" class="min-h-10 rounded border border-slate-300 px-4 font-semibold" @click="scanMaterial">ค้นหารหัส</button></div>
                    <div class="rounded border border-emerald-200 bg-emerald-50 p-3 text-sm sm:col-span-2"><span class="text-slate-600">WIP ที่กำหนดไว้กับ Job</span><strong class="mt-1 block">{{ selectedJob.material?.code || selectedJob.material?.name }} · {{ selectedJob.material?.type }} · คงเหลือ {{ selectedJob.material?.quantity }}</strong></div>
                    <label class="text-sm">จำนวนรับเข้า<input v-model="form.quantity_in" type="number" min="0.001" step="0.001" class="mt-1 w-full rounded border-slate-300" required /></label><label class="text-sm">จำนวนผลิตได้<input v-model="form.quantity_out" type="number" min="0" step="0.001" class="mt-1 w-full rounded border-slate-300" required /></label><label class="text-sm">ยอดเสีย<input v-model="form.defect_count" type="number" min="0" step="0.001" class="mt-1 w-full rounded border-slate-300" required /></label>
                    <label v-if="['ST3','ST4'].includes(station.code)" class="text-sm">ยอด FT<input v-model="form.ft_count" type="number" min="0" step="0.001" class="mt-1 w-full rounded border-slate-300" /></label>
                    <label v-if="station.code === 'ST2'" class="text-sm">ประเภทการเคลือบ<select v-model="form.sub_type" class="mt-1 w-full rounded border-slate-300" required><option value="">เลือกประเภท</option><option value="single">เคลือบครั้งเดียว</option><option value="double">ติดฟอยล์ 2 รอบ</option></select></label>
                    <label v-if="station.code === 'ST3'" class="flex items-center gap-2 self-end rounded bg-emerald-50 p-3 text-sm font-semibold"><input v-model="form.has_labeling" type="checkbox" class="rounded border-slate-300 text-emerald-700" /> แปะผักแล้ว</label>
                    <label class="text-sm sm:col-span-2 xl:col-span-4">หมายเหตุ<textarea v-model="form.notes" rows="2" class="mt-1 w-full rounded border-slate-300" maxlength="2000" /></label>
                    <div v-if="Number(form.defect_count) > 0 && defectTypes.length" class="space-y-2 rounded border border-amber-200 bg-amber-50 p-3 sm:col-span-2 xl:col-span-4"><div class="flex items-center justify-between"><strong class="text-sm">แยกประเภทของเสีย (ไม่บังคับ)</strong><button type="button" class="rounded border border-amber-400 px-3 py-1 text-xs font-bold" @click="addDefectRow">+ เพิ่มประเภท</button></div><div v-for="(row, index) in defectRows" :key="index" class="grid gap-2 sm:grid-cols-3"><select v-model="row.defect_type_id" class="rounded border-slate-300 text-sm"><option value="">เลือกประเภท</option><option v-for="type in defectTypes" :key="type.id" :value="String(type.id)">{{ type.name }}</option></select><input v-model="row.quantity" type="number" min="0.001" step="0.001" class="rounded border-slate-300 text-sm" placeholder="จำนวน" /><input v-model="row.notes" class="rounded border-slate-300 text-sm" placeholder="หมายเหตุ" /></div></div>
                    <div v-if="station.code === 'ST5'" class="rounded border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900 sm:col-span-2 xl:col-span-4">สถานีทาสี/ปุลาย: กรอกยอดเสียรวมเมื่อเช็กงานปลายกะ</div>
                    <div class="sm:col-span-2 xl:col-span-4"><button :disabled="busy || !materials.length" class="min-h-11 rounded bg-[var(--hmi-ink)] px-5 font-bold text-white disabled:opacity-50">{{ editingLogId ? 'บันทึกการแก้ไข' : 'บันทึกร่าง' }}</button><button v-if="editingLogId" type="button" class="ml-2 min-h-11 rounded border border-slate-300 px-5 font-semibold" @click="resetForm">ยกเลิกแก้ไข</button><span v-if="!materials.length" class="ml-3 text-sm text-slate-500">ยังไม่มีวัตถุดิบคงเหลือสำหรับสถานีนี้</span></div>
                </form>
            </section>

            <section v-if="selectedJob" class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white">
                <div class="flex items-center justify-between bg-[var(--hmi-panel)] px-4 py-3"><div><h2 class="m-0 font-bold">รายการผลิตใน Job {{ selectedJob.job_no }}</h2><p class="m-0 text-xs text-[var(--hmi-mute)]">{{ jobLogs.length }} รายการ · ยืนยันแล้วจึงปรับ stock และส่งวัตถุดิบต่อ</p></div><span class="mono rounded-sm bg-[var(--hmi-yellow)] px-2 py-1 text-xs font-extrabold">{{ shiftLabel(selectedJob.shift) }}</span></div>
                <div class="overflow-x-auto"><table class="tbl min-w-[900px]"><thead><tr><th>#</th><th>วัตถุดิบ</th><th>รับเข้า</th><th>ผลิต</th><th>เสีย</th><th>สถานะ</th><th>บันทึกโดย</th><th>จัดการ</th></tr></thead><tbody><tr v-for="log in jobLogs" :key="log.id"><td>{{ log.id }}</td><td>{{ log.material?.code || log.material?.name }}</td><td>{{ log.quantity_in }}</td><td>{{ log.quantity_out }}</td><td>{{ log.defect_count }}</td><td><span class="rounded-full px-3 py-1 text-xs font-bold" :class="log.status === 'confirmed' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-900'">{{ log.status }}</span></td><td>{{ log.user?.name || '-' }}</td><td class="space-x-2 whitespace-nowrap"><button v-if="log.status === 'draft'" :disabled="busy" class="rounded border border-slate-300 px-3 py-2 text-xs font-bold" @click="editLog(log)">แก้ไข</button><button v-if="log.status === 'draft'" :disabled="busy" class="rounded bg-emerald-700 px-3 py-2 text-xs font-bold text-white" @click="confirmLog(log)">ยืนยัน</button><button v-if="log.status !== 'cancelled'" :disabled="busy" class="rounded border border-red-300 px-3 py-2 text-xs font-bold text-red-700" @click="cancelLog(log)">ยกเลิก</button></td></tr><tr v-if="!jobLogs.length"><td colspan="8" class="py-10 text-center text-slate-500">ยังไม่มีรายการใน Job นี้</td></tr></tbody></table></div>
            </section>
        </main>
    </AppLayout>
</template>
