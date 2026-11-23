<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, reactive, ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { lineStyle } from '@/lib/cut-board-line'

type Master = { id: number; code?: string; name?: string; label?: string; mm?: number; is_default?: boolean }
type Job = { id: number; line: string; qc_date: string; shift: string; qc_inspector: string; status: string; lot: string; job_no: string; running_no: number; product_name: string; lines_count: number; wip_qty: number; fg_qty: number; ng_count: number }
const props = defineProps<{ line: string; today: string; showCreate?: boolean; inspector?: string; processing: Job[]; completed: Job[]; masters: Record<string, Master[]>; inspectors: string[]; filters: Record<string, string>; urls: { store: string; prefix: string } }>()
const page = usePage<any>()
const creating = ref(props.showCreate ?? false)
const filter = reactive({ date_from: props.filters.date_from ?? '', date_to: props.filters.date_to ?? '', shift: props.filters.shift ?? '', qc_inspector: props.filters.qc_inspector ?? '', sheet_type_id: props.filters.sheet_type_id ?? '', edge_id: props.filters.edge_id ?? '', size_id: props.filters.size_id ?? '', thickness_id: props.filters.thickness_id ?? '' })
const form = useForm({ qc_date: props.today, shift: '', qc_inspector: props.inspector ?? '', sheet_type_id: '', edge_id: '', size_id: '', thickness_id: '', face_id: '' })
const hasFilters = computed(() => Object.values(filter).some(Boolean))
function search() { router.get(props.urls.prefix, Object.fromEntries(Object.entries(filter).filter(([, value]) => value)), { preserveState: true, preserveScroll: true }) }
function clearSearch() { Object.keys(filter).forEach((key) => (filter as any)[key] = ''); search() }
function createJob() { form.post(props.urls.store, { preserveScroll: true, onSuccess: () => { creating.value = false; form.reset('shift', 'qc_inspector', 'sheet_type_id', 'edge_id', 'size_id', 'thickness_id', 'face_id') } }) }
function remove(job: Job) { if (window.confirm(`ลบงาน ${job.job_no} และข้อมูล WIP/FG ทั้งหมด?`)) router.delete(`${props.urls.prefix}/${job.id}`, { preserveScroll: true }) }
function statusText(status: string) { return ({ draft: 'รอเริ่มงาน', in_process: 'กำลังคัด', success: 'สำเร็จ', cancel: 'ยกเลิก' } as Record<string, string>)[status] ?? status }
function dateText(date: string) {
    if (!date) return '-'
    const parsed = new Date(date.length === 10 ? `${date}T00:00:00` : date)
    return Number.isNaN(parsed.getTime()) ? date : parsed.toLocaleDateString('th-TH')
}
</script>

<template>
    <Head :title="`ติดแท็กออนไลน์ ${line}`" />
    <AppLayout :title="`ติดแท็กออนไลน์ ${line}`" full-width hide-header>
        <main class="hmi flex flex-col gap-4 pb-5" :style="lineStyle(line)">
            <div v-if="page.props.flash?.success" role="status" class="flex items-center gap-2.5 border-l-4 border-[#12A060] bg-[#E7F5EE] px-4 py-3 text-sm font-medium text-[#0B5E3C]">{{ page.props.flash.success }}</div>
            <header class="sticky top-14 z-40 overflow-hidden rounded-t-md bg-[var(--hmi-surface)] shadow-[0_6px_16px_-8px_rgba(0,0,0,.35)] ring-1 ring-inset ring-[var(--hmi-line)] lg:top-[78px]">
                <div class="hazard" aria-hidden="true"></div>
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[var(--hmi-line)] px-5 py-4">
                    <div class="flex min-w-0 flex-wrap items-center gap-4">
                        <div class="flex flex-col items-center rounded bg-[var(--line-color)] px-3 py-1.5 leading-none text-white ring-1 ring-inset ring-white/25"><span class="tag text-[11px] font-semibold">Line</span><span class="mono text-[28px] font-extrabold">{{ line.replace(/\\D/g, '').padStart(2, '0') }}</span></div>
                        <div><span class="text-xs text-[var(--hmi-mute)]">Inventory / End-line QC</span><h1 class="m-0 text-[22px] font-bold text-[var(--hmi-title)]">ติดแท็กออนไลน์ · {{ line }}</h1><p class="m-0 mt-1 text-sm text-[var(--hmi-mute)]">สร้างงาน QC, บันทึก WIP/FG, พิมพ์แท็ก และส่งออกรายงาน</p></div>
                    </div>
                    <div class="flex flex-wrap gap-2"><Link href="/cut-boards" class="inline-flex min-h-11 items-center rounded border border-[var(--hmi-line)] bg-white px-4 text-sm font-semibold text-[var(--hmi-text)] hover:bg-[var(--hmi-panel)]">งานคัดบอร์ด</Link><button class="inline-flex min-h-11 items-center rounded bg-violet-600 px-4 text-sm font-bold text-white hover:bg-violet-700" @click="creating = !creating">{{ creating ? 'ปิดฟอร์ม' : '+ สร้างงาน QC' }}</button></div>
                </div>
                <nav aria-label="เลือกไลน์ติดแท็กออนไลน์" class="flex flex-wrap items-center gap-2 px-5 py-3">
                    <span class="tag mr-1 text-[13px] text-[var(--hmi-mute)]">Line</span>
                    <Link v-for="item in ['L1','L2','L3','L4']" :key="item" :href="`/end-line-qc/${item}`" :style="lineStyle(item)" :aria-current="item === line ? 'page' : undefined" class="inline-flex min-h-9 items-center gap-2 rounded border px-3 text-sm font-bold" :class="item === line ? 'border-[var(--hmi-yellow)] bg-[var(--line-color)] text-white' : 'border-[var(--hmi-line)] text-[var(--hmi-text)] hover:bg-[var(--hmi-panel)]'"><span class="size-2.5 rounded-full bg-[var(--line-color)] ring-1 ring-white/50"></span>ไลน์ {{ item.slice(1) }}</Link>
                </nav>
            </header>

            <section v-if="creating" class="overflow-hidden rounded-md border border-[var(--hmi-steel)] bg-white">
                <div class="flex items-center gap-3 bg-[var(--hmi-panel)] px-4 py-2.5"><span class="mono rounded-sm bg-[var(--hmi-yellow)] px-2 py-0.5 text-[13px] font-extrabold text-[var(--hmi-ink)]">NEW</span><h3 class="m-0 text-[17px] font-bold">สร้างงานติดแท็กออนไลน์</h3></div>
                <div class="p-4">
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <label class="text-sm">วันที่คัด<input v-model="form.qc_date" type="date" class="mt-1 w-full rounded-lg border-slate-300" /></label>
                    <label class="text-sm">กะ<select v-model="form.shift" class="mt-1 w-full rounded-lg border-slate-300"><option value="">เลือกกะ</option><option>A</option><option>B</option><option>T</option></select></label>
                    <label class="text-sm">ผู้คัด<input v-model="form.qc_inspector" readonly class="mt-1 w-full rounded-lg border-slate-300 bg-slate-50" placeholder="ชื่อผู้ใช้ที่เข้าสู่ระบบ" /></label>
                    <label class="text-sm">ชนิดแผ่น<select v-model="form.sheet_type_id" class="mt-1 w-full rounded-lg border-slate-300"><option value="">เลือกชนิดแผ่น</option><option v-for="item in masters.sheetTypes" :key="item.id" :value="String(item.id)">{{ item.code }} · {{ item.name }}</option></select></label>
                    <label class="text-sm">ขอบ<select v-model="form.edge_id" class="mt-1 w-full rounded-lg border-slate-300"><option value="">เลือกขอบ</option><option v-for="item in masters.edges" :key="item.id" :value="String(item.id)">{{ item.code }} · {{ item.name }}</option></select></label>
                    <label class="text-sm">ขนาด<select v-model="form.size_id" class="mt-1 w-full rounded-lg border-slate-300"><option value="">เลือกขนาด</option><option v-for="item in masters.sizes" :key="item.id" :value="String(item.id)">{{ item.code }} · {{ item.label }}</option></select></label>
                    <label class="text-sm">ความหนา<select v-model="form.thickness_id" class="mt-1 w-full rounded-lg border-slate-300"><option value="">เลือกความหนา</option><option v-for="item in masters.thicknesses" :key="item.id" :value="String(item.id)">{{ item.code }} · {{ item.label }}</option></select></label>
                    <label class="text-sm">ผิวหน้า<select v-model="form.face_id" class="mt-1 w-full rounded-lg border-slate-300"><option value="">ใช้ค่าตั้งต้น</option><option v-for="item in masters.faces" :key="item.id" :value="String(item.id)">{{ item.code }} · {{ item.name }}</option></select></label>
                </div>
                <div v-if="Object.keys(form.errors).length" class="mt-3 text-sm text-red-600">{{ Object.values(form.errors).join(' · ') }}</div>
                <button :disabled="form.processing" class="mt-4 min-h-11 rounded bg-[var(--hmi-yellow)] px-5 font-bold text-[var(--hmi-ink)] disabled:opacity-50" @click="createJob">{{ form.processing ? 'กำลังบันทึก…' : 'เริ่มงานใหม่' }}</button>
                </div>
            </section>

            <section class="overflow-hidden rounded-md border border-[var(--hmi-steel)] bg-white">
                <div class="flex flex-wrap items-center justify-between gap-3 bg-[var(--hmi-panel)] px-4 py-3"><div><h3 class="m-0 text-[17px] font-bold">ค้นหารายการงาน</h3><p class="m-0 text-xs text-[var(--hmi-mute)]">กรองตามวันที่ กะ ผู้คัด และสเปกสินค้า</p></div><button v-if="hasFilters" class="text-sm text-slate-600 underline" @click="clearSearch">ล้างตัวกรอง</button></div>
                <div class="grid gap-3 bg-[#F7F8F9] p-4 sm:grid-cols-2 lg:grid-cols-4">
                    <label class="text-sm">ตั้งแต่<input v-model="filter.date_from" type="date" class="mt-1 w-full rounded-lg border-slate-300" /></label><label class="text-sm">ถึงวันที่<input v-model="filter.date_to" type="date" class="mt-1 w-full rounded-lg border-slate-300" /></label>
                    <label class="text-sm">กะ<select v-model="filter.shift" class="mt-1 w-full rounded-lg border-slate-300"><option value="">ทุกกะ</option><option>A</option><option>B</option><option>T</option></select></label>
                    <label class="text-sm">ผู้คัด<select v-model="filter.qc_inspector" class="mt-1 w-full rounded-lg border-slate-300"><option value="">ทุกคน</option><option v-for="name in inspectors" :key="name">{{ name }}</option></select></label>
                    <label v-for="[key, label, options] in [['sheet_type_id','ชนิดแผ่น',masters.sheetTypes],['edge_id','ขอบ',masters.edges],['size_id','ขนาด',masters.sizes],['thickness_id','ความหนา',masters.thicknesses]] as any" :key="key" class="text-sm">{{ label }}<select v-model="(filter as any)[key]" class="mt-1 w-full rounded-lg border-slate-300"><option value="">ทั้งหมด</option><option v-for="item in options" :key="item.id" :value="String(item.id)">{{ item.code }} · {{ item.name ?? item.label }}</option></select></label>
                </div><div class="bg-[#F7F8F9] px-4 pb-4"><button class="min-h-10 rounded bg-[var(--hmi-ink)] px-5 text-sm font-bold text-white" @click="search">ค้นหา</button></div>
            </section>

            <section class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-[var(--hmi-screen)]">
                <div class="flex items-center justify-between bg-[var(--hmi-panel)] px-4 py-3"><div><h3 class="m-0 font-bold text-[var(--hmi-title)]">งานที่กำลังคัด</h3><p class="m-0 text-xs text-[var(--hmi-mute)]">OPEN · {{ processing.length }} รายการ</p></div><span class="mono rounded-sm bg-[var(--hmi-yellow)] px-2 py-1 text-xs font-extrabold text-[var(--hmi-ink)]">RUNNING</span></div>
                <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Job No.</th><th>วันที่</th><th>กะ / ผู้คัด</th><th>ชนิดสินค้า</th><th>WIP / FG</th><th>สถานะ</th><th class="text-right">จัดการ</th></tr></thead><tbody><tr v-for="job in processing" :key="job.id"><td class="font-semibold">{{ job.job_no }}</td><td>{{ dateText(job.qc_date) }}</td><td>{{ job.shift }} · {{ job.qc_inspector }}</td><td>{{ job.product_name || 'ยังไม่ระบุสินค้า' }}</td><td>{{ job.wip_qty }} / {{ job.fg_qty }}</td><td><span class="rounded-sm bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-900">{{ statusText(job.status) }}</span></td><td class="space-x-2 text-right"><Link :href="`${urls.prefix}/${job.id}/qc`" class="inline-flex min-h-9 items-center rounded bg-[var(--hmi-ink)] px-3 font-bold text-white">เปิดงาน</Link><button class="inline-flex min-h-9 items-center rounded border border-red-400 px-3 text-red-700" @click="remove(job)">ลบ</button></td></tr><tr v-if="!processing.length"><td colspan="7" class="py-10 text-center text-[var(--hmi-mute)]">ไม่มีงานที่กำลังคัด</td></tr></tbody></table></div>
                <div class="border-t border-[var(--hmi-line)] px-4 py-3 text-xs text-[var(--hmi-mute)]">แสดง {{ processing.length }} รายการ</div>
            </section>

            <section class="overflow-hidden rounded-md border border-[var(--hmi-yellow)] bg-[var(--hmi-screen)]">
                <div class="flex items-center justify-between bg-[#FFF8E1] px-4 py-3"><div><h3 class="m-0 font-bold text-[var(--hmi-title)]">รายการที่คัดผ่านแล้ว</h3><p class="m-0 text-xs text-[var(--hmi-mute)]">COMPLETED · {{ completed.length }} รายการ</p></div><span class="mono rounded-sm bg-[var(--hmi-yellow)] px-2 py-1 text-xs font-extrabold text-[var(--hmi-ink)]">PAST</span></div>
                <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Job No.</th><th>วันที่</th><th>กะ</th><th>ชนิดสินค้า</th><th>WIP</th><th>FG</th><th>สถานะ</th><th>รายงาน</th></tr></thead><tbody><tr v-for="job in completed" :key="job.id"><td class="font-semibold">{{ job.job_no }}</td><td>{{ dateText(job.qc_date) }}</td><td>{{ job.shift }}</td><td>{{ job.product_name || '-' }}</td><td>{{ job.wip_qty }}</td><td>{{ job.fg_qty }}</td><td>{{ statusText(job.status) }}</td><td class="space-x-3"><Link :href="`${urls.prefix}/${job.id}/qc`" class="font-bold text-[var(--hmi-ink)] underline">ดู</Link><a :href="`${urls.prefix}/${job.id}/wip-report`" class="font-bold text-blue-700 underline">WIP CSV</a><a :href="`${urls.prefix}/${job.id}/fg-report`" class="font-bold text-blue-700 underline">FG CSV</a></td></tr><tr v-if="!completed.length"><td colspan="8" class="py-10 text-center text-[var(--hmi-mute)]">ยังไม่มีงานที่คัดจบ</td></tr></tbody></table></div>
            </section>
        </main>
    </AppLayout>
</template>
