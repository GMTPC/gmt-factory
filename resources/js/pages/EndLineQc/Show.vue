<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { lineStyle } from '@/lib/cut-board-line'

type RecordLine = { id: number; product_state: 'WIP' | 'FG'; line_no: string; qty: number; weight: string | null; time_at: string | null; unit: string; status: string; remark: string | null; brand: string | null; sku_code: string | null; wip_code: string | null; wip_status_name: string | null; full_tag: string | null; spec?: { code: string; name: string } | null }
type Header = { id: number; job_no: string; qc_date: string; shift: string; qc_inspector: string; status: string; lot: string; sheet_type?: { name: string; code: string }; edge?: { name: string; code: string }; size?: { label: string; code: string }; thickness?: { label: string; code: string }; face?: { name: string } }
type Master = { id: number; code?: string; name?: string; label?: string; is_default?: boolean }
const props = defineProps<{ line: string; header: Header; lines: RecordLine[]; masters: Record<string, Master[]>; urls: { prefix: string; save: string; wipReport: string; fgReport: string; wipExport: string } }>()
const page = usePage<any>()
const wipForm = useForm({ product_state: 'WIP', weight: '', time: '', qty: '', unit: '2/1', spec_id: '', wip_status_code: '', wip_status_name: '', running_wip: '' })
const fgForm = useForm({ product_state: 'FG', weight: '', time: '', qty: '', unit: '2/1', spec_id: '', brand_id: '', running_fg: '' })
const specLabel = computed(() => [props.header.sheet_type?.name, props.header.edge?.name, props.header.size?.label, props.header.thickness?.label, props.header.face?.name].filter(Boolean).join(' · '))
const wipLines = computed(() => props.lines.filter((item) => item.product_state === 'WIP'))
const fgLines = computed(() => props.lines.filter((item) => item.product_state === 'FG'))
const closed = computed(() => ['success', 'cancel'].includes(props.header.status))
const fgEntryEnabled = false
function add(form: any) { form.post(props.urls.save, { preserveScroll: true, onSuccess: () => form.reset('weight', 'time', 'qty', 'spec_id', 'wip_status_code', 'wip_status_name', 'running_wip', 'brand_id', 'running_fg') }) }
function remove(item: RecordLine) { if (window.confirm(`ลบรายการ ${item.product_state} #${item.line_no}?`)) router.delete(`/end-line-qc/measure/${item.id}`, { preserveScroll: true }) }
function updateStatus(item: RecordLine, status: string) { router.post(`/end-line-qc/measure/${item.id}`, { status }, { preserveScroll: true }) }
function edit(item: RecordLine) {
    const qty = window.prompt('จำนวน', String(item.qty))
    if (qty === null) return
    const time = window.prompt('เวลา (HH:MM)', item.time_at ?? '')
    if (time === null) return
    router.post(`/end-line-qc/measure/${item.id}`, { qty, time, weight: item.weight, unit: item.unit, remark: item.remark }, { preserveScroll: true })
}
function finish() { if (window.confirm('ยืนยันจบงาน QC นี้? หลังจบจะไม่สามารถแก้ไขรายการได้')) router.post(`${props.urls.prefix}/${props.header.id}/finish`, {}, { preserveScroll: true }) }
function dateText(date: string) {
    if (!date) return '-'
    const parsed = new Date(date.length === 10 ? `${date}T00:00:00` : date)
    return Number.isNaN(parsed.getTime()) ? date : parsed.toLocaleDateString('th-TH')
}
</script>

<template>
    <Head :title="`${header.job_no} · QC ${line}`" />
    <AppLayout :title="`ติดแท็กออนไลน์ ${line}`" full-width hide-header>
        <main class="hmi flex flex-col gap-4 pb-5" :style="lineStyle(line)">
            <div v-if="page.props.flash?.success" role="status" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">{{ page.props.flash.success }}</div>
            <header class="overflow-hidden rounded-t-md bg-[var(--hmi-surface)] shadow-sm ring-1 ring-inset ring-[var(--hmi-line)]">
                <div class="hazard" aria-hidden="true"></div>
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[var(--hmi-line)] px-5 py-4">
                    <div class="flex min-w-0 flex-wrap items-center gap-4">
                        <div class="flex flex-col items-center rounded bg-[var(--line-color)] px-3 py-1.5 leading-none text-white ring-1 ring-inset ring-white/25"><span class="tag text-[11px] font-semibold">Line</span><span class="mono text-[28px] font-extrabold">{{ line.replace(/\\D/g, '').padStart(2, '0') }}</span></div>
                        <div><p class="m-0 text-xs text-[var(--hmi-mute)]">ติดแท็กออนไลน์ / {{ header.job_no }}</p><h1 class="m-0 text-[22px] font-bold text-[var(--hmi-title)]">{{ specLabel || 'รายละเอียดงานติดแท็ก' }}</h1><p class="m-0 mt-1 text-sm text-[var(--hmi-mute)]">วันที่ {{ dateText(header.qc_date) }} · กะ {{ header.shift }} · ผู้คัด {{ header.qc_inspector }} · LOT {{ header.lot }}</p></div>
                    </div>
                    <div class="flex flex-wrap gap-2"><Link :href="urls.prefix" class="inline-flex min-h-11 items-center rounded border border-[var(--hmi-line)] bg-white px-4 text-sm font-semibold text-[var(--hmi-text)] hover:bg-[var(--hmi-panel)]">กลับหน้ารายการ</Link><a :href="urls.wipReport" class="inline-flex min-h-11 items-center rounded border border-blue-300 bg-white px-4 text-sm font-semibold text-blue-700">ส่งออก WIP CSV</a><a :href="urls.fgReport" class="inline-flex min-h-11 items-center rounded border border-blue-300 bg-white px-4 text-sm font-semibold text-blue-700">ส่งออก FG CSV</a><button v-if="!closed" class="inline-flex min-h-11 items-center rounded border-2 border-[var(--hmi-yellow)] bg-[#C81E1E] px-4 text-sm font-bold text-white" @click="finish">จบการทำงาน</button><span class="inline-flex min-h-11 items-center rounded bg-[var(--hmi-panel)] px-4 text-sm font-bold">{{ header.status }}</span></div>
                </div>
                <nav aria-label="เลือกไลน์ติดแท็กออนไลน์" class="flex flex-wrap items-center gap-2 px-5 py-3"><span class="tag mr-1 text-[13px] text-[var(--hmi-mute)]">Line</span><Link v-for="item in ['L1','L2','L3','L4']" :key="item" :href="`/end-line-qc/${item}`" :style="lineStyle(item)" :aria-current="item === line ? 'page' : undefined" class="inline-flex min-h-9 items-center gap-2 rounded border px-3 text-sm font-bold" :class="item === line ? 'border-[var(--hmi-yellow)] bg-[var(--line-color)] text-white' : 'border-[var(--hmi-line)] text-[var(--hmi-text)] hover:bg-[var(--hmi-panel)]'"><span class="size-2.5 rounded-full bg-[var(--line-color)] ring-1 ring-white/50"></span>ไลน์ {{ item.slice(1) }}</Link></nav>
            </header>

            <section v-if="!closed" class="grid gap-5">
                <form class="space-y-4 rounded-md border border-[var(--hmi-line)] bg-[var(--hmi-screen)] p-4" @submit.prevent="add(wipForm)">
                    <div class="flex items-center justify-between"><div><h3 class="m-0 text-[17px] font-bold text-[var(--hmi-title)]">รับเข้า WIP</h3><p class="m-0 text-xs text-[var(--hmi-mute)]">บันทึก WIP, สถานะ, จำนวนและแท็ก</p></div><span class="mono rounded-sm bg-[var(--hmi-panel)] px-2 py-1 text-xs font-extrabold text-[var(--hmi-ink)]">WIP IN</span></div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="text-sm">สถานะ WIP<select v-model="wipForm.wip_status_code" class="mt-1 w-full rounded-lg border-slate-300"><option value="">เลือกสถานะ</option><option value="91">91 · รอเจีย</option><option value="98">98 · ชิ้น</option><option value="99">99 · รอคัด</option></select></label>
                        <label class="text-sm">จำนวน<input v-model="wipForm.qty" type="number" min="0" class="mt-1 w-full rounded-lg border-slate-300" /></label>
                        <label class="text-sm">น้ำหนัก<input v-model="wipForm.weight" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border-slate-300" /></label>
                        <label class="text-sm">เวลา<input v-model="wipForm.time" type="time" class="mt-1 w-full rounded-lg border-slate-300" /></label>
                        <label class="text-sm">ชุดที่ / running no.<input v-model="wipForm.running_wip" type="number" min="1" placeholder="ถัดไปอัตโนมัติ" class="mt-1 w-full rounded-lg border-slate-300" /></label>
                        <label class="text-sm">หน่วย<select v-model="wipForm.unit" class="mt-1 w-full rounded-lg border-slate-300"><option>2/1</option><option>2/2</option></select></label>
                        <label class="text-sm sm:col-span-2">สเปกสินค้า<select v-model="wipForm.spec_id" class="mt-1 w-full rounded-lg border-slate-300"><option value="">ไม่ระบุ</option><option v-for="item in masters.specs" :key="item.id" :value="String(item.id)">{{ item.code }} · {{ item.name }}</option></select></label>
                    </div>
                    <p v-if="Object.keys(wipForm.errors).length" class="text-sm text-red-600">{{ Object.values(wipForm.errors).join(' · ') }}</p>
                    <button :disabled="wipForm.processing" class="min-h-11 rounded bg-[var(--hmi-ink)] px-5 font-bold text-white disabled:opacity-50">บันทึก WIP</button>
                </form>

                <form v-if="fgEntryEnabled" class="space-y-4 rounded-md border border-[var(--hmi-line)] bg-[var(--hmi-screen)] p-4" @submit.prevent="add(fgForm)">
                    <div class="flex items-center justify-between"><div><h3 class="text-lg font-bold text-blue-800">ผลผลิต FG</h3><p class="text-xs text-slate-500">บันทึก FG พร้อมยี่ห้อและข้อมูลแท็ก</p></div><span class="rounded bg-blue-100 px-3 py-1 text-xs font-bold text-blue-800">FG OUT</span></div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="text-sm">ยี่ห้อ<select v-model="fgForm.brand_id" class="mt-1 w-full rounded-lg border-slate-300"><option value="">เลือกยี่ห้อ</option><option v-for="item in masters.brands" :key="item.id" :value="String(item.id)">{{ item.code }} · {{ item.name }}</option></select></label>
                        <label class="text-sm">จำนวน<input v-model="fgForm.qty" type="number" min="0" class="mt-1 w-full rounded-lg border-slate-300" /></label>
                        <label class="text-sm">น้ำหนัก<input v-model="fgForm.weight" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border-slate-300" /></label>
                        <label class="text-sm">เวลา<input v-model="fgForm.time" type="time" class="mt-1 w-full rounded-lg border-slate-300" /></label>
                        <label class="text-sm">ชุดที่ / running no.<input v-model="fgForm.running_fg" type="number" min="1" placeholder="ถัดไปอัตโนมัติ" class="mt-1 w-full rounded-lg border-slate-300" /></label>
                        <label class="text-sm">หน่วย<select v-model="fgForm.unit" class="mt-1 w-full rounded-lg border-slate-300"><option>2/1</option><option>2/2</option></select></label>
                        <label class="text-sm sm:col-span-2">สเปกสินค้า<select v-model="fgForm.spec_id" class="mt-1 w-full rounded-lg border-slate-300"><option value="">ใช้สเปกงาน</option><option v-for="item in masters.specs" :key="item.id" :value="String(item.id)">{{ item.code }} · {{ item.name }}</option></select></label>
                    </div>
                    <p v-if="Object.keys(fgForm.errors).length" class="text-sm text-red-600">{{ Object.values(fgForm.errors).join(' · ') }}</p>
                    <button :disabled="fgForm.processing" class="min-h-11 rounded bg-[#1565C0] px-5 font-bold text-white disabled:opacity-50">บันทึก FG</button>
                </form>
            </section>
            <section v-else class="border-l-4 border-[var(--hmi-yellow)] bg-[#FFF8E1] p-4 text-sm text-[#6B4E00]">งานนี้ปิดแล้ว รายการถูกล็อกเพื่อรักษาประวัติ</section>

            <section v-for="[state, title, records] in [['WIP','รายการรับเข้า WIP',wipLines],['FG','รายการผลผลิต FG',fgLines]] as any" :key="state" class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-[var(--hmi-screen)]">
                <div class="flex items-center justify-between bg-[var(--hmi-panel)] px-4 py-3"><div><h3 class="m-0 font-bold text-[var(--hmi-title)]">{{ title }}</h3><p class="m-0 text-xs text-[var(--hmi-mute)]">{{ records.length }} รายการ · {{ records.reduce((sum: number, row: RecordLine) => sum + Number(row.qty || 0), 0) }} แผ่น</p></div><span class="mono rounded-sm bg-[var(--hmi-yellow)] px-2 py-1 text-xs font-extrabold text-[var(--hmi-ink)]">{{ state }}</span></div>
                <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>ชุดที่</th><th>สเปก / สถานะ</th><th>SKU / แท็ก</th><th>เวลา</th><th>จำนวน</th><th>ผล QC</th><th class="text-right">การทำงาน</th></tr></thead><tbody><tr v-for="item in records" :key="item.id"><td class="font-bold">{{ item.line_no }}</td><td>{{ item.spec?.name || item.wip_status_name || item.brand || '-' }}</td><td class="max-w-64 break-all text-xs">{{ item.sku_code || item.wip_code || item.full_tag || '-' }}</td><td>{{ item.time_at || '-' }}</td><td class="font-bold">{{ item.qty }} <span class="text-xs font-normal text-slate-500">({{ item.unit }})</span></td><td><select :value="item.status" :disabled="closed" class="field py-1 text-xs" @change="updateStatus(item, ($event.target as HTMLSelectElement).value)"><option value="draft">รอตรวจ</option><option value="success">ผ่าน</option><option value="reject">NG</option></select></td><td class="space-x-2 text-right"><a :href="`/end-line-qc/tag/${state.toLowerCase()}/${item.id}`" target="_blank" class="inline-flex min-h-9 items-center rounded border border-[var(--hmi-line)] bg-white px-3 font-semibold">พิมพ์แท็ก</a><button v-if="!closed" class="inline-flex min-h-9 items-center rounded border border-[var(--hmi-line)] bg-white px-3 font-semibold" @click="edit(item)">แก้ไข</button><button v-if="!closed" class="inline-flex min-h-9 items-center rounded border border-red-400 bg-white px-3 text-red-700" @click="remove(item)">ลบ</button></td></tr><tr v-if="!records.length"><td colspan="7" class="py-8 text-center text-[var(--hmi-mute)]">ยังไม่มีรายการ</td></tr></tbody></table></div>
            </section>
        </main>
    </AppLayout>
</template>
