<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue'
import type { IScannerControls } from '@zxing/browser'

// Scan a Tag QR / Code128 with the device camera.
// Live video needs a secure context (HTTPS or localhost); on plain HTTP the phone's camera app is used
// to take a photo of the Tag instead, which is then decoded in the browser.
const emit = defineEmits<{ detected: [text: string]; close: [] }>()

const video = ref<HTMLVideoElement | null>(null)
const photoInput = ref<HTMLInputElement | null>(null)
const liveSupported = typeof window !== 'undefined' && window.isSecureContext && !!navigator.mediaDevices?.getUserMedia
const mode = ref<'live' | 'photo'>(liveSupported ? 'live' : 'photo')
const status = ref(liveSupported ? 'กำลังเปิดกล้อง...' : '')
const error = ref('')
const busy = ref(false)
let controls: IScannerControls | null = null
let done = false

async function createReader() {
    const [{ BrowserMultiFormatReader }, { BarcodeFormat, DecodeHintType }] = await Promise.all([import('@zxing/browser'), import('@zxing/library')])
    const hints = new Map()
    hints.set(DecodeHintType.POSSIBLE_FORMATS, [BarcodeFormat.QR_CODE, BarcodeFormat.CODE_128, BarcodeFormat.CODE_39, BarcodeFormat.EAN_13])
    hints.set(DecodeHintType.TRY_HARDER, true)
    return new BrowserMultiFormatReader(hints)
}

function finish(text: string) {
    if (done) return
    done = true
    stopLive()
    navigator.vibrate?.(80)
    emit('detected', text.trim())
}

function stopLive() {
    controls?.stop()
    controls = null
}

async function startLive() {
    error.value = ''; status.value = 'กำลังเปิดกล้อง...'
    try {
        const reader = await createReader()
        if (!video.value) return
        controls = await reader.decodeFromConstraints({ video: { facingMode: { ideal: 'environment' } } }, video.value, (result) => {
            if (result) finish(result.getText())
        })
        status.value = 'เล็ง QR / บาร์โค้ดของ Tag ให้อยู่ในกรอบ'
    } catch (e) {
        const name = (e as { name?: string })?.name
        error.value = name === 'NotAllowedError' ? 'ไม่ได้รับอนุญาตให้ใช้กล้อง · กดอนุญาตกล้องในเบราว์เซอร์ หรือใช้ถ่ายรูปแทน'
            : name === 'NotFoundError' ? 'ไม่พบกล้องในอุปกรณ์นี้ · ใช้ถ่ายรูปแทน'
            : 'เปิดกล้องไม่ได้ · ใช้ถ่ายรูปแทน'
        status.value = ''
        mode.value = 'photo'
    }
}

async function onPhoto(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0]
    if (!file) return
    busy.value = true; error.value = ''
    const url = URL.createObjectURL(file)
    try {
        const reader = await createReader()
        const result = await reader.decodeFromImageUrl(url)
        finish(result.getText())
    } catch {
        error.value = 'อ่าน QR / บาร์โค้ดจากรูปไม่ได้ · ถ่ายใหม่ให้ใกล้และชัดขึ้น'
    } finally {
        URL.revokeObjectURL(url)
        busy.value = false
        if (photoInput.value) photoInput.value.value = ''
    }
}

function useLive() { done = false; mode.value = 'live'; nextTick(startLive) }
function usePhoto() { stopLive(); mode.value = 'photo'; status.value = ''; photoInput.value?.click() }
function close() { stopLive(); emit('close') }

onMounted(() => { if (mode.value === 'live') startLive() })
onBeforeUnmount(stopLive)
</script>

<template>
    <Teleport to="body">
        <div class="fixed inset-0 z-[10020] flex items-end justify-center bg-slate-950/80 sm:items-center sm:p-4" role="presentation" @click.self="close" @keydown.esc="close">
            <section role="dialog" aria-modal="true" aria-label="สแกนด้วยกล้อง" class="flex max-h-[95vh] w-full max-w-md flex-col overflow-hidden rounded-t-xl bg-white shadow-2xl sm:rounded-xl">
                <div class="flex items-center justify-between gap-3 border-b border-slate-200 px-4 py-3">
                    <div class="min-w-0"><h2 class="m-0 text-base font-bold">สแกนด้วยกล้อง</h2><p class="m-0 text-xs text-slate-500">QR Code หรือบาร์โค้ดบน Tag WIP</p></div>
                    <button type="button" class="flex size-9 shrink-0 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100" aria-label="ปิดกล้อง" @click="close"><i class="ph ph-x text-xl" aria-hidden="true"></i></button>
                </div>

                <div v-if="mode === 'live'" class="relative aspect-[3/4] w-full bg-black sm:aspect-square">
                    <video ref="video" class="h-full w-full object-cover" muted playsinline autoplay></video>
                    <div class="pointer-events-none absolute inset-0 flex items-center justify-center" aria-hidden="true">
                        <div class="h-3/5 w-4/5 rounded-xl border-4 border-emerald-400 shadow-[0_0_0_9999px_rgba(0,0,0,0.35)]"></div>
                    </div>
                    <p v-if="status" class="absolute inset-x-0 bottom-0 m-0 bg-black/60 px-4 py-2 text-center text-sm font-semibold text-white">{{ status }}</p>
                </div>

                <div v-else class="space-y-3 p-4 text-sm">
                    <p v-if="!liveSupported" class="m-0 rounded border border-amber-200 bg-amber-50 px-3 py-2 text-amber-900">หน้านี้ไม่ได้เปิดผ่าน HTTPS เบราว์เซอร์จึงไม่ให้เปิดกล้องสด · ใช้ถ่ายรูป Tag แทนได้</p>
                    <button type="button" :disabled="busy" class="flex min-h-14 w-full items-center justify-center gap-2 rounded-md bg-emerald-700 px-4 text-base font-bold text-white disabled:opacity-50" @click="photoInput?.click()"><i class="ph ph-camera text-2xl" aria-hidden="true"></i>{{ busy ? 'กำลังอ่านรูป...' : 'ถ่ายรูป Tag' }}</button>
                    <p class="m-0 text-xs text-slate-500">ถ่ายให้ QR หรือบาร์โค้ดอยู่กลางภาพ ชัด และไม่สะท้อนแสง</p>
                </div>
                <input ref="photoInput" type="file" accept="image/*" capture="environment" class="hidden" @change="onPhoto" />

                <p v-if="error" role="alert" class="m-0 border-t border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800">{{ error }}</p>
                <div class="flex gap-2 border-t border-slate-200 p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
                    <button type="button" class="min-h-11 flex-1 rounded border border-slate-300 px-4 font-semibold" @click="close">ยกเลิก</button>
                    <button v-if="mode === 'live'" type="button" class="flex min-h-11 flex-1 items-center justify-center gap-1.5 rounded border border-slate-300 px-4 font-semibold" @click="usePhoto"><i class="ph ph-image" aria-hidden="true"></i>ถ่ายรูปแทน</button>
                    <button v-else-if="liveSupported" type="button" class="flex min-h-11 flex-1 items-center justify-center gap-1.5 rounded border border-slate-300 px-4 font-semibold" @click="useLive"><i class="ph ph-video-camera" aria-hidden="true"></i>กล้องสด</button>
                </div>
            </section>
        </div>
    </Teleport>
</template>
