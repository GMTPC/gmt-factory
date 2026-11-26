// Helpers for printing a same-origin PDF from inside the page (no extra tab to close afterwards).

/** Phones/tablets cannot render or print a PDF inside an iframe (Android Chrome has no inline PDF viewer). */
export function isTouchOnly(): boolean {
    return !!window.matchMedia?.('(pointer: coarse)').matches && !window.matchMedia?.('(pointer: fine)').matches
}

/** Print a PDF without showing it: load it in a hidden iframe, then open the print dialog (phones open a tab). */
let hiddenFrame: HTMLIFrameElement | null = null
export function printPdf(url: string): void {
    if (isTouchOnly()) {
        window.open(url, '_blank', 'noopener')
        return
    }
    hiddenFrame?.remove()
    const frame = document.createElement('iframe')
    hiddenFrame = frame
    frame.setAttribute('aria-hidden', 'true')
    frame.tabIndex = -1
    Object.assign(frame.style, { position: 'fixed', right: '0', bottom: '0', width: '1px', height: '1px', border: '0', opacity: '0', pointerEvents: 'none' })
    frame.onload = () => window.setTimeout(() => printFrame(frame, url), 300)
    frame.src = url
    document.body.appendChild(frame)
}

/** Open the browser print dialog for a PDF already loaded in an iframe; falls back to a new tab. */
export function printFrame(frame: HTMLIFrameElement | null, fallbackUrl: string): void {
    try {
        if (!frame?.contentWindow) throw new Error('no frame')
        frame.contentWindow.focus()
        frame.contentWindow.print()
    } catch {
        window.open(fallbackUrl, '_blank', 'noopener')
    }
}

/**
 * Print server-rendered HTML in the current page (window.print) — used on phones, where a PDF would be
 * downloaded instead of printed. The fragment brings its own @media print rules that hide everything else.
 */
export async function printHtml(url: string): Promise<void> {
    const response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'text/html' } })
    if (!response.ok) throw new Error(`โหลด Tag เพื่อพิมพ์ไม่สำเร็จ (HTTP ${response.status})`)
    const html = await response.text()
    let root = document.getElementById('fin-print-root')
    if (!root) {
        root = document.createElement('div')
        root.id = 'fin-print-root'
        document.body.appendChild(root)
    }
    root.innerHTML = html
    document.body.classList.add('fin-printing')
    const cleanup = () => {
        document.body.classList.remove('fin-printing')
        document.getElementById('fin-print-root')?.remove()
        window.removeEventListener('afterprint', cleanup)
    }
    window.addEventListener('afterprint', cleanup)
    // Let the SVG codes lay out before the print snapshot is taken.
    await new Promise((resolve) => requestAnimationFrame(() => requestAnimationFrame(resolve)))
    window.print()
}

/**
 * Print a Finishing tag: PDF in a hidden frame on desktop; on phones open the mPDF PDF in a new tab.
 * (In-page HTML printing via printHtml() printed blank pages on the Android handheld, so it is not used for now.)
 */
export async function printTag(tagUrl: string): Promise<void> {
    if (isTouchOnly()) {
        window.open(tagUrl, '_blank', 'noopener')
        return
    }
    printPdf(tagUrl)
}
