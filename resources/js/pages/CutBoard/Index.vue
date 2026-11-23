<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

type Job = {
    id: number
    job_no: string
    production_line: string
    work_date: string
    status: string
    headers_count: number
    lines_count: number
}

defineProps<{
    jobs: { data: Job[]; links: Array<{ url: string | null; label: string; active: boolean }> }
    createUrl: string
}>()
</script>

<template>
    <Head title="งานคัดบอร์ด" />
    <AppLayout title="งานคัดบอร์ด">
        <div class="mb-4 flex justify-end">
            <Link :href="createUrl" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
                + สร้าง Job ใหม่
            </Link>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-semibold text-slate-900">รายการ Job คัดบอร์ด</h2>
                <p class="mt-1 text-sm text-slate-500">เริ่มจากเลือกไลน์ แล้วเพิ่มกลุ่มสินค้าและรายการ WIP/FG</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Job No.</th>
                            <th class="px-5 py-3">ไลน์</th>
                            <th class="px-5 py-3">วันที่</th>
                            <th class="px-5 py-3">Headers</th>
                            <th class="px-5 py-3">Lines</th>
                            <th class="px-5 py-3">สถานะ</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="job in jobs.data" :key="job.id" class="text-slate-700">
                            <td class="px-5 py-4 font-medium text-slate-900">{{ job.job_no }}</td>
                            <td class="px-5 py-4">{{ job.production_line }}</td>
                            <td class="px-5 py-4">{{ job.work_date }}</td>
                            <td class="px-5 py-4">{{ job.headers_count }}</td>
                            <td class="px-5 py-4">{{ job.lines_count }}</td>
                            <td class="px-5 py-4"><span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs">{{ job.status }}</span></td>
                            <td class="px-5 py-4 text-right"><Link :href="`/cut-boards/${job.id}`" class="font-medium text-blue-600 hover:underline">เปิดงาน</Link></td>
                        </tr>
                        <tr v-if="jobs.data.length === 0">
                            <td colspan="7" class="px-5 py-12 text-center text-slate-500">ยังไม่มี Job คัดบอร์ด</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
