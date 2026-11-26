<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { computed, onMounted, ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'

type Station = { id: number; code: string; name: string; input_material: string; output_material: string; is_final: boolean; is_enabled: boolean; is_machine_on?: boolean; jobs?: { id: number; product_type_code: string | null; product_type_name: string | null; employee_names: (string | { name: string })[] | null }[] }
const props = defineProps<{ stations: Station[] }>()
const remembered = ref('')
const activeCount = (station: Station) => station.jobs?.length ?? 0
const totalActiveJobs = computed(() => props.stations.filter((station) => station.is_enabled).reduce((sum, station) => sum + activeCount(station), 0))
const sorters = (job: NonNullable<Station['jobs']>[number]) => (job.employee_names ?? []).map((item) => typeof item === 'string' ? item : item.name).join(', ') || '-'
onMounted(() => {
    const savedStation = localStorage.getItem('finishing.station') ?? ''
    remembered.value = props.stations.some((station) => station.code === savedStation && station.is_enabled) ? savedStation : ''
    if (savedStation && !remembered.value) localStorage.removeItem('finishing.station')
    // No auto-redirect: this page must always be reachable. Offer the last station as a shortcut instead.
})

const colors = ['#059669', '#d97706', '#0369a1', '#7c3aed', '#dc2626', '#0f766e']
</script>

<template>
    <Head title="Finishing · เลือกสถานี" />
    <AppLayout title="Finishing Production" full-width>
        <main class="hmi flex flex-col gap-4 pb-6">
            <header class="overflow-hidden rounded-md border border-[var(--hmi-line)] bg-[var(--hmi-surface)] shadow-sm">
                <div class="hazard" aria-hidden="true"></div>
                <div class="flex flex-wrap items-center justify-between gap-4 bg-[var(--hmi-panel)] px-5 py-4">
                    <div><p class="m-0 text-xs uppercase tracking-wide text-[var(--hmi-mute)]">Inventory / Finishing Production</p><h1 class="m-0 text-2xl font-bold text-[var(--hmi-title)]">เลือกสถานีผลิต</h1><p class="m-0 mt-1 text-sm text-[var(--hmi-mute)]">บันทึกวัตถุดิบ ผลผลิต ยอดเสีย และติดตามการไหลของงาน</p><p class="m-0 mt-2 inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-bold" :class="totalActiveJobs ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700'"><i class="ph-fill ph-gear-six" aria-hidden="true"></i>กำลังแปรรูปทั้งหมด {{ totalActiveJobs }} Job</p></div>
                    <div class="flex gap-2"><Link href="/finishing/dashboard" class="rounded border border-[var(--hmi-line)] bg-white px-4 py-2 text-sm font-semibold">Dashboard</Link><Link href="/finishing/reports" class="rounded border border-[var(--hmi-line)] bg-white px-4 py-2 text-sm font-semibold">รายงาน</Link><Link v-if="remembered" :href="`/finishing/station/${remembered}/jobs`" class="rounded bg-emerald-700 px-4 py-2 text-sm font-bold text-white">ทำต่อที่ {{ remembered }} ›</Link></div>
                </div>
            </header>
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <component :is="station.is_enabled ? Link : 'div'" v-for="(station, index) in stations" :key="station.id" :href="station.is_enabled ? `/finishing/station/${station.code}/jobs` : undefined" :aria-disabled="station.is_enabled ? undefined : 'true'" class="group rounded-md border border-[var(--hmi-line)] bg-white p-5 shadow-sm transition" :class="station.is_enabled ? 'hover:-translate-y-0.5 hover:shadow-md' : 'cursor-not-allowed opacity-60 grayscale'" :style="{ borderTop: `4px solid ${colors[index % colors.length]}` }">
                    <div class="flex items-start justify-between gap-3"><div><span class="mono rounded-sm px-2 py-1 text-xs font-extrabold text-white" :style="{ backgroundColor: colors[index % colors.length] }">{{ station.code }}</span><h2 class="mt-3 text-lg font-bold text-[var(--hmi-title)]">{{ station.name }}</h2></div><span v-if="station.code === remembered" class="ml-auto shrink-0 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-bold text-emerald-800">ล่าสุด</span><span v-if="!station.is_enabled" class="shrink-0 rounded-full bg-slate-200 px-2 py-0.5 text-xs font-bold text-slate-700">ยังไม่เปิดใช้</span><span v-else class="text-2xl text-slate-300 group-hover:text-slate-600">›</span></div>
                    <div v-if="station.is_enabled" class="mt-3 flex items-center justify-between gap-3 rounded-md px-3 py-2" :class="activeCount(station) ? 'bg-emerald-50' : 'bg-slate-50'">
                        <div><p class="m-0 text-xs text-slate-500">กำลังแปรรูป</p><p class="m-0 leading-tight"><strong class="mono text-3xl" :class="activeCount(station) ? 'text-emerald-800' : 'text-slate-400'">{{ activeCount(station) }}</strong> <span class="text-sm font-semibold text-slate-700">Job</span></p></div>
                        <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-bold" :class="station.is_machine_on ? 'bg-emerald-600 text-white' : 'bg-red-100 text-red-700'">{{ station.is_machine_on ? '● เดินเครื่อง' : '● เครื่องหยุด' }}</span>
                    </div>
                    <ul v-if="station.is_enabled && activeCount(station)" class="m-0 mt-2 space-y-1.5 p-0">
                        <li v-for="job in station.jobs!.slice(0, 3)" :key="job.id" class="m-0 list-none rounded border border-slate-200 bg-white px-3 py-1.5 text-xs">
                            <p class="m-0 truncate font-bold text-slate-800"><template v-if="job.product_type_code"><span class="mono">{{ job.product_type_code }}</span>{{ job.product_type_name ? ` · ${job.product_type_name}` : '' }}</template><span v-else class="font-semibold text-amber-700">สินค้า: รอ Tag แรก</span></p>
                            <p class="m-0 truncate text-slate-600"><span class="font-semibold">ผู้คัด:</span> {{ sorters(job) }}</p>
                        </li>
                        <li v-if="activeCount(station) > 3" class="m-0 list-none text-xs text-slate-500">และอีก {{ activeCount(station) - 3 }} Job</li>
                    </ul>
                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm"><div class="rounded bg-slate-50 p-3"><p class="m-0 text-xs text-slate-500">วัตถุดิบเข้า</p><p class="m-0 mt-1 font-semibold">{{ station.input_material }}</p></div><div class="rounded bg-slate-50 p-3"><p class="m-0 text-xs text-slate-500">ผลผลิต</p><p class="m-0 mt-1 font-semibold">{{ station.output_material }}</p></div></div>
                    <p v-if="station.is_enabled" class="mb-0 mt-4 text-sm font-bold text-emerald-700">เปิดรายการ Job →</p><p v-else class="mb-0 mt-4 text-sm font-semibold text-slate-500">สถานีนี้ยังไม่เปิดใช้งาน</p>
                </component>
            </section>
        </main>
    </AppLayout>
</template>
