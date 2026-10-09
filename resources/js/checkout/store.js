import { reactive, computed } from 'vue';

// Estado del checkout. `state`: selección de tickets, cupón y total;
// `form` (más abajo): el resto del formulario (datos, identificación,
// ubicación, facturación, errores del servidor).
export const state = reactive({
    config: {
        lang: 'esp',
        eventId: null,
        multiple: false,
        maxSelection: 1,
        couponUrl: '',
        layout: 'flat',
    },
    groups: [],
    tickets: {},
    // ids seleccionados, en orden de selección
    selected: [],
    activeGroup: null,
    // 'max' | null -- aviso de máximo de selección alcanzado
    warning: null,
    // 'empty' | 'mandatory' | null -- error al intentar pagar
    error: null,
    coupon: {
        code: '',          // código aplicado (se envía como coupon_code)
        percentage: null,  // % de descuento del cupón aplicado
        loading: false,
        feedback: '',      // texto (puede traer <br> con tickets inválidos)
        feedbackType: '',  // 'success' | 'danger' | ''
    },
});

export const form = reactive({
    lang: 'esp',
    event: {},
    urls: {},
    countries: [],
    chileId: null,
    inputs: [],
    csrf: '',
    flashError: '',
    // errores del servidor por campo, con las claves de Laravel
    // ('name', 'invoice_data.rut', 'ticket_document.12', ...)
    errors: {},
    values: {
        email: '',
        name: '',
        lastname: '',
        gender: '',
        nationality_country_id: '',
        rut: '',
        passport: '',
        country_id: '',
        region_id: '',
        city_id: '',
        custom_city: '',
    },
    // respuestas de texto a los campos propios del evento ({ clave: valor });
    // los de archivo no tienen estado (input file nativo)
    inputValues: {},
    billing: {
        method: '', // 'receipt' (boleta) | 'invoice' (factura)
        invoice: {
            business_name: '',
            rut: '',
            business_activity: '',
            address: '',
            city: '',
            phone: '',
            note: '',
        },
    },
    // campos autocompletados con datos guardados del cliente: no editables
    // (se corrigen desde Mi Perfil). { name: true, gender: true, ... }
    locked: {},
    identity: {
        modalOpen: false,
        step: 'email',      // 'email' | 'code'
        canSms: false,
        email: '',          // correo en proceso de verificación
        identified: false,  // el correo del form quedó fijado
        viaSession: false,  // autocompletado por sesión activa (sin OTP)
        hasData: false,     // el cliente tenía datos guardados
    },
});

export function isLocked(field) {
    return !!form.locked[field];
}

export function initForm(payload) {
    Object.assign(form, {
        lang: payload.lang || 'esp',
        event: payload.event || {},
        urls: payload.urls || {},
        countries: payload.countries || [],
        chileId: payload.chileId,
        inputs: payload.inputs || [],
        csrf: payload.csrfToken || '',
        flashError: payload.flashError || '',
        errors: payload.errors || {},
        paymentContactEmail: payload.paymentContactEmail || '',
    });

    // Valor inicial: si la página vuelve de un envío con errores, se respeta
    // exactamente lo enviado (un campo vaciado llega como null por
    // ConvertEmptyStringsToNull y no debe rellenarse con el autofill); si no,
    // se usan los datos guardados del cliente.
    const old = payload.old || {};
    const autofill = payload.autofill || {};
    const submitted = Object.keys(old).length > 0;
    Object.keys(form.values).forEach((key) => {
        form.values[key] = String((submitted ? old[key] : autofill[key]) ?? '');
    });
    form.inputValues = {};
    form.inputs.filter((input) => input.type === 'text').forEach((input) => {
        form.inputValues[input.key] = String(old[input.key] ?? '');
    });

    form.billing.method = String(old.billing_method ?? '');
    const oldInvoice = old.invoice_data || {};
    Object.keys(form.billing.invoice).forEach((key) => {
        form.billing.invoice[key] = String(oldInvoice[key] ?? '');
    });

    form.sessionIdentified = !!payload.sessionIdentified;
    form.autofill = autofill;
    // cuenta con sesión iniciada (guard customer); cambia si un código
    // verificado inicia sesión como otra cuenta
    form.sessionEmail = payload.sessionIdentified ? (autofill.email || '') : '';
    form.oldEmail = submitted ? (old.email || '') : '';

    init({ ...payload.tickets, lang: payload.lang });
}

export function fieldError(name) {
    const messages = form.errors[name];
    return messages && messages.length ? messages[0] : '';
}

// Al editar un campo se quita su error del servidor.
export function clearFieldError(name) {
    if (form.errors[name]) delete form.errors[name];
}

export function init(payload) {
    Object.assign(state.config, {
        lang: payload.lang || 'esp',
        eventId: payload.eventId,
        multiple: !!payload.multiple,
        maxSelection: payload.maxSelection || 1,
        couponUrl: payload.couponUrl,
        layout: payload.layout || 'flat',
    });
    state.groups = payload.groups || [];
    state.tickets = payload.tickets || {};

    const preselected = (payload.preselected || []).filter((id) => state.tickets[id]);
    state.selected = state.config.multiple ? preselected.slice(0, state.config.maxSelection) : preselected.slice(0, 1);

    if (state.config.layout === 'tabs' && state.selected.length) {
        const group = state.groups.find((g) => g.ticketIds.includes(state.selected[0]));
        state.activeGroup = group ? group.key : null;
    }
}

export const subtotal = computed(() =>
    state.selected.reduce((sum, id) => sum + (state.tickets[id] ? state.tickets[id].price : 0), 0)
);

export const total = computed(() => {
    if (state.coupon.code && state.coupon.percentage !== null) {
        return subtotal.value * (100 - state.coupon.percentage) / 100;
    }
    return subtotal.value;
});

export const hasSelection = computed(() => state.selected.length > 0);

export function isSelected(id) {
    return state.selected.includes(id);
}

// En modo radio (is_multiple_selection_ticket = 0) elegir un ticket reemplaza
// al anterior, aunque esté en otra pestaña; en modo checkbox se bloquea al
// superar max_selection_ticket. Cualquier cambio quita el cupón aplicado.
export function toggleTicket(id) {
    if (state.config.multiple) {
        if (isSelected(id)) {
            state.selected = state.selected.filter((s) => s !== id);
        } else {
            if (state.selected.length >= state.config.maxSelection) {
                state.warning = 'max';
                return;
            }
            state.selected = [...state.selected, id];
        }
    } else {
        if (isSelected(id)) return;
        state.selected = [id];
    }

    state.warning = null;
    state.error = null; // se vuelve a validar al pagar
    clearCoupon();
}

export function clearCoupon() {
    state.coupon.code = '';
    state.coupon.percentage = null;
    state.coupon.feedback = '';
    state.coupon.feedbackType = '';
}

// Validación de tickets antes de pagar (la llama submit.js): al menos uno, y
// al menos uno de los obligatorios si el evento los tiene.
export function validate() {
    if (!hasSelection.value) {
        state.error = 'empty';
        return false;
    }

    const mandatory = Object.values(state.tickets).filter((t) => t.isMandatory);
    if (mandatory.length && !mandatory.some((t) => isSelected(t.id))) {
        state.error = 'mandatory';
        return false;
    }

    state.error = null;
    return true;
}
