<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import { lineFromUrl, lineStyle } from '@/lib/cut-board-line'
import FinishingMobileNav from '@/components/FinishingMobileNav.vue'

const page = usePage()
const currentLine = computed(() => lineFromUrl(page.url))
const isFinishing = computed(() => /^\/finishing(\/|\?|$)/.test(page.url))
// topbar เป็น Blade (นอก Vue app) — teleport ได้เฉพาะเมื่อมีช่องรองรับใน DOM
const hasTopbarSlot = typeof document !== 'undefined' && !!document.getElementById('topbar-line-slot')

withDefaults(defineProps<{
    title: string
    fullWidth?: boolean
    hideHeader?: boolean
}>(), {
    fullWidth: false,
    hideHeader: false,
})
</script>

<template>
    <Teleport v-if="hasTopbarSlot && currentLine" to="#topbar-line-slot">
        <Link :href="`/cut-boards/lines/${currentLine}`" :style="lineStyle(currentLine)" :aria-label="`กำลังทำงานที่ไลน์ ${currentLine.slice(1)} — ไปหน้าภาพรวมไลน์`"
              class="inline-flex h-9 items-center gap-2 rounded-md bg-[var(--line-color)] py-1 pl-1 pr-3.5 text-white shadow-sm hover:opacity-90">
            <span class="inline-flex size-7 items-center justify-center rounded bg-white text-base font-extrabold text-[var(--line-color)]">{{ currentLine.slice(1) }}</span>
            <span class="flex flex-col leading-tight">
                <span class="text-[15px] font-bold">ไลน์ {{ currentLine.slice(1) }}</span>
                <span class="hidden text-[11px] opacity-85 sm:block">QC คัดบอร์ด</span>
            </span>
        </Link>
    </Teleport>
    <div :class="fullWidth ? 'w-full px-3 py-4 sm:px-4' : 'mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8'">
        <div v-if="!hideHeader" class="mb-6 flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-slate-500">{{ isFinishing ? 'Finishing' : 'Cut Board' }}</p>
                <h1 class="text-2xl font-semibold text-slate-900">{{ title }}</h1>
            </div>
            <nav v-if="isFinishing" class="hidden flex-wrap gap-2 text-sm md:flex">
                <Link href="/finishing" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-slate-700 hover:bg-slate-50">เลือกสถานี</Link>
                <Link href="/finishing/dashboard" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-slate-700 hover:bg-slate-50">ผังเครื่องจักร</Link>
                <Link href="/finishing/reports" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-slate-700 hover:bg-slate-50">รายงาน</Link>
            </nav>
            <nav v-else class="flex flex-wrap gap-2 text-sm">
                <Link href="/cut-boards" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-slate-700 hover:bg-slate-50">
                    งานคัดบอร์ด
                </Link>
                <Link href="/checker-qc-scan" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-slate-700 hover:bg-slate-50">
                    QC Scan
                </Link>
                <Link href="/forklift-receive" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-slate-700 hover:bg-slate-50">
                    Forklift Receive
                </Link>
                <Link href="/bx-print" class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-slate-700 hover:bg-slate-50">
                    BX Print
                </Link>
            </nav>
        </div>
        <slot />
    </div>
    <FinishingMobileNav v-if="isFinishing" />
</template>
