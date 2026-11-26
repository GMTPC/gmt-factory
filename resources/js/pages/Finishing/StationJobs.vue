<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, onMounted, onUnmounted, reactive, ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { errorText, finishingRequest } from '@/lib/finishing-api'

type Material = { id: number; code: string | null; name: string; type: string; quantity: string; status: string }
type RecorderOption = { id: number; name: string }
type StatusEvent = { id: number; is_running: boolean; reason: string; created_at: string; user?: { name: string } | null }
type Station = { id: number; code: string; name: string; input_material: string; output_material: string; is_machine_on: boolean; latest_status_event?: StatusEvent | null }
type Job = { id: number; job_no: string; station_id: number; shift_date: string; shift: string; status: string; notes: string | null; production_logs_count: number; material: Material | null; employee_names?: (string | { name: string })[] | null; machine_name?: string | null; pvc_type?: string | null; raw_material_name?: string | null; inspector_name?: string | null; user?: { name: string }; recorder?: RecorderOption | null }
const props = defineProps<{ station: Station; statusEvents: StatusEvent[]; statusReady: boolean; jobs: Job[]; shift: { shift_date: string; shift: string }; employeeOptions: string[]; recorderOptions: RecorderOption[]; machineOptions: string[]; pvcTypes: string[]; inspectors: string[]; currentUserId: number; csrf: string }>()
const busy = ref(false)
const showCreateJob = ref(false)
const showStatusDialog = ref(false)
const statusReason = ref('')
const statusError = ref('')
const statusBusy = ref(false)
const showEmployeeDropdown = ref(false)
const employeeSearch = ref('')
const busyJob = ref<number | null>(null)
const message = ref('')
const error = ref('')
const form = reactive({ employee_names: [] as string[], machine_name: props.machineOptions[0] ?? '', pvc_type: props.pvcTypes[0] ?? '', recorder_user_id: String(props.currentUserId), inspector_name: props.inspectors[0] ?? '', wip_code: '', shift_date: props.shift.shift_date, shift: props.shift.shift, notes: '' })
const filteredEmployees = computed(() => props.employeeOptions.filter((name) => name.toLocaleLowerCase().includes(employeeSearch.value.trim().toLocaleLowerCase())))
const employeeSummary = computed(() => form.employee_names.length ? `${form.employee_names.length} คน: ${form.employee_names.join(', ')}` : 'เลือกพนักงานที่ทำงาน')
type WipDetails = { wip_type: string; wip_code: string; wip_name: string }
const assignments = reactive<Record<number, WipDetails>>({})

onMounted(() => localStorage.setItem('finishing.station', props.station.code))

// Machine status card: andon-style (green = running, red = stopped) with how long it has been in that state.
const running = computed(() => props.station.is_machine_on)
const statusSince = computed(() => props.station.latest_status_event?.created_at ?? null)
const now = ref(Date.now())
let clock: number | undefined
onMounted(() => { clock = window.setInterval(() => { now.value = Date.now() }, 30000) })
onUnmounted(() => window.clearInterval(clock))
const statusDuration = computed(() => {
    if (!statusSince.value) return ''
    const minutes = Math.max(0, Math.floor((now.value - new Date(statusSince.value).getTime()) / 60000))
    const days = Math.floor(minutes / 1440)
    const hours = Math.floor((minutes % 1440) / 60)
    return [days ? `${days} วัน` : '', hours ? `${hours} ชม.` : '', `${minutes % 60} นาที`].filter(Boolean).join(' ')
})
const quickReasons = computed(() => running.value
    ? ['จบกะ', 'พักเบรก', 'เครื่องเสีย / ซ่อม', 'เปลี่ยนงาน / ตั้งเครื่อง', 'รอวัตถุดิบ']
    : ['เริ่มกะ', 'ซ่อมเสร็จ', 'ตั้งเครื่องเสร็จ', 'วัตถุดิบมาแล้ว'])
function openStatusDialog() { statusReason.value = ''; statusError.value = ''; showStatusDialog.value = true }

async function changeStationStatus() {
    if (!statusReason.value.trim()) { statusError.value = 'กรุณาระบุเหตุผล'; return }
    statusBusy.value = true; statusError.value = ''; error.value = ''; message.value = ''
    const nextRunning = !props.station.is_machine_on
    try {
        await finishingRequest(`/api/finishing/stations/${props.station.id}/machine`, 'PATCH', { is_machine_on: nextRunning, reason: statusReason.value.trim() }, props.csrf)
        showStatusDialog.value = false
        statusReason.value = ''
        message.value = `${nextRunning ? 'เริ่มเดินเครื่อง' : 'หยุดเครื่อง'} ${props.station.code} แล้ว`
        router.reload({ preserveScroll: true })
    } catch (e) { statusError.value = errorText(e) } finally { statusBusy.value = false }
}

async function createJob() {
    message.value = ''; error.value = ''; busy.value = true
    try {
        await finishingRequest('/api/finishing/jobs', 'POST', { station_id: props.station.id, ...form }, props.csrf)
        message.value = 'สร้าง Job แล้ว เลือกเปิด Job ในรายการเพื่อเริ่มแปรรูป'
        form.employee_names = []; form.wip_code = ''; form.notes = ''
        showEmployeeDropdown.value = false; employeeSearch.value = ''
        showCreateJob.value = false
        router.reload({ preserveScroll: true })
    } catch (e) { error.value = errorText(e) } finally { busy.value = false }
}

function assignmentFor(jobId: number): WipDetails {
    return assignments[jobId] ??= { wip_type: '', wip_code: '', wip_name: '' }
}

async function specifyMaterial(job: Job) {
    const wip = assignments[job.id]
    if (!wip?.wip_type || !wip.wip_name) return
    message.value = ''; error.value = ''; busyJob.value = job.id
    try {
        await finishingRequest(`/api/finishing/jobs/${job.id}/material`, 'PATCH', wip, props.csrf)
        message.value = `ผูก WIP ให้ ${job.job_no} แล้ว`
        router.reload({ preserveScroll: true })
    } catch (e) { error.value = errorText(e) } finally { busyJob.value = null }
}

async function completeJob(job: Job) {
    if (!window.confirm(`ปิด Job ${job.job_no}?`)) return
    message.value = ''; error.value = ''; busyJob.value = job.id
    try {
        await finishingRequest(`/api/finishing/jobs/${job.id}/complete`, 'PATCH', {}, props.csrf)
        message.value = `ปิด Job ${job.job_no} แล้ว`
        router.reload({ preserveScroll: true })
    } catch (e) { error.value = errorText(e) } finally { busyJob.value = null }
}

function materialLabel(material: Material | null) {
    if (!material) return 'ยังไม่ได้กำหนด WIP'
    return `${material.code || material.name} · ${material.type} · คงเหลือ ${material.quantity}`
}

function jobDetails(job: Job) {
    return [job.machine_name, job.pvc_type ? `PVC ${job.pvc_type}` : '', job.raw_material_name ? `วัตถุดิบ ${job.raw_material_name}` : '', job.recorder?.name ? `ผู้บันทึก ${job.recorder.name}` : '', job.inspector_name ? `ผู้ตรวจ ${job.inspector_name}` : '']
        .filter(Boolean).join(' · ')
}

function formatDate(value: string) { return value.slice(0, 10) }
function formatStatusTime(value: string) { return new Date(value).toLocaleString('th-TH', { timeZone: 'Asia/Bangkok' }) }
function employeeLabel(employee: string | { name: string }) { return typeof employee === 'string' ? employee : employee.name }
</script>

<template>
    <Head :title="`Finishing · Jobs · ${station.code}`" />
    <AppLayout :title="`Finishing · Jobs · ${station.code}`" full-width>
    <main class="hmi flex flex-col gap-4 pb-6">
            <header class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white shadow-sm">
                <div class="hazard" aria-hidden="true"></div>
                <div class="flex flex-wrap items-center justify-between gap-3 bg-[var(--hmi-panel)] px-5 py-4">
                    <div class="flex items-center gap-3"><span class="mono rounded bg-emerald-700 px-4 py-3 text-lg font-extrabold text-white">{{ station.code }}</span><div><p class="m-0 text-xs text-[var(--hmi-mute)]">FINISHING · {{ station.input_material }}</p><h1 class="m-0 text-xl font-bold">Job · {{ station.name }}</h1><p class="m-0 mt-1 text-sm text-[var(--hmi-mute)]">สร้าง Job โดยกำหนด WIP ก่อน แล้วเปิด Job เพื่อบันทึกการแปรรูป</p></div></div>
                    <div class="flex gap-2"><Link href="/finishing/dashboard" class="rounded border border-[var(--hmi-line)] bg-white px-4 py-2 text-sm font-semibold">ผังเครื่องจักร</Link><Link :href="`/finishing/station/${station.code}/history`" class="rounded border border-[var(--hmi-line)] bg-white px-4 py-2 text-sm font-semibold">ประวัติ</Link></div>
                </div>
                <nav class="flex flex-wrap gap-2 border-t border-[var(--hmi-line)] px-5 py-3"><Link v-for="item in ['ST1','ST2','ST3','ST4','ST5','ST6']" :key="item" :href="`/finishing/station/${item}/jobs`" class="rounded border px-3 py-2 text-sm font-bold" :class="item === station.code ? 'border-emerald-700 bg-emerald-700 text-white' : 'border-[var(--hmi-line)] bg-white text-[var(--hmi-text)]'">{{ item }}</Link></nav>
            </header>

            <div v-if="message" role="status" class="rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ message }}</div>
            <div v-if="error" role="alert" class="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ error }}</div>

            <section class="overflow-hidden rounded-md border-2 bg-white shadow-sm" :class="running ? 'border-emerald-600' : 'border-red-600'" aria-live="polite">
                <div class="flex items-center gap-4 px-4 py-4 sm:px-5" :class="running ? 'bg-emerald-50' : 'bg-red-50'">
                    <span class="relative flex size-14 shrink-0 items-center justify-center rounded-full" :class="running ? 'bg-emerald-600' : 'bg-red-600'" aria-hidden="true">
                        <span v-if="running" class="absolute inset-0 rounded-full bg-emerald-500 opacity-40 motion-safe:animate-ping"></span>
                        <i class="relative text-3xl text-white" :class="running ? 'ph-fill ph-gear-six motion-safe:animate-spin [animation-duration:4s]' : 'ph-fill ph-pause'"></i>
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="m-0 text-xs font-bold text-[var(--hmi-mute)]">สถานะเครื่อง · {{ station.code }} · {{ station.name }}</p>
                        <p class="m-0 text-2xl font-extrabold leading-tight" :class="running ? 'text-emerald-800' : 'text-red-700'">{{ running ? 'เครื่องกำลังเดิน' : 'เครื่องหยุด' }}</p>
                        <p v-if="statusSince" class="m-0 mt-0.5 text-sm text-slate-700">{{ running ? 'เดินมาแล้ว' : 'หยุดมาแล้ว' }} <strong>{{ statusDuration }}</strong> · ตั้งแต่ {{ formatStatusTime(statusSince) }}</p>
                    </div>
                </div>
                <div class="space-y-3 px-4 py-4 sm:px-5">
                    <p v-if="station.latest_status_event" class="m-0 flex items-start gap-2 text-sm text-slate-700"><i class="ph ph-chat-text mt-0.5 text-lg text-slate-500" aria-hidden="true"></i><span>เหตุผล: <strong>{{ station.latest_status_event.reason }}</strong> · โดย {{ station.latest_status_event.user?.name || 'ผู้ใช้เดิม' }}</span></p>
                    <button v-if="statusReady" type="button" class="flex min-h-14 w-full items-center justify-center gap-3 rounded-md px-8 text-lg font-extrabold text-white shadow-sm transition active:scale-[0.99] sm:w-auto" :class="running ? 'bg-red-600 hover:bg-red-700' : 'bg-emerald-600 hover:bg-emerald-700'" @click="openStatusDialog">
                        <i class="ph-fill text-2xl" :class="running ? 'ph-stop' : 'ph-play'" aria-hidden="true"></i>{{ running ? 'หยุดเครื่อง' : 'เริ่มเดินเครื่อง' }}
                    </button>
                </div>
                <details v-if="statusEvents.length" class="border-t border-[var(--hmi-line)] px-4 py-3 text-sm sm:px-5">
                    <summary class="cursor-pointer font-semibold text-[var(--hmi-text)]">ประวัติเดิน / หยุดเครื่อง</summary>
                    <ul class="m-0 mt-3 space-y-2 p-0">
                        <li v-for="event in statusEvents" :key="event.id" class="m-0 flex list-none items-start gap-2">
                            <span class="mt-0.5 inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-0.5 text-xs font-bold" :class="event.is_running ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-700'"><i class="ph-fill" :class="event.is_running ? 'ph-play' : 'ph-stop'" aria-hidden="true"></i>{{ event.is_running ? 'เดิน' : 'หยุด' }}</span>
                            <span class="min-w-0">{{ event.reason }} <span class="text-[var(--hmi-mute)]">· {{ event.user?.name || 'ผู้ใช้เดิม' }} · {{ formatStatusTime(event.created_at) }}</span></span>
                        </li>
                    </ul>
                </details>
            </section>

            <div v-if="showStatusDialog" class="fixed inset-0 z-[110] flex items-end justify-center bg-slate-950/50 p-4 sm:items-center" role="presentation" @click.self="showStatusDialog = false">
                <section role="dialog" aria-modal="true" :aria-label="running ? 'หยุดเครื่อง' : 'เริ่มเดินเครื่อง'" class="w-full max-w-lg overflow-hidden rounded-md bg-white shadow-2xl">
                    <div class="flex items-center gap-3 px-5 py-4 text-white" :class="running ? 'bg-red-600' : 'bg-emerald-600'">
                        <i class="ph-fill text-3xl" :class="running ? 'ph-stop-circle' : 'ph-play-circle'" aria-hidden="true"></i>
                        <div><h2 class="m-0 text-lg font-extrabold text-white">{{ running ? 'หยุดเครื่อง' : 'เริ่มเดินเครื่อง' }} · {{ station.code }}</h2><p class="m-0 text-sm text-white/90">{{ station.name }} · เลือกหรือพิมพ์เหตุผล</p></div>
                    </div>
                    <form class="space-y-4 p-5" @submit.prevent="changeStationStatus">
                        <div class="flex flex-wrap gap-2">
                            <button v-for="reason in quickReasons" :key="reason" type="button" class="min-h-10 rounded-full border px-4 text-sm font-semibold" :class="statusReason === reason ? (running ? 'border-red-600 bg-red-600 text-white' : 'border-emerald-600 bg-emerald-600 text-white') : 'border-slate-300 bg-white text-slate-700'" @click="statusReason = reason; statusError = ''">{{ reason }}</button>
                        </div>
                        <label class="block text-sm font-semibold">เหตุผล<textarea v-model="statusReason" rows="2" maxlength="500" required class="mt-1 w-full rounded border-slate-300" placeholder="เลือกด้านบน หรือพิมพ์เหตุผลเอง"></textarea></label>
                        <p v-if="statusError" role="alert" class="m-0 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">{{ statusError }}</p>
                        <div class="flex justify-end gap-2">
                            <button type="button" class="min-h-11 rounded border border-[var(--hmi-line)] bg-white px-4 font-semibold" @click="showStatusDialog = false">ยกเลิก</button>
                            <button :disabled="statusBusy || !statusReason.trim()" class="flex min-h-11 items-center gap-2 rounded px-5 font-bold text-white disabled:opacity-50" :class="running ? 'bg-red-600' : 'bg-emerald-600'"><i class="ph-fill" :class="running ? 'ph-stop' : 'ph-play'" aria-hidden="true"></i>{{ statusBusy ? 'กำลังบันทึก...' : running ? 'ยืนยันหยุดเครื่อง' : 'ยืนยันเริ่มเดินเครื่อง' }}</button>
                        </div>
                    </form>
                </section>
            </div>

            <section class="flex flex-wrap items-center justify-between gap-3 rounded-md border border-[var(--hmi-line)] bg-white px-5 py-4 shadow-sm">
                <div><p class="m-0 text-xs font-bold text-[var(--hmi-mute)]">NEW JOB</p><h2 class="m-0 font-bold">สร้าง Job ใหม่</h2><p class="m-0 text-xs text-[var(--hmi-mute)]">ระบุ WIP และข้อมูลกะ เพื่อสร้าง Job</p></div>
                <button type="button" class="min-h-10 rounded bg-[var(--hmi-ink)] px-5 font-bold text-white" @click="showCreateJob = true">+ สร้าง Job ใหม่</button>
            </section>

            <div v-if="showCreateJob" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/50 p-4" role="presentation" tabindex="-1" @click.self="showCreateJob = false; showEmployeeDropdown = false; employeeSearch = ''" @keydown.esc="showCreateJob = false; showEmployeeDropdown = false; employeeSearch = ''">
            <section role="dialog" aria-modal="true" aria-label="Create new Job" class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-md border border-[var(--hmi-line)] bg-white shadow-2xl">
                <div class="flex items-center gap-3 bg-[var(--hmi-panel)] px-4 py-3"><span class="mono rounded-sm bg-[var(--hmi-yellow)] px-2 py-1 text-xs font-extrabold">NEW JOB</span><div><h2 class="m-0 font-bold">สร้าง Job ใหม่</h2><p class="m-0 text-xs text-[var(--hmi-mute)]">ระบุ WIP ที่จะใช้กับ Job นี้ ระบบจะสร้าง WIP และผูกกับ Job ให้โดยอัตโนมัติ</p></div></div>
                <div v-if="error" role="alert" class="mx-4 mt-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ error }}</div>
                <form class="grid gap-4 p-4 sm:grid-cols-2" @submit.prevent="createJob">
                    <label class="text-sm">วันที่เริ่มทำงาน<input v-model="form.shift_date" type="date" class="mt-1 w-full rounded border-slate-300" required /></label>

                    <div class="text-sm"><span class="mb-1 block">เริ่มงาน</span><div class="flex min-h-10 items-center rounded border border-red-200 bg-red-50 px-3 font-bold text-red-700">เริ่มงานทันทีเมื่อสร้าง Job</div></div>

                    <fieldset class="text-sm"><legend class="mb-1">กะ</legend><div class="grid grid-cols-2 gap-2"><label class="flex cursor-pointer items-center justify-center gap-2 rounded border px-3 py-2" :class="form.shift === 'day' ? 'border-red-600 bg-red-600 text-white' : 'border-slate-300 bg-white'"><input v-model="form.shift" type="radio" value="day" class="sr-only" /><span>กะเช้า</span></label><label class="flex cursor-pointer items-center justify-center gap-2 rounded border px-3 py-2" :class="form.shift === 'night' ? 'border-red-600 bg-red-600 text-white' : 'border-slate-300 bg-white'"><input v-model="form.shift" type="radio" value="night" class="sr-only" /><span>กะดึก</span></label></div></fieldset>

                    <fieldset class="relative text-sm sm:col-span-2">
                        <legend class="mb-1">รายชื่อพนักงานที่ทำงาน</legend>
                        <template v-if="employeeOptions.length">
                            <button type="button" class="flex min-h-10 w-full items-center justify-between gap-3 rounded border border-[var(--hmi-line)] bg-white px-3 py-2 text-left text-[var(--hmi-text)]" :aria-expanded="showEmployeeDropdown" aria-controls="finishing-employee-options" @click="showEmployeeDropdown = !showEmployeeDropdown; employeeSearch = ''">
                                <span class="min-w-0 flex-1 truncate">{{ employeeSummary }}</span><span aria-hidden="true" class="text-[var(--hmi-mute)]">▾</span>
                            </button>
                            <div v-if="showEmployeeDropdown" id="finishing-employee-options" class="absolute left-0 right-0 top-full z-20 mt-1 overflow-hidden rounded border border-[var(--hmi-line)] bg-white shadow-lg" @keydown.esc.stop="showEmployeeDropdown = false; employeeSearch = ''">
                                <div class="border-b border-[var(--hmi-line)] bg-[var(--hmi-panel)] p-2"><input v-model="employeeSearch" type="search" autofocus class="w-full rounded border-slate-300 text-sm" placeholder="ค้นหาพนักงาน" aria-label="ค้นหาพนักงาน" /></div>
                                <div class="max-h-56 overflow-y-auto p-1">
                                    <label v-for="employee in filteredEmployees" :key="employee" class="flex cursor-pointer items-center gap-3 rounded px-3 py-2 hover:bg-[var(--hmi-panel)]"><input v-model="form.employee_names" type="checkbox" :value="employee" class="rounded border-slate-400 text-red-600 focus:ring-red-500" /><span>{{ employee }}</span></label>
                                    <p v-if="!filteredEmployees.length" class="m-0 px-3 py-4 text-center text-[var(--hmi-mute)]">ไม่พบรายชื่อพนักงาน</p>
                                </div>
                                <div class="flex items-center justify-between border-t border-[var(--hmi-line)] bg-[var(--hmi-panel)] px-3 py-2"><button type="button" class="font-semibold text-[var(--hmi-text)]" @click="form.employee_names = []">ล้างค่า</button><button type="button" class="font-bold text-red-700" @click="showEmployeeDropdown = false; employeeSearch = ''">เสร็จ</button></div>
                            </div>
                        </template>
                        <p v-else class="m-0 rounded border border-amber-300 bg-amber-50 p-3 text-amber-800">ยังไม่มีรายชื่อพนักงาน</p>
                    </fieldset>

                    <fieldset class="text-sm sm:col-span-2"><legend class="mb-1">เครื่อง</legend><p v-if="machineOptions.length === 1" class="m-0 inline-flex min-h-10 items-center rounded border border-red-600 bg-red-600 px-3 py-2 font-semibold text-white">{{ machineOptions[0] }}</p><div v-else class="flex flex-wrap gap-2"><label v-for="machine in machineOptions" :key="machine" class="cursor-pointer rounded border px-3 py-2" :class="form.machine_name === machine ? 'border-red-600 bg-red-600 text-white' : 'border-slate-300 bg-white'"><input v-model="form.machine_name" type="radio" :value="machine" class="sr-only" /><span>{{ machine }}</span></label></div></fieldset>


                    <div v-if="pvcTypes.length === 1" class="text-sm">ชนิด PVC<p class="m-0 mt-1 flex min-h-10 items-center rounded border border-slate-300 bg-slate-100 px-3 font-semibold">{{ pvcTypes[0] }}</p></div><label v-else class="text-sm">ชนิด PVC<select v-model="form.pvc_type" class="mt-1 w-full rounded border-slate-300" required><option v-for="type in pvcTypes" :key="type" :value="type">{{ type }}</option></select></label>
                    <label class="text-sm">ผู้บันทึก<select v-model="form.recorder_user_id" class="mt-1 w-full rounded border-slate-300" required><option v-for="recorder in recorderOptions" :key="recorder.id" :value="String(recorder.id)">{{ recorder.name }}</option></select></label>

                    <fieldset class="text-sm sm:col-span-2"><legend class="mb-1">ผู้ตรวจสอบ</legend><div class="grid grid-cols-2 gap-2"><label v-for="inspector in inspectors" :key="inspector" class="cursor-pointer rounded border px-3 py-2 text-center" :class="form.inspector_name === inspector ? 'border-red-600 bg-red-600 text-white' : 'border-slate-300 bg-white'"><input v-model="form.inspector_name" type="radio" :value="inspector" class="sr-only" /><span>{{ inspector }}</span></label></div></fieldset>

                    <label class="text-sm">รหัส / Lot (ถ้ามี)<input v-model="form.wip_code" class="mt-1 w-full rounded border-slate-300" maxlength="100" placeholder="ไม่บังคับ" /></label>
                    <label class="text-sm">หมายเหตุ<input v-model="form.notes" maxlength="2000" class="mt-1 w-full rounded border-slate-300" placeholder="ไม่บังคับ" /></label>
                    <div class="sm:col-span-2 flex justify-end gap-2"><button type="button" class="rounded border border-[var(--hmi-line)] bg-white px-4 py-2 font-semibold" @click="showCreateJob = false; showEmployeeDropdown = false; employeeSearch = ''">ยกเลิก</button><button :disabled="busy || !form.employee_names.length || !form.machine_name || !form.pvc_type || !form.recorder_user_id || !form.inspector_name" class="min-h-10 rounded bg-red-600 px-5 font-bold text-white disabled:opacity-50">เริ่มงาน</button></div>
                </form>
            </section>
            </div>

            <section class="xl:overflow-hidden xl:rounded-md xl:border xl:border-[var(--hmi-line)] xl:bg-white xl:shadow-sm">
                <div class="flex items-center justify-between gap-3 py-1 xl:bg-[var(--hmi-panel)] xl:px-4 xl:py-3"><div><h2 class="m-0 font-bold">รายการ Job · {{ station.code }}</h2><p class="m-0 text-xs text-[var(--hmi-mute)]">เปิด Job เพื่อทำงานกับ WIP ที่กำหนดไว้</p></div><span class="shrink-0 rounded bg-amber-400 px-2 py-1 text-xs font-extrabold">{{ jobs.length }} JOB</span></div>
                <div class="grid gap-3 pt-2 xl:hidden">
                    <article v-for="job in jobs" :key="job.id" class="min-w-0 overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white shadow-sm">
                        <div class="flex flex-wrap items-start justify-between gap-2 bg-[var(--hmi-panel)] px-4 py-3">
                            <div class="min-w-0"><p class="m-0 text-xs font-bold text-[var(--hmi-mute)]">JOB NO.</p><h3 class="m-0 mt-1 break-all text-base font-bold text-[var(--hmi-title)]">{{ job.job_no }}</h3></div>
                            <span class="shrink-0 rounded-full px-3 py-1 text-xs font-bold" :class="job.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'">{{ job.status === 'active' ? 'กำลังทำงาน' : 'ปิดแล้ว' }}</span>
                        </div>
                        <div class="space-y-3 px-4 py-3 text-sm">
                            <div><p class="m-0 text-xs font-semibold text-[var(--hmi-mute)]">WIP ประจำ Job</p><p class="m-0 mt-1 break-words font-semibold">{{ materialLabel(job.material) }}</p></div>
                            <p v-if="job.employee_names?.length" class="m-0 break-words text-[var(--hmi-mute)]"><strong class="text-[var(--hmi-text)]">พนักงาน:</strong> {{ job.employee_names.map(employeeLabel).join(', ') }}</p>
                            <p v-if="jobDetails(job)" class="m-0 break-words text-[var(--hmi-mute)]">{{ jobDetails(job) }}</p>
                            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 border-t border-[var(--hmi-line)] pt-3"><div><dt class="text-xs text-[var(--hmi-mute)]">วันที่</dt><dd class="m-0 font-semibold">{{ formatDate(job.shift_date) }}</dd></div><div><dt class="text-xs text-[var(--hmi-mute)]">กะ</dt><dd class="m-0 font-semibold">{{ job.shift === 'day' ? 'กะเช้า' : 'กะดึก' }}</dd></div><div><dt class="text-xs text-[var(--hmi-mute)]">ผู้สร้าง</dt><dd class="m-0 break-words font-semibold">{{ job.user?.name || '-' }}</dd></div><div><dt class="text-xs text-[var(--hmi-mute)]">รายการ</dt><dd class="m-0 font-semibold">{{ job.production_logs_count }}</dd></div></dl>
                            <div v-if="job.status === 'active' && !job.material" class="grid gap-2 border-t border-[var(--hmi-line)] pt-3"><input v-model="assignmentFor(job.id).wip_type" class="w-full rounded border-slate-300 text-sm" placeholder="ประเภท WIP" /><input v-model="assignmentFor(job.id).wip_code" class="w-full rounded border-slate-300 text-sm" placeholder="รหัส / Lot" /><input v-model="assignmentFor(job.id).wip_name" class="w-full rounded border-slate-300 text-sm" placeholder="ชื่อ / รายละเอียด WIP" /><button :disabled="busyJob === job.id || !assignmentFor(job.id).wip_type || !assignmentFor(job.id).wip_name" class="min-h-10 rounded bg-emerald-700 px-3 py-2 font-bold text-white disabled:opacity-50" @click="specifyMaterial(job)">ระบุ WIP</button></div>
                        </div>
                        <div class="grid gap-2 border-t border-[var(--hmi-line)] px-4 py-3" :class="job.status === 'active' ? 'grid-cols-2' : 'grid-cols-1'">
                            <p v-if="job.status === 'active' && (!job.material || Number(job.material.quantity) <= 0)" class="col-span-full m-0 rounded bg-amber-50 px-3 py-2 text-center text-xs font-semibold text-amber-800">{{ job.material ? 'รับ WIP ก่อนบันทึกผลผลิต' : 'กำหนด WIP ก่อนบันทึกผลผลิต' }}</p>
                            <Link :href="`/finishing/station/${station.code}/jobs/${job.id}`" class="flex min-h-10 items-center justify-center rounded bg-emerald-800 px-3 py-2 font-bold text-white">เปิด Job ›</Link>
                            <button v-if="job.status === 'active'" :disabled="busyJob === job.id" class="min-h-10 rounded border border-red-300 px-3 py-2 font-bold text-red-700 disabled:opacity-50" @click="completeJob(job)">ปิด Job</button>
                        </div>
                    </article>
                    <p v-if="!jobs.length" class="m-0 py-8 text-center text-sm text-[var(--hmi-mute)]">ยังไม่มี Job ของสถานีนี้</p>
                </div>
                <div class="hidden overflow-x-auto xl:block"><table class="tbl min-w-[1000px]"><thead><tr><th>Job No.</th><th>WIP ประจำ Job</th><th>วันที่</th><th>กะ</th><th>ผู้สร้าง</th><th>รายการ</th><th>สถานะ</th><th>จัดการ</th></tr></thead><tbody>
                    <tr v-for="job in jobs" :key="job.id"><td class="font-bold">{{ job.job_no }}</td><td class="min-w-64">{{ materialLabel(job.material) }}<div v-if="job.employee_names?.length" class="mt-1 text-xs text-slate-600">พนักงาน: {{ job.employee_names.map(employeeLabel).join(', ') }}</div><div v-if="jobDetails(job)" class="mt-1 text-xs text-slate-600">{{ jobDetails(job) }}</div><div v-if="job.status === 'active' && !job.material" class="mt-2 grid min-w-[420px] grid-cols-2 gap-2"><input v-model="assignmentFor(job.id).wip_type" class="rounded border-slate-300 text-sm" placeholder="ประเภท WIP" /><input v-model="assignmentFor(job.id).wip_code" class="rounded border-slate-300 text-sm" placeholder="รหัส / Lot" /><input v-model="assignmentFor(job.id).wip_name" class="rounded border-slate-300 text-sm" placeholder="ชื่อ / รายละเอียด WIP" /><button :disabled="busyJob === job.id || !assignmentFor(job.id).wip_type || !assignmentFor(job.id).wip_name" class="rounded bg-emerald-700 px-3 py-2 text-xs font-bold text-white disabled:opacity-50" @click="specifyMaterial(job)">ระบุ WIP</button></div></td><td>{{ formatDate(job.shift_date) }}</td><td>{{ job.shift === 'day' ? 'กะเช้า' : 'กะดึก' }}</td><td>{{ job.user?.name || '-' }}</td><td>{{ job.production_logs_count }}</td><td><span class="rounded-full px-3 py-1 text-xs font-bold" :class="job.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'">{{ job.status === 'active' ? 'กำลังทำงาน' : 'ปิดแล้ว' }}</span></td><td class="whitespace-nowrap"><Link :href="`/finishing/station/${station.code}/jobs/${job.id}`" class="rounded bg-emerald-800 px-3 py-2 text-xs font-bold text-white">เปิด Job ›</Link><span v-if="job.status === 'active' && (!job.material || Number(job.material.quantity) <= 0)" class="ml-2 text-xs text-amber-700">{{ job.material ? 'รับ WIP ก่อนบันทึกผลผลิต' : 'กำหนด WIP ก่อนบันทึกผลผลิต' }}</span><button v-if="job.status === 'active'" :disabled="busyJob === job.id" class="ml-2 rounded border border-red-300 px-3 py-2 text-xs font-bold text-red-700 disabled:opacity-50" @click="completeJob(job)">ปิด Job</button></td></tr>
                    <tr v-if="!jobs.length"><td colspan="8" class="py-10 text-center text-slate-500">ยังไม่มี Job ของสถานีนี้</td></tr>
                </tbody></table></div>
            </section>
        </main>
    </AppLayout>
</template>
