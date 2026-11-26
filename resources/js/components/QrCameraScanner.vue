<script setup lang="ts">
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue'

// Scan a Tag QR / Code128 with the device camera.
// - Uses the browser's native BarcodeDetector when available (Chrome on Android: much better on long, dense
//   Code128 bars), with ZXing as the fallback.
// - Opens the camera at high resolution with continuous focus, and decodes only the area inside the guide box
//   at full resolution so thin bars stay readable.
// - A Tag carries several barcodes (full code, SKU, lot): `accept` lets the page keep scanning until the right
//   one is read, and tells the user what was read instead.
// Live video needs a secure context (HTTPS, localhost or a browser flag); otherwise a photo is taken and decoded.
const props = defineProps<{ accept?: (text: string) => boolean; hint?: string }>()
const emit = defineEmits<{ detected: [text: string]; close: [] }>()

type DetectedBarcode = { rawValue: string }
type NativeDetector = { detect(source: CanvasImageSource): Promise<DetectedBarcode[]> }
type ZxingReader = { decodeFromCanvas(canvas: HTMLCanvasElement): { getText(): string } }
type Decoder = (canvas: HTMLCanvasElement) => Promise<string[]>

const video = ref<HTMLVideoElement | null>(null)
const guide = ref<HTMLDivElement | null>(null)
const photoInput = ref<HTMLInputElement | null>(null)
const liveSupported = typeof window !== 'undefined' && window.isSecureContext && !!navigator.mediaDevices?.getUserMedia
const mode = ref<'live' | 'photo'>(liveSupported ? 'live' : 'photo')
const status = ref(liveSupported ? 'กำลังเปิดกล้อง...' : '')
const error = ref('')
const rejected = ref('')
const busy = ref(false)
const zoomRange = ref<{ min: number; max: number } | null>(null)
const zoom = ref(1)
const torchSupported = ref(false)
const torch = ref(false)
let stream: MediaStream | null = null
let timer: number | undefined
let done = false
let decoderPromise: Promise<Decoder> | null = null
const canvas = typeof document !== 'undefined' ? document.createElement('canvas') : null

const FORMATS = ['qr_code', 'code_128', 'code_39', 'ean_13']

function getDecoder(): Promise<Decoder> {
    decoderPromise ??= (async () => {
        const Native = (window as unknown as { BarcodeDetector?: { new (o: { formats: string[] }): NativeDetector; getSupportedFormats?: () => Promise<string[]> } }).BarcodeDetector
        if (Native) {
            try {
                const supported = (await Native.getSupportedFormats?.()) ?? FORMATS
                const formats = FORMATS.filter((format) => supported.includes(format))
                if (formats.includes('code_128')) {
                    const detector = new Native({ formats })
                    return async (source: HTMLCanvasElement) => (await detector.detect(source)).map((barcode) => barcode.rawValue)
                }
            } catch { /* fall back to ZXing */ }
        }
        const [{ BrowserMultiFormatReader }, { BarcodeFormat, DecodeHintType }] = await Promise.all([import('@zxing/browser'), import('@zxing/library')])
        const hints = new Map()
        hints.set(DecodeHintType.POSSIBLE_FORMATS, [BarcodeFormat.QR_CODE, BarcodeFormat.CODE_128, BarcodeFormat.CODE_39, BarcodeFormat.EAN_13])
        hints.set(DecodeHintType.TRY_HARDER, true)
        const reader = new BrowserMultiFormatReader(hints) as unknown as ZxingReader
        return async (source: HTMLCanvasElement) => {
            try { return [reader.decodeFromCanvas(source).getText()] } catch { return [] }
        }
    })()
    return decoderPromise
}

/** Returns true when a usable code was found (and emitted). */
function handle(values: string[]): boolean {
    const texts = values.map((value) => value.trim()).filter(Boolean)
    if (!texts.length) return false
    const match = props.accept ? texts.find((text) => props.accept!(text)) : texts[0]
    if (match) { finish(match); return true }
    rejected.value = texts[0]
    return false
}

function finish(text: string) {
    if (done) return
    done = true
    stopLive()
    navigator.vibrate?.(80)
    emit('detected', text)
}

function stopLive() {
    window.clearTimeout(timer)
    timer = undefined
    stream?.getTracks().forEach((track) => track.stop())
    stream = null
    torch.value = false
}

/** Copy the part of the frame under the guide box (plus a margin) into the canvas at camera resolution. */
function grabGuideArea(): HTMLCanvasElement | null {
    const el = video.value
    if (!el || !canvas || !el.videoWidth || !guide.value) return null
    const vw = el.videoWidth, vh = el.videoHeight
    const view = el.getBoundingClientRect(), box = guide.value.getBoundingClientRect()
    // object-cover: the frame is scaled to fill the element and centred, overflow cropped.
    const scale = Math.max(view.width / vw, view.height / vh)
    const offsetX = (vw * scale - view.width) / 2, offsetY = (vh * scale - view.height) / 2
    const marginX = box.width * 0.08, marginY = box.height * 0.15
    const sx = Math.max(0, (box.left - view.left - marginX + offsetX) / scale)
    const sy = Math.max(0, (box.top - view.top - marginY + offsetY) / scale)
    const sw = Math.min(vw - sx, (box.width + marginX * 2) / scale)
    const sh = Math.min(vh - sy, (box.height + marginY * 2) / scale)
    if (sw < 10 || sh < 10) return null
    canvas.width = Math.round(sw); canvas.height = Math.round(sh)
    canvas.getContext('2d', { willReadFrequently: true })?.drawImage(el, sx, sy, sw, sh, 0, 0, canvas.width, canvas.height)
    return canvas
}

async function tick() {
    if (done || !stream) return
    try {
        const frame = grabGuideArea()
        if (frame && handle(await (await getDecoder())(frame))) return
    } catch { /* keep scanning */ }
    if (!done && stream) timer = window.setTimeout(tick, 120)
}

async function startLive() {
    error.value = ''; rejected.value = ''; status.value = 'กำลังเปิดกล้อง...'
    try {
        getDecoder()
        stream = await navigator.mediaDevices.getUserMedia({
            audio: false,
            video: { facingMode: { ideal: 'environment' }, width: { ideal: 1920 }, height: { ideal: 1080 } },
        })
        const track = stream.getVideoTracks()[0]
        const caps = (track.getCapabilities?.() ?? {}) as MediaTrackCapabilities & { focusMode?: string[]; zoom?: { min: number; max: number }; torch?: boolean }
        if (caps.focusMode?.includes('continuous')) await track.applyConstraints({ advanced: [{ focusMode: 'continuous' } as MediaTrackConstraintSet] }).catch(() => {})
        if (caps.zoom && caps.zoom.max > caps.zoom.min) {
            zoomRange.value = { min: caps.zoom.min, max: Math.min(caps.zoom.max, 4) }
            zoom.value = Math.max(caps.zoom.min, 1)
        }
        torchSupported.value = !!caps.torch
        if (!video.value || done) { stopLive(); return }
        video.value.srcObject = stream
        await video.value.play().catch(() => {})
        status.value = props.hint ?? 'เล็ง QR / บาร์โค้ดของ Tag ให้อยู่ในกรอบ'
        tick()
    } catch (e) {
        stopLive()
        const name = (e as { name?: string })?.name
        error.value = name === 'NotAllowedError' ? 'ไม่ได้รับอนุญาตให้ใช้กล้อง · กดอนุญาตกล้องในเบราว์เซอร์ หรือใช้ถ่ายรูปแทน'
            : name === 'NotFoundError' ? 'ไม่พบกล้องในอุปกรณ์นี้ · ใช้ถ่ายรูปแทน'
            : 'เปิดกล้องไม่ได้ · ใช้ถ่ายรูปแทน'
        status.value = ''
        mode.value = 'photo'
    }
}

async function setZoom(value: number) {
    zoom.value = value
    await stream?.getVideoTracks()[0]?.applyConstraints({ advanced: [{ zoom: value } as MediaTrackConstraintSet] }).catch(() => {})
}
async function toggleTorch() {
    const next = !torch.value
    await stream?.getVideoTracks()[0]?.applyConstraints({ advanced: [{ torch: next } as MediaTrackConstraintSet] }).then(() => { torch.value = next }).catch(() => {})
}
function zoomSteps() {
    const range = zoomRange.value
    if (!range) return []
    return [1, 1.5, 2, 3].filter((step) => step >= range.min && step <= range.max)
}

async function onPhoto(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0]
    if (!file || !canvas) return
    busy.value = true; error.value = ''; rejected.value = ''
    try {
        const bitmap = await createImageBitmap(file)
        const decode = await getDecoder()
        // Try the full photo, then a 2x-shrunk copy (ZXing sometimes reads oversized photos better smaller).
        for (const factor of [1, 0.5]) {
            const maxSide = 2400 * factor
            const scale = Math.min(1, maxSide / Math.max(bitmap.width, bitmap.height))
            canvas.width = Math.round(bitmap.width * scale); canvas.height = Math.round(bitmap.height * scale)
            canvas.getContext('2d', { willReadFrequently: true })?.drawImage(bitmap, 0, 0, canvas.width, canvas.height)
            if (handle(await decode(canvas))) return
        }
        error.value = rejected.value
            ? `อ่านได้ "${rejected.value}" แต่ไม่ใช่รหัส Tag WIP · ถ่ายให้เห็นบาร์โค้ดเส้นยาวด้านบนของ Tag`
            : 'อ่าน QR / บาร์โค้ดจากรูปไม่ได้ · ถ่ายใหม่ให้ใกล้และชัดขึ้น'
    } catch {
        error.value = 'อ่าน QR / บาร์โค้ดจากรูปไม่ได้ · ถ่ายใหม่ให้ใกล้และชัดขึ้น'
    } finally {
        busy.value = false
        if (photoInput.value) photoInput.value.value = ''
    }
}

function useLive() { done = false; mode.value = 'live'; nextTick(startLive) }
function usePhoto() { stopLive(); mode.value = 'photo'; status.value = ''; photoInput.value?.click() }
function close() { stopLive(); emit('close') }

onMounted(() => { if (mode.value === 'live') startLive() })
onBeforeUnmount(() => { done = true; stopLive() })
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
                        <div ref="guide" class="relative h-[32%] w-[92%] rounded-lg border-4 border-emerald-400 shadow-[0_0_0_9999px_rgba(0,0,0,0.35)]">
                            <div class="absolute inset-x-2 top-1/2 h-0.5 bg-red-500/80"></div>
                        </div>
                    </div>
                    <div v-if="zoomRange || torchSupported" class="absolute inset-x-0 top-2 flex justify-center gap-1.5">
                        <button v-for="step in zoomSteps()" :key="step" type="button" class="min-h-9 min-w-11 rounded-full px-3 text-sm font-bold" :class="zoom === step ? 'bg-white text-slate-900' : 'bg-black/50 text-white'" @click="setZoom(step)">{{ step }}x</button>
                        <button v-if="torchSupported" type="button" class="flex min-h-9 min-w-11 items-center justify-center rounded-full px-3" :class="torch ? 'bg-amber-300 text-slate-900' : 'bg-black/50 text-white'" aria-label="ไฟฉาย" @click="toggleTorch"><i class="ph ph-flashlight text-lg" aria-hidden="true"></i></button>
                    </div>
                    <div class="absolute inset-x-0 bottom-0">
                        <p v-if="rejected" class="m-0 bg-amber-500/90 px-4 py-1.5 text-center text-xs font-bold text-slate-950">อ่านได้ "{{ rejected }}" ไม่ใช่รหัส Tag เต็ม · เล็งบาร์โค้ดเส้นยาวด้านบน</p>
                        <p v-if="status" class="m-0 bg-black/60 px-4 py-2 text-center text-sm font-semibold text-white">{{ status }}</p>
                    </div>
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
