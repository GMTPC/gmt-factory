<script setup lang="ts">
import { dismissToast, toasts } from '@/lib/toast'

const styles = {
    success: { box: 'border-emerald-300 bg-emerald-50 text-emerald-900', icon: 'ph-fill ph-check-circle text-emerald-600' },
    error: { box: 'border-red-300 bg-red-50 text-red-900', icon: 'ph-fill ph-warning-circle text-red-600' },
    info: { box: 'border-slate-300 bg-white text-slate-900', icon: 'ph-fill ph-info text-slate-500' },
} as const
</script>

<template>
    <Teleport to="body">
        <div class="pointer-events-none fixed inset-x-3 top-3 z-[10050] flex flex-col items-stretch gap-2 sm:left-auto sm:right-4 sm:top-4 sm:w-80" aria-live="polite">
            <TransitionGroup enter-from-class="translate-x-4 opacity-0" enter-active-class="transition duration-200 ease-out" leave-active-class="transition duration-150 ease-in" leave-to-class="opacity-0">
                <div v-for="item in toasts" :key="item.id" :role="item.type === 'error' ? 'alert' : 'status'" class="pointer-events-auto flex items-start gap-2 rounded-lg border px-3 py-2.5 text-sm font-semibold shadow-lg" :class="styles[item.type].box">
                    <i class="mt-0.5 shrink-0 text-lg" :class="styles[item.type].icon" aria-hidden="true"></i>
                    <span class="min-w-0 flex-1 break-words">{{ item.text }}</span>
                    <button type="button" class="-mr-1 shrink-0 rounded p-0.5 opacity-60 hover:opacity-100" aria-label="ปิดการแจ้งเตือน" @click="dismissToast(item.id)"><i class="ph ph-x" aria-hidden="true"></i></button>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>
