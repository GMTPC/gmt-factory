<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'

type Station = { id: number; code: string; name: string; input_material: string; output_material: string; is_final: boolean }
const props = defineProps<{ stations: Station[] }>()
const page = usePage()
const remembered = ref('')
onMounted(() => {
    const savedStation = localStorage.getItem('finishing.station') ?? ''
    remembered.value = props.stations.some((station) => station.code === savedStation) ? savedStation : ''
    if (savedStation && !remembered.value) localStorage.removeItem('finishing.station')
    if (remembered.value && !new URLSearchParams(window.location.search).has('choose')) {
        router.visit(`/finishing/station/${remembered.value}/jobs`, { replace: true })
    }
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
                    <div><p class="m-0 text-xs uppercase tracking-wide text-[var(--hmi-mute)]">Inventory / Finishing Production</p><h1 class="m-0 text-2xl font-bold text-[var(--hmi-title)]">เลือกสถานีผลิต</h1><p class="m-0 mt-1 text-sm text-[var(--hmi-mute)]">บันทึกวัตถุดิบ ผลผลิต ยอดเสีย และติดตามการไหลของงาน</p></div>
                    <div class="flex gap-2"><Link href="/finishing/dashboard" class="rounded border border-[var(--hmi-line)] bg-white px-4 py-2 text-sm font-semibold">Dashboard</Link><Link href="/finishing/reports" class="rounded border border-[var(--hmi-line)] bg-white px-4 py-2 text-sm font-semibold">รายงาน</Link><Link v-if="remembered" href="/finishing?choose=1" class="rounded border border-[var(--hmi-line)] bg-white px-4 py-2 text-sm font-semibold">เลือกสถานีอื่น</Link></div>
                </div>
            </header>
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <Link v-for="(station, index) in stations" :key="station.id" :href="`/finishing/station/${station.code}/jobs`" class="group rounded-md border border-[var(--hmi-line)] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md" :style="{ borderTop: `4px solid ${colors[index % colors.length]}` }">
                    <div class="flex items-start justify-between gap-3"><div><span class="mono rounded-sm px-2 py-1 text-xs font-extrabold text-white" :style="{ backgroundColor: colors[index % colors.length] }">{{ station.code }}</span><h2 class="mt-3 text-lg font-bold text-[var(--hmi-title)]">{{ station.name }}</h2></div><span class="text-2xl text-slate-300 group-hover:text-slate-600">›</span></div>
                    <div class="mt-4 grid grid-cols-2 gap-3 text-sm"><div class="rounded bg-slate-50 p-3"><p class="m-0 text-xs text-slate-500">วัตถุดิบเข้า</p><p class="m-0 mt-1 font-semibold">{{ station.input_material }}</p></div><div class="rounded bg-slate-50 p-3"><p class="m-0 text-xs text-slate-500">ผลผลิต</p><p class="m-0 mt-1 font-semibold">{{ station.output_material }}</p></div></div>
                    <p class="mb-0 mt-4 text-sm font-bold text-emerald-700">เปิดรายการ Job →</p>
                </Link>
            </section>
        </main>
    </AppLayout>
</template>
