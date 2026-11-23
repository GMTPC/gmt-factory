<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

type Line = { id: number; job_no: string; source_type: string; source_id: string; spec_code: string; sku_code: string | null; lot_no: string | null; qty: string; interface_qr: string | null; tag: string | null; tag_date: string | null; print_status: string }

const props = defineProps<{ skuCode: string; lines: Line[]; exportUrl: string }>()
const form = useForm({ line_id: 0 })

function markPrinted(lineId: number) {
    form.line_id = lineId
    form.post(`/bx-print-lines/${lineId}/printed`, { preserveScroll: true })
}
</script>

<template>
    <Head :title="`BX Print ${skuCode}`" />
    <AppLayout :title="`BX Print · ${skuCode}`">
        <div class="mb-5 flex items-center justify-between rounded-xl border border-slate-200 bg-white p-5 shadow-sm"><div><p class="text-sm text-slate-500">SKU</p><h2 class="text-lg font-semibold">{{ skuCode }}</h2></div><a :href="props.exportUrl" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">Export CSV</a></div>
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"><div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Job</th><th class="px-5 py-3">Source</th><th class="px-5 py-3">Spec</th><th class="px-5 py-3">Interface QR</th><th class="px-5 py-3">Tag</th><th class="px-5 py-3"></th></tr></thead><tbody class="divide-y divide-slate-100"><tr v-for="line in lines" :key="line.id"><td class="px-5 py-4">{{ line.job_no }}</td><td class="px-5 py-4">{{ line.source_type }} · {{ line.source_id }}</td><td class="px-5 py-4">{{ line.spec_code }}</td><td class="px-5 py-4 font-mono text-xs">{{ line.interface_qr || '-' }}</td><td class="px-5 py-4 font-mono text-xs">{{ line.tag || '-' }}</td><td class="px-5 py-4 text-right"><button type="button" :disabled="form.processing || line.print_status === 'printed'" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-medium text-white disabled:opacity-50" @click="markPrinted(line.id)">{{ line.print_status === 'printed' ? 'พิมพ์แล้ว' : 'Mark Printed' }}</button></td></tr><tr v-if="lines.length === 0"><td colspan="6" class="px-5 py-12 text-center text-slate-500">ไม่พบรายการ</td></tr></tbody></table></div></div>
        <div class="mt-4"><Link href="/bx-print" class="text-sm text-slate-600 hover:underline">← กลับ BX Print</Link></div>
    </AppLayout>
</template>
