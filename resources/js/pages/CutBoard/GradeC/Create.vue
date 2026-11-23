<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { lineStyle } from '@/lib/cut-board-line'
const props = defineProps<{ line: string; employeeGroups: Array<{ id: number; employee1: string | null; employee2: string | null }>; productTypes: Record<string, { name: string }>; storeUrl: string; backUrl: string }>()
const form = useForm({ shift_code: 'A', employee_group_id: '', product_type: 'A1', sheet_size: 'standard' })
function submit() { form.post(props.storeUrl) }
</script>
<template>
    <Head :title="`เริ่มคัดแผ่น C · ${line}`" /><AppLayout :title="`เริ่มคัดแผ่น C · ${line}`" full-width><main class="w-full p-4 lg:p-6">
        <header class="mb-5 rounded-md border border-[var(--hmi-line)] bg-[var(--hmi-surface)] p-5" :style="lineStyle(line)"><div class="tag text-xs text-[var(--hmi-mute)]">CUT BOARD · GRADE C</div><h1 class="m-0 text-2xl font-extrabold">เริ่มรอบคัดแผ่น C · {{ line }}</h1><p class="mb-0 text-sm text-[var(--hmi-mute)]">เลือกกะ กลุ่มพนักงาน และสเปกสินค้าเพื่อเปิดรอบคัด</p></header>
        <form class="grid gap-5 rounded-md border border-[var(--hmi-line)] bg-[var(--hmi-screen)] p-5 md:grid-cols-2" @submit.prevent="submit">
            <label class="text-sm font-semibold">กะการทำงาน<select v-model="form.shift_code" class="hmi-input mt-1"><option value="A">กะ A</option><option value="B">กะ B</option><option value="T">กะ T</option></select><small v-if="form.errors.shift_code" class="text-red-600">{{ form.errors.shift_code }}</small></label>
            <label class="text-sm font-semibold">กลุ่มพนักงาน<select v-model="form.employee_group_id" class="hmi-input mt-1"><option value="">เลือกกลุ่มพนักงาน</option><option v-for="group in employeeGroups" :key="group.id" :value="String(group.id)">กลุ่ม {{ group.id }} · {{ [group.employee1, group.employee2].filter(Boolean).join(' / ') || 'ไม่ระบุชื่อ' }}</option></select><small v-if="form.errors.employee_group_id" class="text-red-600">{{ form.errors.employee_group_id }}</small></label>
            <label class="text-sm font-semibold">ชนิดสินค้า<select v-model="form.product_type" class="hmi-input mt-1"><option v-for="(type, key) in productTypes" :key="key" :value="key">{{ type.name }}</option></select></label>
            <label class="text-sm font-semibold">ขนาดแผ่น<select v-model="form.sheet_size" class="hmi-input mt-1"><option value="standard">มาตรฐาน</option><option value="large">ขนาดใหญ่</option></select></label>
            <p class="md:col-span-2 rounded border border-sky-200 bg-sky-50 p-3 text-sm text-sky-900">ระบบจะเปิดงานเป็นสถานะกำลังคัดก่อน เมื่อบันทึกจำนวนและกดยืนยันจบงาน ระบบจะสร้าง WIP/ล็อตเป็นชุดละ 100 แผ่น และผูกข้อมูลกะกับผู้คัด</p>
            <div class="flex gap-2 md:col-span-2"><Link :href="backUrl" class="hmi-button">ยกเลิก</Link><button class="hmi-button-primary" :disabled="form.processing || !form.employee_group_id">{{ form.processing ? 'กำลังบันทึก…' : 'เริ่มคัดแผ่น C' }}</button></div>
        </form>
    </main></AppLayout>
</template>
<style scoped>.hmi-input{display:block;width:100%;min-height:2.6rem;border:1px solid var(--hmi-line);border-radius:.3rem;background:var(--hmi-panel);padding:.55rem;color:var(--hmi-text)}.hmi-button,.hmi-button-primary{display:inline-flex;min-height:2.5rem;align-items:center;justify-content:center;border:1px solid var(--hmi-line);border-radius:.3rem;padding:.5rem 1rem;font-weight:700}.hmi-button-primary{background:var(--line-color);border-color:var(--line-color);color:white}.hmi-button{background:var(--hmi-panel)}</style>
