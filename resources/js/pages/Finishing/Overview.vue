<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { errorText } from '@/lib/finishing-api'
import { printTag } from '@/lib/print-pdf'
import { toast } from '@/lib/toast'

type ActiveJob = { id: number; shift: string; shift_date: string; product_type_code: string | null; product_type_name: string | null; employees: string[]; inspector_name: string | null; tag_count: number; started_at: string | null }
type StationCard = { id: number; code: string; name: string; is_enabled: boolean; is_machine_on: boolean; status_reason: string | null; status_since: string | null; jobs: ActiveJob[]; today_tags: number; today_quantity_out: number; today_weight_kg: number; today_downtime_minutes: number }
type Kpis = { active_jobs: number; tags: number; quantity_in: number; quantity_out: number; weight_kg: number; defects: number; running: number; enabled: number; downtime_minutes: number; stops: number }
type RecentTag = { id: number; job_id: number; station_code: string; time: string | null; product_type_code: string | null; lot: string | null; tag_code: string | null; quantity_out: number; weight_kg: number | null; recorded_by: string | null; tag_url: string | null }
type Stop = { id: number; station_code: string; stopped_at_text: string; resumed_at_text: string | null; ongoing: boolean; minutes: number; reason: string; stopped_by: string | null }
const props = defineProps<{
    date: string; shift: { shift: string; shift_date: string }; kpis: Kpis; stations: StationCard[]
    byProduct: { code: string; name: string | null; tags: number; quantity_out: number; weight_kg: number }[]
    byShift: { shift: string; tags: number; quantity_out: number; weight_kg: number }[]; recent: RecentTag[]; downtime: Stop[]
}>()

const activeStations = computed(() => props.stations.filter((station) => station.is_enabled))
const disabledCodes = computed(() => props.stations.filter((station) => !station.is_enabled).map((station) => station.code))
const thaiDate = computed(() => new Date(`${props.date}T00:00:00`).toLocaleDateString('th-TH', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }))
const maxProduct = computed(() => Math.max(1, ...props.byProduct.map((row) => row.quantity_out)))
const updatedAt = ref(new Date())
const now = ref(Date.now())

function qty(value: number) { return Number(value).toLocaleString('th-TH', { maximumFractionDigits: 3 }) }
function kg(value: number | null) { return value == null ? '-' : Number(value).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }
function duration(minutes: number) {
    const total = Math.round(minutes)
    const hours = Math.floor(total / 60)
    return hours ? `${hours} ชม. ${total % 60} นาที` : `${total} นาที`
}
function since(iso: string | null) { return iso ? duration(Math.max(0, (now.value - new Date(iso).getTime()) / 60000)) : '' }
function shiftLabel(value: string) { return value === 'day' ? 'กะเช้า' : 'กะดึก' }
function print(url: string) { printTag(url).catch((e) => toast(errorText(e), 'error')) }

// A wall/office screen: refresh the numbers every minute.
let refresh: number | undefined
let clock: number | undefined
onMounted(() => {
    refresh = window.setInterval(() => router.reload({ onSuccess: () => { updatedAt.value = new Date() } }), 60000)
    clock = window.setInterval(() => { now.value = Date.now() }, 30000)
})
onUnmounted(() => { window.clearInterval(refresh); window.clearInterval(clock) })
</script>

<template>
    <Head title="Dashboard Finishing" />
    <AppLayout title="Dashboard Finishing" full-width hide-header>
        <main class="hmi flex min-w-0 flex-col gap-4 pb-6">
            <header class="flex flex-wrap items-center gap-3 rounded-md border border-[var(--hmi-line)] bg-white px-4 py-3 shadow-sm">
                <div class="min-w-0 flex-1">
                    <p class="m-0 text-xs text-[var(--hmi-mute)]">FINISHING · อัปเดต {{ updatedAt.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' }) }} (รีเฟรชทุก 1 นาที)</p>
                    <h1 class="m-0 text-xl font-bold text-[var(--hmi-title)]">Dashboard Finishing · {{ thaiDate }}</h1>
                </div>
                <span class="rounded-full px-3 py-1 text-sm font-bold" :class="shift.shift === 'day' ? 'bg-amber-100 text-amber-900' : 'bg-indigo-100 text-indigo-900'">ตอนนี้ {{ shiftLabel(shift.shift) }}</span>
                <div class="flex flex-wrap gap-2">
                    <Link href="/finishing?choose=1" class="rounded bg-emerald-700 px-4 py-2 text-sm font-bold text-white">เลือกสถานี / เริ่มงาน</Link>
                    <Link href="/finishing/dashboard" class="rounded border border-[var(--hmi-line)] bg-white px-4 py-2 text-sm font-semibold">ผังเครื่องจักร</Link>
                    <Link href="/finishing/daily" class="rounded border border-[var(--hmi-line)] bg-white px-4 py-2 text-sm font-semibold">สรุปการคัดตามวัน</Link>
                </div>
            </header>

            <section class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
                <article class="rounded-md border border-[var(--hmi-line)] bg-white p-4 shadow-sm"><p class="m-0 text-xs font-bold text-slate-500">เครื่องเดินอยู่</p><p class="mono m-0 mt-1 text-3xl font-extrabold" :class="kpis.running < kpis.enabled ? 'text-red-700' : 'text-emerald-800'">{{ kpis.running }}<span class="text-lg text-slate-400">/{{ kpis.enabled }}</span></p><p class="m-0 text-xs text-slate-500">สถานีที่เปิดใช้</p></article>
                <article class="rounded-md border border-[var(--hmi-line)] bg-white p-4 shadow-sm"><p class="m-0 text-xs font-bold text-slate-500">Job กำลังทำ</p><p class="mono m-0 mt-1 text-3xl font-extrabold">{{ kpis.active_jobs }}</p><p class="m-0 text-xs text-slate-500">Job</p></article>
                <article class="rounded-md border border-[var(--hmi-line)] bg-white p-4 shadow-sm"><p class="m-0 text-xs font-bold text-slate-500">Tag วันนี้</p><p class="mono m-0 mt-1 text-3xl font-extrabold">{{ kpis.tags }}</p><p class="m-0 text-xs text-slate-500">ใบ</p></article>
                <article class="rounded-md border border-emerald-300 bg-emerald-50 p-4 shadow-sm"><p class="m-0 text-xs font-bold text-emerald-800">WIP ออกวันนี้</p><p class="mono m-0 mt-1 text-3xl font-extrabold text-emerald-900">{{ qty(kpis.quantity_out) }}</p><p class="m-0 text-xs text-emerald-800">แผ่น · เข้า {{ qty(kpis.quantity_in) }}</p></article>
                <article class="rounded-md border border-[var(--hmi-line)] bg-white p-4 shadow-sm"><p class="m-0 text-xs font-bold text-slate-500">น้ำหนักวันนี้</p><p class="mono m-0 mt-1 text-3xl font-extrabold">{{ kg(kpis.weight_kg) }}</p><p class="m-0 text-xs text-slate-500">กก.<template v-if="kpis.defects"> · เสีย {{ qty(kpis.defects) }}</template></p></article>
                <article class="rounded-md border p-4 shadow-sm" :class="kpis.downtime_minutes ? 'border-red-300 bg-red-50' : 'border-[var(--hmi-line)] bg-white'"><p class="m-0 text-xs font-bold" :class="kpis.downtime_minutes ? 'text-red-800' : 'text-slate-500'">หยุดเครื่องวันนี้</p><p class="mono m-0 mt-1 text-2xl font-extrabold" :class="kpis.downtime_minutes ? 'text-red-700' : ''">{{ duration(kpis.downtime_minutes) }}</p><p class="m-0 text-xs text-slate-500">{{ kpis.stops }} ครั้ง</p></article>
            </section>

            <section class="grid gap-4 lg:grid-cols-2">
                <article v-for="station in activeStations" :key="station.id" class="overflow-hidden rounded-md border-2 bg-white shadow-sm" :class="station.is_machine_on ? 'border-emerald-500' : 'border-red-500'">
                    <div class="flex items-center gap-3 px-4 py-3" :class="station.is_machine_on ? 'bg-emerald-50' : 'bg-red-50'">
                        <span class="mono rounded bg-emerald-700 px-2.5 py-1.5 text-lg font-extrabold text-white">{{ station.code }}</span>
                        <div class="min-w-0 flex-1"><h2 class="m-0 text-lg font-bold">{{ station.name }}</h2><p class="m-0 truncate text-xs" :class="station.is_machine_on ? 'text-emerald-800' : 'font-semibold text-red-800'">{{ station.status_reason || 'ยังไม่มีประวัติสถานะ' }}<template v-if="station.status_since"> · {{ station.is_machine_on ? 'เดินมาแล้ว' : 'หยุดมาแล้ว' }} {{ since(station.status_since) }}</template></p></div>
                        <span class="shrink-0 rounded-full px-3 py-1 text-sm font-bold text-white" :class="station.is_machine_on ? 'bg-emerald-600' : 'bg-red-600'">{{ station.is_machine_on ? '● เดินเครื่อง' : '● หยุดเครื่อง' }}</span>
                    </div>
                    <dl class="m-0 grid grid-cols-4 divide-x divide-slate-100 border-y border-slate-100 text-center">
                        <div class="px-2 py-2"><dt class="text-[11px] text-slate-500">Tag วันนี้</dt><dd class="mono m-0 text-lg font-bold">{{ station.today_tags }}</dd></div>
                        <div class="px-2 py-2"><dt class="text-[11px] text-slate-500">แผ่น</dt><dd class="mono m-0 text-lg font-bold text-emerald-900">{{ qty(station.today_quantity_out) }}</dd></div>
                        <div class="px-2 py-2"><dt class="text-[11px] text-slate-500">กก.</dt><dd class="mono m-0 text-lg font-bold">{{ kg(station.today_weight_kg) }}</dd></div>
                        <div class="px-2 py-2"><dt class="text-[11px] text-slate-500">หยุด</dt><dd class="mono m-0 text-lg font-bold" :class="station.today_downtime_minutes ? 'text-red-700' : 'text-slate-400'">{{ Math.round(station.today_downtime_minutes) }}′</dd></div>
                    </dl>
                    <div class="space-y-2 p-4">
                        <p class="m-0 text-xs font-bold text-slate-600">กำลังแปรรูป {{ station.jobs.length }} Job</p>
                        <Link v-for="job in station.jobs" :key="job.id" :href="`/finishing/station/${station.code}/jobs/${job.id}`" class="block rounded border border-slate-200 px-3 py-2 text-sm hover:border-emerald-500">
                            <span class="flex items-start justify-between gap-2"><strong class="min-w-0 break-words"><template v-if="job.product_type_code"><span class="mono">{{ job.product_type_code }}</span>{{ job.product_type_name ? ` · ${job.product_type_name}` : '' }}</template><span v-else class="text-amber-700">สินค้า: รอ Tag แรก</span></strong><span class="shrink-0 text-xs font-bold text-emerald-800">เปิด ›</span></span>
                            <span class="mt-1 block text-xs text-slate-700"><span class="font-semibold">ผู้คัด:</span> {{ job.employees.length ? job.employees.join(', ') : '-' }}</span>
                            <span class="block text-xs text-slate-500">{{ job.shift_date }} · {{ shiftLabel(job.shift) }} · เริ่ม {{ job.started_at }} · {{ job.tag_count }} Tag<template v-if="job.inspector_name"> · คุม {{ job.inspector_name }}</template></span>
                        </Link>
                        <div v-if="!station.jobs.length" class="flex items-center justify-between gap-2 rounded border border-dashed border-slate-300 px-3 py-2 text-sm text-slate-500"><span>ไม่มี Job ที่กำลังทำ</span><Link :href="`/finishing/station/${station.code}/jobs`" class="font-bold text-emerald-800">สร้าง Job ›</Link></div>
                    </div>
                </article>
            </section>
            <p v-if="disabledCodes.length" class="m-0 -mt-2 text-xs text-slate-500">{{ disabledCodes.join(', ') }} ยังไม่เปิดใช้</p>

            <div class="grid min-w-0 gap-4 xl:grid-cols-[minmax(0,1fr)_380px]">
                <section class="min-w-0 overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white shadow-sm">
                    <div class="flex items-center justify-between bg-[var(--hmi-panel)] px-4 py-3"><div><h2 class="m-0 font-bold">Tag WIP ล่าสุด</h2><p class="m-0 text-xs text-[var(--hmi-mute)]">10 รายการล่าสุดของวันนี้</p></div><Link href="/finishing/daily" class="text-sm font-bold text-emerald-800">ดูทั้งวัน ›</Link></div>
                    <div class="overflow-x-auto">
                        <table class="tbl min-w-[760px]">
                            <thead><tr><th class="w-px">Tag</th><th>เวลา</th><th>สถานี</th><th>สินค้า</th><th>Lot</th><th class="text-right">แผ่น</th><th class="text-right">กก.</th><th>บันทึกโดย</th></tr></thead>
                            <tbody>
                                <tr v-for="row in recent" :key="row.id">
                                    <td><button v-if="row.tag_url" type="button" class="inline-flex items-center gap-1 rounded bg-emerald-700 px-2.5 py-1.5 text-xs font-bold text-white" @click="print(row.tag_url)"><i class="ph ph-printer" aria-hidden="true"></i>พิมพ์</button></td>
                                    <td class="mono">{{ row.time }}</td>
                                    <td class="font-bold">{{ row.station_code }}</td>
                                    <td class="mono">{{ row.product_type_code || '-' }}</td>
                                    <td class="mono">{{ row.lot || '-' }}</td>
                                    <td class="mono text-right font-bold text-emerald-900">{{ qty(row.quantity_out) }}</td>
                                    <td class="mono text-right">{{ kg(row.weight_kg) }}</td>
                                    <td class="text-sm">{{ row.recorded_by || '-' }}</td>
                                </tr>
                                <tr v-if="!recent.length"><td colspan="8" class="py-8 text-center text-slate-500">วันนี้ยังไม่มี Tag</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <aside class="flex min-w-0 flex-col gap-4">
                    <section class="rounded-md border border-[var(--hmi-line)] bg-white p-4 shadow-sm">
                        <h2 class="m-0 mb-3 font-bold">แยกตามกะ</h2>
                        <div class="grid grid-cols-2 gap-3">
                            <div v-for="row in byShift" :key="row.shift" class="rounded border p-3" :class="row.shift === shift.shift ? 'border-emerald-400 bg-emerald-50' : 'border-slate-200'">
                                <p class="m-0 text-xs font-bold text-slate-600">{{ shiftLabel(row.shift) }}</p>
                                <p class="mono m-0 text-xl font-extrabold">{{ qty(row.quantity_out) }} <span class="text-xs font-normal text-slate-500">แผ่น</span></p>
                                <p class="m-0 text-xs text-slate-500">{{ row.tags }} Tag · {{ kg(row.weight_kg) }} กก.</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-md border border-[var(--hmi-line)] bg-white p-4 shadow-sm">
                        <h2 class="m-0 mb-3 font-bold">แยกตามสินค้า</h2>
                        <ul class="m-0 space-y-2.5 p-0">
                            <li v-for="row in byProduct" :key="row.code" class="m-0 list-none text-sm">
                                <div class="flex items-baseline justify-between gap-3"><span class="min-w-0 truncate"><strong class="mono">{{ row.code }}</strong><span v-if="row.name" class="text-xs text-slate-500"> · {{ row.name }}</span></span><span class="mono shrink-0 font-bold">{{ qty(row.quantity_out) }}</span></div>
                                <div class="mt-1 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-emerald-600" :style="{ width: `${(row.quantity_out / maxProduct) * 100}%` }"></div></div>
                                <p class="m-0 mt-0.5 text-[11px] text-slate-500">{{ row.tags }} Tag · {{ kg(row.weight_kg) }} กก.</p>
                            </li>
                            <li v-if="!byProduct.length" class="m-0 list-none py-2 text-center text-sm text-slate-500">ยังไม่มีข้อมูล</li>
                        </ul>
                    </section>

                    <section class="rounded-md border border-[var(--hmi-line)] bg-white p-4 shadow-sm">
                        <div class="mb-3 flex items-baseline justify-between"><h2 class="m-0 font-bold">หยุดเครื่องวันนี้</h2><Link href="/finishing/reports" class="text-xs font-bold text-emerald-800">รายงาน ›</Link></div>
                        <ul class="m-0 space-y-2 p-0">
                            <li v-for="stop in downtime" :key="stop.id" class="m-0 flex list-none items-start justify-between gap-3 text-sm">
                                <span class="min-w-0"><strong>{{ stop.station_code }}</strong> · {{ stop.reason }}<span class="block text-xs text-slate-500">{{ stop.stopped_at_text.slice(-5) }} → {{ stop.ongoing ? 'ยังหยุดอยู่' : stop.resumed_at_text?.slice(-5) }}</span></span>
                                <span class="mono shrink-0 font-bold text-red-700">{{ duration(stop.minutes) }}</span>
                            </li>
                            <li v-if="!downtime.length" class="m-0 list-none py-2 text-center text-sm text-slate-500">วันนี้ยังไม่มีการหยุดเครื่อง</li>
                        </ul>
                    </section>
                </aside>
            </div>
        </main>
    </AppLayout>
</template>
