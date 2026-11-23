<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

type Line = { id: number; job_no: string; production_line: string; header_no: string; source_type: string; source_id: string; spec_code: string; qty: string }

const props = defineProps<{ lines: Line[]; storeUrl: string }>()
const form = useForm({ line_id: 0 })

function receive(lineId: number) {
    form.line_id = lineId
    form.post(props.storeUrl, { preserveScroll: true })
}
</script>

<template>
    <Head title="Forklift Receive" />
    <AppLayout title="Forklift Receive">
        <div class="mb-5 rounded-xl border border-blue-200 bg-blue-50 p-5 text-blue-900">
            <h2 class="font-semibold">รายการผ่าน QC ที่รอรับเข้า WMS</h2>
            <p class="mt-1 text-sm">ตรวจสอบ Source และ Spec ก่อนกดยืนยันรับเข้า</p>
        </div>
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-5 py-3">Job</th><th class="px-5 py-3">Header</th><th class="px-5 py-3">Source</th><th class="px-5 py-3">Spec</th><th class="px-5 py-3">Qty</th><th class="px-5 py-3"></th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="line in lines" :key="line.id"><td class="px-5 py-4"><div class="font-medium">{{ line.job_no }}</div><div class="text-xs text-slate-500">{{ line.production_line }}</div></td><td class="px-5 py-4">{{ line.header_no }}</td><td class="px-5 py-4">{{ line.source_type }} · {{ line.source_id }}</td><td class="px-5 py-4">{{ line.spec_code }}</td><td class="px-5 py-4">{{ line.qty }}</td><td class="px-5 py-4 text-right"><button type="button" :disabled="form.processing" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-medium text-white disabled:opacity-50" @click="receive(line.id)">รับเข้า WMS</button></td></tr>
                        <tr v-if="lines.length === 0"><td colspan="6" class="px-5 py-12 text-center text-slate-500">ไม่มีรายการที่รอรับเข้า</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
