import { form } from './store';

// Pide un token CSRF nuevo (PublicController::csrfToken). Si la sesión había
// expirado, Laravel crea una nueva en esta misma petición.
async function refreshCsrf() {
    const res = await fetch(form.urls.csrf, {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
    });
    const body = await res.json();
    form.csrf = body.token;
}

/**
 * fetch con el CSRF vigente del store. Ante un 419 (token rotado o sesión
 * vencida) renueva el token y reintenta una sola vez, sin que el usuario
 * pierda lo que ya llenó.
 *
 * body: objeto plano (se envía como JSON) o FormData (multipart).
 * Devuelve { status, body } (body = JSON parseado, o null si no es JSON).
 */
export async function request(url, { method = 'POST', body = null } = {}, retried = false) {
    const isFormData = body instanceof FormData;

    if (isFormData) body.set('_token', form.csrf);

    const res = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'X-CSRF-TOKEN': form.csrf,
            'X-Requested-With': 'XMLHttpRequest',
            ...(body && !isFormData ? { 'Content-Type': 'application/json' } : {}),
        },
        body: body ? (isFormData ? body : JSON.stringify(body)) : undefined,
    });

    if (res.status === 419 && !retried) {
        await refreshCsrf();
        return request(url, { method, body }, true);
    }

    let parsed = null;
    try {
        parsed = await res.json();
    } catch (e) {
        parsed = null;
    }

    return { status: res.status, body: parsed };
}

export function postJson(url, data) {
    return request(url, { body: data });
}
