import { reactive } from 'vue'

// Small app-wide toast store; <ToastStack /> (mounted in AppLayout) renders it top-right.
export type ToastType = 'success' | 'error' | 'info'
export type Toast = { id: number; type: ToastType; text: string }

export const toasts = reactive<Toast[]>([])
let sequence = 0

export function toast(text: string, type: ToastType = 'success', durationMs = type === 'error' ? 6000 : 3000): void {
    const id = ++sequence
    toasts.push({ id, type, text })
    if (toasts.length > 4) toasts.splice(0, toasts.length - 4)
    window.setTimeout(() => dismissToast(id), durationMs)
}

export function dismissToast(id: number): void {
    const index = toasts.findIndex((item) => item.id === id)
    if (index >= 0) toasts.splice(index, 1)
}
