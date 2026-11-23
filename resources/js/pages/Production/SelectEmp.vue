<script setup lang="ts">
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import { computed, nextTick, onUnmounted, ref, watch } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { lineStyle } from '@/lib/cut-board-line'

type Quantity = number | string | null
type Job = { id: number; group_code: string; status: string; can_delete?: boolean; started_at: string | null; work_date: string; product_name: string | null; wip_count: number | string; grade_a?: Quantity; grade_b?: Quantity; grade_c?: Quantity }
type Employee = { id: number; name: string; remark: string | null }
type Group = { id: number; status: number; division: string; employee1: string; employee2: string }
type PastJob = { id: number; job_no: string; work_date: string; status: string }
type PastRow = PastJob & { group_code: string; product_name: string | null; wip_qty: number; fg_qty: number; grade_a: number; grade_b: number; grade_c: number }
type Paginated<T> = { data: T[]; current_page: number; last_page: number; per_page: number; total: number; from: number | null; to: number | null }

const props = withDefaults(defineProps<{
    line: string
    lineLabel: string
    worktime: number
    workDate: string
    jobs: Job[]
    employeeCount: number
    employees?: Employee[]
    groups?: Group[]
    history?: PastJob[]
    pastJobs?: Paginated<PastRow>
    daily?: Array<{ work_date: string; qty: number }>
    totals: { wip: number; fg: number; ng: number; hd: number }
    canEndShift: boolean
    canDeleteOpenJobs?: boolean
    urls: Record<string, string>
}>(), {
    employees: () => [],
    groups: () => [],
    history: () => [],
    daily: () => [],
})
const pageProps = usePage<{ flash?: { success?: string; error?: string } }>()

type Panel = 'employees' | 'groups' | 'history' | 'daily'
const modal = ref<Panel | 'endShift' | null>(null)
const modalDialog = ref<HTMLElement | null>(null)
const loadingPanel = ref<Panel | null>(null)

function openPanel(panel: Panel) {
    modal.value = panel
    loadingPanel.value = panel
    router.reload({
        only: panel === 'groups' ? ['groups', 'employees'] : [panel],
        onFinish: () => { loadingPanel.value = null },
    })
}
const jobForm = useForm({ group_code: '', work_date: props.workDate })
const employeeForm = useForm({ employees: [{ name: '', remark: '' }] })
const groupForm = useForm({ groups: [{ employee1: '', employee2: '', division: 'QC' }] })
const shiftForm = useForm({ end_tape: '', waste_qty: String(Math.max(0, Math.round(props.totals.ng))) })
const deleting = ref<number | null>(null)
const pastLoading = ref(false)
// ค้นหา + เปลี่ยนหน้า "รายการที่คัดผ่านมา" — ทำที่ server (หน้าละ 15) โหลดเฉพาะ prop pastJobs
const pastSearch = ref(typeof window !== 'undefined' ? new URLSearchParams(window.location.search).get('past_q') ?? '' : '')
function loadPastJobs(target: number) {
    pastLoading.value = true
    const query = pastSearch.value.trim()
    router.reload({
        only: ['pastJobs'],
        data: { past_page: target, past_q: query || undefined },
        onFinish: () => { pastLoading.value = false },
    })
}
function goPastPage(target: number) {
    if (!props.pastJobs || target < 1 || target > props.pastJobs.last_page || target === props.pastJobs.current_page) return
    loadPastJobs(target)
}
let pastSearchTimer: number | undefined
watch(pastSearch, () => {
    window.clearTimeout(pastSearchTimer)
    pastSearchTimer = window.setTimeout(() => loadPastJobs(1), 350)
})
const pastJobStatus: Record<string, { label: string; cls: string; dot: string }> = {
    draft: { label: 'รอเริ่มงาน', cls: 'border-[#D9A300] bg-[#FFF8E1] text-[#6B4E00]', dot: 'bg-[#F5B800]' },
    scanning: { label: 'กำลังคัด', cls: 'border-[#D9A300] bg-[#FFF8E1] text-[#6B4E00]', dot: 'bg-[#F5B800]' },
    qc_completed: { label: 'คัดเสร็จ', cls: 'border-[#7AA7D9] bg-[#EAF2FB] text-[#1E4E8C]', dot: 'bg-[#3B82F6]' },
    receiving: { label: 'กำลังรับเข้า', cls: 'border-[#7AA7D9] bg-[#EAF2FB] text-[#1E4E8C]', dot: 'bg-[#3B82F6]' },
    received: { label: 'รับเข้าแล้ว', cls: 'border-[#12A060] bg-[#E7F5EE] text-[#0B5E3C]', dot: 'bg-[#12A060]' },
    completed: { label: 'ปิดงานแล้ว', cls: 'border-[#12A060] bg-[#E7F5EE] text-[#0B5E3C]', dot: 'bg-[#12A060]' },
    cancelled: { label: 'ยกเลิก', cls: 'border-[#C81E1E] bg-[#FCECEA] text-[#B42318]', dot: 'bg-[#C81E1E]' },
}
function pastStatus(status: string) {
    return pastJobStatus[status] ?? { label: status, cls: 'border-[#A9B1BB] bg-[#F1F3F5] text-[#4A5563]', dot: 'bg-[#A9B1BB]' }
}
const jobSearch = ref('')
const pageSize = ref(10)
const page = ref(1)
const lineLinks = computed(() => ['L1', 'L2', 'L3', 'L4'].map((line) => ({ line, url: `${props.urls.linePrefix}/${line}` })))
const lineNumber = computed(() => props.line.replace(/\D/g, '').padStart(2, '0'))
// การ์ด "งานที่กำลังคัด" แสดงเฉพาะงานที่ยังไม่จบ (W) — งานที่คัดจบไปอยู่การ์ด "รายการที่คัดผ่านมา"
const runningJobs = computed(() => props.jobs.filter((job) => job.status === 'W'))
const filteredJobs = computed(() => {
    const search = jobSearch.value.trim().toLocaleLowerCase()
    if (!search) return runningJobs.value
    return runningJobs.value.filter((job) => [job.group_code, job.product_name, job.work_date, dateText(job.work_date)].some((value) => String(value ?? '').toLocaleLowerCase().includes(search)))
})
const pageCount = computed(() => Math.max(1, Math.ceil(filteredJobs.value.length / pageSize.value)))
const visibleJobs = computed(() => filteredJobs.value.slice((page.value - 1) * pageSize.value, page.value * pageSize.value))
const openJobsCount = computed(() => props.jobs.filter((job) => job.status === 'W').length)
const readouts = computed(() => [
    { th: 'งานทั้งหมด', en: 'Jobs', value: props.jobs.length, unit: 'งาน', cls: 'led-white' },
    { th: 'กำลังคัด', en: 'Running', value: openJobsCount.value, unit: 'งาน', cls: 'led-amber' },
    { th: 'คัดเสร็จ', en: 'Done', value: props.jobs.filter((job) => job.status === 'E').length, unit: 'งาน', cls: 'led-green' },
    { th: 'คัดแล้วทั้งหมด', en: 'FG Out', value: props.totals.fg, unit: 'แผ่น', cls: 'led-blue' },
])
const tools: Array<{ panel: Panel; label: string; en: string; icon: string }> = [
    { panel: 'history', label: 'รายการงานที่ผ่านมา', en: 'History', icon: 'ph-clock-counter-clockwise' },
    { panel: 'employees', label: 'รายชื่อพนักงาน', en: 'Employees', icon: 'ph-user' },
    { panel: 'groups', label: 'จัดกลุ่มพนักงาน', en: 'Groups', icon: 'ph-users-three' },
    { panel: 'daily', label: 'สรุปข้อมูลต่อวัน', en: 'Daily', icon: 'ph-chart-bar' },
]
const modalTitles: Record<string, string> = { employees: 'รายชื่อพนักงาน', groups: 'จัดกลุ่มพนักงาน', history: 'รายการงานที่ผ่านมา', daily: 'สรุปยอดรายวัน', endShift: 'จบกะการทำงาน' }
watch([jobSearch, pageSize], () => { page.value = 1 })
watch(pageCount, (count) => { page.value = Math.min(page.value, count) })
let modalOpener: HTMLElement | null = null
let previousBodyOverflow = ''
watch(modal, async (current, previous) => {
    if (current && !previous) {
        modalOpener = document.activeElement instanceof HTMLElement ? document.activeElement : null
        previousBodyOverflow = document.body.style.overflow
        document.body.style.overflow = 'hidden'
        await nextTick()
        modalDialog.value?.querySelector<HTMLElement>('button, input, select')?.focus()
    } else if (!current && previous) {
        document.body.style.overflow = previousBodyOverflow
        modalOpener?.focus()
    }
})
onUnmounted(() => {
    if (modal.value) document.body.style.overflow = previousBodyOverflow
    window.clearTimeout(pastSearchTimer)
})
function modalKeys(event: KeyboardEvent) {
    if (event.key === 'Escape') return closeModal()
    if (event.key !== 'Tab') return
    const elements = modalDialog.value?.querySelectorAll<HTMLElement>('button:not(:disabled), a[href], input:not(:disabled), select:not(:disabled)')
    if (!elements?.length) return
    const first = elements[0]
    const last = elements[elements.length - 1]
    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault()
        last.focus()
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault()
        first.focus()
    }
}

function openEndShift() {
    shiftForm.waste_qty = String(Math.max(0, Math.round(props.totals.ng)))
    modal.value = 'endShift'
}
function quantity(value: Quantity | undefined) {
    return value === null || value === undefined ? '—' : Number(value).toLocaleString('th-TH')
}
function dateText(value: string | null) {
    if (!value) return '-'
    const date = value.slice(0, 10)
    return /^\d{4}-\d{2}-\d{2}$/.test(date) ? date.split('-').reverse().join('-') : value
}
function statusText(status: string) {
    return status === 'W' ? 'กำลังคัด' : status === 'E' ? 'คัดเสร็จ' : status
}
function exportCsv() {
    // Overview report; the WMS interface CSV keeps its existing export flow.
    const rows = [
        ['กลุ่ม', 'ชนิดสินค้า', 'เกรด A', 'เกรด B', 'เกรด C', 'สถานะ', 'วันที่ผลิต'],
        ...props.jobs.map((job) => [job.group_code, job.product_name, job.grade_a, job.grade_b, job.grade_c, statusText(job.status), dateText(job.work_date)]),
    ]
    const csv = rows.map((row) => row.map((value) => {
        let text = String(value ?? '')
        if (typeof value === 'string' && /^[=+\-@\t\r]/.test(text)) text = "'" + text
        return '"' + text.replaceAll('"', '""') + '"'
    }).join(',')).join('\r\n')
    const url = URL.createObjectURL(new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' }))
    const link = document.createElement('a')
    link.href = url
    link.download = 'cut-board-' + props.line + '-' + props.workDate + '.csv'
    link.click()
    window.setTimeout(() => URL.revokeObjectURL(url), 1000)
}

function createJob() {
    jobForm.post(props.urls.storeJob, { preserveScroll: true, onSuccess: () => jobForm.reset('group_code') })
}

function deleteJob(jobId: number) {
    if (!window.confirm('ยืนยันลบงานที่ยังไม่จบ? รายการ WIP/FG และข้อมูลของ Job นี้จะถูกลบด้วย')) return
    deleting.value = jobId
    router.delete(`${props.urls.jobPrefix}/${jobId}`, { preserveScroll: true, onFinish: () => { deleting.value = null } })
}

function addEmployeeRow() { employeeForm.employees.push({ name: '', remark: '' }) }
function removeEmployeeRow(index: number) {
    if (employeeForm.employees.length > 1) employeeForm.employees.splice(index, 1)
}
function saveEmployees() {
    employeeForm.post(props.urls.storeEmployees, { preserveScroll: true, onSuccess: () => { employeeForm.reset(); modal.value = null } })
}
function disableEmployee(employee: Employee) {
    if (!window.confirm(`ปิดการใช้งาน ${employee.name}?`)) return
    router.delete(`${props.urls.employeePrefix}/${employee.id}`, { preserveScroll: true, only: ['employees', 'employeeCount'] })
}

function addGroupRow() { groupForm.groups.push({ employee1: '', employee2: '', division: 'QC' }) }
function removeGroupRow(index: number) {
    if (groupForm.groups.length > 1) groupForm.groups.splice(index, 1)
}
function saveGroups() {
    groupForm.post(props.urls.storeGroups, { preserveScroll: true, onSuccess: () => { groupForm.reset(); modal.value = null } })
}
function toggleGroup(group: Group, event: Event) {
    const status = (event.target as HTMLInputElement).checked
    router.patch(`${props.urls.groupPrefix}/${group.id}`, { status }, { preserveScroll: true, only: ['groups'] })
}
function endShift() {
    if (!window.confirm('ยืนยันปิดกะ? ระบบจะบันทึกยอด END TAPE และยอดเสีย')) return
    shiftForm.post(props.urls.endShift, { preserveScroll: true, onSuccess: () => { modal.value = null } })
}
function openHistory(job: PastJob) {
    router.get(`${props.urls.historyPrefix}/${job.id}`)
}
function closeModal() {
    modal.value = null
}
</script>

<template>
    <Head :title="'QC คัดบอร์ด ' + lineLabel" />
    <AppLayout :title="'QC คัดบอร์ด ' + lineLabel" full-width hide-header>
        <div class="hmi flex flex-col gap-4 pb-5" :style="lineStyle(line)">
            <div v-if="pageProps.props.flash?.success" role="status" class="flex items-center gap-2.5 border-l-4 border-[#12A060] bg-[#E7F5EE] px-4 py-3 text-sm font-medium text-[#0B5E3C]"><i class="ph ph-check-circle text-lg" aria-hidden="true"></i>{{ pageProps.props.flash.success }}</div>
            <div v-if="pageProps.props.flash?.error" role="alert" class="flex items-center gap-2.5 border-l-4 border-[#C81E1E] bg-[#FCECEA] px-4 py-3 text-sm font-medium text-[#B42318]"><i class="ph ph-x-circle text-lg" aria-hidden="true"></i>{{ pageProps.props.flash.error }}</div>

            <!-- ===================== LINE CONTROL PANEL ===================== -->
            <!-- ส่วนหัวติดด้านบนขณะเลื่อน (อยู่ใต้ topbar ของธีม) — ต้องเป็นลูกตรงของ .hmi เพื่อให้ sticky ได้ตลอดทั้งหน้า -->
            <header aria-label="แผงควบคุมไลน์" class="sticky top-14 z-40 overflow-hidden rounded-t-md bg-[var(--hmi-surface)] shadow-[0_6px_16px_-8px_rgba(0,0,0,.35)] ring-1 ring-inset ring-[var(--hmi-line)] lg:top-[78px]">
                <div class="hazard" aria-hidden="true"></div>

                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-[var(--hmi-line)] px-5 py-4">
                    <div class="flex min-w-0 flex-wrap items-center gap-4">
                        <div class="flex flex-col items-center rounded bg-[var(--line-color)] px-3 py-1.5 leading-none text-white ring-1 ring-inset ring-white/25">
                            <span class="tag text-[11px] font-semibold">Line</span>
                            <span class="mono text-[28px] font-extrabold">{{ lineNumber }}</span>
                        </div>
                        <div class="flex min-w-0 flex-col gap-1">
                            <span class="text-xs text-[var(--hmi-mute)]">ระบบ QC · คัดบอร์ด &nbsp;/&nbsp; รอบการทำงาน {{ worktime + 1 }}</span>
                            <h1 class="m-0 text-[22px] font-bold text-[var(--hmi-title)]">ภาพรวม{{ lineLabel }}</h1>
                        </div>
                        <div class="flex items-center gap-2 rounded border border-[var(--hmi-line)] bg-[var(--hmi-screen)] px-3 py-1.5">
                            <span class="lamp" :class="openJobsCount ? 'lamp-green' : 'lamp-off'" aria-hidden="true"></span>
                            <span class="tag text-[15px] font-bold" :class="openJobsCount ? 'text-[#047857]' : 'text-[#5B6573]'">{{ openJobsCount ? 'Run' : 'Idle' }}</span>
                            <span class="text-xs text-[var(--hmi-text)]">{{ openJobsCount ? 'เหลือ ' + openJobsCount + ' งานที่ยังไม่จบ' : 'ไม่มีงานค้างในรอบนี้' }}</span>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-2">
                        <Link :href="urls.gradeC ?? `/cut-boards/lines/${line}/grade-c`" class="inline-flex min-h-11 items-center gap-2 rounded border border-[var(--line-color)] bg-[var(--line-color)] px-4 text-sm font-bold text-white shadow-sm hover:brightness-95">
                            <i class="ph ph-scissors" aria-hidden="true"></i> คัดแผ่น C
                        </Link>
                        <Link :href="urls.endLineQc ?? `/end-line-qc/${line}`" class="inline-flex min-h-11 items-center gap-2 rounded border border-[#8B5CF6] bg-[#8B5CF6] px-4 text-sm font-bold text-white shadow-sm hover:brightness-95">
                            <i class="ph ph-tag" aria-hidden="true"></i> ติดแท็กออนไลน์
                        </Link>
                        <button type="button" class="inline-flex min-h-11 cursor-pointer items-center gap-1.5 rounded border border-[var(--hmi-line)] px-4 text-sm font-medium text-[var(--hmi-text)] hover:bg-[var(--hmi-panel)] disabled:cursor-not-allowed disabled:opacity-40"
                                :disabled="!jobs.length" title="ดาวน์โหลดรายงานรายการงานในรอบนี้" @click="exportCsv">
                            <i class="ph ph-download-simple" aria-hidden="true"></i> Export CSV
                        </button>
                        <button type="button" class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded border-2 border-[var(--hmi-yellow)] bg-[#C81E1E] px-4.5 text-sm font-bold text-white hover:bg-[#A31717] disabled:cursor-not-allowed disabled:opacity-45"
                                :disabled="!canEndShift" :title="canEndShift ? 'ตรวจยอดและบันทึก END TAPE ก่อนปิดกะ' : 'ต้องจบงานคัดที่เปิดอยู่ทั้งหมดก่อน'" @click="openEndShift">
                            <i class="ph-fill ph-stop" aria-hidden="true"></i> จบการทำงาน
                        </button>
                    </div>
                </div>

                <!-- เปลี่ยนไลน์ -->
                <nav aria-label="เลือกไลน์ผลิต" class="flex flex-wrap items-center gap-2 border-b border-[var(--hmi-line)] px-5 py-3">
                    <span class="tag mr-1 text-[13px] text-[var(--hmi-mute)]">Line</span>
                    <Link v-for="item in lineLinks" :key="item.line" :href="item.url" :style="lineStyle(item.line)" :aria-current="item.line === line ? 'page' : undefined"
                          class="inline-flex min-h-9 items-center gap-2 rounded border px-3 text-sm font-bold"
                          :class="item.line === line ? 'border-[var(--hmi-yellow)] bg-[var(--line-color)] text-white' : 'border-[var(--hmi-line)] text-[var(--hmi-text)] hover:bg-[var(--hmi-panel)]'">
                        <span class="size-2.5 rounded-full bg-[var(--line-color)] ring-1 ring-white/50" aria-hidden="true"></span>ไลน์ {{ item.line.slice(1) }}
                    </Link>
                </nav>
            </header>

            <!-- ตัวเลขสรุป — ต่อจากส่วนหัว (-mt-4 หักระยะ gap ของ flex ให้ติดกัน) -->
            <section aria-label="ยอดสรุปไลน์" class="-mt-4 overflow-hidden rounded-b-md bg-[var(--hmi-surface)] ring-1 ring-inset ring-[var(--hmi-line)]">
                <!-- LED readouts -->
                <div class="grid grid-cols-2 gap-3 px-5 pb-5 pt-4 lg:grid-cols-4">
                    <div v-for="readout in readouts" :key="readout.en" class="flex flex-col gap-1 rounded border border-[var(--hmi-line)] bg-[var(--hmi-screen)] px-3.5 py-3">
                        <span class="flex justify-between text-xs text-[var(--hmi-mute)]"><span>{{ readout.th }}</span><span class="tag">{{ readout.en }}</span></span>
                        <span class="led" :class="readout.cls">{{ quantity(readout.value) }}</span>
                        <span class="text-[11px] text-[var(--hmi-mute)]">{{ readout.unit }}</span>
                    </div>
                </div>
            </section>

            <!-- ===================== เริ่มงานใหม่ ===================== -->
            <section aria-labelledby="new-job" class="overflow-hidden rounded-md border border-[var(--hmi-steel)] bg-white">
                <div class="flex items-center gap-3 bg-[var(--hmi-panel)] px-4 py-2.5">
                    <span class="mono rounded-sm bg-[var(--hmi-yellow)] px-2 py-0.5 text-[13px] font-extrabold text-[var(--hmi-ink)]">NEW</span>
                    <div class="flex flex-col">
                        <h2 id="new-job" class="m-0 text-[17px] font-bold text-[var(--hmi-title)]">เริ่มงานใหม่</h2>
                        <span class="tag text-xs text-[var(--hmi-mute)]">Start Job</span>
                    </div>
                </div>
                <form class="flex flex-wrap items-end gap-4 bg-[#F7F8F9] p-4" @submit.prevent="createJob">
                    <fieldset class="m-0 flex flex-col gap-1.5 border-0 p-0">
                        <legend class="mb-1.5 p-0 text-[13px] font-semibold">กลุ่ม <span class="text-[#C81E1E]" aria-hidden="true">*</span></legend>
                        <div class="flex gap-2">
                            <button v-for="group in ['A', 'B', 'T']" :key="group" type="button" :disabled="jobForm.processing" :aria-pressed="jobForm.group_code === group"
                                    class="inline-flex min-h-11 min-w-16 cursor-pointer items-center justify-center gap-1.5 rounded border-2 px-4 text-[15px] font-bold transition-colors disabled:cursor-not-allowed"
                                    :class="jobForm.group_code === group ? 'border-[var(--hmi-yellow)] bg-[var(--hmi-ink)] text-[var(--hmi-yellow)]' : 'border-[#A9B1BB] bg-white hover:border-[var(--hmi-ink)] hover:bg-[#F1F3F5]'"
                                    @click="jobForm.group_code = group">
                                <i v-if="jobForm.group_code === group" class="ph ph-check-circle" aria-hidden="true"></i>กลุ่ม {{ group }}
                            </button>
                        </div>
                        <span v-if="jobForm.errors.group_code" class="text-xs text-[#B42318]">{{ jobForm.errors.group_code }}</span>
                    </fieldset>
                    <label class="flex w-full flex-col gap-1.5 text-[13px] font-semibold sm:w-52">วันที่
                        <input v-model="jobForm.work_date" type="date" class="field" :disabled="jobForm.processing" required />
                        <span v-if="jobForm.errors.work_date" class="text-xs font-normal text-[#B42318]">{{ jobForm.errors.work_date }}</span>
                    </label>
                    <button type="submit" :disabled="jobForm.processing || !jobForm.group_code || !jobForm.work_date"
                            class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded bg-[var(--hmi-yellow)] px-6 text-[15px] font-bold text-[var(--hmi-ink)] hover:bg-[#DCA500] disabled:cursor-not-allowed disabled:opacity-45">
                        <i class="ph ph-play" aria-hidden="true"></i>{{ jobForm.processing ? 'กำลังเริ่มงาน…' : 'เริ่มงานใหม่' }}
                    </button>
                    <p class="m-0 w-full text-xs text-[#4A5563]">ระบบจะระบุชนิดสินค้าจาก WIP รายการแรกที่สแกนเข้า</p>
                </form>
            </section>

            <!-- ===================== รายการงานวันนี้ ===================== -->
            <section aria-labelledby="job-list" class="overflow-hidden rounded-md border border-[var(--hmi-steel)] bg-white">
                <div class="flex flex-wrap items-center justify-between gap-3 bg-[var(--hmi-panel)] px-4 py-2.5">
                    <div class="flex items-center gap-3">
                        <span class="mono rounded-sm bg-[var(--hmi-yellow)] px-2 py-0.5 text-[13px] font-extrabold text-[var(--hmi-ink)]">RUN</span>
                        <div class="flex flex-col">
                            <h2 id="job-list" class="m-0 text-[17px] font-bold text-[var(--hmi-title)]">งานที่กำลังคัด</h2>
                            <span class="tag text-xs text-[var(--hmi-mute)]">Running · {{ runningJobs.length }} งานที่ยังไม่จบ</span>
                        </div>
                    </div>
                    <label class="flex w-full items-center gap-2 sm:w-auto">
                        <span class="sr-only">ค้นหา</span>
                        <span class="flex h-10 w-full items-center gap-2 rounded border border-[var(--hmi-line)] bg-white px-3 sm:w-64">
                            <i class="ph ph-magnifying-glass text-[#4A5563]" aria-hidden="true"></i>
                            <input v-model="jobSearch" type="search" class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm focus:outline-none focus:ring-0" placeholder="ค้นหากลุ่มหรือชนิดสินค้า" />
                        </span>
                    </label>
                </div>
                <div class="overflow-x-auto">
                    <table class="tbl min-w-[820px]">
                        <thead><tr>
                            <th>#</th><th>กลุ่ม</th><th class="w-[40%]">ชนิดสินค้า</th>
                            <th class="text-center!">A / B / C <span class="sr-only">(จำนวนแผ่น)</span></th>
                            <th>สถานะ</th><th>วันที่</th><th><span class="sr-only">การทำงาน</span></th>
                        </tr></thead>
                        <tbody>
                            <tr v-for="(job, index) in visibleJobs" :key="job.id">
                                <td class="mono text-[#4A5563]">{{ (page - 1) * pageSize + index + 1 }}</td>
                                <td><span class="mono inline-flex size-8 items-center justify-center rounded bg-[#F1F3F5] text-[15px] font-extrabold">{{ job.group_code }}</span></td>
                                <td class="whitespace-normal! font-medium">{{ job.product_name || 'ยังไม่ระบุชนิดสินค้า' }}</td>
                                <td class="mono text-center text-[15px] font-bold" :aria-label="'เกรด A ' + quantity(job.grade_a) + ', เกรด B ' + quantity(job.grade_b) + ', เกรด C ' + quantity(job.grade_c)">
                                    <span class="text-[#0B6A43]">{{ quantity(job.grade_a) }}</span><span class="mx-1.5 text-[#A9B1BB]">/</span><span class="text-[#B45309]">{{ quantity(job.grade_b) }}</span><span class="mx-1.5 text-[#A9B1BB]">/</span><span class="text-[#B42318]">{{ quantity(job.grade_c) }}</span>
                                </td>
                                <td>
                                    <span class="inline-flex items-center gap-1.5 rounded-sm border px-2 py-0.5 text-xs font-bold"
                                          :class="job.status === 'W' ? 'border-[#D9A300] bg-[#FFF8E1] text-[#6B4E00]' : 'border-[#12A060] bg-[#E7F5EE] text-[#0B5E3C]'">
                                        <span class="size-2 rounded-full" :class="job.status === 'W' ? 'bg-[#F5B800]' : 'bg-[#12A060]'" aria-hidden="true"></span>{{ statusText(job.status) }}
                                    </span>
                                </td>
                                <td class="mono">{{ dateText(job.work_date) }}</td>
                                <td class="text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <Link :href="urls.workPrefix + '/' + job.id" class="inline-flex min-h-9 items-center gap-1.5 rounded bg-[var(--hmi-ink)] px-3.5 text-[13px] font-bold text-white hover:opacity-90">
                                            เปิดงาน <i class="ph ph-caret-right" aria-hidden="true"></i>
                                        </Link>
                                        <button v-if="canDeleteOpenJobs && job.status === 'W' && job.can_delete" type="button" class="inline-flex min-h-9 cursor-pointer items-center rounded border border-[#C81E1E] bg-white px-2.5 text-[#B42318] hover:bg-[#FCECEA] disabled:opacity-50"
                                                :disabled="deleting === job.id" :aria-label="'ลบงานกลุ่ม ' + job.group_code" title="ลบงานที่ยังไม่จบ" @click="deleteJob(job.id)"><i class="ph ph-trash" aria-hidden="true"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!filteredJobs.length"><td colspan="7" class="py-12! text-center text-[#4A5563]"><i class="ph ph-clipboard-text mb-2 block text-3xl" aria-hidden="true"></i><p class="m-0 font-semibold text-[#1B2129]">{{ jobSearch ? 'ไม่พบงานที่ค้นหา' : 'ไม่มีงานที่กำลังคัด' }}</p><p v-if="!jobSearch" class="m-0 mt-1 text-sm">เลือกกลุ่มและวันที่ แล้วกดเริ่มงานใหม่ด้านบน</p></td></tr>
                        </tbody>
                    </table>
                </div>
                <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-[var(--hmi-steel)] px-4 py-2.5 text-[13px] text-[#4A5563]">
                    <p class="m-0">แสดง {{ visibleJobs.length }} จาก {{ filteredJobs.length }} รายการ</p>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-2"><span class="sr-only">จำนวนรายการต่อหน้า</span><select v-model.number="pageSize" class="field h-9! w-auto! text-sm!"><option :value="10">10 / หน้า</option><option :value="25">25 / หน้า</option><option :value="50">50 / หน้า</option></select></label>
                        <div v-if="pageCount > 1" class="inline-flex items-center gap-2">
                            <button type="button" class="inline-flex size-9 cursor-pointer items-center justify-center rounded border border-[#A9B1BB] bg-white hover:bg-[#F1F3F5] disabled:opacity-40" :disabled="page <= 1" aria-label="หน้าก่อนหน้า" @click="page--"><i class="ph ph-caret-left" aria-hidden="true"></i></button>
                            <span class="mono">{{ page }} / {{ pageCount }}</span>
                            <button type="button" class="inline-flex size-9 cursor-pointer items-center justify-center rounded border border-[#A9B1BB] bg-white hover:bg-[#F1F3F5] disabled:opacity-40" :disabled="page >= pageCount" aria-label="หน้าถัดไป" @click="page++"><i class="ph ph-caret-right" aria-hidden="true"></i></button>
                        </div>
                    </div>
                </footer>
            </section>

            <!-- ===================== รายการที่คัดผ่านมา (paginate 15) ===================== -->
            <section v-if="pastJobs" aria-labelledby="past-jobs" class="overflow-hidden rounded-md border-2 border-[#DCA500] bg-white shadow-md">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b-2 border-[#DCA500] bg-[#FFF8E1] px-4 py-2.5">
                    <div class="flex items-center gap-3">
                        <span class="mono rounded-sm bg-[var(--hmi-yellow)] px-2 py-0.5 text-[13px] font-extrabold text-[var(--hmi-ink)]">PAST</span>
                        <div class="flex flex-col">
                            <h2 id="past-jobs" class="m-0 text-[17px] font-bold text-[var(--hmi-title)]">รายการที่คัดผ่านมา</h2>
                            <span class="tag text-xs text-[var(--hmi-mute)]">Completed · งานที่คัดจบแล้ว {{ quantity(pastJobs.total) }} รายการ</span>
                        </div>
                    </div>
                    <div class="flex w-full items-center gap-3 sm:w-auto">
                        <span v-if="pastLoading" role="status" class="inline-flex shrink-0 items-center gap-1.5 text-xs text-[var(--hmi-mute)]"><i class="ph ph-spinner animate-spin" aria-hidden="true"></i>กำลังโหลด…</span>
                        <label class="flex w-full items-center sm:w-auto">
                            <span class="sr-only">ค้นหารายการที่คัดผ่านมา</span>
                            <span class="flex h-10 w-full items-center gap-2 rounded border border-[var(--hmi-line)] bg-white px-3 sm:w-72">
                                <i class="ph ph-magnifying-glass text-[#4A5563]" aria-hidden="true"></i>
                                <input v-model="pastSearch" type="search" class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm focus:outline-none focus:ring-0" placeholder="ค้นหา เลข Job, สินค้า, กลุ่ม, วันที่ (dd-mm-yyyy)" />
                            </span>
                        </label>
                    </div>
                </div>
                <div class="overflow-x-auto" :class="{ 'opacity-60': pastLoading }">
                    <table class="tbl min-w-[900px]">
                        <thead><tr>
                            <th>#</th><th>วันที่</th><th>กลุ่ม</th><th class="w-[34%]">ชนิดสินค้า</th>
                            <th class="text-right!">WIP</th><th class="text-center!">A / B / C <span class="sr-only">(จำนวนแผ่น)</span></th>
                            <th>สถานะ</th><th><span class="sr-only">การทำงาน</span></th>
                        </tr></thead>
                        <tbody>
                            <tr v-for="(job, index) in pastJobs.data" :key="job.id">
                                <td class="mono text-[#4A5563]">{{ (pastJobs.from ?? 1) + index }}</td>
                                <td class="mono">{{ dateText(job.work_date) }}</td>
                                <td><span class="mono inline-flex size-8 items-center justify-center rounded bg-[#F1F3F5] text-[15px] font-extrabold">{{ job.group_code }}</span></td>
                                <td class="whitespace-normal!">
                                    <div class="font-medium">{{ job.product_name || 'ยังไม่ระบุชนิดสินค้า' }}</div>
                                    <div class="mono text-[11px] text-[#4A5563]">{{ job.job_no }}</div>
                                </td>
                                <td class="mono text-right text-[15px] font-bold">{{ quantity(job.wip_qty) }}</td>
                                <td class="mono text-center text-[15px] font-bold" :aria-label="'เกรด A ' + quantity(job.grade_a) + ', เกรด B ' + quantity(job.grade_b) + ', เกรด C ' + quantity(job.grade_c)">
                                    <span class="text-[#0B6A43]">{{ quantity(job.grade_a) }}</span><span class="mx-1.5 text-[#A9B1BB]">/</span><span class="text-[#B45309]">{{ quantity(job.grade_b) }}</span><span class="mx-1.5 text-[#A9B1BB]">/</span><span class="text-[#B42318]">{{ quantity(job.grade_c) }}</span>
                                </td>
                                <td>
                                    <span class="inline-flex items-center gap-1.5 rounded-sm border px-2 py-0.5 text-xs font-bold" :class="pastStatus(job.status).cls">
                                        <span class="size-2 rounded-full" :class="pastStatus(job.status).dot" aria-hidden="true"></span>{{ pastStatus(job.status).label }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <Link :href="urls.historyPrefix + '/' + job.id" class="inline-flex min-h-9 items-center gap-1.5 rounded border border-[var(--hmi-ink)] bg-white px-3.5 text-[13px] font-bold text-[var(--hmi-ink)] hover:bg-[var(--hmi-surface)]">
                                        เปิดดู <i class="ph ph-caret-right" aria-hidden="true"></i>
                                    </Link>
                                </td>
                            </tr>
                            <tr v-if="!pastJobs.data.length"><td colspan="8" class="py-12! text-center text-[#4A5563]"><i class="ph ph-clock-counter-clockwise mb-2 block text-3xl" aria-hidden="true"></i><p class="m-0 font-semibold text-[#1B2129]">{{ pastSearch.trim() ? "ไม่พบงานที่ค้นหา" : "ยังไม่มีงานที่คัดจบ" }}</p></td></tr>
                        </tbody>
                    </table>
                </div>
                <footer class="flex flex-wrap items-center justify-between gap-3 border-t border-[var(--hmi-steel)] px-4 py-2.5 text-[13px] text-[#4A5563]">
                    <p class="m-0">แสดง {{ pastJobs.from ?? 0 }}–{{ pastJobs.to ?? 0 }} จาก {{ quantity(pastJobs.total) }} รายการ · หน้าละ {{ pastJobs.per_page }}</p>
                    <nav v-if="pastJobs.last_page > 1" aria-label="เปลี่ยนหน้ารายการที่คัดผ่านมา" class="inline-flex items-center gap-2">
                        <button type="button" class="inline-flex size-9 cursor-pointer items-center justify-center rounded border border-[#A9B1BB] bg-white hover:bg-[#F1F3F5] disabled:cursor-not-allowed disabled:opacity-40" :disabled="pastLoading || pastJobs.current_page <= 1" aria-label="หน้าแรก" @click="goPastPage(1)"><i class="ph ph-caret-double-left" aria-hidden="true"></i></button>
                        <button type="button" class="inline-flex size-9 cursor-pointer items-center justify-center rounded border border-[#A9B1BB] bg-white hover:bg-[#F1F3F5] disabled:cursor-not-allowed disabled:opacity-40" :disabled="pastLoading || pastJobs.current_page <= 1" aria-label="หน้าก่อนหน้า" @click="goPastPage(pastJobs.current_page - 1)"><i class="ph ph-caret-left" aria-hidden="true"></i></button>
                        <span class="mono min-w-16 text-center">{{ pastJobs.current_page }} / {{ pastJobs.last_page }}</span>
                        <button type="button" class="inline-flex size-9 cursor-pointer items-center justify-center rounded border border-[#A9B1BB] bg-white hover:bg-[#F1F3F5] disabled:cursor-not-allowed disabled:opacity-40" :disabled="pastLoading || pastJobs.current_page >= pastJobs.last_page" aria-label="หน้าถัดไป" @click="goPastPage(pastJobs.current_page + 1)"><i class="ph ph-caret-right" aria-hidden="true"></i></button>
                        <button type="button" class="inline-flex size-9 cursor-pointer items-center justify-center rounded border border-[#A9B1BB] bg-white hover:bg-[#F1F3F5] disabled:cursor-not-allowed disabled:opacity-40" :disabled="pastLoading || pastJobs.current_page >= pastJobs.last_page" aria-label="หน้าสุดท้าย" @click="goPastPage(pastJobs.last_page)"><i class="ph ph-caret-double-right" aria-hidden="true"></i></button>
                    </nav>
                </footer>
            </section>

            <!-- ===================== เงื่อนไขจบการทำงาน ===================== -->
            <section aria-labelledby="end-rules" class="border-l-4 border-[var(--hmi-yellow)] bg-[#FFF8E1] px-4 py-3 text-[#6B4E00]">
                <h2 id="end-rules" class="m-0 mb-1.5 flex items-center gap-2 text-sm font-bold"><i class="ph ph-warning" aria-hidden="true"></i>เงื่อนไขก่อนจบการทำงาน</h2>
                <ol class="m-0 grid list-none gap-1 p-0 text-[13px] md:grid-cols-3">
                    <li>1. คลังสินค้าต้องสแกนข้อมูลครบก่อน</li>
                    <li>2. คลังสินค้าต้องออก CSV ก่อน</li>
                    <li>3. ต้องจบการทำงานทุกตัว</li>
                </ol>
            </section>

            <!-- ===================== เครื่องมือประจำไลน์ ===================== -->
            <nav class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="เครื่องมือประจำไลน์">
                <button v-for="tool in tools" :key="tool.panel" type="button"
                        class="group flex cursor-pointer items-center gap-3 rounded-md border border-[var(--hmi-steel)] bg-white p-3.5 text-left transition-colors hover:border-[var(--hmi-ink)] hover:bg-[#F7F8F9]"
                        @click="openPanel(tool.panel)">
                    <span class="inline-flex size-11 shrink-0 items-center justify-center rounded bg-[var(--hmi-panel)] text-xl text-[var(--hmi-ink)]"><i class="ph" :class="tool.icon" aria-hidden="true"></i></span>
                    <span class="flex min-w-0 flex-col">
                        <span class="text-[15px] font-bold">{{ tool.label }}</span>
                        <span class="tag text-[11px] text-[#4A5563]">{{ tool.en }}</span>
                    </span>
                    <i class="ph ph-caret-right ml-auto text-[#A9B1BB] group-hover:text-[var(--hmi-ink)]" aria-hidden="true"></i>
                </button>
            </nav>
        </div>

        <!-- ===================== Dialog ===================== -->
        <Teleport to="body">
        <div v-if="modal" class="hmi fixed inset-0 z-[10000] flex items-center justify-center bg-[#0E1318]/70 p-4" :style="lineStyle(line)" @click.self="closeModal" @keydown="modalKeys">
            <section ref="modalDialog" role="dialog" aria-modal="true" :aria-label="modalTitles[modal]" class="max-h-[90dvh] w-full max-w-4xl overflow-y-auto rounded-md bg-white shadow-2xl">
                <div class="sticky top-0 z-[1]">
                    <div class="hazard" aria-hidden="true"></div>
                    <header class="flex items-center justify-between gap-3 bg-[var(--hmi-surface)] px-5 py-3.5">
                        <h2 class="m-0 text-lg font-bold text-[var(--hmi-title)]">{{ modalTitles[modal] }} <span class="ml-1 text-sm font-medium text-[var(--hmi-mute)]">· {{ lineLabel }}</span></h2>
                        <button type="button" class="cursor-pointer rounded px-3 py-1 text-sm text-[var(--hmi-mute)] hover:bg-[var(--hmi-panel)] hover:text-[var(--hmi-title)]" @click="closeModal">ปิด ✕</button>
                    </header>
                </div>

                <div v-if="loadingPanel === modal" class="p-8 text-center text-[#4A5563]" role="status">กำลังโหลดข้อมูล...</div>

                <div v-else-if="modal === 'employees'" class="flex flex-col gap-5 p-5">
                    <form class="flex flex-col gap-3 rounded bg-[#F7F8F9] p-4" @submit.prevent="saveEmployees">
                        <div v-for="(employee, index) in employeeForm.employees" :key="index" class="grid gap-2 sm:grid-cols-[1fr_1fr_auto]">
                            <input v-model="employee.name" class="field" placeholder="ชื่อพนักงาน" required />
                            <input v-model="employee.remark" class="field" placeholder="หมายเหตุ" />
                            <button type="button" class="min-h-11 cursor-pointer rounded border border-[#C81E1E] bg-white px-3 text-sm text-[#B42318] hover:bg-[#FCECEA]" @click="removeEmployeeRow(index)">ลบแถว</button>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <button type="button" class="cursor-pointer text-sm font-bold text-[var(--hmi-ink)] hover:underline" @click="addEmployeeRow">＋ เพิ่มรายชื่อ</button>
                            <button type="submit" :disabled="employeeForm.processing" class="min-h-11 cursor-pointer rounded bg-[var(--hmi-yellow)] px-5 text-sm font-bold text-[var(--hmi-ink)] hover:bg-[#DCA500] disabled:opacity-50">บันทึกรายชื่อ</button>
                        </div>
                    </form>
                    <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>ชื่อ</th><th>หมายเหตุ</th><th><span class="sr-only">จัดการ</span></th></tr></thead><tbody>
                        <tr v-for="employee in employees" :key="employee.id"><td class="font-medium">{{ employee.name }}</td><td>{{ employee.remark || '-' }}</td><td class="text-right"><button type="button" class="min-h-[34px] cursor-pointer rounded border border-[#C81E1E] bg-white px-2.5 text-[13px] font-semibold text-[#B42318] hover:bg-[#FCECEA]" @click="disableEmployee(employee)">ปิดใช้งาน</button></td></tr>
                        <tr v-if="employees.length === 0"><td colspan="3" class="py-8! text-center text-[#4A5563]">ยังไม่มีรายชื่อพนักงาน</td></tr>
                    </tbody></table></div>
                </div>

                <div v-else-if="modal === 'groups'" class="flex flex-col gap-5 p-5">
                    <form class="flex flex-col gap-3 rounded bg-[#F7F8F9] p-4" @submit.prevent="saveGroups">
                        <div v-for="(group, index) in groupForm.groups" :key="index" class="grid gap-2 sm:grid-cols-[1fr_1fr_1fr_auto]">
                            <select v-model="group.employee1" class="field" required><option value="" disabled>พนักงานคนที่ 1</option><option v-for="e in employees" :key="e.id" :value="e.id">{{ e.name }}</option></select>
                            <select v-model="group.employee2" class="field" required><option value="" disabled>พนักงานคนที่ 2</option><option v-for="e in employees" :key="e.id" :value="e.id">{{ e.name }}</option></select>
                            <input v-model="group.division" class="field" placeholder="แผนก" required />
                            <button type="button" class="min-h-11 cursor-pointer rounded border border-[#C81E1E] bg-white px-3 text-sm text-[#B42318] hover:bg-[#FCECEA]" @click="removeGroupRow(index)">ลบ</button>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <button type="button" class="cursor-pointer text-sm font-bold text-[var(--hmi-ink)] hover:underline" @click="addGroupRow">＋ เพิ่มกลุ่ม</button>
                            <button type="submit" :disabled="groupForm.processing" class="min-h-11 cursor-pointer rounded bg-[var(--hmi-yellow)] px-5 text-sm font-bold text-[var(--hmi-ink)] hover:bg-[#DCA500] disabled:opacity-50">บันทึกกลุ่ม</button>
                        </div>
                    </form>
                    <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>พนักงาน</th><th>กลุ่ม/แผนก</th><th>สถานะ</th></tr></thead><tbody>
                        <tr v-for="group in groups" :key="group.id">
                            <td class="font-medium">{{ group.employee1 }} - {{ group.employee2 }}</td>
                            <td>{{ group.division }}</td>
                            <td><label class="inline-flex cursor-pointer items-center gap-2"><input type="checkbox" class="size-4 accent-[var(--line-color)]" :checked="Boolean(Number(group.status))" @change="toggleGroup(group, $event)" />
                                <span class="inline-flex items-center gap-1.5 text-[13px] font-semibold"><span class="lamp size-2.5!" :class="Number(group.status) ? 'lamp-green' : 'lamp-off'" aria-hidden="true"></span>{{ Number(group.status) ? 'เปิด' : 'ปิด' }}</span></label></td>
                        </tr>
                        <tr v-if="groups.length === 0"><td colspan="3" class="py-8! text-center text-[#4A5563]">ยังไม่มีกลุ่มพนักงาน</td></tr>
                    </tbody></table></div>
                </div>

                <div v-else-if="modal === 'history'" class="flex flex-col gap-4 p-5">
                    <div class="flex flex-wrap gap-2">
                        <Link v-for="item in lineLinks" :key="item.line" :href="item.url" :style="lineStyle(item.line)" class="inline-flex min-h-9 items-center gap-2 rounded border px-3 text-sm font-bold"
                              :class="item.line === line ? 'border-[var(--line-color)] bg-[var(--line-color)] text-white' : 'border-[#A9B1BB] bg-white hover:bg-[#F1F3F5]'">
                            <span class="size-2.5 rounded-full bg-[var(--line-color)] ring-1 ring-white/60" aria-hidden="true"></span>ไลน์ {{ item.line.slice(1) }}
                        </Link>
                    </div>
                    <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>Job</th><th>วันที่ทำงาน</th><th>สถานะ</th><th><span class="sr-only">จัดการ</span></th></tr></thead><tbody>
                        <tr v-for="job in history" :key="job.id"><td class="mono font-bold">{{ job.job_no }}</td><td class="mono">{{ dateText(job.work_date) }}</td><td>{{ job.status }}</td>
                            <td class="text-right"><button type="button" class="inline-flex min-h-9 cursor-pointer items-center gap-1.5 rounded bg-[var(--hmi-ink)] px-3.5 text-[13px] font-bold text-white hover:opacity-90" @click="openHistory(job)">เปิดประวัติ <i class="ph ph-caret-right" aria-hidden="true"></i></button></td></tr>
                        <tr v-if="history.length === 0"><td colspan="4" class="py-8! text-center text-[#4A5563]">ยังไม่มีงานที่ผ่านมา</td></tr>
                    </tbody></table></div>
                </div>

                <div v-else-if="modal === 'daily'" class="p-5">
                    <div class="overflow-x-auto"><table class="tbl"><thead><tr><th>วันที่</th><th class="text-right!">จำนวน WIP</th></tr></thead><tbody>
                        <tr v-for="row in daily" :key="row.work_date"><td class="mono">{{ row.work_date }}</td><td class="mono text-right text-base font-extrabold">{{ Number(row.qty).toLocaleString() }}</td></tr>
                        <tr v-if="daily.length === 0"><td colspan="2" class="py-8! text-center text-[#4A5563]">ยังไม่มีข้อมูล</td></tr>
                    </tbody></table></div>
                </div>

                <form v-else class="flex flex-col gap-4 p-5" @submit.prevent="endShift">
                    <p class="m-0 flex items-center gap-2.5 border-l-4 border-[#C81E1E] bg-[#FCECEA] px-3 py-2.5 text-sm text-[#B42318]"><i class="ph ph-warning" aria-hidden="true"></i>ยืนยันแล้วระบบจะบันทึกรอบงาน ปริมาณ END TAPE และ NG</p>
                    <div class="grid grid-cols-2 gap-3 rounded bg-[var(--hmi-surface)] p-3 sm:grid-cols-4">
                        <div class="flex flex-col gap-1 rounded border border-[var(--hmi-line)] bg-[var(--hmi-screen)] px-3 py-2.5"><span class="text-xs text-[var(--hmi-mute)]">รับเข้า</span><span class="led led-blue text-[28px]!">{{ quantity(totals.wip) }}</span></div>
                        <div class="flex flex-col gap-1 rounded border border-[var(--hmi-line)] bg-[var(--hmi-screen)] px-3 py-2.5"><span class="text-xs text-[var(--hmi-mute)]">ออก FG</span><span class="led led-green text-[28px]!">{{ quantity(totals.fg) }}</span></div>
                        <div class="flex flex-col gap-1 rounded border border-[var(--hmi-line)] bg-[var(--hmi-screen)] px-3 py-2.5"><span class="text-xs text-[var(--hmi-mute)]">คงค้าง HD</span><span class="led led-white text-[28px]!">{{ quantity(totals.hd) }}</span></div>
                        <div class="flex flex-col gap-1 rounded border border-[var(--hmi-line)] bg-[var(--hmi-screen)] px-3 py-2.5"><span class="text-xs text-[var(--hmi-mute)]">เสีย NG</span><span class="led led-red text-[28px]!">{{ quantity(totals.ng) }}</span></div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="flex flex-col gap-1.5 text-[13px] font-semibold">จำนวน END TAPE <input v-model="shiftForm.end_tape" type="number" min="1" step="0.0001" class="field mono" required />
                            <span v-if="shiftForm.errors.end_tape" class="text-xs font-normal text-[#B42318]">{{ shiftForm.errors.end_tape }}</span></label>
                        <label class="flex flex-col gap-1.5 text-[13px] font-semibold">จำนวน NG <input v-model="shiftForm.waste_qty" type="number" min="0" step="1" class="field mono" required /></label>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" :disabled="shiftForm.processing || !canEndShift" class="inline-flex min-h-12 cursor-pointer items-center gap-2 rounded border-2 border-[var(--hmi-yellow)] bg-[#C81E1E] px-6 text-[15px] font-bold text-white hover:bg-[#A31717] disabled:cursor-not-allowed disabled:opacity-50">
                            <i class="ph-fill ph-stop" aria-hidden="true"></i> ยืนยันปิดกะ
                        </button>
                    </div>
                </form>
            </section>
        </div>
        </Teleport>
    </AppLayout>
</template>
