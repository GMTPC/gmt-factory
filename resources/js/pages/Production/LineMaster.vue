<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { cutBoardLineTheme } from '@/lib/cut-board-line'

type Section = 'history' | 'employees' | 'groups' | 'daily-summary'
type Employee = { id: number; name: string; remark?: string | null; status?: number }
type Group = { id: number; status: number; division: string; employee1: string; employee2: string }
type PastJob = { id: number; job_no: string; work_date: string; status: string }
type DailyRow = { work_date: string; qty: number }

const props = defineProps<{
    line: string
    lineLabel: string
    section: Section
    data: { employees?: Employee[]; groups?: Group[]; history?: PastJob[]; daily?: DailyRow[] }
    urls: Record<string, string>
}>()
const page = usePage<{ flash?: { success?: string; error?: string } }>()
const lineTheme = cutBoardLineTheme(props.line)

const sectionInfo: Record<Section, { title: string; description: string }> = {
    history: { title: 'ประวัติงานที่ผ่านมา', description: 'เลือก Job เพื่อดูรายการ Scan และสถานะงานของไลน์นี้' },
    employees: { title: 'รายชื่อพนักงาน', description: 'เพิ่มรายชื่อพนักงานหรือปิดการใช้งานในไลน์นี้' },
    groups: { title: 'จัดกลุ่มพนักงาน', description: 'กำหนดคู่พนักงานและสถานะกลุ่มของไลน์นี้' },
    'daily-summary': { title: 'สรุปยอดรายวัน', description: 'ยอดรับเข้า WIP แยกตามวันที่ผลิตของไลน์นี้' },
}
const employees = props.data.employees ?? []
const groups = props.data.groups ?? []
const history = props.data.history ?? []
const daily = props.data.daily ?? []
const employeeForm = useForm({ employees: [{ name: '', remark: '' }] })
const groupForm = useForm({ groups: [{ employee1: '', employee2: '', division: 'QC' }] })

function addEmployeeRow() { employeeForm.employees.push({ name: '', remark: '' }) }
function removeEmployeeRow(index: number) {
    if (employeeForm.employees.length > 1) employeeForm.employees.splice(index, 1)
}
function saveEmployees() {
    employeeForm.post(props.urls.employeeStore, { preserveScroll: true, onSuccess: () => employeeForm.reset() })
}
function disableEmployee(employee: Employee) {
    if (!window.confirm(`ปิดการใช้งาน ${employee.name}?`)) return
    router.delete(`${props.urls.employeePrefix}/${employee.id}`, { preserveScroll: true })
}
function addGroupRow() { groupForm.groups.push({ employee1: '', employee2: '', division: 'QC' }) }
function removeGroupRow(index: number) {
    if (groupForm.groups.length > 1) groupForm.groups.splice(index, 1)
}
function saveGroups() {
    groupForm.post(props.urls.groupStore, { preserveScroll: true, onSuccess: () => groupForm.reset() })
}
function toggleGroup(group: Group, event: Event) {
    const status = (event.target as HTMLInputElement).checked
    router.patch(`${props.urls.groupPrefix}/${group.id}`, { status }, { preserveScroll: true })
}
function dateText(value: string | null) {
    return value ? new Date(value).toLocaleString('th-TH') : '-'
}
</script>

<template>
    <Head :title="`${sectionInfo[section].title} ${lineLabel}`" />
    <AppLayout :title="`${sectionInfo[section].title} ${lineLabel}`" full-width>
        <div class="mx-auto max-w-7xl space-y-5 pb-6">
            <div v-if="page.props.flash?.success" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ page.props.flash.success }}</div>
            <div v-if="page.props.flash?.error" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-800">{{ page.props.flash.error }}</div>

            <header class="flex flex-wrap items-center justify-between gap-4 rounded-2xl px-5 py-5 text-white shadow-sm sm:px-7" :style="{ background: lineTheme.gradient }">
                <div>
                    <p class="text-sm text-slate-300">Cut Board · {{ lineLabel }}</p>
                    <h1 class="mt-1 text-2xl font-bold">{{ sectionInfo[section].title }}</h1>
                    <p class="mt-1 text-sm text-slate-300">{{ sectionInfo[section].description }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link :href="urls.overview" class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-slate-900 hover:bg-slate-100">กลับภาพรวมไลน์</Link>
                    <Link :href="urls.history" class="rounded-lg border border-white/20 px-3 py-2 text-sm hover:bg-white/10">ประวัติงาน</Link>
                    <Link :href="urls.employees" class="rounded-lg border border-white/20 px-3 py-2 text-sm hover:bg-white/10">พนักงาน</Link>
                    <Link :href="urls.groups" class="rounded-lg border border-white/20 px-3 py-2 text-sm hover:bg-white/10">กลุ่มพนักงาน</Link>
                    <Link :href="`${urls.overview}/daily-summary`" class="rounded-lg border border-white/20 px-3 py-2 text-sm hover:bg-white/10">ยอดรายวัน</Link>
                </div>
            </header>

            <section v-if="section === 'history'" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b px-5 py-4"><h2 class="font-bold text-slate-900">Job ที่ผ่านมา · {{ history.length }} รายการล่าสุด</h2></div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-slate-50 text-left text-slate-500"><tr><th class="px-5 py-3">Job</th><th class="px-5 py-3">วันที่ทำงาน</th><th class="px-5 py-3">สถานะ</th><th class="px-5 py-3 text-right">การทำงาน</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="job in history" :key="job.id">
                                <td class="px-5 py-3">{{ job.job_no }}</td><td class="px-5 py-3">{{ dateText(job.work_date) }}</td><td class="px-5 py-3">{{ job.status }}</td>
                                <td class="px-5 py-3 text-right"><Link :href="`${urls.jobShowPrefix}/${job.id}`" class="rounded-lg bg-emerald-600 px-3 py-2 font-semibold text-white">ดูรายการ Scan</Link></td>
                            </tr>
                            <tr v-if="history.length === 0"><td colspan="4" class="px-5 py-12 text-center text-slate-500">ยังไม่มีประวัติการทำงาน</td></tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section v-else-if="section === 'employees'" class="space-y-5">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-bold text-slate-900">เพิ่มรายชื่อพนักงาน</h2>
                    <form class="mt-4 space-y-3" @submit.prevent="saveEmployees">
                        <div v-for="(employee, index) in employeeForm.employees" :key="index" class="grid gap-2 sm:grid-cols-[1fr_1fr_auto]">
                            <input v-model="employee.name" class="rounded-lg border-slate-300" placeholder="ชื่อพนักงาน" required />
                            <input v-model="employee.remark" class="rounded-lg border-slate-300" placeholder="หมายเหตุ" />
                            <button type="button" class="rounded-lg border border-rose-200 px-3 text-rose-600" @click="removeEmployeeRow(index)">ลบแถว</button>
                        </div>
                        <div class="flex justify-between"><button type="button" class="text-sm font-semibold text-blue-700" @click="addEmployeeRow">＋ เพิ่มแถว</button><button type="submit" :disabled="employeeForm.processing" class="rounded-lg bg-blue-700 px-4 py-2 font-semibold text-white disabled:opacity-50">บันทึกรายชื่อ</button></div>
                    </form>
                </div>
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b px-5 py-4"><h2 class="font-bold text-slate-900">พนักงานใน {{ lineLabel }}</h2></div>
                    <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-left text-slate-500"><tr><th class="px-5 py-3">ชื่อ</th><th class="px-5 py-3">หมายเหตุ</th><th class="px-5 py-3">สถานะ</th><th class="px-5 py-3 text-right">การทำงาน</th></tr></thead><tbody class="divide-y divide-slate-100">
                        <tr v-for="employee in employees" :key="employee.id"><td class="px-5 py-3 font-medium">{{ employee.name }}</td><td class="px-5 py-3">{{ employee.remark || '-' }}</td><td class="px-5 py-3">{{ employee.status ? 'ใช้งาน' : 'ปิดใช้งาน' }}</td><td class="px-5 py-3 text-right"><button v-if="employee.status" type="button" class="text-rose-600 hover:underline" @click="disableEmployee(employee)">ปิดใช้งาน</button><span v-else class="text-slate-400">—</span></td></tr>
                        <tr v-if="employees.length === 0"><td colspan="4" class="px-5 py-12 text-center text-slate-500">ยังไม่มีรายชื่อพนักงาน</td></tr>
                    </tbody></table></div>
                </div>
            </section>

            <section v-else-if="section === 'groups'" class="space-y-5">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="font-bold text-slate-900">เพิ่มกลุ่มพนักงาน</h2>
                    <form class="mt-4 space-y-3" @submit.prevent="saveGroups">
                        <div v-for="(group, index) in groupForm.groups" :key="index" class="grid gap-2 sm:grid-cols-[1fr_1fr_1fr_auto]">
                            <select v-model="group.employee1" class="rounded-lg border-slate-300" required><option value="" disabled>พนักงานคนที่ 1</option><option v-for="employee in employees" :key="employee.id" :value="employee.id">{{ employee.name }}</option></select>
                            <select v-model="group.employee2" class="rounded-lg border-slate-300" required><option value="" disabled>พนักงานคนที่ 2</option><option v-for="employee in employees" :key="employee.id" :value="employee.id">{{ employee.name }}</option></select>
                            <input v-model="group.division" class="rounded-lg border-slate-300" placeholder="แผนก" required />
                            <button type="button" class="rounded-lg border border-rose-200 px-3 text-rose-600" @click="removeGroupRow(index)">ลบแถว</button>
                        </div>
                        <div class="flex justify-between"><button type="button" class="text-sm font-semibold text-blue-700" @click="addGroupRow">＋ เพิ่มแถว</button><button type="submit" :disabled="groupForm.processing" class="rounded-lg bg-orange-600 px-4 py-2 font-semibold text-white disabled:opacity-50">บันทึกกลุ่ม</button></div>
                    </form>
                </div>
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b px-5 py-4"><h2 class="font-bold text-slate-900">กลุ่มพนักงานใน {{ lineLabel }}</h2></div>
                    <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-left text-slate-500"><tr><th class="px-5 py-3">พนักงาน</th><th class="px-5 py-3">แผนก</th><th class="px-5 py-3">สถานะ</th><th class="px-5 py-3 text-right">เปิด/ปิด</th></tr></thead><tbody class="divide-y divide-slate-100">
                        <tr v-for="group in groups" :key="group.id"><td class="px-5 py-3">{{ group.employee1 }} – {{ group.employee2 }}</td><td class="px-5 py-3">{{ group.division }}</td><td class="px-5 py-3">{{ group.status ? 'เปิดใช้งาน' : 'ปิดใช้งาน' }}</td><td class="px-5 py-3 text-right"><input type="checkbox" :checked="Boolean(group.status)" aria-label="เปลี่ยนสถานะกลุ่ม" @change="toggleGroup(group, $event)" /></td></tr>
                        <tr v-if="groups.length === 0"><td colspan="4" class="px-5 py-12 text-center text-slate-500">ยังไม่มีกลุ่มพนักงาน</td></tr>
                    </tbody></table></div>
                </div>
            </section>

            <section v-else class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b px-5 py-4"><h2 class="font-bold text-slate-900">ยอด WIP ตามวันที่ผลิต · {{ lineLabel }}</h2></div>
                <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead class="bg-slate-50 text-left text-slate-500"><tr><th class="px-5 py-3">วันที่ผลิต</th><th class="px-5 py-3 text-right">จำนวน WIP</th></tr></thead><tbody class="divide-y divide-slate-100">
                    <tr v-for="row in daily" :key="row.work_date"><td class="px-5 py-3">{{ row.work_date }}</td><td class="px-5 py-3 text-right font-semibold tabular-nums">{{ Number(row.qty).toLocaleString() }}</td></tr>
                    <tr v-if="daily.length === 0"><td colspan="2" class="px-5 py-12 text-center text-slate-500">ยังไม่มีข้อมูลยอดรายวัน</td></tr>
                </tbody></table></div>
            </section>
        </div>
    </AppLayout>
</template>
