<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

defineProps<{ station: { code: string; name: string }; logs: { data: any[]; links: { url: string | null; label: string; active: boolean }[]; total: number }; filters: Record<string, string> }>()
function dateText(value: string) { return value ? new Date(`${value.slice(0, 10)}T00:00:00`).toLocaleDateString('th-TH') : '-' }
</script>

<template>
    <Head :title="`ประวัติ ${station.code} · Finishing`" />
    <AppLayout :title="`ประวัติ · ${station.name}`" full-width>
        <main class="hmi flex flex-col gap-4 pb-6">
            <header class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white"><div class="hazard" aria-hidden="true"></div><div class="flex flex-wrap items-center justify-between gap-3 bg-[var(--hmi-panel)] px-5 py-4"><div><p class="m-0 text-xs text-[var(--hmi-mute)]">FINISHING PRODUCTION · HISTORY</p><h1 class="m-0 text-xl font-bold">ประวัติ · {{ station.name }}</h1></div><Link :href="`/finishing/station/${station.code}/jobs`" class="rounded bg-[var(--hmi-ink)] px-4 py-2 text-sm font-bold text-white">กลับรายการ Job</Link></div></header>
            <section class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white">
                <form class="grid gap-3 bg-slate-50 p-4 sm:grid-cols-2 xl:grid-cols-5" method="get"><label class="text-sm">ตั้งแต่<input name="date_from" type="date" :value="filters.date_from" class="mt-1 w-full rounded border-slate-300" /></label><label class="text-sm">ถึงวันที่<input name="date_to" type="date" :value="filters.date_to" class="mt-1 w-full rounded border-slate-300" /></label><label class="text-sm">กะ<select name="shift" :value="filters.shift" class="mt-1 w-full rounded border-slate-300"><option value="">ทุกกะ</option><option value="day">กะเช้า</option><option value="night">กะดึก</option></select></label><label class="text-sm">สถานะ<select name="status" :value="filters.status" class="mt-1 w-full rounded border-slate-300"><option value="">ทุกสถานะ</option><option value="draft">draft</option><option value="confirmed">confirmed</option><option value="cancelled">cancelled</option></select></label><div class="flex items-end"><button class="rounded bg-slate-900 px-5 py-2 text-sm font-bold text-white">ค้นหา</button></div></form>
                <div class="overflow-x-auto"><table class="tbl min-w-[950px]"><thead><tr><th>วันที่</th><th>กะ</th><th>วัตถุดิบ</th><th>รับเข้า</th><th>ผลิตได้</th><th>เสีย</th><th>FT</th><th>สถานะ</th><th>ผู้บันทึก</th></tr></thead><tbody><tr v-for="log in logs.data" :key="log.id"><td>{{ dateText(log.shift_date) }}</td><td>{{ log.shift === 'day' ? 'เช้า' : 'ดึก' }}</td><td>{{ log.material?.code || log.material?.name }}</td><td>{{ log.quantity_in }}</td><td>{{ log.quantity_out }}</td><td>{{ log.defect_count }}</td><td>{{ log.ft_count ?? '-' }}</td><td>{{ log.status }}</td><td>{{ log.user?.name || '-' }}</td></tr><tr v-if="!logs.data.length"><td colspan="9" class="py-10 text-center text-slate-500">ไม่พบรายการ</td></tr></tbody></table></div>
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200 p-3 text-sm"><span>ทั้งหมด {{ logs.total }} รายการ</span><nav class="flex flex-wrap gap-1"><a v-for="link in logs.links" :key="link.label" :href="link.url || undefined" class="rounded border px-3 py-1" :class="link.active ? 'border-emerald-700 bg-emerald-700 text-white' : 'border-slate-200'" v-html="link.label" /></nav></div>
            </section>
        </main>
    </AppLayout>
</template>
