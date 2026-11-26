<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'

const STORAGE_KEY = 'finishing.station'
const page = usePage()
const path = computed(() => page.url.split('?')[0].replace(/\/+$/, '') || '/')
const urlStation = computed(() => path.value.match(/^\/finishing\/station\/(ST[1-6])(?:\/|$)/)?.[1] ?? null)
const lastStation = ref<string | null>(null)
// Remember the last station so "Job" / "ประวัติ" still work from the dashboard or reports.
const station = computed(() => urlStation.value ?? lastStation.value)

watch(urlStation, (code) => {
    if (!code) return
    lastStation.value = code
    try { localStorage.setItem(STORAGE_KEY, code) } catch { /* storage unavailable */ }
}, { immediate: true })

onMounted(() => {
    if (!lastStation.value) {
        try { lastStation.value = localStorage.getItem(STORAGE_KEY) } catch { /* storage unavailable */ }
    }
    document.body.classList.add('has-finishing-nav')
})
onUnmounted(() => document.body.classList.remove('has-finishing-nav'))

const items = computed(() => [
    // ?choose=1 stops SelectStation from auto-redirecting to the remembered station.
    { key: 'stations', label: 'สถานี', icon: 'ph-squares-four', href: '/finishing?choose=1', active: path.value === '/finishing' },
    {
        key: 'jobs', label: station.value ? `Job ${station.value}` : 'Job', icon: 'ph-clipboard-text',
        href: station.value ? `/finishing/station/${station.value}/jobs` : '/finishing',
        active: /^\/finishing\/station\/ST[1-6]\/(jobs|record)/.test(path.value),
    },
    {
        key: 'history', label: 'ประวัติ', icon: 'ph-clock-counter-clockwise',
        href: station.value ? `/finishing/station/${station.value}/history` : '/finishing',
        active: /^\/finishing\/station\/ST[1-6]\/history/.test(path.value),
    },
    { key: 'dashboard', label: 'ผังเครื่อง', icon: 'ph-factory', href: '/finishing/dashboard', active: path.value.startsWith('/finishing/dashboard') },
    { key: 'reports', label: 'รายงาน', icon: 'ph-chart-bar', href: '/finishing/reports', active: path.value.startsWith('/finishing/reports') },
])
</script>

<template>
    <nav aria-label="เมนู Finishing" class="finishing-mobile-nav fixed inset-x-0 bottom-0 z-40 border-t border-slate-200 bg-white/95 shadow-[0_-4px_16px_rgba(15,23,42,0.08)] backdrop-blur md:hidden">
        <ul class="m-0 grid list-none grid-cols-5 p-0">
            <li v-for="item in items" :key="item.key" class="m-0 list-none">
                <Link :href="item.href" :aria-current="item.active ? 'page' : undefined"
                      class="flex min-h-16 flex-col items-center justify-center gap-0.5 px-1 text-[11px] font-semibold leading-tight"
                      :class="item.active ? 'text-emerald-700' : 'text-slate-500 hover:text-slate-800'">
                    <span class="flex h-7 w-12 items-center justify-center rounded-full" :class="item.active ? 'bg-emerald-100' : ''">
                        <i class="text-xl" :class="[item.active ? 'ph-fill' : 'ph', item.icon]" aria-hidden="true"></i>
                    </span>
                    <span class="max-w-full truncate">{{ item.label }}</span>
                </Link>
            </li>
        </ul>
    </nav>
</template>

<style>
.finishing-mobile-nav {
    padding-bottom: env(safe-area-inset-bottom);
}

/* Keep the page content and footer clear of the fixed bar on small screens. */
@media (max-width: 767.98px) {
    body.has-finishing-nav .page-wrapper {
        padding-bottom: calc(4.25rem + env(safe-area-inset-bottom));
    }
}
</style>
