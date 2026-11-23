<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

type ProductionLine = { value: string; label: string }

const props = defineProps<{
    productionLines: ProductionLine[]
    storeUrl: string
    backUrl: string
}>()

const form = useForm({
    production_line: '',
    work_date: new Date().toISOString().slice(0, 10),
})

function submit() {
    form.post(props.storeUrl)
}
</script>

<template>
    <Head title="สร้าง Job คัดบอร์ด" />
    <AppLayout title="สร้าง Job คัดบอร์ด">
        <div class="max-w-2xl rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-slate-900">เริ่มงานคัดบอร์ด</h2>
                <p class="mt-1 text-sm text-slate-500">เลือกไลน์ผลิตและวันที่ทำงานก่อนเพิ่ม Header และ Lines</p>
            </div>

            <form class="space-y-5" @submit.prevent="submit">
                <div>
                    <label for="production_line" class="mb-1 block text-sm font-medium text-slate-700">ไลน์ผลิต</label>
                    <select id="production_line" v-model="form.production_line" class="w-full rounded-lg border-slate-300" required>
                        <option value="" disabled>เลือกไลน์ผลิต</option>
                        <option v-for="line in productionLines" :key="line.value" :value="line.value">{{ line.label }}</option>
                    </select>
                    <p v-if="form.errors.production_line" class="mt-1 text-sm text-red-600">{{ form.errors.production_line }}</p>
                </div>

                <div>
                    <label for="work_date" class="mb-1 block text-sm font-medium text-slate-700">วันที่ทำงาน</label>
                    <input id="work_date" v-model="form.work_date" type="date" class="w-full rounded-lg border-slate-300" required />
                    <p v-if="form.errors.work_date" class="mt-1 text-sm text-red-600">{{ form.errors.work_date }}</p>
                </div>

                <div class="flex justify-end gap-3">
                    <Link :href="backUrl" class="rounded-lg border border-slate-300 px-4 py-2 text-sm text-slate-700">ยกเลิก</Link>
                    <button type="submit" :disabled="form.processing" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white disabled:opacity-50">
                        {{ form.processing ? 'กำลังสร้าง...' : 'สร้าง Job' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
