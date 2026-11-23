<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { computed, nextTick, onMounted, ref } from 'vue'

type WipItem = { id: number; barcode: string; product_name: string | null; qty: number; worktime_index: number; group_id: number }
type ProductType = { code: string; name: string }
type EmployeeGroup = { id: number; employee1: string; employee2: string }
type Summary = { input: number; output: number; holding: number; ng: number; worktime_index: number }
type HoldingLabel = { barcode: string | null; lot: string | null; worktime_index: number }

const props = defineProps<{
    line: string
    job: { id: number; group: string; status: string; work_date: string; division: string }
    items: WipItem[]
    productTypes: ProductType[]
    summaries: Summary[]
    holdingLabels: HoldingLabel[]
    employeeGroups: EmployeeGroup[]
    totals: { input: number; fg: number; ng: number; holding: number }
    backUrl: string
    storeWipUrl: string
    finishWorkUrl: string
}>()
const page = usePage<{ flash?: { success?: string } }>()

const form = useForm({ barcode: '', employee_group_id: '' })
const finishForm = useForm<{ finish?: string }>({})
const barcodeInput = ref<HTMLInputElement | null>(null)
const scannedQty = computed(() => {
    const suffix = form.barcode.trim().slice(21, 24)
    return /^\d{3}$/.test(suffix) ? Number(suffix) : 0
})

function focusScanner() {
    nextTick(() => barcodeInput.value?.focus())
}

function submitScan() {
    if (form.processing || !form.barcode.trim()) return
    form.post(props.storeWipUrl, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('barcode')
            focusScanner()
        },
    })
}

function finishWork() {
    if (form.processing || !window.confirm('ยืนยันจบงานนี้? ระบบจะบันทึกสรุปยอด WIP, FG, NG และยอดคงค้าง')) return
    finishForm.post(props.finishWorkUrl, { preserveScroll: true })
}

onMounted(focusScanner)
</script>

<template>
    <Head :title="`งานคัดบอร์ด ${line} · #${job.id}`" />
    <AppLayout :title="`ระบบคัดบอร์ด · ${line}`" full-width>
        <div class="space-y-5">
            <header class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div>
                    <p class="text-sm text-slate-500">{{ line }} · กลุ่ม {{ job.group }} · {{ job.division }}</p>
                    <h2 class="text-xl font-bold text-slate-900">งาน #{{ job.id }}</h2>
                    <p class="text-sm" :class="job.status === 'W' ? 'text-emerald-700' : 'text-red-700'">{{ job.status === 'W' ? 'กำลังคัด' : 'จบการทำงาน' }} · วันที่ผลิต {{ new Date(job.work_date).toLocaleDateString('th-TH') }}</p>
                </div>
                <Link :href="backUrl" class="rounded-lg bg-amber-500 px-4 py-2 font-semibold text-white">กลับหน้ารายการงาน</Link>
            </header>

            <div v-if="page.props.flash?.success" class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ page.props.flash.success }}</div>
            <div v-if="finishForm.errors.finish" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ finishForm.errors.finish }}</div>

            <section v-if="job.status === 'W'" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="mb-4 text-lg font-bold text-slate-900">ข้อมูลเข้า (WIP)</h3>
                <form class="grid gap-3 lg:grid-cols-[minmax(220px,1fr)_minmax(320px,2fr)_auto]" @submit.prevent="submitScan">
                    <label class="text-sm font-medium text-slate-600">ผู้คัด
                        <select v-model="form.employee_group_id" class="mt-1 w-full rounded-lg border-slate-300" required>
                            <option value="" disabled>เลือกผู้คัด</option>
                            <option v-for="group in employeeGroups" :key="group.id" :value="group.id">{{ group.employee1 }} - {{ group.employee2 }}</option>
                        </select>
                        <span v-if="form.errors.employee_group_id" class="mt-1 block text-xs text-red-600">{{ form.errors.employee_group_id }}</span>
                    </label>
                    <label class="text-sm font-medium text-slate-600">สแกนบาร์โค้ด WIP
                        <input ref="barcodeInput" v-model="form.barcode" autocomplete="off" class="mt-1 w-full rounded-lg border-slate-300 text-lg" placeholder="สแกนบาร์โค้ด แล้วกด Enter" :disabled="form.processing" @keydown.enter.prevent="submitScan" />
                        <span v-if="form.errors.barcode" class="mt-1 block text-xs text-red-600">{{ form.errors.barcode }}</span>
                    </label>
                    <div class="flex items-end gap-2">
                        <div class="min-w-20 rounded-lg bg-slate-50 px-3 py-2 text-center"><span class="block text-xs text-slate-500">จำนวน</span><b>{{ scannedQty || '—' }}</b></div>
                        <button type="submit" class="rounded-lg bg-sky-600 px-5 py-2.5 font-semibold text-white disabled:opacity-50" :disabled="form.processing">{{ form.processing ? 'กำลังบันทึก…' : 'บันทึก WIP' }}</button>
                    </div>
                </form>
                <p v-if="page.props.flash?.success" class="mt-3 rounded-lg bg-emerald-50 p-3 text-sm font-medium text-emerald-800">{{ page.props.flash.success }}</p>
                <p v-if="employeeGroups.length === 0" class="mt-3 text-sm text-amber-700">ยังไม่มีกลุ่มผู้คัดที่เปิดใช้งาน กลับไปหน้าหลักเพื่อจัดกลุ่มพนักงานก่อน</p>
            </section>

            <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div v-for="item in [{ label: 'จำนวนแผ่นเข้า', value: totals.input }, { label: 'จำนวนแผ่นออก FG', value: totals.fg }, { label: 'คงค้าง HD', value: totals.holding }, { label: 'เสีย NG', value: totals.ng }]" :key="item.label" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-sm text-slate-500">{{ item.label }}</p><p class="mt-1 text-2xl font-bold text-slate-900">{{ Number(item.value).toLocaleString() }}</p>
                </div>
            </section>

            <section v-if="job.status === 'W'" class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div><h3 class="font-bold text-slate-900">จบงานคัดบอร์ด</h3><p class="text-sm text-slate-500">ยอดคงค้างจะคำนวณจาก WIP − FG − NG และบันทึกเป็น HD</p></div>
                <button type="button" class="rounded-lg bg-rose-600 px-4 py-2 font-semibold text-white disabled:opacity-50" :disabled="finishForm.processing || totals.holding < 0" @click="finishWork">{{ finishForm.processing ? 'กำลังบันทึก…' : 'จบงาน' }}</button>
                <p v-if="totals.holding < 0" class="w-full text-sm text-red-700">ยอดคงค้างติดลบ กรุณาตรวจยอดก่อนจบงาน</p>
            </section>

            <section v-if="job.status === 'E'" class="grid gap-4 xl:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-3 text-lg font-bold text-slate-900">สรุปงานที่ปิดแล้ว</h3>
                    <div v-for="summary in summaries" :key="summary.worktime_index" class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                        <div>WIP เข้า <b class="block text-xl">{{ Number(summary.input).toLocaleString() }}</b></div>
                        <div>FG ออก <b class="block text-xl">{{ Number(summary.output).toLocaleString() }}</b></div>
                        <div>คงค้าง HD <b class="block text-xl">{{ Number(summary.holding).toLocaleString() }}</b></div>
                        <div>เสีย NG <b class="block text-xl">{{ Number(summary.ng).toLocaleString() }}</b></div>
                    </div>
                    <p v-if="summaries.length === 0" class="text-sm text-slate-500">ไม่มีข้อมูลสรุปที่บันทึกไว้</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="mb-3 text-lg font-bold text-slate-900">บาร์โค้ดคงค้าง (HD)</h3>
                    <div v-for="label in holdingLabels" :key="`${label.worktime_index}-${label.lot}`" class="rounded-lg bg-amber-50 p-3">
                        <p class="font-mono font-semibold">{{ label.barcode || '—' }}</p><p class="text-sm text-slate-600">LOT {{ label.lot || '—' }}</p>
                    </div>
                    <p v-if="holdingLabels.length === 0" class="text-sm text-slate-500">ไม่มีบาร์โค้ดคงค้าง</p>
                </div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                    <div><h3 class="text-lg font-bold text-slate-900">บาร์โค้ดยิงรับเข้า</h3><p class="text-sm text-slate-500">{{ productTypes[0]?.code || 'ยังไม่กำหนดชนิดสินค้า' }} · {{ productTypes[0]?.name || '' }}</p></div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-sm">{{ items.length }} รายการ</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600"><tr><th class="px-4 py-3">#</th><th class="px-4 py-3">บาร์โค้ด</th><th class="px-4 py-3">สินค้า</th><th class="px-4 py-3">ผู้คัด (รหัสกลุ่ม)</th><th class="px-4 py-3 text-right">จำนวน</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(item, index) in items" :key="item.id"><td class="px-4 py-3">{{ index + 1 }}</td><td class="px-4 py-3 font-mono">{{ item.barcode }} <span class="rounded bg-sky-100 px-1.5 py-0.5 text-xs">{{ item.barcode.slice(0, 2) }}</span></td><td class="px-4 py-3">{{ item.product_name || '-' }}</td><td class="px-4 py-3">{{ employeeGroups.find((group) => group.id === item.group_id) ? `${employeeGroups.find((group) => group.id === item.group_id)?.employee1} - ${employeeGroups.find((group) => group.id === item.group_id)?.employee2}` : `กลุ่ม #${item.group_id}` }}</td><td class="px-4 py-3 text-right font-semibold">{{ Number(item.qty).toLocaleString() }}</td></tr>
                            <tr v-if="items.length === 0"><td colspan="5" class="px-4 py-10 text-center text-slate-500">ยังไม่มีบาร์โค้ด WIP ในงานนี้</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
