<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { nextTick, ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'

type Job = { id: number; job_no: string; production_line: string; work_date: string; status: string }
type Header = { id: number; header_no: string; group_code: string | null; sku_code: string | null; lot_no: string | null; status: string }
type EmployeeGroup = { id: number; employee1: string; employee2: string }

const props = defineProps<{
    jobs: Job[]
    selectedJob: Job | null
    headers: Header[]
    employeeGroups: EmployeeGroup[]
    storeUrl: string
    locations: Record<string, unknown>[]
}>()

const scanner = ref<HTMLInputElement | null>(null)
const result = ref('')
const form = useForm({
    job_id: props.selectedJob?.id || '',
    header_id: props.headers[0]?.id || '',
    raw_scan_code: '',
    source_type: 'WIP',
    employee_group_id: '',
    source_id: '',
    wip_code: '',
    fg_code: '',
    spec_code: '',
    spec_name: '',
    spec_snapshot: null,
    sku_code: '',
    lot_no: '',
    qty: '',
})

function changeJob() {
    router.get('/checker-qc-scan', { job: form.job_id }, { preserveState: true, replace: true })
}

function submit() {
    form.post(props.storeUrl, {
        preserveScroll: true,
        onSuccess: () => {
            result.value = 'บันทึกสำเร็จ'
            form.reset('raw_scan_code', 'source_id', 'wip_code', 'fg_code', 'spec_code', 'spec_name', 'sku_code', 'lot_no')
            nextTick(() => scanner.value?.focus())
        },
    })
}
</script>

<template>
    <Head title="QC Scan" />
    <AppLayout title="QC Scan">
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-5 text-blue-900">
            <h2 class="font-semibold">สแกนรายการ WIP / FG</h2>
            <p class="mt-1 text-sm">ทุกการสแกนจะบันทึก Source Code และ Spec Snapshot ก่อนเข้าสู่ขั้นตอนรับเข้า WMS</p>
        </div>

        <div class="mt-5 grid gap-5 lg:grid-cols-[1fr_1.4fr]">
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-semibold text-slate-900">เลือก Job และ Header</h2>
                <div class="mt-4 space-y-4">
                    <select v-model="form.job_id" class="w-full rounded-lg border-slate-300" @change="changeJob">
                        <option value="" disabled>เลือก Job</option>
                        <option v-for="job in jobs" :key="job.id" :value="job.id">{{ job.job_no }} · {{ job.production_line }}</option>
                    </select>
                    <select v-model="form.header_id" class="w-full rounded-lg border-slate-300" :disabled="headers.length === 0">
                        <option value="" disabled>เลือก Header</option>
                        <option v-for="header in headers" :key="header.id" :value="header.id">{{ header.header_no }} · {{ header.group_code || '-' }}</option>
                    </select>
                    <p v-if="headers.length === 0" class="text-sm text-amber-700">Job นี้ยังไม่มี Header กรุณาเพิ่ม Header จากหน้า Job Detail</p>
                    <Link v-if="selectedJob" :href="`/cut-boards/${selectedJob.production_line}/${selectedJob.id}`" class="text-sm text-blue-600 hover:underline">เปิด Job Detail</Link>
                </div>
            </div>

            <form class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm" @submit.prevent="submit">
                <h2 class="font-semibold text-slate-900">บันทึกข้อมูลเข้า WIP / ข้อมูลออก FG</h2>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <input ref="scanner" v-model="form.raw_scan_code" class="rounded-lg border-slate-300 md:col-span-2" placeholder="สแกน QR / Raw Code แล้วกด Enter" autofocus required @keyup.enter="submit" />
                    <select v-model="form.source_type" class="rounded-lg border-slate-300"><option value="WIP">WIP</option><option value="FG">FG</option></select>
                    <select v-if="form.source_type === 'WIP'" v-model="form.employee_group_id" class="rounded-lg border-slate-300" required>
                        <option value="" disabled>เลือกกลุ่มผู้คัด</option>
                        <option v-for="group in employeeGroups" :key="group.id" :value="group.id">{{ group.employee1 }} - {{ group.employee2 }}</option>
                    </select>
                    <p v-if="form.errors.employee_group_id" class="text-sm text-red-600">{{ form.errors.employee_group_id }}</p>
                    <input v-model="form.source_id" class="rounded-lg border-slate-300" placeholder="Source ID (ถ้ามี ระบบจะหาให้อัตโนมัติ)" />
                    <input v-model="form.wip_code" class="rounded-lg border-slate-300" placeholder="WIP Code" />
                    <input v-model="form.fg_code" class="rounded-lg border-slate-300" placeholder="FG Code" />
                    <input v-model="form.spec_code" class="rounded-lg border-slate-300" placeholder="Spec Code (ถ้ามี ระบบจะหาให้อัตโนมัติ)" />
                    <input v-model="form.spec_name" class="rounded-lg border-slate-300" placeholder="ชื่อ Spec" />
                    <input v-model="form.sku_code" class="rounded-lg border-slate-300" placeholder="SKU Code" />
                    <input v-model="form.lot_no" class="rounded-lg border-slate-300" placeholder="Lot No." />
                    <input v-model="form.qty" type="number" min="0.001" step="0.001" class="rounded-lg border-slate-300" placeholder="จำนวน (ถ้ามี ระบบอ่านจาก QR)" />
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <span v-if="result" class="text-sm font-medium text-emerald-600">{{ result }}</span>
                    <button type="submit" :disabled="form.processing || !form.header_id" class="ml-auto rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white disabled:opacity-50">บันทึก Scan</button>
                </div>
                <div v-if="form.errors.raw_scan_code" class="mt-3 text-sm text-red-600">{{ form.errors.raw_scan_code }}</div>
            </form>
        </div>
    </AppLayout>
</template>
