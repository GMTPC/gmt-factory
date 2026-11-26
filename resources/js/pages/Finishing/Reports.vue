<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { reactive, ref, watch } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { errorText, finishingRequest } from '@/lib/finishing-api'

type Station = { id: number; code: string; name: string }
type Summary = { quantity_in: number; quantity_out: number; defects: number; ft: number; byStation: { station: string; quantity_out: number; defects: number; logs: number }[]; byShift: { shift: string; quantity_out: number; defects: number; logs: number }[]; daily: { date: string; quantity_out: number; defects: number }[] }
type DowntimePeriod = { id: number; station_code: string; station_name: string; stopped_at_text: string; resumed_at_text: string | null; ongoing: boolean; minutes: number; reason: string; stopped_by: string | null; resume_reason: string | null; resumed_by: string | null }
type Downtime = { periods: DowntimePeriod[]; byStation: { station_code: string; station_name: string; stop_count: number; minutes: number; longest_minutes: number; ongoing: boolean }[]; byReason: { reason: string; stop_count: number; minutes: number }[]; total_minutes: number; stop_count: number; ongoing_count: number }
const props = defineProps<{ stations: Station[]; filters: { date_from?: string | null; date_to?: string | null; station_id?: number | string | null }; defects: { name: string; quantity: number }[]; downtime: Downtime; csrf: string; isAdmin: boolean; summary: Summary }>()
const filters = reactive({ date_from: props.filters.date_from || '', date_to: props.filters.date_to || '', station_id: props.filters.station_id ? String(props.filters.station_id) : '' })
const defectName = ref('')
const error = ref('')
const message = ref('')
const busy = ref(false)
watch(() => props.filters, (value) => Object.assign(filters, { date_from: value.date_from || '', date_to: value.date_to || '', station_id: value.station_id ? String(value.station_id) : '' }))
function search() { router.get('/finishing/reports', { ...filters }, { preserveState: true, preserveScroll: true }) }
function clear() { Object.assign(filters, { date_from: '', date_to: '', station_id: '' }); search() }
function exportUrl(path = 'export') { const query = new URLSearchParams(Object.entries(filters).filter(([, value]) => value)); return `/api/finishing/reports/${path}?${query.toString()}` }
function duration(minutes: number) {
    const total = Math.round(minutes)
    const hours = Math.floor(total / 60)
    return hours ? `${hours} ชม. ${total % 60} นาที` : `${total} นาที`
}
const maxReasonMinutes = () => Math.max(1, ...props.downtime.byReason.map((row) => row.minutes))
async function addDefectType() {
    error.value = ''; message.value = ''; busy.value = true
    try { await finishingRequest('/api/finishing/defect-types', 'POST', { name: defectName.value }, props.csrf); message.value = 'เพิ่มประเภทของเสียแล้ว'; defectName.value = '' }
    catch (e) { error.value = errorText(e) } finally { busy.value = false }
}
</script>

<template>
    <Head title="Finishing · รายงาน" />
    <AppLayout title="Finishing · รายงาน" full-width>
        <main class="hmi flex flex-col gap-4 pb-6">
            <header class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white shadow-sm"><div class="hazard" aria-hidden="true"></div><div class="flex flex-wrap items-center justify-between gap-3 bg-[var(--hmi-panel)] px-5 py-4"><div><p class="m-0 text-xs text-[var(--hmi-mute)]">FINISHING PRODUCTION</p><h1 class="m-0 text-xl font-bold">รายงานการผลิต ของเสีย และการหยุดเครื่อง</h1></div><div class="flex gap-2"><Link href="/finishing/dashboard" class="rounded border border-[var(--hmi-line)] bg-white px-4 py-2 text-sm font-semibold">Dashboard</Link><Link href="/finishing?choose=1" class="rounded bg-[var(--hmi-ink)] px-4 py-2 text-sm font-bold text-white">เลือกสถานี</Link></div></div></header>
            <div v-if="message" role="status" class="rounded border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">{{ message }}</div><div v-if="error" role="alert" class="rounded border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">{{ error }}</div>
            <section class="rounded-md border border-[var(--hmi-line)] bg-white p-4 shadow-sm"><div class="mb-3 flex flex-wrap items-center justify-between gap-2"><div><h2 class="m-0 font-bold">ค้นหารายการ</h2><p class="m-0 text-xs text-[var(--hmi-mute)]">กรองตามวันที่กะและสถานี</p></div><a :href="exportUrl()" class="rounded border border-[var(--hmi-line)] px-3 py-2 text-sm font-semibold">ส่งออก Excel (ผลิต)</a></div><form class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" @submit.prevent="search"><label class="text-sm">ตั้งแต่วันที่<input v-model="filters.date_from" type="date" class="mt-1 w-full rounded border-slate-300" /></label><label class="text-sm">ถึงวันที่<input v-model="filters.date_to" type="date" class="mt-1 w-full rounded border-slate-300" /></label><label class="text-sm">สถานี<select v-model="filters.station_id" class="mt-1 w-full rounded border-slate-300"><option value="">ทุกสถานี</option><option v-for="station in stations" :key="station.id" :value="String(station.id)">{{ station.code }} · {{ station.name }}</option></select></label><div class="flex items-end gap-2"><button class="rounded bg-[var(--hmi-ink)] px-5 py-2 font-bold text-white">ค้นหา</button><button type="button" class="rounded border border-slate-300 px-4 py-2 font-semibold" @click="clear">ล้างตัวกรอง</button></div></form></section>
            <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4"><article v-for="item in [{ label: 'รับเข้ารวม', value: summary.quantity_in }, { label: 'ผลิตได้', value: summary.quantity_out }, { label: 'ยอดเสีย', value: summary.defects }, { label: 'FT', value: summary.ft }]" :key="item.label" class="rounded-md border border-[var(--hmi-line)] bg-white p-4 shadow-sm"><p class="m-0 text-sm text-[var(--hmi-mute)]">{{ item.label }}</p><strong class="mono text-2xl">{{ Number(item.value || 0).toLocaleString() }}</strong></article></section>
            <section class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white shadow-sm" aria-labelledby="downtime-title">
                <div class="flex flex-wrap items-center justify-between gap-2 bg-[var(--hmi-panel)] px-4 py-3">
                    <div><h2 id="downtime-title" class="m-0 font-bold">รายงานหยุดเครื่องจักร</h2><p class="m-0 text-xs text-[var(--hmi-mute)]">จากการกด เดิน / หยุดเครื่อง ที่หน้าสถานี · ใช้ตัวกรองวันที่และสถานีด้านบน</p></div>
                    <a :href="exportUrl('downtime-export')" class="rounded border border-[var(--hmi-line)] bg-white px-3 py-2 text-sm font-semibold">ส่งออก Excel</a>
                </div>
                <div class="grid grid-cols-3 divide-x divide-[var(--hmi-line)] border-b border-[var(--hmi-line)]">
                    <div class="p-3"><p class="m-0 text-xs text-[var(--hmi-mute)]">เวลาหยุดรวม</p><strong class="mono text-xl text-red-700">{{ duration(downtime.total_minutes) }}</strong></div>
                    <div class="p-3"><p class="m-0 text-xs text-[var(--hmi-mute)]">จำนวนครั้ง</p><strong class="mono text-xl">{{ downtime.stop_count }}</strong></div>
                    <div class="p-3"><p class="m-0 text-xs text-[var(--hmi-mute)]">ยังหยุดอยู่</p><strong class="mono text-xl" :class="downtime.ongoing_count ? 'text-red-700' : ''">{{ downtime.ongoing_count }}</strong></div>
                </div>
                <div class="grid gap-0 xl:grid-cols-2 xl:divide-x xl:divide-[var(--hmi-line)]">
                    <div class="overflow-x-auto"><table class="tbl min-w-[420px]"><thead><tr><th>สถานี</th><th>ครั้ง</th><th>เวลาหยุดรวม</th><th>นานสุด</th></tr></thead><tbody>
                        <tr v-for="row in downtime.byStation" :key="row.station_code"><td><strong>{{ row.station_code }}</strong> {{ row.station_name }}<span v-if="row.ongoing" class="ml-2 rounded-full bg-red-100 px-2 py-0.5 text-xs font-bold text-red-700">หยุดอยู่</span></td><td>{{ row.stop_count }}</td><td class="font-bold text-red-700">{{ duration(row.minutes) }}</td><td>{{ duration(row.longest_minutes) }}</td></tr>
                        <tr v-if="!downtime.byStation.length"><td colspan="4" class="py-6 text-center text-slate-500">ไม่มีการหยุดเครื่องในช่วงที่เลือก</td></tr>
                    </tbody></table></div>
                    <div class="p-4">
                        <h3 class="m-0 mb-3 text-sm font-bold">สาเหตุการหยุด</h3>
                        <ul class="m-0 space-y-2 p-0">
                            <li v-for="row in downtime.byReason" :key="row.reason" class="m-0 list-none">
                                <div class="flex items-baseline justify-between gap-3 text-sm"><span class="min-w-0 truncate">{{ row.reason }} <span class="text-[var(--hmi-mute)]">· {{ row.stop_count }} ครั้ง</span></span><strong class="mono shrink-0">{{ duration(row.minutes) }}</strong></div>
                                <div class="mt-1 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-red-500" :style="{ width: `${(row.minutes / maxReasonMinutes()) * 100}%` }"></div></div>
                            </li>
                            <li v-if="!downtime.byReason.length" class="m-0 list-none py-4 text-center text-sm text-slate-500">ไม่มีข้อมูล</li>
                        </ul>
                    </div>
                </div>
                <details class="border-t border-[var(--hmi-line)]" :open="downtime.periods.length <= 10">
                    <summary class="cursor-pointer px-4 py-3 text-sm font-semibold">รายการหยุดเครื่องทั้งหมด ({{ downtime.periods.length }})</summary>
                    <div class="overflow-x-auto"><table class="tbl min-w-[900px]"><thead><tr><th>สถานี</th><th>หยุดเมื่อ</th><th>เดินต่อเมื่อ</th><th>เวลาหยุด</th><th>เหตุผลหยุด</th><th>ผู้กดหยุด</th><th>เหตุผลเริ่ม / ผู้กด</th></tr></thead><tbody>
                        <tr v-for="row in downtime.periods" :key="row.id"><td class="font-bold">{{ row.station_code }}</td><td class="whitespace-nowrap">{{ row.stopped_at_text }}</td><td class="whitespace-nowrap"><span v-if="row.ongoing" class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-bold text-red-700">ยังหยุดอยู่</span><template v-else>{{ row.resumed_at_text }}</template></td><td class="whitespace-nowrap font-bold text-red-700">{{ duration(row.minutes) }}</td><td>{{ row.reason }}</td><td>{{ row.stopped_by || '-' }}</td><td>{{ row.resume_reason ? `${row.resume_reason} · ${row.resumed_by || '-'}` : '-' }}</td></tr>
                        <tr v-if="!downtime.periods.length"><td colspan="7" class="py-6 text-center text-slate-500">ไม่มีการหยุดเครื่องในช่วงที่เลือก</td></tr>
                    </tbody></table></div>
                </details>
            </section>
            <section class="grid gap-4 xl:grid-cols-2"><article class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white"><div class="bg-[var(--hmi-panel)] px-4 py-3"><h2 class="m-0 font-bold">ยอดผลิตแยกตามสถานี</h2></div><div class="overflow-x-auto"><table class="tbl min-w-[500px]"><thead><tr><th>สถานี</th><th>รายการ</th><th>ผลิตได้</th><th>เสีย</th></tr></thead><tbody><tr v-for="row in summary.byStation" :key="row.station"><td>{{ row.station }}</td><td>{{ row.logs }}</td><td>{{ Number(row.quantity_out).toLocaleString() }}</td><td class="text-red-700">{{ Number(row.defects).toLocaleString() }}</td></tr><tr v-if="!summary.byStation.length"><td colspan="4" class="py-6 text-center text-slate-500">ไม่มีข้อมูลในช่วงที่เลือก</td></tr></tbody></table></div></article>
                <article class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white"><div class="bg-[var(--hmi-panel)] px-4 py-3"><h2 class="m-0 font-bold">ยอดผลิตแยกตามกะ</h2></div><div class="overflow-x-auto"><table class="tbl min-w-[500px]"><thead><tr><th>กะ</th><th>รายการ</th><th>ผลิตได้</th><th>เสีย</th></tr></thead><tbody><tr v-for="row in summary.byShift" :key="row.shift"><td>{{ row.shift === 'day' ? 'กะเช้า' : 'กะดึก' }}</td><td>{{ row.logs }}</td><td>{{ Number(row.quantity_out).toLocaleString() }}</td><td class="text-red-700">{{ Number(row.defects).toLocaleString() }}</td></tr><tr v-if="!summary.byShift.length"><td colspan="4" class="py-6 text-center text-slate-500">ไม่มีข้อมูลในช่วงที่เลือก</td></tr></tbody></table></div></article></section>
            <section class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white"><div class="bg-[var(--hmi-panel)] px-4 py-3"><h2 class="m-0 font-bold">รายวัน</h2></div><div class="overflow-x-auto"><table class="tbl min-w-[500px]"><thead><tr><th>วันที่กะ</th><th>ผลิตได้</th><th>เสีย</th></tr></thead><tbody><tr v-for="row in summary.daily" :key="row.date"><td>{{ row.date }}</td><td>{{ Number(row.quantity_out).toLocaleString() }}</td><td class="text-red-700">{{ Number(row.defects).toLocaleString() }}</td></tr><tr v-if="!summary.daily.length"><td colspan="3" class="py-6 text-center text-slate-500">ไม่มีข้อมูลในช่วงที่เลือก</td></tr></tbody></table></div></section>
            <section class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white"><div class="bg-[var(--hmi-panel)] px-4 py-3"><h2 class="m-0 font-bold">สรุปประเภทของเสีย</h2></div><div class="overflow-x-auto"><table class="tbl min-w-[400px]"><thead><tr><th>ประเภท</th><th>จำนวน</th></tr></thead><tbody><tr v-for="row in defects" :key="row.name"><td>{{ row.name }}</td><td>{{ Number(row.quantity).toLocaleString() }}</td></tr><tr v-if="!defects.length"><td colspan="2" class="py-6 text-center text-slate-500">ไม่มีรายละเอียดของเสียในช่วงที่เลือก</td></tr></tbody></table></div></section>
            <section v-if="isAdmin" class="flex flex-wrap items-end gap-3 rounded-md border border-[var(--hmi-line)] bg-white p-4"><div class="mr-auto"><h2 class="m-0 font-bold">จัดการประเภทของเสีย</h2><p class="m-0 text-xs text-[var(--hmi-mute)]">เพิ่มประเภทกลางสำหรับทุกสถานี</p></div><label class="min-w-64 flex-1 text-sm">ชื่อประเภท<input v-model="defectName" class="mt-1 w-full rounded border-slate-300" maxlength="120" /></label><button :disabled="busy || !defectName.trim()" class="rounded bg-[var(--hmi-ink)] px-5 py-2 font-bold text-white disabled:opacity-50" @click="addDefectType">เพิ่มประเภท</button></section>
        </main>
    </AppLayout>
</template>
