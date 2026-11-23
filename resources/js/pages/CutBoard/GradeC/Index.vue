<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { lineStyle } from '@/lib/cut-board-line'

type Tran = { id: number; running_no: number; amount: number; wip_lot: string; wms_lot: string }
type Process = { id: number; qc_date: string; product_type_name: string; product_type_full: string; brand_name: string; shift_desc: string; employee_name: string; amount: number; loss_amount: number; status: string; running_no: string | null; transactions: Tran[] }
type Page<T> = { data: T[]; current_page: number; last_page: number; per_page: number; from: number | null; to: number | null; total: number; links: Array<{ url: string | null; label: string; active: boolean }> }
const props = defineProps<{ line: string; openProcesses: Process[]; completedProcesses: Page<Process>; productTypes: Record<string, { name: string }>; filters: { date_from: string; date_to: string; product_type: string }; canDelete: boolean; urls: { line: string; index: string; create: string } }>()
const filters = ref({ ...props.filters })
function search() { router.get(props.urls.index, filters.value, { preserveState: true, preserveScroll: true }) }
function remove(process: Process) {
    if (window.confirm(`ลบรายการคัด C วันที่ ${process.qc_date} และคืนรายการ WIP ที่เกี่ยวข้องหรือไม่?`)) router.delete(`${props.urls.index}/${process.id}`, { preserveScroll: true })
}
function textDate(value: string) { return value ? new Date(`${value}T00:00:00`).toLocaleDateString('th-TH') : '—' }
</script>

<template>
    <Head :title="`คัดแผ่น C · ${line}`" />
    <AppLayout :title="`คัดแผ่น C · ${line}`" full-width>
        <main class="hmi flex w-full flex-col gap-4 p-4 lg:p-5" :style="lineStyle(line)">
            <header class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-[var(--hmi-surface)]" :style="lineStyle(line)">
                <div class="h-2 border-b border-[var(--hmi-line)]" style="background: repeating-linear-gradient(135deg, var(--line-color) 0 12px, #f5b800 12px 20px)" />
                <div class="flex flex-wrap items-center justify-between gap-4 p-5">
                    <div><div class="tag text-xs text-[var(--hmi-mute)]">CUT BOARD · GRADE C</div><h1 class="m-0 text-2xl font-extrabold text-[var(--hmi-title)]">คัดแผ่น C · {{ line }}</h1><p class="m-0 mt-1 text-sm text-[var(--hmi-mute)]">เริ่มรอบคัด บันทึกจำนวน แบ่ง WIP และพิมพ์ป้ายสำหรับคลัง/WMS</p></div>
                    <div class="flex gap-2"><Link :href="urls.line" class="hmi-button">กลับหน้าภาพรวมไลน์</Link><Link :href="urls.create" class="hmi-button-primary">＋ เริ่มคัดแผ่น C</Link></div>
                </div>
            </header>

            <section class="rounded-md border border-[var(--hmi-line)] bg-[var(--hmi-screen)] p-4">
                <h2 class="mb-3 text-base font-bold">ค้นหารายการคัดเสร็จ</h2>
                <form class="grid gap-3 md:grid-cols-4" @submit.prevent="search">
                    <label class="text-sm">ตั้งแต่วันที่<input v-model="filters.date_from" type="date" class="hmi-input mt-1"></label>
                    <label class="text-sm">ถึงวันที่<input v-model="filters.date_to" type="date" class="hmi-input mt-1"></label>
                    <label class="text-sm">ชนิดสินค้า<select v-model="filters.product_type" class="hmi-input mt-1"><option value="">ทั้งหมด</option><option v-for="(type, key) in productTypes" :key="key" :value="key">{{ type.name }}</option></select></label>
                    <div class="flex items-end gap-2"><button class="hmi-button-primary">ค้นหา</button><button type="button" class="hmi-button" @click="filters = { date_from: '', date_to: '', product_type: '' }; search()">ล้าง</button></div>
                </form>
            </section>

            <section aria-labelledby="open-grade-c" class="overflow-hidden rounded-md border border-[var(--hmi-steel)] bg-white">
                <div class="flex flex-wrap items-center justify-between gap-3 bg-[var(--hmi-panel)] px-4 py-2.5">
                    <div class="flex items-center gap-3"><span class="mono rounded-sm bg-[var(--hmi-yellow)] px-2 py-0.5 text-[13px] font-extrabold text-[var(--hmi-ink)]">RUN</span><div><h2 id="open-grade-c" class="m-0 text-[17px] font-bold text-[var(--hmi-title)]">งานที่กำลังคัด</h2><span class="tag text-xs text-[var(--hmi-mute)]">Running · {{ openProcesses.length }} รอบที่ยังไม่จบ</span></div></div>
                    <span class="rounded-sm border border-[#D9A300] bg-[#FFF8E1] px-2.5 py-1 text-xs font-bold text-[#6B4E00]">RUNNING</span>
                </div>
                <div class="overflow-x-auto"><table class="tbl min-w-[1000px]"><thead><tr><th>#</th><th>วันที่</th><th>ชนิด/เกรด</th><th>กะ</th><th>ผู้คัด</th><th class="text-right!">จำนวน</th><th class="text-right!">เสีย</th><th><span class="sr-only">จัดการ</span></th></tr></thead><tbody>
                    <tr v-for="(p, index) in openProcesses" :key="p.id"><td class="mono text-[#4A5563]">{{ index + 1 }}</td><td class="mono">{{ textDate(p.qc_date) }}</td><td class="whitespace-normal! font-medium">{{ p.product_type_name }} · {{ p.product_type_full }}</td><td class="mono">{{ p.shift_desc || '—' }}</td><td>{{ p.employee_name || '—' }}</td><td class="mono text-right font-bold">{{ p.amount.toLocaleString() }}</td><td class="mono text-right">{{ p.loss_amount.toLocaleString() }}</td><td class="text-right"><Link :href="`${urls.index}/${p.id}`" class="hmi-button-primary">เปิดงาน →</Link></td></tr>
                    <tr v-if="!openProcesses.length"><td colspan="8" class="py-12! text-center text-[#4A5563]"><i class="ph ph-clipboard-text mb-2 block text-3xl" aria-hidden="true"></i><p class="m-0 font-semibold text-[#1B2129]">ไม่มีรอบคัดที่กำลังทำงาน</p><p class="m-0 mt-1 text-sm">กดเริ่มคัดแผ่น C เพื่อเปิดรอบใหม่</p></td></tr>
                </tbody></table></div>
                <footer class="flex items-center justify-between gap-3 border-t border-[var(--hmi-steel)] px-4 py-2.5 text-[13px] text-[#4A5563]"><p class="m-0">แสดง {{ openProcesses.length ? 1 : 0 }}–{{ openProcesses.length }} จาก {{ openProcesses.length }} รายการ</p><span class="mono">{{ openProcesses.length ? '1 / 1' : '—' }}</span></footer>
            </section>

            <section aria-labelledby="completed-grade-c" class="overflow-hidden rounded-md border-2 border-[#DCA500] bg-white shadow-md">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b-2 border-[#DCA500] bg-[#FFF8E1] px-4 py-2.5"><div class="flex items-center gap-3"><span class="mono rounded-sm bg-[var(--hmi-yellow)] px-2 py-0.5 text-[13px] font-extrabold text-[var(--hmi-ink)]">PAST</span><div><h2 id="completed-grade-c" class="m-0 text-[17px] font-bold text-[var(--hmi-title)]">รายการที่คัดผ่านมา</h2><span class="tag text-xs text-[var(--hmi-mute)]">Completed · คัดเสร็จแล้ว {{ completedProcesses.total.toLocaleString() }} รายการ</span></div></div><span class="rounded-sm border border-[#12A060] bg-[#E7F5EE] px-2.5 py-1 text-xs font-bold text-[#0B5E3C]">DONE</span></div>
                <div class="overflow-x-auto"><table class="tbl min-w-[1100px]"><thead><tr><th>#</th><th>วันที่</th><th>ชนิด/เกรด</th><th>กะ</th><th>ผู้คัด</th><th>Running</th><th class="text-right!">จำนวน</th><th class="text-right!">เสีย</th><th>ป้าย / จัดการ</th></tr></thead><tbody>
                    <tr v-for="(p, index) in completedProcesses.data" :key="p.id"><td class="mono text-[#4A5563]">{{ ((completedProcesses.current_page - 1) * completedProcesses.per_page) + index + 1 }}</td><td class="mono">{{ textDate(p.qc_date) }}</td><td class="whitespace-normal! font-medium">{{ p.product_type_name }} · {{ p.product_type_full }}</td><td class="mono">{{ p.shift_desc || '—' }}</td><td>{{ p.employee_name || '—' }}</td><td class="mono">{{ p.running_no || '—' }}</td><td class="mono text-right font-bold">{{ p.amount.toLocaleString() }}</td><td class="mono text-right">{{ p.loss_amount.toLocaleString() }}</td><td><div class="flex flex-wrap gap-1.5"><a :href="`${urls.index}/${p.id}/print-all`" target="_blank" class="hmi-button">พิมพ์ป้าย</a><a :href="`${urls.index}/${p.id}/export-csv`" class="hmi-button">CSV</a><button v-if="canDelete" class="hmi-button-danger" @click="remove(p)">ลบ</button></div></td></tr>
                    <tr v-if="!completedProcesses.data.length"><td colspan="9" class="py-12! text-center text-[#4A5563]"><i class="ph ph-clock-counter-clockwise mb-2 block text-3xl" aria-hidden="true"></i><p class="m-0 font-semibold text-[#1B2129]">ยังไม่มีงานที่คัดจบ</p></td></tr>
                </tbody></table></div>
                <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-[var(--hmi-steel)] px-4 py-2.5 text-[13px] text-[#4A5563]"><p class="m-0">แสดง {{ completedProcesses.from ?? 0 }}–{{ completedProcesses.to ?? 0 }} จาก {{ completedProcesses.total.toLocaleString() }} รายการ · หน้าละ {{ completedProcesses.per_page }}</p>
                    <nav v-if="completedProcesses.last_page > 1" aria-label="เปลี่ยนหน้ารายการคัดเสร็จ" class="inline-flex items-center gap-2"><template v-for="link in completedProcesses.links" :key="link.label"><Link v-if="link.url" :href="link.url" preserve-scroll class="inline-flex min-h-9 items-center justify-center rounded border px-3 text-sm" :class="link.active ? 'border-[var(--hmi-ink)] bg-[var(--hmi-ink)] font-bold text-white' : 'border-[#A9B1BB] bg-white hover:bg-[#F1F3F5]'" v-html="link.label" /><span v-else class="inline-flex min-h-9 items-center rounded border border-[#D5D9DE] bg-[#F1F3F5] px-3 text-sm text-[#8A929C]" v-html="link.label" /></template></nav>
                </footer>
            </section>
        </main>
    </AppLayout>
</template>

<style scoped>
.hmi-button,.hmi-button-primary,.hmi-button-danger{display:inline-flex;min-height:2.35rem;align-items:center;justify-content:center;border:1px solid var(--hmi-line);border-radius:.3rem;padding:.45rem .8rem;font-size:.85rem;font-weight:700;color:var(--hmi-text);background:var(--hmi-screen)}.hmi-button:hover{background:var(--hmi-surface)}.hmi-button-primary{background:var(--line-color);border-color:var(--line-color);color:white}.hmi-button-danger{border-color:#ef4444;color:#b91c1c}.hmi-input{display:block;width:100%;min-height:2.5rem;border:1px solid var(--hmi-line);border-radius:.3rem;background:var(--hmi-panel);padding:.5rem;color:var(--hmi-text)}
</style>
