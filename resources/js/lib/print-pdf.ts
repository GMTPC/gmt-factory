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
