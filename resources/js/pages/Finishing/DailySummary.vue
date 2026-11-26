<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, reactive } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'

type RawMaterialUse = { name: string; quantity: number | null; unit: string | null }
type JobRow = {
    id: number; station_code: string; station_name: string; shift: string; status: string; product_type_code: string | null; product_type_name: string | null
    machine_name: string | null; pvc_type: string | null; recorder: string | null; raw_materials: RawMaterialUse[]
    tags: number; quantity_in: number; quantity_out: number; defects: number; weight_kg: number
}
type Totals = { jobs: number; active_jobs: number; tags: number; quantity_in: number; quantity_out: number; defects: number; weight_kg: number }
const props = defineProps<{ filters: { date: string; station_id: number | null }; stations: { id: number; code: string; name: string }[]; jobs: JobRow[]; totals: Totals }>()

const form = reactive({ date: props.filters.date, station_id: props.filters.station_id ? String(props.filters.station_id) : '' })
const query = computed(() => new URLSearchParams(Object.entries(form).filter(([, value]) => value) as [string, string][]).toString())
const dayExportUrl = computed(() => `/finishing/daily/export?${query.value}`)
const thaiDate = computed(() => new Date(`${props.filters.date}T00:00:00`).toLocaleDateString('th-TH', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }))
const byShift = computed(() => (['day', 'night'] as const).map((shift) => {
    const rows = props.jobs.filter((job) => job.shift === shift)
    return { shift, jobs: rows.length, tags: rows.reduce((sum, row) => sum + row.tags, 0), quantity_out: rows.reduce((sum, row) => sum + row.quantity_out, 0), weight_kg: rows.reduce((sum, row) => sum + row.weight_kg, 0) }
}))

function load() { router.get('/finishing/daily', Object.fromEntries(Object.entries(form).filter(([, value]) => value)), { preserveState: true, preserveScroll: true }) }
function shiftDay(days: number) {
    const date = new Date(`${form.date}T00:00:00`)
    date.setDate(date.getDate() + days)
    form.date = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
    load()
}
function qty(value: number) { return Number(value).toLocaleString('th-TH', { maximumFractionDigits: 3 }) }
function kg(value: number) { return Number(value).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }
function shiftLabel(value: string) { return value === 'day' ? 'กะเช้า' : 'กะดึก' }
function rawText(items: RawMaterialUse[]) { return items.length ? items.map((item) => item.quantity != null ? `${item.name} ${qty(item.quantity)} ${item.unit ?? ''}`.trim() : item.name).join(', ') : '-' }
</script>

<template>
    <Head title="สรุปการคัดตามวัน · Finishing" />
    <AppLayout title="สรุปการคัดตามวัน" full-width>
        <main class="hmi flex min-w-0 flex-col gap-4 pb-6">
            <section class="rounded-md border border-[var(--hmi-line)] bg-white p-4 shadow-sm">
                <div class="flex flex-wrap items-end gap-3">
                    <div class="min-w-0 flex-1"><p class="m-0 text-xs text-[var(--hmi-mute)]">FINISHING · สรุปการคัดตามวัน (ตามวันที่ของ Job)</p><h1 class="m-0 text-xl font-bold">{{ thaiDate }}</h1></div>
                    <div class="flex items-end gap-1">
                        <button type="button" class="min-h-10 rounded border border-slate-300 bg-white px-3 font-bold" aria-label="วันก่อนหน้า" @click="shiftDay(-1)">‹</button>
                        <label class="text-sm">วันที่<input v-model="form.date" type="date" class="mt-1 block min-h-10 rounded border-slate-300" @change="load" /></label>
                        <button type="button" class="min-h-10 rounded border border-slate-300 bg-white px-3 font-bold" aria-label="วันถัดไป" @click="shiftDay(1)">›</button>
                    </div>
                    <label class="text-sm">สถานี<select v-model="form.station_id" class="mt-1 block min-h-10 rounded border-slate-300" @change="load"><option value="">ทุกสถานี</option><option v-for="station in stations" :key="station.id" :value="String(station.id)">{{ station.code }} · {{ station.name }}</option></select></label>
                    <a :href="dayExportUrl" class="flex min-h-10 items-center gap-2 rounded bg-emerald-700 px-4 font-bold text-white" :class="totals.tags ? '' : 'pointer-events-none opacity-50'" :aria-disabled="!totals.tags"><i class="ph ph-file-csv text-lg" aria-hidden="true"></i>Export CSV ทั้งวัน (B+)</a>
                </div>
            </section>

            <section class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
                <article class="rounded-md border border-[var(--hmi-line)] bg-white p-4 shadow-sm"><p class="m-0 text-xs font-bold text-slate-500">Job</p><p class="mono m-0 mt-1 text-3xl font-extrabold">{{ totals.jobs }}</p><p class="m-0 text-xs text-slate-500">กำลังทำ {{ totals.active_jobs }}</p></article>
                <article class="rounded-md border border-[var(--hmi-line)] bg-white p-4 shadow-sm"><p class="m-0 text-xs font-bold text-slate-500">Tag ที่ออก</p><p class="mono m-0 mt-1 text-3xl font-extrabold">{{ totals.tags }}</p><p class="m-0 text-xs text-slate-500">ใบ</p></article>
                <article class="rounded-md border border-[var(--hmi-line)] bg-white p-4 shadow-sm"><p class="m-0 text-xs font-bold text-slate-500">WIP เข้า</p><p class="mono m-0 mt-1 text-3xl font-extrabold">{{ qty(totals.quantity_in) }}</p><p class="m-0 text-xs text-slate-500">แผ่น</p></article>
                <article class="rounded-md border border-emerald-300 bg-emerald-50 p-4 shadow-sm"><p class="m-0 text-xs font-bold text-emerald-800">WIP ออก</p><p class="mono m-0 mt-1 text-3xl font-extrabold text-emerald-900">{{ qty(totals.quantity_out) }}</p><p class="m-0 text-xs text-emerald-800">แผ่น</p></article>
                <article class="rounded-md border border-[var(--hmi-line)] bg-white p-4 shadow-sm"><p class="m-0 text-xs font-bold text-slate-500">น้ำหนักรวม</p><p class="mono m-0 mt-1 text-3xl font-extrabold">{{ kg(totals.weight_kg) }}</p><p class="m-0 text-xs text-slate-500">กก.</p></article>
                <article class="rounded-md border p-4 shadow-sm" :class="totals.defects ? 'border-amber-300 bg-amber-50' : 'border-[var(--hmi-line)] bg-white'"><p class="m-0 text-xs font-bold text-slate-500">ของเสีย</p><p class="mono m-0 mt-1 text-3xl font-extrabold">{{ qty(totals.defects) }}</p><p class="m-0 text-xs text-slate-500">แผ่น</p></article>
            </section>

            <section class="grid gap-3 sm:grid-cols-2">
                <article v-for="row in byShift" :key="row.shift" class="flex items-center justify-between gap-3 rounded-md border border-[var(--hmi-line)] bg-white px-4 py-3 shadow-sm">
                    <span class="mono rounded-sm px-2 py-1 text-xs font-extrabold" :class="row.shift === 'day' ? 'bg-amber-100 text-amber-900' : 'bg-indigo-100 text-indigo-900'">{{ shiftLabel(row.shift) }}</span>
                    <span class="text-sm text-slate-600">{{ row.jobs }} Job · {{ row.tags }} Tag</span>
                    <span class="mono text-sm font-bold">{{ qty(row.quantity_out) }} แผ่น · {{ kg(row.weight_kg) }} กก.</span>
                </article>
            </section>

            <section class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white shadow-sm">
                <div class="flex items-center justify-between bg-[var(--hmi-panel)] px-4 py-3"><div><h2 class="m-0 font-bold">รายการ Job ของวัน</h2><p class="m-0 text-xs text-[var(--hmi-mute)]">Export CSV ระดับ Job ได้จากปุ่มหน้าแต่ละแถว</p></div><span class="rounded bg-amber-400 px-2 py-1 text-xs font-extrabold">{{ jobs.length }} JOB</span></div>
                <div class="overflow-x-auto">
                    <table class="tbl min-w-[1300px]">
                        <thead><tr><th class="w-px">จัดการ</th><th>สถานี</th><th>กะ</th><th>สินค้า</th><th>เครื่อง</th><th>ชนิดแผ่น</th><th>วัตถุดิบ</th><th class="text-right">Tag</th><th class="text-right">WIP เข้า</th><th class="text-right">WIP ออก</th><th class="text-right">น้ำหนัก (กก.)</th><th class="text-right">เสีย</th><th>สถานะ</th></tr></thead>
                        <tbody>
                            <tr v-for="job in jobs" :key="job.id" class="align-top">
                                <td class="whitespace-nowrap"><div class="flex gap-1.5">
                                    <Link :href="`/finishing/station/${job.station_code}/jobs/${job.id}/summary`" class="inline-flex min-h-9 items-center rounded border border-slate-300 bg-white px-3 text-xs font-bold text-slate-800">ดูสรุป</Link>
                                    <a :href="`/finishing/jobs/${job.id}/export`" class="inline-flex min-h-9 items-center gap-1 rounded bg-emerald-700 px-3 text-xs font-bold text-white" :class="job.tags ? '' : 'pointer-events-none opacity-40'" :aria-disabled="!job.tags"><i class="ph ph-file-csv" aria-hidden="true"></i>CSV</a>
                                </div></td>
                                <td class="whitespace-nowrap"><strong>{{ job.station_code }}</strong><div class="text-xs text-slate-500">{{ job.station_name }}</div></td>
                                <td class="whitespace-nowrap">{{ shiftLabel(job.shift) }}</td>
                                <td class="min-w-48"><template v-if="job.product_type_code"><strong class="mono">{{ job.product_type_code }}</strong><div v-if="job.product_type_name" class="text-xs text-slate-600">{{ job.product_type_name }}</div></template><span v-else class="text-xs text-slate-400">ยังไม่มี Tag</span></td>
                                <td>{{ job.machine_name || '-' }}</td>
                                <td>{{ job.pvc_type || '-' }}</td>
                                <td class="min-w-48 text-sm">{{ rawText(job.raw_materials) }}</td>
                                <td class="mono text-right">{{ job.tags }}</td>
                                <td class="mono text-right">{{ qty(job.quantity_in) }}</td>
                                <td class="mono text-right font-bold text-emerald-900">{{ qty(job.quantity_out) }}</td>
                                <td class="mono text-right">{{ kg(job.weight_kg) }}</td>
                                <td class="mono text-right" :class="job.defects ? 'text-amber-700' : 'text-slate-400'">{{ qty(job.defects) }}</td>
                                <td><span class="whitespace-nowrap rounded-full px-3 py-1 text-xs font-bold" :class="job.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'">{{ job.status === 'active' ? 'กำลังทำงาน' : 'จบงานแล้ว' }}</span></td>
                            </tr>
                            <tr v-if="!jobs.length"><td colspan="13" class="py-10 text-center text-slate-500">ไม่มี Job ในวันที่เลือก</td></tr>
                        </tbody>
                        <tfoot v-if="jobs.length"><tr class="border-t-2 border-slate-800 font-bold"><td colspan="7">รวมทั้งวัน · {{ totals.jobs }} Job</td><td class="mono text-right">{{ totals.tags }}</td><td class="mono text-right">{{ qty(totals.quantity_in) }}</td><td class="mono text-right text-emerald-900">{{ qty(totals.quantity_out) }}</td><td class="mono text-right">{{ kg(totals.weight_kg) }}</td><td class="mono text-right">{{ qty(totals.defects) }}</td><td></td></tr></tfoot>
                    </table>
                </div>
            </section>
        </main>
    </AppLayout>
</template>
