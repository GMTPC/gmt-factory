export async function finishingRequest<T>(url: string, method: string, payload: unknown, csrf: string): Promise<T> {
    const response = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: payload === undefined ? undefined : JSON.stringify(payload),
    })
    const data = await response.json().catch(() => ({}))
    if (!response.ok) {
        const error = new Error(data.message ?? 'บันทึกข้อมูลไม่สำเร็จ') as Error & { errors?: Record<string, string[]> }
        error.errors = data.errors
        throw error
    }
    return data as T
}

export function errorText(error: unknown): string {
    const value = error as Error & { errors?: Record<string, string[]> }
    return Object.values(value.errors ?? {}).flat().join(' ') || value.message || 'เกิดข้อผิดพลาด'
}
