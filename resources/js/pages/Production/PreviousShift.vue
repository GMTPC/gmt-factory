<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

type PreviousJob = {
    id: number
    group_code: string
    status: string
    work_date: string | null
    product_name: string | null
    wip_count: number
}

const props = defineProps<{
    line: string
    shift: { index: number; ended_at: string | null }
    jobs: PreviousJob[]
    backUrl: string
    workPrefix: string
}>()

function dateText(value: string | null) {
    return value ? new Date(value).toLocaleString('th-TH') : '-'
}
</script>

<template>
    <Head :title="`ประวัติงานคัดบอร์ด ${line}`" />
    <AppLayout :title="`งานที่ผ่านมา · ${line}`" full-width>
        <div class="space-y-5">
            <header class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div>
                    <p class="text-sm text-slate-500">PQC{{ dateText(shift.ended_at) }}</p>
                    <h2 class="text-xl font-bold text-slate-900">รอบการทำงาน {{ shift.index + 1 }} · {{ line }}</h2>
                    <p class="text-sm text-slate-500">ปิดกะเมื่อ {{ dateText(shift.ended_at) }}</p>
                </div>
                <Link :href="backUrl" class="rounded-lg bg-amber-500 px-4 py-2 font-semibold text-white">กลับหน้ารายการงาน</Link>
            </header>

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h3 class="text-lg font-bold text-slate-900">รายการงานในรอบนี้</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr><th class="px-4 py-3 text-left">#</th><th class="px-4 py-3 text-left">กลุ่ม</th><th class="px-4 py-3 text-left">ชนิดสินค้า</th><th class="px-4 py-3 text-left">วันที่ผลิต</th><th class="px-4 py-3 text-right">จำนวนบาร์โค้ด WIP</th><th class="px-4 py-3 text-right">รายการ</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(job, index) in jobs" :key="job.id">
                                <td class="px-4 py-3">{{ index + 1 }}</td>
                                <td class="px-4 py-3">{{ job.group_code }}</td>
                                <td class="px-4 py-3">{{ job.product_name || 'ไม่ระบุชนิดสินค้า' }}</td>
                                <td class="px-4 py-3">{{ dateText(job.work_date) }}</td>
                                <td class="px-4 py-3 text-right">{{ Number(job.wip_count).toLocaleString() }}</td>
                                <td class="px-4 py-3 text-right"><Link :href="`${workPrefix}/${job.id}`" class="rounded-md bg-emerald-600 px-3 py-2 font-semibold text-white">ดูข้อมูล</Link></td>
                            </tr>
                            <tr v-if="jobs.length === 0"><td colspan="6" class="px-4 py-10 text-center text-slate-500">รอบนี้ไม่มีรายการงาน</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
