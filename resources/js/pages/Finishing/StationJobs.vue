<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { onMounted, reactive, ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { errorText, finishingRequest } from '@/lib/finishing-api'

type Material = { id: number; code: string | null; name: string; type: string; quantity: string; status: string }
type Job = { id: number; job_no: string; station_id: number; shift_date: string; shift: string; status: string; notes: string | null; production_logs_count: number; material: Material | null; user?: { name: string } }
const props = defineProps<{ station: { id: number; code: string; name: string; input_material: string; output_material: string }; jobs: Job[]; shift: { shift_date: string; shift: string }; csrf: string }>()
const busy = ref(false)
const busyJob = ref<number | null>(null)
const message = ref('')
const error = ref('')
const form = reactive({ wip_type: '', wip_code: '', wip_name: '', shift_date: props.shift.shift_date, shift: props.shift.shift, notes: '' })
type WipDetails = { wip_type: string; wip_code: string; wip_name: string }
const assignments = reactive<Record<number, WipDetails>>({})

onMounted(() => localStorage.setItem('finishing.station', props.station.code))

async function createJob() {
    message.value = ''; error.value = ''; busy.value = true
    try {
        await finishingRequest('/api/finishing/jobs', 'POST', { station_id: props.station.id, ...form }, props.csrf)
        message.value = 'สร้าง Job แล้ว เลือกเปิด Job ในรายการเพื่อเริ่มแปรรูป'
        form.wip_type = ''; form.wip_code = ''; form.wip_name = ''
        form.notes = ''
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

function formatDate(value: string) { return value.slice(0, 10) }
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

            <section class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white shadow-sm">
                <div class="flex items-center gap-3 bg-[var(--hmi-panel)] px-4 py-3"><span class="mono rounded-sm bg-[var(--hmi-yellow)] px-2 py-1 text-xs font-extrabold">NEW JOB</span><div><h2 class="m-0 font-bold">สร้าง Job ใหม่</h2><p class="m-0 text-xs text-[var(--hmi-mute)]">ระบุ WIP ที่จะใช้กับ Job นี้ ระบบจะสร้าง WIP และผูกกับ Job ให้โดยอัตโนมัติ</p></div></div>
                <form class="grid gap-3 p-4 sm:grid-cols-2 xl:grid-cols-4" @submit.prevent="createJob">
                    <label class="text-sm">ประเภท WIP<input v-model="form.wip_type" class="mt-1 w-full rounded border-slate-300" maxlength="50" placeholder="เช่น ขาวผ่อง, PVC, เกรด C" required /></label>
                    <label class="text-sm">รหัส WIP / Lot<input v-model="form.wip_code" class="mt-1 w-full rounded border-slate-300" maxlength="100" placeholder="ไม่บังคับ" /></label>
                    <label class="text-sm">ชื่อ / รายละเอียด WIP<input v-model="form.wip_name" class="mt-1 w-full rounded border-slate-300" maxlength="255" required /></label>
                    <label class="text-sm">วันที่ Job<input v-model="form.shift_date" type="date" class="mt-1 w-full rounded border-slate-300" required /></label>
                    <label class="text-sm">กะ Job<select v-model="form.shift" class="mt-1 w-full rounded border-slate-300" required><option value="day">กะเช้า (08:00–17:00)</option><option value="night">กะดึก (17:00–08:00)</option></select></label>
                    <label class="text-sm">หมายเหตุ<input v-model="form.notes" maxlength="2000" class="mt-1 w-full rounded border-slate-300" placeholder="ไม่บังคับ" /></label>
                    <div class="sm:col-span-2 xl:col-span-4"><button :disabled="busy" class="min-h-10 rounded bg-[var(--hmi-ink)] px-5 font-bold text-white disabled:opacity-50">+ ระบุ WIP และสร้าง Job</button></div>
                </form>
            </section>

            <section class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white shadow-sm">
                <div class="flex items-center justify-between bg-[var(--hmi-panel)] px-4 py-3"><div><h2 class="m-0 font-bold">รายการ Job · {{ station.code }}</h2><p class="m-0 text-xs text-[var(--hmi-mute)]">เปิด Job เพื่อทำงานกับ WIP ที่กำหนดไว้</p></div><span class="rounded bg-amber-400 px-2 py-1 text-xs font-extrabold">{{ jobs.length }} JOB</span></div>
                <div class="overflow-x-auto"><table class="tbl min-w-[1000px]"><thead><tr><th>Job No.</th><th>WIP ประจำ Job</th><th>วันที่</th><th>กะ</th><th>ผู้สร้าง</th><th>รายการ</th><th>สถานะ</th><th>จัดการ</th></tr></thead><tbody>
                    <tr v-for="job in jobs" :key="job.id"><td class="font-bold">{{ job.job_no }}</td><td class="min-w-64">{{ materialLabel(job.material) }}<div v-if="job.status === 'active' && !job.material" class="mt-2 grid min-w-[420px] grid-cols-2 gap-2"><input v-model="assignmentFor(job.id).wip_type" class="rounded border-slate-300 text-sm" placeholder="ประเภท WIP" /><input v-model="assignmentFor(job.id).wip_code" class="rounded border-slate-300 text-sm" placeholder="รหัส / Lot" /><input v-model="assignmentFor(job.id).wip_name" class="rounded border-slate-300 text-sm" placeholder="ชื่อ / รายละเอียด WIP" /><button :disabled="busyJob === job.id || !assignmentFor(job.id).wip_type || !assignmentFor(job.id).wip_name" class="rounded bg-emerald-700 px-3 py-2 text-xs font-bold text-white disabled:opacity-50" @click="specifyMaterial(job)">ระบุ WIP</button></div></td><td>{{ formatDate(job.shift_date) }}</td><td>{{ job.shift === 'day' ? 'กะเช้า' : 'กะดึก' }}</td><td>{{ job.user?.name || '-' }}</td><td>{{ job.production_logs_count }}</td><td><span class="rounded-full px-3 py-1 text-xs font-bold" :class="job.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'">{{ job.status === 'active' ? 'กำลังทำงาน' : 'ปิดแล้ว' }}</span></td><td class="whitespace-nowrap"><Link v-if="job.material && (Number(job.material.quantity) > 0 || job.status !== 'active')" :href="`/finishing/station/${station.code}/jobs/${job.id}`" class="rounded bg-emerald-800 px-3 py-2 text-xs font-bold text-white">เปิด Job ›</Link><span v-else-if="job.status === 'active'" class="text-xs text-amber-700">{{ job.material ? 'รอ WIP เข้าระบบ' : 'กำหนด WIP ก่อนเปิด' }}</span><button v-if="job.status === 'active'" :disabled="busyJob === job.id" class="ml-2 rounded border border-red-300 px-3 py-2 text-xs font-bold text-red-700 disabled:opacity-50" @click="completeJob(job)">ปิด Job</button></td></tr>
                    <tr v-if="!jobs.length"><td colspan="8" class="py-10 text-center text-slate-500">ยังไม่มี Job ของสถานีนี้</td></tr>
                </tbody></table></div>
            </section>
        </main>
    </AppLayout>
</template>
