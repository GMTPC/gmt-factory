type RequestError = Error & { errors?: Record<string, string[]>; status?: number }

function fail(message: string, status?: number, errors?: Record<string, string[]>): never {
    const error = new Error(message) as RequestError
    error.status = status
    error.errors = errors
    throw error
}

export async function finishingRequest<T>(url: string, method: string, payload: unknown, csrf: string): Promise<T> {
    // IIS (WebDAV) answers PUT / PATCH / DELETE with 405, so send them as POST and let Laravel read the real
    // method from X-HTTP-Method-Override (Symfony honours this header on POST requests).
    const verb = method.toUpperCase()
    const tunnelled = !['GET', 'HEAD', 'POST'].includes(verb)
    let response: Response
    try {
        response = await fetch(url, {
            method: tunnelled ? 'POST' : verb,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
                ...(tunnelled ? { 'X-HTTP-Method-Override': verb } : {}),
            },
            body: payload === undefined ? undefined : JSON.stringify(payload),
        })
    } catch {
        return fail('เชื่อมต่อเซิร์ฟเวอร์ไม่ได้ กรุณาตรวจสอบเครือข่ายแล้วลองใหม่')
    }

    const isJson = (response.headers.get('content-type') ?? '').includes('application/json')
    const data = isJson ? await response.json().catch(() => ({})) : {}
    if (!response.ok) {
        if (response.status === 419) return fail('หมดเวลาการเข้าสู่ระบบ กรุณารีเฟรชหน้าแล้วลองใหม่ (419)', 419)
        if (response.status === 401) return fail('กรุณาเข้าสู่ระบบใหม่ (401)', 401)
        return fail(data.message ?? `บันทึกข้อมูลไม่สำเร็จ (HTTP ${response.status})`, response.status, data.errors)
    }
    // A redirect to the login page comes back as 200 HTML: treat it as a failure, not a silent success.
    if (response.redirected || !isJson) return fail(`บันทึกข้อมูลไม่สำเร็จ · เซิร์ฟเวอร์ตอบกลับไม่ถูกต้อง กรุณารีเฟรชหน้า (HTTP ${response.status})`, response.status)
    return data as T
}

export function errorText(error: unknown): string {
    const value = error as RequestError
    return Object.values(value.errors ?? {}).flat().join(' ') || value.message || 'เกิดข้อผิดพลาด'
}
