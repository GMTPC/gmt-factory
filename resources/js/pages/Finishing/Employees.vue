<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { computed, nextTick, reactive, ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { errorText, finishingRequest } from '@/lib/finishing-api'
import { toast } from '@/lib/toast'

type Employee = { id: number; name: string; nickname: string | null; display_name: string; is_active: boolean; updated_at: string; minutes_today: number; minutes_month: number; working_now: boolean }
const props = defineProps<{ employees: Employee[]; monthLabel: string; csrf: string }>()
function hours(minutes: number) {
    if (!minutes) return '-'
    const total = Math.round(minutes)
    const h = Math.floor(total / 60)
    return h ? `${h} ชม. ${String(total % 60).padStart(2, '0')} น.` : `${total} น.`
}

const form = reactive({ name: '', nickname: '' })
const formError = ref('')
const saving = ref(false)
const nameInput = ref<HTMLInputElement | null>(null)
const search = ref('')
const showInactive = ref(false)
const editingId = ref<number | null>(null)
const edit = reactive({ name: '', nickname: '' })
const busyId = ref<number | null>(null)

const activeCount = computed(() => props.employees.filter((employee) => employee.is_active).length)
const preview = computed(() => form.name.trim() ? (form.nickname.trim() ? `${form.name.trim()} (${form.nickname.trim()})` : form.name.trim()) : '')
const rows = computed(() => {
    const keyword = search.value.trim().toLocaleLowerCase()
    return props.employees
        .filter((employee) => showInactive.value || employee.is_active)
        .filter((employee) => !keyword || employee.display_name.toLocaleLowerCase().includes(keyword))
})

function reload() { router.reload({ only: ['employees'], preserveScroll: true }) }

async function add() {
    if (saving.value || !form.name.trim()) return
    saving.value = true; formError.value = ''
    try {
        const employee = await finishingRequest<Employee>('/api/finishing/employees', 'POST', { name: form.name, nickname: form.nickname || null }, props.csrf)
        toast(`เพิ่ม ${employee.display_name} แล้ว`)
        form.name = ''; form.nickname = ''
        reload()
        nextTick(() => nameInput.value?.focus())
    } catch (e) { formError.value = errorText(e) } finally { saving.value = false }
}

function startEdit(employee: Employee) { editingId.value = employee.id; edit.name = employee.name; edit.nickname = employee.nickname ?? '' }
async function saveEdit(employee: Employee) {
    busyId.value = employee.id
    try {
        const updated = await finishingRequest<Employee>(`/api/finishing/employees/${employee.id}`, 'PATCH', { name: edit.name, nickname: edit.nickname || null }, props.csrf)
        toast(`บันทึก ${updated.display_name} แล้ว`)
        editingId.value = null
        reload()
    } catch (e) { toast(errorText(e), 'error') } finally { busyId.value = null }
}
async function toggleActive(employee: Employee) {
    busyId.value = employee.id
    try {
        await finishingRequest(`/api/finishing/employees/${employee.id}`, 'PATCH', { is_active: !employee.is_active }, props.csrf)
        toast(employee.is_active ? `ปิดใช้งาน ${employee.display_name} แล้ว · จะไม่แสดงในตัวเลือก` : `เปิดใช้งาน ${employee.display_name} แล้ว`)
        reload()
    } catch (e) { toast(errorText(e), 'error') } finally { busyId.value = null }
}
async function remove(employee: Employee) {
    if (!window.confirm(`ลบ ${employee.display_name}?\nJob เดิมที่มีชื่อนี้ยังแสดงชื่อเหมือนเดิม`)) return
    busyId.value = employee.id
    try {
        await finishingRequest(`/api/finishing/employees/${employee.id}`, 'DELETE', undefined, props.csrf)
        toast(`ลบ ${employee.display_name} แล้ว`)
        reload()
    } catch (e) { toast(errorText(e), 'error') } finally { busyId.value = null }
}
</script>

<template>
    <Head title="พนักงาน Finishing" />
    <AppLayout title="พนักงาน Finishing" full-width hide-header>
        <main class="hmi flex min-w-0 flex-col gap-4 pb-6">
            <header class="rounded-md border border-[var(--hmi-line)] bg-white px-4 py-3 shadow-sm">
                <p class="m-0 text-xs text-[var(--hmi-mute)]">FINISHING · MASTER</p>
                <h1 class="m-0 text-xl font-bold text-[var(--hmi-title)]">พนักงาน Finishing</h1>
                <p class="m-0 text-sm text-[var(--hmi-mute)]">รายชื่อที่ใช้เลือก "พนักงานที่ทำงาน" ตอนสร้าง Job · ใช้งานอยู่ {{ activeCount }} คน · ชั่วโมงนับจากเวลาเริ่ม Job ถึงจบงาน</p>
            </header>

            <section class="rounded-md border border-[var(--hmi-line)] bg-white p-4 shadow-sm">
                <h2 class="m-0 mb-3 font-bold">เพิ่มพนักงาน</h2>
                <form class="grid gap-3 sm:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_auto]" @submit.prevent="add">
                    <label class="text-sm font-semibold">ชื่อ - นามสกุล<input ref="nameInput" v-model="form.name" required maxlength="150" class="mt-1 block min-h-11 w-full rounded border-slate-300" placeholder="เช่น สมชาย ใจดี" @input="formError = ''" /></label>
                    <label class="text-sm font-semibold">ชื่อเล่น (ถ้ามี)<input v-model="form.nickname" maxlength="50" class="mt-1 block min-h-11 w-full rounded border-slate-300" placeholder="เช่น ชาย" @input="formError = ''" /></label>
                    <button :disabled="saving || !form.name.trim()" class="flex min-h-11 items-center justify-center gap-2 self-end rounded bg-emerald-700 px-5 font-bold text-white disabled:opacity-50"><i class="ph ph-user-plus text-lg" aria-hidden="true"></i>{{ saving ? 'กำลังเพิ่ม...' : 'เพิ่ม' }}</button>
                </form>
                <p v-if="preview" class="m-0 mt-2 text-xs text-slate-500">จะแสดงในตัวเลือกเป็น: <strong class="text-slate-800">{{ preview }}</strong></p>
                <p v-if="formError" role="alert" class="m-0 mt-2 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">{{ formError }}</p>
            </section>

            <section class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-white shadow-sm">
                <div class="flex flex-wrap items-center gap-3 bg-[var(--hmi-panel)] px-4 py-3">
                    <h2 class="m-0 mr-auto font-bold">รายชื่อ ({{ rows.length }})</h2>
                    <input v-model="search" type="search" class="min-h-10 w-full rounded border-slate-300 text-sm sm:w-64" placeholder="ค้นหาชื่อ / ชื่อเล่น" aria-label="ค้นหาพนักงาน" />
                    <label class="flex items-center gap-2 text-sm"><input v-model="showInactive" type="checkbox" class="rounded border-slate-400 text-emerald-700" /> แสดงที่ปิดใช้งาน</label>
                </div>
                <ul class="m-0 divide-y divide-slate-100 p-0">
                    <li v-for="employee in rows" :key="employee.id" class="m-0 flex list-none flex-wrap items-center gap-3 px-4 py-2.5" :class="employee.is_active ? '' : 'bg-slate-50'">
                        <template v-if="editingId === employee.id">
                            <input v-model="edit.name" maxlength="150" class="min-h-10 min-w-0 flex-[2] rounded border-slate-300 text-sm" aria-label="ชื่อ - นามสกุล" @keydown.enter.prevent="saveEdit(employee)" />
                            <input v-model="edit.nickname" maxlength="50" class="min-h-10 w-32 min-w-0 flex-1 rounded border-slate-300 text-sm" placeholder="ชื่อเล่น" aria-label="ชื่อเล่น" @keydown.enter.prevent="saveEdit(employee)" />
                            <div class="flex gap-2"><button type="button" :disabled="busyId === employee.id || !edit.name.trim()" class="min-h-10 rounded bg-emerald-700 px-4 text-sm font-bold text-white disabled:opacity-50" @click="saveEdit(employee)">บันทึก</button><button type="button" class="min-h-10 rounded border border-slate-300 px-4 text-sm font-semibold" @click="editingId = null">ยกเลิก</button></div>
                        </template>
                        <template v-else>
                            <div class="min-w-0 flex-1">
                                <p class="m-0 font-semibold" :class="employee.is_active ? 'text-slate-900' : 'text-slate-400 line-through'">{{ employee.name }}<span v-if="employee.nickname" class="font-normal text-slate-500"> ({{ employee.nickname }})</span></p>
                            </div>
                            <span v-if="employee.working_now" class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-800">● กำลังทำงาน</span>
                            <dl class="m-0 flex gap-4 text-right text-xs">
                                <div><dt class="text-slate-500">วันนี้</dt><dd class="mono m-0 font-bold" :class="employee.minutes_today ? 'text-slate-900' : 'text-slate-300'">{{ hours(employee.minutes_today) }}</dd></div>
                                <div><dt class="text-slate-500">{{ monthLabel }}</dt><dd class="mono m-0 font-bold" :class="employee.minutes_month ? 'text-slate-900' : 'text-slate-300'">{{ hours(employee.minutes_month) }}</dd></div>
                            </dl>
                            <span v-if="!employee.is_active" class="rounded-full bg-slate-200 px-2 py-0.5 text-xs font-bold text-slate-600">ปิดใช้งาน</span>
                            <div class="flex gap-1.5">
                                <button type="button" :disabled="busyId === employee.id" class="min-h-9 rounded border border-slate-300 bg-white px-3 text-xs font-bold" @click="startEdit(employee)">แก้ไข</button>
                                <button type="button" :disabled="busyId === employee.id" class="min-h-9 rounded border px-3 text-xs font-bold" :class="employee.is_active ? 'border-amber-300 text-amber-800' : 'border-emerald-400 text-emerald-800'" @click="toggleActive(employee)">{{ employee.is_active ? 'ปิดใช้งาน' : 'เปิดใช้งาน' }}</button>
                                <button type="button" :disabled="busyId === employee.id" class="min-h-9 rounded border border-red-300 px-3 text-xs font-bold text-red-700" @click="remove(employee)">ลบ</button>
                            </div>
                        </template>
                    </li>
                    <li v-if="!rows.length" class="m-0 list-none px-4 py-8 text-center text-sm text-slate-500">ไม่พบพนักงาน</li>
                </ul>
            </section>
        </main>
    </AppLayout>
</template>
