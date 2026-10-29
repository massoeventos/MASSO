import { nextTick } from 'vue';
import { form, validate as validateTickets } from './store';
import { t } from './i18n';
import { checkRules } from './rules';

// Validación del formulario antes de pagar. Las reglas son las del propio
// HTML (required, type=email, maxlength, accept) más las de setCustomValidity
// (RUT), así no se duplican: acá solo se recorren los campos con la API de
// validación nativa y los errores se guardan en form.errors, el mismo mapa
// que usan los errores del servidor, para mostrarlos todos junto a su campo
// (el form tiene novalidate: sin las burbujas del navegador).

// Nombre del input -> clave estilo Laravel ("invoice_data[rut]" -> "invoice_data.rut").
export function errorKey(name) {
    return name.replace(/\[\]$/, '').replace(/\[([^\]]*)\]/g, '.$1');
}

function messageFor(el) {
    const v = el.validity;
    if (v.valueMissing) return el.type === 'file' ? t('requiredFile') : t('required');
    if (v.typeMismatch && el.type === 'email') return t('invalidEmail');
    if (v.tooLong) return t('tooLong', el.maxLength);
    if (v.customError) return el.validationMessage;
    return el.validationMessage || t('required');
}

// Devuelve true si todo es válido. Si no, deja los errores en pantalla y
// lleva al primero (en el orden en que aparecen).
export async function validateForm(formEl) {
    const errors = {};

    for (const el of formEl.elements) {
        if (!el.name || !el.willValidate || el.validity.valid) continue;
        const key = errorKey(el.name);
        if (!errors[key]) errors[key] = [messageFor(el)];
    }

    await checkRules(); // reglas de compra de lo que está en pantalla
    const ticketsOk = validateTickets(); // marca state.error ('empty' | 'mandatory' | 'rules')

    form.errors = errors;
    form.flashError = '';

    if (ticketsOk && Object.keys(errors).length === 0) return true;

    await nextTick();
    focusFirstError(formEl);
    return false;
}

export function focusFirstError(root = document) {
    const first = root.querySelector('.is-invalid, .ticket-error, .alert-danger');
    if (!first) return;
    first.scrollIntoView({ behavior: 'smooth', block: 'center' });
    if (typeof first.focus === 'function' && first.matches('input, select, textarea')) {
        first.focus({ preventScroll: true });
    }
}
