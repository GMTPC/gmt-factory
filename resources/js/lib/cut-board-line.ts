// สีประจำไลน์ — ต้องตรงกับ $cutBoardLineColors ใน resources/views/partials/sidebar.blade.php
const lineThemes = {
    L1: { color: '#059669', gradient: 'linear-gradient(120deg, #064e3b 0%, #059669 100%)' },
    L2: { color: '#d97706', gradient: 'linear-gradient(120deg, #78350f 0%, #b45309 100%)' },
    L3: { color: '#0369a1', gradient: 'linear-gradient(120deg, #0c4a6e 0%, #0369a1 100%)' },
    L4: { color: '#7c3aed', gradient: 'linear-gradient(120deg, #4c1d95 0%, #7c3aed 100%)' },
} as const

export function cutBoardLineTheme(line: string) {
    return lineThemes[line as keyof typeof lineThemes] ?? lineThemes.L1
}

// ตั้ง --line-color ให้ element — ใช้คู่กับสไตล์ HMI (.hmi / .line-themed)
export function lineStyle(line: string): Record<string, string> {
    return { '--line-color': cutBoardLineTheme(line).color }
}

// หาไลน์จาก URL ของหน้า Cut Board เช่น /cut-boards/L3/20005, /cut-boards/lines/L3/history
export function lineFromUrl(url: string): string | null {
    return url.match(/^\/cut-boards\/(?:lines\/)?(L[1-4])(?:[/?#]|$)/)?.[1] ?? null
}
