<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { errorText, finishingRequest } from '@/lib/finishing-api'

type Station = { id: number; code: string; name: string; is_machine_on: boolean; is_enabled?: boolean; latest_status_event?: { reason: string; created_at: string; user?: { name: string } | null } | null; today_logs: number; today_quantity_out: number | null; today_defects: number | null }
type MapStation = { code: string; title: string; flow: string; track: 'white' | 'pvc' | 'paint'; x: number; y: number; w: number; h: number; target: string }
const props = defineProps<{ stations: Station[]; recent: any[]; byShift: { shift: string; quantity_out: number; defects: number; log_count: number }[]; date: string; alerts: Station[]; csrf: string; statusReady: boolean }>()
const busyId = ref<number | null>(null)
const selectedStation = ref<Station | null>(null)
const statusReason = ref('')
const message = ref('')
const error = ref('')
const mapStations: MapStation[] = [
    { code: 'ST1', title: 'ขาวผ่อง', flow: 'WIP จาก QC → WIP ขาวผ่อง', track: 'white', x: 780, y: 24, w: 250, h: 90, target: 'ได้ WIP ขาวผ่อง' },
    { code: 'ST2', title: 'เคลือบ PVC', flow: 'WIP จาก QC → WIP PVC', track: 'pvc', x: 780, y: 404, w: 250, h: 120, target: 'ได้ WIP PVC' },
    { code: 'ST3', title: 'ตัดขาวผ่อง', flow: 'WIP ขาวผ่อง → ตัด → เสีย / Tag → FG', track: 'white', x: 1035, y: 160, w: 125, h: 570, target: 'เป้าหมาย FG · คงเป็น WIP' },
    { code: 'ST4', title: 'ตัด PVC', flow: 'WIP PVC → ตัด → FG', track: 'pvc', x: 225, y: 35, w: 345, h: 80, target: 'เป้าหมาย FG · คงเป็น WIP' },
    { code: 'ST5', title: 'ปุลาย / ทาสี', flow: 'WIP จาก QC → ทาสี → WIP แผ่นเล็ก (กองจบกะ)', track: 'paint', x: 140, y: 395, w: 380, h: 110, target: 'ได้ WIP แผ่นเล็ก' },
    { code: 'ST6', title: 'แพ็ค', flow: 'แพ็ค WIP จากสถานี 5 → Tag → FG', track: 'paint', x: 40, y: 125, w: 140, h: 245, target: 'เป้าหมาย FG · คงเป็น WIP' },
]
const busyStation = (code: string) => props.stations.find((station) => station.code === code)
const isEnabled = (code: string) => busyStation(code)?.is_enabled !== false
function percent(station: Station) { const output = Number(station.today_quantity_out || 0); return output ? Math.round((Number(station.today_defects || 0) / output) * 10000) / 100 : 0 }
function cardClass(track: MapStation['track']) {
    return track === 'white' ? 'border-blue-500 bg-blue-50' : track === 'pvc' ? 'border-violet-500 bg-violet-50' : 'border-orange-500 bg-orange-50'
}
function dotClass(track: MapStation['track']) { return track === 'white' ? 'bg-blue-600' : track === 'pvc' ? 'bg-violet-600' : 'bg-orange-600' }
function openStatusDialog(station: Station) {
    selectedStation.value = station
    statusReason.value = ''
    error.value = ''
}
async function changeStationStatus() {
    const station = selectedStation.value
    if (!station || !statusReason.value.trim()) return
    busyId.value = station.id; error.value = ''; message.value = ''
    const nextRunning = !station.is_machine_on
    try {
        await finishingRequest(`/api/finishing/stations/${station.id}/machine`, 'PATCH', { is_machine_on: nextRunning, reason: statusReason.value.trim() }, props.csrf)
        selectedStation.value = null
        message.value = `${nextRunning ? 'Start' : 'Stop'} Station ${station.code} แล้ว`
        router.reload({ preserveScroll: true })
    } catch (e) { error.value = errorText(e) } finally { busyId.value = null }
}
</script>

<template>
    <Head title="Finishing · ผังเครื่องจักร" />
    <AppLayout title="Finishing · ผังเครื่องจักร" full-width>
        <main class="hmi flex flex-col gap-4 pb-6">
            <header class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white shadow-sm"><div class="hazard" aria-hidden="true"></div><div class="flex flex-wrap items-center justify-between gap-3 bg-[var(--hmi-panel)] px-5 py-4"><div><p class="m-0 text-xs text-[var(--hmi-mute)]">FINISHING PRODUCTION · {{ date }}</p><h1 class="m-0 text-xl font-bold">ผังเครื่องจักร — Finishing</h1><p class="m-0 mt-1 text-sm text-[var(--hmi-mute)]">6 สถานี · 3 สายการผลิต · เปิด Job ก่อนแปรรูป</p></div><div class="flex gap-2"><Link href="/finishing?choose=1" class="rounded border border-[var(--hmi-line)] bg-white px-4 py-2 text-sm font-semibold">เลือกสถานี</Link><Link href="/finishing/reports" class="rounded bg-[var(--hmi-ink)] px-4 py-2 text-sm font-bold text-white">รายงาน</Link></div></div></header>
            <div v-if="message" role="status" class="rounded border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">{{ message }}</div><div v-if="error" role="alert" class="rounded border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">{{ error }}</div>
            <section v-if="alerts.length" class="rounded-md border border-amber-300 bg-amber-50 p-4"><h2 class="m-0 font-bold text-amber-900">แจ้งเตือนยอดเสียสูง</h2><p class="mb-0 mt-1 text-sm text-amber-800">{{ alerts.map(s => `${s.code} ${s.name} · ${percent(s)}%`).join(' / ') }}</p></section>

            <section v-if="statusReady" class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white shadow-sm">
                <div class="bg-[var(--hmi-panel)] px-5 py-3"><h2 class="m-0 font-bold">Start / Stop Station</h2><p class="m-0 text-xs text-[var(--hmi-mute)]">เลือกสถานีและระบุเหตุผลทุกครั้งที่เปลี่ยนสถานะ</p></div>
                <div class="grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                    <div v-for="station in stations" :key="station.id" class="flex flex-col gap-2 rounded border border-[var(--hmi-line)] p-3">
                        <div class="flex items-center justify-between gap-2"><strong>{{ station.code }}</strong><span class="rounded-full px-2 py-1 text-xs font-bold" :class="station.is_machine_on ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'">{{ station.is_machine_on ? 'ทำงาน' : 'หยุด' }}</span></div>
                        <p class="m-0 min-h-10 text-xs text-[var(--hmi-mute)]">{{ station.latest_status_event?.reason || 'ยังไม่มีประวัติสถานะ' }}</p>
                        <button type="button" class="mt-auto rounded px-3 py-2 text-sm font-bold text-white" :class="station.is_machine_on ? 'bg-red-700' : 'bg-emerald-700'" @click="openStatusDialog(station)">{{ station.is_machine_on ? 'Stop Station' : 'Start Station' }}</button>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[var(--hmi-line)] px-5 py-3"><div><h2 class="m-0 font-bold">ผังการไหลของงาน</h2><p class="m-0 text-xs text-[var(--hmi-mute)]">เลือกสถานีเพื่อสร้าง Job แล้วเริ่มบันทึกงาน</p></div><div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs"><span class="flex items-center gap-1"><i class="h-3 w-3 rounded-sm border border-blue-500 bg-blue-100" />ขาวผ่อง</span><span class="flex items-center gap-1"><i class="h-3 w-3 rounded-sm border border-violet-500 bg-violet-100" />PVC</span><span class="flex items-center gap-1"><i class="h-3 w-3 rounded-sm border border-orange-500 bg-orange-100" />ทาสี / แพ็ค</span><span class="rounded-full border border-amber-400 bg-amber-50 px-2 py-1 font-bold text-amber-800">WIP</span><span class="rounded-full border border-emerald-400 bg-emerald-50 px-2 py-1 font-bold text-emerald-800">FG เป้าหมาย</span></div></div>
                <div class="overflow-x-auto p-3 sm:p-5">
                    <div class="relative h-[780px] min-w-[1200px] overflow-hidden rounded-2xl border-2 border-slate-200 bg-white" style="background-image:linear-gradient(to right,#e8edf3 1px,transparent 1px),linear-gradient(to bottom,#e8edf3 1px,transparent 1px);background-size:32px 32px">
                        <svg class="pointer-events-none absolute inset-0 h-full w-full" viewBox="0 0 1200 780" preserveAspectRatio="none" aria-label="เส้นทางการผลิต 3 สาย">
                            <defs><marker id="arrow-white" markerWidth="9" markerHeight="9" refX="7" refY="4.5" orient="auto"><path d="M0,0 L9,4.5 L0,9 z" fill="#2678c8" /></marker><marker id="arrow-pvc" markerWidth="9" markerHeight="9" refX="7" refY="4.5" orient="auto"><path d="M0,0 L9,4.5 L0,9 z" fill="#7c4dcc" /></marker><marker id="arrow-paint" markerWidth="9" markerHeight="9" refX="7" refY="4.5" orient="auto"><path d="M0,0 L9,4.5 L0,9 z" fill="#ed6845" /></marker></defs>
                            <path d="M 900 114 L 1100 114 L 1100 675 L 1040 675" fill="none" stroke="#2678c8" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" marker-end="url(#arrow-white)" opacity=".88" />
                            <path d="M 780 464 L 700 464 L 700 125 L 570 125" fill="none" stroke="#7c4dcc" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" marker-end="url(#arrow-pvc)" opacity=".88" />
                            <path d="M 520 450 L 160 450 L 160 650 L 105 650 L 105 370" fill="none" stroke="#ed6845" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" marker-end="url(#arrow-paint)" opacity=".88" />
                        </svg>
                        <div class="absolute bottom-5 left-[585px] top-5 z-10 flex w-[120px] items-center justify-center rounded-xl border-2 border-dashed border-slate-400 bg-slate-100/90" style="background-image:repeating-linear-gradient(135deg,#e2e8f0 0px,#e2e8f0 8px,#f8fafc 8px,#f8fafc 17px)"><span class="rotate-[-90deg] whitespace-nowrap text-sm font-extrabold tracking-[.2em] text-slate-500">ทางเดินกลาง</span></div>
                        <article v-for="item in mapStations" :key="item.code" class="absolute z-20 overflow-hidden rounded-xl border-2 shadow-md" :class="[cardClass(item.track), isEnabled(item.code) ? '' : 'grayscale']" :style="{ left: `${item.x}px`, top: `${item.y}px`, width: `${item.w}px`, height: `${item.h}px` }">
                            <template v-if="item.code === 'ST3'"><div class="flex h-full flex-col items-stretch p-2"><span class="self-end rounded-full border border-emerald-300 bg-emerald-50 px-2 py-1 text-[10px] font-bold text-emerald-800">เป้าหมาย FG</span><span class="mono mt-2 self-center rounded bg-blue-700 px-2 py-1 text-xs font-extrabold text-white">03</span><Link :href="`/finishing/station/${item.code}/jobs`" class="mt-2 text-center text-sm font-bold text-slate-900">{{ item.title }}</Link><span class="mt-2 rounded-full px-2 py-1 text-center text-[10px] font-bold" :class="busyStation(item.code)?.is_machine_on ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'">{{ busyStation(item.code)?.is_machine_on ? '● ทำงาน' : '● หยุด' }}</span><p class="mt-2 text-center text-[10px] leading-snug text-slate-700">{{ item.flow }}</p><strong class="mt-auto rounded bg-amber-100 px-1 py-2 text-center text-[10px] text-amber-900">คงผลผลิตเป็น WIP</strong></div></template>
                            <template v-else-if="item.code === 'ST6'"><div class="flex h-full flex-col items-center p-2 text-center"><span class="self-end rounded-full border border-emerald-300 bg-emerald-50 px-2 py-1 text-[10px] font-bold text-emerald-800">เป้าหมาย FG</span><span class="mono mt-2 rounded bg-orange-600 px-2 py-1 text-xs font-extrabold text-white">06</span><Link :href="`/finishing/station/${item.code}/jobs`" class="mt-2 font-bold text-slate-900">{{ item.title }}</Link><span class="mt-2 rounded-full px-2 py-1 text-[10px] font-bold" :class="busyStation(item.code)?.is_machine_on ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'">{{ busyStation(item.code)?.is_machine_on ? '● ทำงาน' : '● หยุด' }}</span><p class="mt-auto text-[10px] leading-snug text-slate-700">{{ item.flow }}</p><strong class="mt-2 text-[10px] text-amber-900">WIP คงอยู่จนเปิด FG</strong></div></template>
                            <template v-else><div class="flex h-full flex-col justify-between p-3"><div class="flex items-start justify-between gap-2"><span class="mono rounded bg-white/90 px-2 py-1 text-xs font-extrabold text-slate-900 shadow">{{ item.code }}</span><span class="rounded-full px-2 py-1 text-[10px] font-bold" :class="busyStation(item.code)?.is_machine_on ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'">{{ busyStation(item.code)?.is_machine_on ? '● ทำงาน' : '● หยุด' }}</span></div><div class="flex items-end justify-between gap-3"><div><Link :href="`/finishing/station/${item.code}/jobs`" class="font-bold text-slate-900 hover:underline">{{ item.title }}</Link><p class="m-0 mt-1 text-[11px] leading-snug text-slate-700">{{ item.flow }}</p></div><span class="rounded-full border border-amber-400 bg-amber-50 px-2 py-1 text-[10px] font-bold text-amber-900">{{ item.target }}</span></div><div class="flex items-center justify-between border-t border-black/10 pt-2 text-[10px] text-slate-600"><span>{{ busyStation(item.code)?.today_logs || 0 }} งาน · ผลิต {{ Number(busyStation(item.code)?.today_quantity_out || 0).toLocaleString() }}</span><Link :href="`/finishing/station/${item.code}/jobs`" class="font-bold text-emerald-800 underline">เปิด Jobs</Link></div></div></template>
                            <div v-if="!isEnabled(item.code)" class="absolute inset-0 z-10 flex items-center justify-center bg-slate-100/80" :aria-label="`${item.code} ยังไม่เปิดใช้`"><span class="rounded-full bg-slate-700 px-3 py-1 text-xs font-bold text-white">{{ item.code }} · ยังไม่เปิดใช้</span></div>
                        </article>
                    </div>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-[var(--hmi-line)] bg-amber-50 px-5 py-3 text-xs text-amber-900"><span>ปลายทาง FG ในผังเป็นขั้นตอนเป้าหมาย · ตอนนี้ทุกสถานีบันทึกผลผลิตเป็น WIP</span><Link href="/finishing?choose=1" class="font-bold underline">เปิดหน้าสถานีเพื่อสร้าง Job</Link></div>
            </section>

            <section class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white"><div class="bg-[var(--hmi-panel)] px-4 py-3"><h2 class="m-0 font-bold">สรุปตามกะวันนี้</h2></div><div class="overflow-x-auto"><table class="tbl min-w-[520px]"><thead><tr><th>กะ</th><th>รายการ</th><th>ผลิตได้</th><th>ยอดเสีย</th></tr></thead><tbody><tr v-for="row in byShift" :key="row.shift"><td>{{ row.shift === 'day' ? 'กะเช้า' : 'กะดึก' }}</td><td>{{ row.log_count }}</td><td>{{ Number(row.quantity_out || 0).toLocaleString() }}</td><td class="text-red-700">{{ Number(row.defects || 0).toLocaleString() }}</td></tr><tr v-if="!byShift.length"><td colspan="4" class="py-6 text-center text-slate-500">ยังไม่มีข้อมูล</td></tr></tbody></table></div></section>
            <section class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white"><div class="flex items-center justify-between bg-[var(--hmi-panel)] px-4 py-3"><div><h2 class="m-0 font-bold">รายการยืนยันล่าสุด</h2><p class="m-0 text-xs text-[var(--hmi-mute)]">แสดงรายการล่าสุดของวันนี้</p></div><Link href="/finishing/reports" class="text-sm font-semibold underline">เปิดรายงานทั้งหมด</Link></div><div class="overflow-x-auto"><table class="tbl min-w-[780px]"><thead><tr><th>เวลา</th><th>Job</th><th>สถานี</th><th>วัตถุดิบ</th><th>รับเข้า</th><th>ผลิต</th><th>เสีย</th><th>ผู้บันทึก</th></tr></thead><tbody><tr v-for="log in recent" :key="log.id"><td>{{ log.created_at }}</td><td>{{ log.job?.job_no || '-' }}</td><td>{{ log.station?.code }} · {{ log.station?.name }}</td><td>{{ log.material?.code || log.material?.name }}</td><td>{{ log.quantity_in }}</td><td>{{ log.quantity_out }}</td><td>{{ log.defect_count }}</td><td>{{ log.user?.name }}</td></tr><tr v-if="!recent.length"><td colspan="8" class="py-8 text-center text-slate-500">ยังไม่มีรายการยืนยันวันนี้</td></tr></tbody></table></div></section>
            <div v-if="selectedStation" class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-950/50 p-4" role="presentation" @click.self="selectedStation = null">
                <section role="dialog" aria-modal="true" aria-label="เปลี่ยนสถานะสถานี" class="w-full max-w-lg overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white shadow-2xl">
                    <div class="bg-[var(--hmi-panel)] px-5 py-4"><h2 class="m-0 font-bold">{{ selectedStation.is_machine_on ? 'Stop' : 'Start' }} Station · {{ selectedStation.code }}</h2><p class="m-0 mt-1 text-sm text-[var(--hmi-mute)]">ระบุเหตุผลก่อนเปลี่ยนสถานะสถานี</p></div>
                    <form class="space-y-4 p-5" @submit.prevent="changeStationStatus">
                        <p v-if="selectedStation.latest_status_event" class="m-0 text-sm text-[var(--hmi-mute)]">เหตุผลล่าสุด: {{ selectedStation.latest_status_event.reason }}</p>
                        <label class="block text-sm font-semibold">เหตุผล<textarea v-model="statusReason" rows="3" maxlength="500" required class="mt-1 w-full rounded border-slate-300" placeholder="ระบุเหตุผล"></textarea></label>
                        <p v-if="error" role="alert" class="m-0 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">{{ error }}</p>
                        <div class="flex justify-end gap-2"><button type="button" class="rounded border border-[var(--hmi-line)] bg-white px-4 py-2 font-semibold" @click="selectedStation = null">ยกเลิก</button><button :disabled="busyId !== null || !statusReason.trim()" class="rounded px-4 py-2 font-bold text-white disabled:opacity-50" :class="selectedStation.is_machine_on ? 'bg-red-700' : 'bg-emerald-700'">{{ busyId !== null ? 'กำลังบันทึก...' : selectedStation.is_machine_on ? 'ยืนยัน Stop' : 'ยืนยัน Start' }}</button></div>
                    </form>
                </section>
            </div>
        </main>
    </AppLayout>
</template>
