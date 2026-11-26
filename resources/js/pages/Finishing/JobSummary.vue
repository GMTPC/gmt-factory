<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { errorText, finishingRequest } from '@/lib/finishing-api'
import { printPdf } from '@/lib/print-pdf'
import { toast } from '@/lib/toast'

type RawMaterialUse = { name: string; quantity: number | null; unit: string | null }
type Station = { id: number; code: string; name: string }
type Job = {
    id: number; status: string; shift_date: string; shift: string; product_type_code?: string | null; product_type_name?: string | null
    employee_names?: (string | { name: string })[] | null; machine_name?: string | null; pvc_type?: string | null; inspector_name?: string | null
    recorder?: { name: string } | null; user?: { name: string } | null; raw_materials?: RawMaterialUse[] | null; raw_material_name?: string | null
}
type SummaryItem = { sequence: number; in_code: string | null; in_quantity: number; out_code: string | null; out_lot: string | null; out_quantity: number; weight_kg: number | null; time: string | null; date_time: string | null; defects: number; recorded_by: string | null; tag_url: string | null }
type Summary = {
    items: SummaryItem[]; pending_items: { in_code: string; in_quantity: number }[]; input_tags: number; input_quantity: number; output_tags: number
    quantity_in: number; quantity_out: number; defects: number; weight_kg: number; pending_quantity: number; draft_count: number
    started_at: string | null; first_scan_at: string | null; last_scan_at: string | null; ended_at: string | null; is_completed: boolean; minutes: number; scan_minutes: number
}
type DowntimePeriod = { id: number; stopped_at_text: string; resumed_at_text: string | null; ongoing: boolean; minutes: number; reason: string; stopped_by: string | null; resume_reason: string | null; resumed_by: string | null }
const props = defineProps<{ station: Station; job: Job; summary: Summary; downtime: { periods: DowntimePeriod[]; minutes: number }; csrf: string }>()

const finishing = ref(false)
const recordUrl = computed(() => `/finishing/station/${props.station.code}/jobs/${props.job.id}`)
const product = computed(() => props.job.product_type_code ? `${props.job.product_type_code}${props.job.product_type_name ? ` · ${props.job.product_type_name}` : ''}` : 'สินค้า: กำหนดจาก Tag แรก')
const employees = computed(() => (props.job.employee_names ?? []).map((item) => typeof item === 'string' ? item : item.name))
const rawMaterials = computed<RawMaterialUse[]>(() => props.job.raw_materials ?? (props.job.raw_material_name ? [{ name: props.job.raw_material_name, quantity: null, unit: null }] : []))
const yieldPercent = computed(() => props.summary.input_quantity > 0 ? Math.round((props.summary.quantity_out / props.summary.input_quantity) * 1000) / 10 : null)

function qty(value: number | string | null | undefined) { return value == null ? '-' : Number(value).toLocaleString('th-TH', { maximumFractionDigits: 3 }) }
function kg(value: number | null | undefined) { return value == null ? '-' : Number(value).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }
function duration(minutes: number) {
    const total = Math.round(minutes)
    const days = Math.floor(total / 1440)
    const hours = Math.floor((total % 1440) / 60)
    return [days ? `${days} วัน` : '', hours ? `${hours} ชม.` : '', `${total % 60} นาที`].filter(Boolean).join(' ')
}
function shiftLabel(value: string) { return value === 'day' ? 'กะเช้า' : 'กะดึก' }

async function finishJob() {
    if (finishing.value || !window.confirm('ยืนยันจบงาน? หลังจบงานจะยิง Tag เพิ่มใน Job นี้ไม่ได้')) return
    finishing.value = true
    try {
        await finishingRequest(`/api/finishing/jobs/${props.job.id}/complete`, 'PATCH', {}, props.csrf)
        toast('จบงานแล้ว')
        router.reload()
    } catch (e) { toast(errorText(e), 'error') } finally { finishing.value = false }
}
</script>

<template>
    <Head :title="`สรุปการแปรรูป · ${station.code}`" />
    <AppLayout :title="`สรุปการแปรรูป · ${station.code}`" full-width hide-header>
        <main class="hmi flex min-w-0 flex-col gap-4 pb-6">
            <header class="flex flex-wrap items-center gap-3 rounded-md border border-[var(--hmi-line)] bg-white px-4 py-3 shadow-sm">
                <Link :href="recordUrl" class="mono shrink-0 rounded bg-emerald-700 px-3 py-2 text-lg font-extrabold text-white">{{ station.code }}</Link>
                <div class="min-w-0 flex-1">
                    <p class="m-0 text-xs text-[var(--hmi-mute)]">สรุปการแปรรูป · {{ station.name }} · {{ job.shift_date.slice(0, 10) }} {{ shiftLabel(job.shift) }}</p>
                    <h1 class="m-0 break-words text-xl font-bold text-[var(--hmi-title)]">{{ product }}</h1>
                </div>
                <span class="shrink-0 rounded-full px-3 py-1 text-xs font-bold" :class="job.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'">{{ job.status === 'active' ? 'กำลังทำงาน' : 'จบงานแล้ว' }}</span>
                <div class="flex shrink-0 gap-2">
                    <Link :href="recordUrl" class="rounded border border-[var(--hmi-line)] bg-white px-4 py-2 text-sm font-semibold">‹ กลับหน้าแปรรูป</Link>
                    <Link :href="`/finishing/station/${station.code}/jobs`" class="rounded border border-[var(--hmi-line)] bg-white px-4 py-2 text-sm font-semibold">รายการ Job</Link>
                    <button v-if="job.status === 'active'" type="button" :disabled="finishing || summary.draft_count > 0" class="flex items-center gap-2 rounded bg-red-600 px-5 py-2 text-sm font-extrabold text-white disabled:opacity-50" @click="finishJob"><i class="ph-fill ph-flag-checkered text-lg" aria-hidden="true"></i>{{ finishing ? 'กำลังจบงาน...' : 'ยืนยันจบงาน' }}</button>
                </div>
            </header>

            <div v-if="summary.draft_count > 0" role="alert" class="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">มีรายการร่าง {{ summary.draft_count }} รายการ · ต้องยืนยันหรือยกเลิกก่อนจบงาน</div>

            <section class="grid grid-cols-2 gap-3 lg:grid-cols-6">
                <article class="rounded-md border border-[var(--hmi-line)] bg-white p-4 shadow-sm"><p class="m-0 text-xs font-bold text-slate-500">WIP เข้า</p><p class="mono m-0 mt-1 text-3xl font-extrabold">{{ qty(summary.input_quantity) }}</p><p class="m-0 text-xs text-slate-500">แผ่น · {{ summary.input_tags }} Tag</p></article>
                <article class="rounded-md border border-emerald-300 bg-emerald-50 p-4 shadow-sm"><p class="m-0 text-xs font-bold text-emerald-800">WIP ออก</p><p class="mono m-0 mt-1 text-3xl font-extrabold text-emerald-900">{{ qty(summary.quantity_out) }}</p><p class="m-0 text-xs text-emerald-800">แผ่น · {{ summary.output_tags }} Tag<template v-if="yieldPercent != null"> · {{ yieldPercent }}%</template></p></article>
                <article class="rounded-md border border-[var(--hmi-line)] bg-white p-4 shadow-sm"><p class="m-0 text-xs font-bold text-slate-500">น้ำหนักรวม</p><p class="mono m-0 mt-1 text-3xl font-extrabold">{{ kg(summary.weight_kg) }}</p><p class="m-0 text-xs text-slate-500">กก.</p></article>
                <article class="rounded-md border p-4 shadow-sm" :class="summary.defects > 0 ? 'border-amber-300 bg-amber-50' : 'border-[var(--hmi-line)] bg-white'"><p class="m-0 text-xs font-bold text-slate-500">ของเสีย</p><p class="mono m-0 mt-1 text-3xl font-extrabold">{{ qty(summary.defects) }}</p><p class="m-0 text-xs text-slate-500">แผ่น</p></article>
                <article class="rounded-md border p-4 shadow-sm" :class="summary.pending_quantity > 0 ? 'border-amber-300 bg-amber-50' : 'border-[var(--hmi-line)] bg-white'"><p class="m-0 text-xs font-bold text-slate-500">รับเข้า ยังไม่แปรรูป</p><p class="mono m-0 mt-1 text-3xl font-extrabold">{{ qty(summary.pending_quantity) }}</p><p class="m-0 text-xs text-slate-500">แผ่น</p></article>
                <article class="rounded-md border border-[var(--hmi-line)] bg-white p-4 shadow-sm"><p class="m-0 text-xs font-bold text-slate-500">ใช้เวลา</p><p class="mono m-0 mt-1 text-2xl font-extrabold">{{ duration(summary.minutes) }}</p><p class="m-0 text-xs text-slate-500">หยุดเครื่อง {{ duration(downtime.minutes) }}</p></article>
            </section>

            <div class="grid min-w-0 gap-4 xl:grid-cols-[minmax(0,1fr)_360px]">
                <div class="flex min-w-0 flex-col gap-4">
                    <section class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white shadow-sm">
                        <div class="bg-[var(--hmi-panel)] px-4 py-3"><h2 class="m-0 font-bold">รายการ WIP เข้า → WIP ออก</h2><p class="m-0 text-xs text-[var(--hmi-mute)]">{{ summary.items.length }} รายการ · ทุกรายการใน Job</p></div>
                        <div class="overflow-x-auto">
                            <table class="tbl min-w-[1100px]">
                                <thead><tr><th class="w-px">ลำดับ</th><th>Tag WIP เข้า</th><th class="text-right">จำนวนเข้า</th><th class="w-px"></th><th>Tag WIP ออก</th><th class="text-right">จำนวนออก</th><th class="text-right">เสีย</th><th class="text-right">น้ำหนัก (กก.)</th><th>เวลา</th><th>บันทึกโดย</th><th class="w-px">Tag</th></tr></thead>
                                <tbody>
                                    <tr v-for="item in summary.items" :key="item.sequence">
                                        <td class="mono font-bold">{{ item.sequence }}<span v-if="item.out_lot" class="block text-[11px] font-normal text-slate-500">{{ item.out_lot.slice(6) }}</span></td>
                                        <td class="mono text-xs">{{ item.in_code || '-' }}</td>
                                        <td class="mono text-right">{{ qty(item.in_quantity) }}</td>
                                        <td class="text-center text-emerald-700" aria-hidden="true">→</td>
                                        <td class="mono text-xs font-semibold text-emerald-900">{{ item.out_code || '-' }}</td>
                                        <td class="mono text-right font-bold text-emerald-900">{{ qty(item.out_quantity) }}</td>
                                        <td class="mono text-right" :class="item.defects > 0 ? 'text-amber-700' : 'text-slate-400'">{{ qty(item.defects) }}</td>
                                        <td class="mono text-right">{{ kg(item.weight_kg) }}</td>
                                        <td class="whitespace-nowrap text-sm">{{ item.date_time || '-' }}</td>
                                        <td class="text-sm">{{ item.recorded_by || '-' }}</td>
                                        <td><button v-if="item.tag_url" type="button" class="inline-flex items-center gap-1.5 rounded bg-emerald-700 px-3 py-1.5 text-xs font-bold text-white" @click="printPdf(item.tag_url)"><i class="ph ph-printer" aria-hidden="true"></i>พิมพ์</button></td>
                                    </tr>
                                    <tr v-if="!summary.items.length"><td colspan="11" class="py-10 text-center text-slate-500">ยังไม่มีรายการ</td></tr>
                                </tbody>
                                <tfoot v-if="summary.items.length"><tr class="border-t-2 border-slate-800 font-bold"><td colspan="2">รวม {{ summary.items.length }} Tag</td><td class="mono text-right">{{ qty(summary.quantity_in) }}</td><td></td><td></td><td class="mono text-right text-emerald-900">{{ qty(summary.quantity_out) }}</td><td class="mono text-right">{{ qty(summary.defects) }}</td><td class="mono text-right">{{ kg(summary.weight_kg) }}</td><td colspan="3"></td></tr></tfoot>
                            </table>
                        </div>
                    </section>

                    <section v-if="summary.pending_items.length" class="overflow-hidden rounded-md border border-amber-300 bg-white shadow-sm">
                        <div class="bg-amber-50 px-4 py-3"><h2 class="m-0 font-bold text-amber-900">รับเข้าแล้ว ยังไม่ได้แปรรูป ({{ summary.pending_items.length }})</h2></div>
                        <table class="tbl"><thead><tr><th>Tag WIP เข้า</th><th class="text-right">จำนวน</th></tr></thead><tbody><tr v-for="item in summary.pending_items" :key="item.in_code"><td class="mono text-xs">{{ item.in_code }}</td><td class="mono text-right">{{ qty(item.in_quantity) }}</td></tr></tbody></table>
                    </section>

                    <section class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white shadow-sm">
                        <div class="flex items-center justify-between bg-[var(--hmi-panel)] px-4 py-3"><h2 class="m-0 font-bold">หยุดเครื่องระหว่าง Job</h2><span class="mono text-sm font-bold" :class="downtime.minutes ? 'text-red-700' : 'text-slate-500'">{{ duration(downtime.minutes) }}</span></div>
                        <div class="overflow-x-auto">
                            <table class="tbl min-w-[760px]">
                                <thead><tr><th>หยุดเมื่อ</th><th>เดินต่อเมื่อ</th><th class="text-right">เวลาหยุด</th><th>เหตุผล</th><th>ผู้กดหยุด</th><th>เริ่มใหม่ / ผู้กด</th></tr></thead>
                                <tbody>
                                    <tr v-for="period in downtime.periods" :key="period.id"><td class="whitespace-nowrap">{{ period.stopped_at_text }}</td><td class="whitespace-nowrap"><span v-if="period.ongoing" class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-bold text-red-700">ยังหยุดอยู่</span><template v-else>{{ period.resumed_at_text }}</template></td><td class="mono text-right font-bold text-red-700">{{ duration(period.minutes) }}</td><td>{{ period.reason }}</td><td>{{ period.stopped_by || '-' }}</td><td>{{ period.resume_reason ? `${period.resume_reason} · ${period.resumed_by || '-'}` : '-' }}</td></tr>
                                    <tr v-if="!downtime.periods.length"><td colspan="6" class="py-6 text-center text-slate-500">ไม่มีการหยุดเครื่องระหว่าง Job นี้</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>

                <aside class="flex min-w-0 flex-col gap-4">
                    <section class="rounded-md border border-[var(--hmi-line)] bg-white p-4 shadow-sm">
                        <h2 class="m-0 mb-3 font-bold">ผู้เกี่ยวข้อง</h2>
                        <dl class="m-0 space-y-3 text-sm">
                            <div><dt class="text-xs font-bold text-slate-500">ผู้แปรรูป ({{ employees.length }})</dt><dd class="m-0"><ul class="m-0 p-0"><li v-for="name in employees" :key="name" class="m-0 list-none font-semibold">{{ name }}</li><li v-if="!employees.length" class="m-0 list-none">-</li></ul></dd></div>
                            <div><dt class="text-xs font-bold text-slate-500">ผู้ควบคุม / ผู้ตรวจสอบ</dt><dd class="m-0 font-semibold">{{ job.inspector_name || '-' }}</dd></div>
                            <div><dt class="text-xs font-bold text-slate-500">ผู้บันทึก</dt><dd class="m-0 font-semibold">{{ job.recorder?.name || '-' }}</dd></div>
                            <div><dt class="text-xs font-bold text-slate-500">ผู้สร้าง Job</dt><dd class="m-0 font-semibold">{{ job.user?.name || '-' }}</dd></div>
                            <div class="grid grid-cols-2 gap-3"><div><dt class="text-xs font-bold text-slate-500">เครื่อง</dt><dd class="m-0 font-semibold">{{ job.machine_name || '-' }}</dd></div><div><dt class="text-xs font-bold text-slate-500">ชนิดแผ่น</dt><dd class="m-0 font-semibold">{{ job.pvc_type || '-' }}</dd></div></div>
                        </dl>
                    </section>

                    <section class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white shadow-sm">
                        <h2 class="m-0 border-b border-[var(--hmi-line)] px-4 py-3 font-bold">วัตถุดิบที่ใช้</h2>
                        <ul v-if="rawMaterials.length" class="m-0 divide-y divide-slate-100 p-0 text-sm">
                            <li v-for="item in rawMaterials" :key="item.name" class="m-0 flex list-none items-baseline justify-between gap-3 px-4 py-2"><span class="font-semibold">{{ item.name }}</span><span v-if="item.quantity != null" class="mono shrink-0 font-bold">{{ qty(item.quantity) }} {{ item.unit }}</span><span v-else class="shrink-0 text-xs font-bold text-red-600">ยังไม่กรอกจำนวน</span></li>
                        </ul>
                        <p v-else class="m-0 px-4 py-3 text-sm font-semibold text-red-600">ยังไม่ระบุ</p>
                    </section>

                    <section class="rounded-md border border-[var(--hmi-line)] bg-white p-4 shadow-sm">
                        <div class="flex items-baseline justify-between"><h2 class="m-0 font-bold">ไทม์ไลน์</h2><span class="mono text-lg font-extrabold">{{ duration(summary.minutes) }}</span></div>
                        <ol class="m-0 mt-3 space-y-2 border-l-2 border-slate-200 p-0 pl-4 text-sm">
                            <li class="m-0 list-none"><p class="m-0 text-xs text-slate-500">เริ่ม Job</p><p class="m-0 font-semibold">{{ summary.started_at || '-' }}</p></li>
                            <li class="m-0 list-none"><p class="m-0 text-xs text-slate-500">ยิง Tag แรก</p><p class="m-0 font-semibold">{{ summary.first_scan_at || '-' }}</p></li>
                            <li class="m-0 list-none"><p class="m-0 text-xs text-slate-500">ยิง Tag สุดท้าย</p><p class="m-0 font-semibold">{{ summary.last_scan_at || '-' }}</p></li>
                            <li class="m-0 list-none"><p class="m-0 text-xs text-slate-500">{{ summary.is_completed ? 'จบงาน' : 'ถึงตอนนี้' }}</p><p class="m-0 font-semibold">{{ summary.ended_at || '-' }}</p></li>
                        </ol>
                        <dl class="m-0 mt-3 grid grid-cols-2 gap-2 border-t border-slate-100 pt-3 text-sm">
                            <dt class="text-slate-500">ช่วงยิง Tag</dt><dd class="m-0 text-right font-semibold">{{ summary.first_scan_at ? duration(summary.scan_minutes) : '-' }}</dd>
                            <dt class="text-slate-500">หยุดเครื่อง</dt><dd class="m-0 text-right font-semibold" :class="downtime.minutes ? 'text-red-700' : ''">{{ duration(downtime.minutes) }}</dd>
                        </dl>
                    </section>
                </aside>
            </div>
        </main>
    </AppLayout>
</template>
