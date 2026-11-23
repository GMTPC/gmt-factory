<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

type Line = { id: number; job_no: string; production_line: string; header_no: string; source_type: string; source_id: string; spec_code: string; sku_code: string | null; lot_no: string | null; qty: string; print_status: string; show_url: string }

defineProps<{ lines: Line[] }>()
</script>

<template>
    <Head title="BX Print" />
    <AppLayout title="BX Print">
        <div class="mb-5 rounded-xl border border-blue-200 bg-blue-50 p-5 text-blue-900">
            <h2 class="font-semibold">รายการที่รับเข้า WMS แล้ว</h2>
            <p class="mt-1 text-sm">เลือกดู Tag และ Export ตาม SKU ได้จากรายการด้านล่าง</p>
        </div>
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Job</th><th class="px-5 py-3">Source</th><th class="px-5 py-3">Spec</th><th class="px-5 py-3">SKU</th><th class="px-5 py-3">Lot</th><th class="px-5 py-3">สถานะ</th><th class="px-5 py-3"></th></tr></thead><tbody class="divide-y divide-slate-100"><tr v-for="line in lines" :key="line.id"><td class="px-5 py-4 font-medium">{{ line.job_no }}</td><td class="px-5 py-4">{{ line.source_type }} · {{ line.source_id }}</td><td class="px-5 py-4">{{ line.spec_code }}</td><td class="px-5 py-4">{{ line.sku_code || '-' }}</td><td class="px-5 py-4">{{ line.lot_no || '-' }}</td><td class="px-5 py-4">{{ line.print_status }}</td><td class="px-5 py-4 text-right"><Link :href="line.show_url" class="font-medium text-blue-600 hover:underline">ดู Tag</Link></td></tr><tr v-if="lines.length === 0"><td colspan="7" class="px-5 py-12 text-center text-slate-500">ยังไม่มีรายการสำหรับพิมพ์ Tag</td></tr></tbody></table></div>
        </div>
    </AppLayout>
</template>
