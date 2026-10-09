import { reactive, nextTick } from 'vue';
import { form, state, validate, hasSelection } from './store';
import { request, postJson } from './api';
import { t } from './i18n';

// Pago y envío del formulario. Orden: tickets -> validación HTML5 del form ->
// aviso de compra duplicada -> modal de confirmación (salvo "Registrar"
// gratis) -> envío por fetch (FormData, con archivos) a PublicController::process(),
// que responde JSON: { redirect } | { webpay: { url, token } } | 422 { errors } / { message }.
export const payment = reactive({
    method: '',            // 'webpay' | 'transfer' | 'free'
    confirmOpen: false,
    duplicate: null,       // compra previa detectada (check-duplicate)
    duplicateOpen: false,
    bypassDuplicate: false,
    duplicateFeedback: { text: '', type: '' },
    resending: false,
    submitting: false,
});

let formEl = null;

export function registerForm(el) {
    formEl = el;
}

export async function startPayment(method) {
    payment.method = method;

    if (!validate()) return;
    if (!(await reportFormValidity())) return;

    if (payment.bypassDuplicate) {
        payment.bypassDuplicate = false;
    } else {
        const duplicate = await checkDuplicate();
        if (duplicate) {
            payment.duplicate = duplicate;
            payment.duplicateFeedback = { text: '', type: '' };
            payment.duplicateOpen = true;
            return;
        }
    }

    if (method === 'free') {
        await submit();
    } else {
        payment.confirmOpen = true;
    }
}

// Si el primer campo inválido está en una pestaña de tickets oculta (ej. el
// documento de un ticket elegido en otra categoría), se muestra esa pestaña
// para que el navegador pueda señalarlo.
async function reportFormValidity() {
    const invalid = formEl.querySelector(':invalid');
    if (!invalid) return true;

    const panel = invalid.closest('[data-group-key]');
    if (panel && state.activeGroup !== panel.dataset.groupKey) {
        state.activeGroup = panel.dataset.groupKey;
        await nextTick();
    }
    formEl.reportValidity();
    return false;
}

async function checkDuplicate() {
    const email = form.values.email.trim();
    if (!email || !hasSelection.value) return null;
    try {
        const { body } = await postJson(form.urls.checkDuplicate, { email, tickets: state.selected.map(String) });
        return body && body.duplicate && body.payment ? body.payment : null;
    } catch (e) {
        return null; // si falla el chequeo, no se bloquea la compra
    }
}

export async function continueAfterDuplicate() {
    payment.duplicateOpen = false;
    payment.bypassDuplicate = true;
    await startPayment(payment.method);
}

export async function resendLastPayment() {
    if (payment.resending || !payment.duplicate) return;
    payment.resending = true;
    try {
        const { status, body } = await postJson(form.urls.resendLastPayment, { payment_id: payment.duplicate.id });
        payment.duplicateFeedback = {
            text: (body && body.message) || t('dupResendOk'),
            type: status === 200 ? 'success' : 'danger',
        };
    } catch (e) {
        payment.duplicateFeedback = { text: t('dupResendError'), type: 'danger' };
    } finally {
        payment.resending = false;
    }
}

export async function submit() {
    if (payment.submitting) return;
    payment.submitting = true;
    form.flashError = '';

    const data = new FormData(formEl);
    data.set('payment', payment.method);

    try {
        const { status, body } = await request(form.urls.process, { body: data });

        if (status === 200 && body && body.redirect) {
            window.location.href = body.redirect;
            return; // se mantiene "Procesando..." mientras navega
        }
        if (status === 200 && body && body.webpay) {
            goToWebpay(body.webpay.url, body.webpay.token);
            return;
        }

        payment.confirmOpen = false;
        if (status === 422 && body && body.errors) {
            form.errors = body.errors;
        } else if (status === 419) {
            form.flashError = t('sessionExpired');
        } else {
            form.flashError = (body && body.message) || t('submitError');
        }
    } catch (e) {
        payment.confirmOpen = false;
        form.flashError = t('submitError');
    }

    payment.submitting = false;
    await nextTick();
    scrollToFirstError();
}

// Mismo POST que hacía guest/webpay.blade.php: token_ws hacia Transbank.
function goToWebpay(url, token) {
    const webpayForm = document.createElement('form');
    webpayForm.method = 'POST';
    webpayForm.action = url;
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'token_ws';
    input.value = token;
    webpayForm.appendChild(input);
    document.body.appendChild(webpayForm);
    webpayForm.submit();
}

function scrollToFirstError() {
    const target = document.querySelector('#checkout-app .is-invalid, #checkout-app .alert-danger');
    if (target) target.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
