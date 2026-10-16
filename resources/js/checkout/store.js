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
        // compra por entradas: la lista muestra las entradas principales y
        // cada unidad elegida trae sus propios complementarios
        entriesMode: false,
    },
    groups: [],
    tickets: {},
    // ids seleccionados, en orden de selección
    selected: [],
    // unidades por ticket seleccionado ({ id: n }); sin entrada = 1
    quantities: {},
    // complementarios de cada entrada: { 'principalId:n': { id: unidades } }
    entryItems: {},
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
        entriesMode: !!payload.entriesMode,
    });
    state.tickets = payload.tickets || {};
    // por entradas, los complementarios no se listan sueltos: se eligen
    // dentro de cada entrada (grupos que quedan vacíos se ocultan)
    state.groups = (payload.groups || [])
        .map((group) => (state.config.entriesMode
            ? { ...group, ticketIds: group.ticketIds.filter((id) => !isComplement(id)) }
            : group))
        .filter((group) => group.ticketIds.length);
    if (state.config.entriesMode && state.config.layout === 'tabs' && state.groups.length < 2) {
        state.config.layout = 'single';
    }

    const preselected = (payload.preselected || []).filter((id) => state.tickets[id] && !state.tickets[id].soldOut && !isComplement(id));
    state.selected = state.config.multiple ? preselected.slice(0, state.config.maxSelection) : preselected.slice(0, 1);
    state.quantities = {};
    const oldQuantities = payload.preselectedQuantities || {};
    state.selected.forEach((id) => {
        const qty = Math.min(Number(oldQuantities[id]) || 1, state.tickets[id].maxQuantity || 1);
        if (qty > 1) state.quantities[id] = qty;
    });

    state.entryItems = {};
    restoreEntries(payload.preselectedEntries || []);

    if (state.config.layout === 'tabs' && state.selected.length) {
        const group = state.groups.find((g) => g.ticketIds.includes(state.selected[0]));
        state.activeGroup = group ? group.key : null;
    }
}

function isComplement(id) {
    return state.config.entriesMode && state.tickets[id] && state.tickets[id].kind === 'complementario';
}

// Tras un error de validación: las entradas enviadas, en el mismo orden.
function restoreEntries(entries) {
    const seen = {};
    entries.forEach((entry) => {
        const id = entry.ticket;
        if (!isSelected(id)) return;
        const n = seen[id] = (seen[id] || 0);
        seen[id]++;
        if (n >= quantityOf(id)) return;
        Object.entries(entry.items || {}).forEach(([itemId, qty]) => setItemQuantity(entryKey(id, n), Number(itemId), qty));
    });
}

export function quantityOf(id) {
    return isSelected(id) ? (state.quantities[id] || 1) : 0;
}

// Total de unidades elegidas: es lo que limita max_selection_ticket.
export const units = computed(() => state.selected.reduce((sum, id) => sum + quantityOf(id), 0));

// --- Entradas (solo con entriesMode) -------------------------------------

export const entryKey = (ticketId, n) => `${ticketId}:${n}`;

// Una entrada por cada unidad de entrada principal elegida, numeradas en el
// orden de la selección: [{ key, number, ticket, items: { id: unidades } }]
export const entries = computed(() => {
    if (!state.config.entriesMode) return [];
    const list = [];
    state.selected.forEach((id) => {
        for (let n = 0; n < quantityOf(id); n++) {
            const key = entryKey(id, n);
            list.push({ key, number: list.length + 1, ticket: state.tickets[id], items: state.entryItems[key] || {} });
        }
    });
    return list;
});

// Complementarios que se pueden elegir con una entrada principal.
export function complementsFor(principalId) {
    return Object.values(state.tickets)
        .filter((ticket) => ticket.kind === 'complementario')
        .filter((ticket) => !ticket.allowedWith.length || ticket.allowedWith.includes(principalId));
}

export function itemQuantity(key, id) {
    return (state.entryItems[key] && state.entryItems[key][id]) || 0;
}

// Unidades de un complementario en todas las entradas, menos una.
function itemUnitsElsewhere(id, exceptKey) {
    return entries.value.reduce((sum, entry) => sum + (entry.key === exceptKey ? 0 : itemQuantity(entry.key, id)), 0);
}

// Por entrada: su máximo por compra, sin pasar de lo que queda disponible
// entre todas las entradas.
export function maxItemQuantity(key, id) {
    const ticket = state.tickets[id];
    if (!ticket || ticket.soldOut) return 0;
    return Math.max(0, Math.min(ticket.maxQuantity, ticket.available - itemUnitsElsewhere(id, key)));
}

export function setItemQuantity(key, id, qty) {
    const value = Math.min(Math.max(0, Math.floor(Number(qty)) || 0), maxItemQuantity(key, id));
    const items = { ...(state.entryItems[key] || {}) };
    if (value > 0) items[id] = value;
    else delete items[id];
    if (Object.keys(items).length) state.entryItems[key] = items;
    else delete state.entryItems[key];
    state.error = null;
    clearCoupon();
}

// Al quitar entradas se borran los complementarios de las que ya no existen.
function pruneEntries() {
    Object.keys(state.entryItems).forEach((key) => {
        const [id, n] = key.split(':').map(Number);
        if (n >= quantityOf(id)) delete state.entryItems[key];
    });
}

// Todo lo que se compra: { ticketId: unidades }, entradas y complementarios.
export const cart = computed(() => {
    const result = {};
    state.selected.forEach((id) => { result[id] = quantityOf(id); });
    entries.value.forEach((entry) => {
        Object.entries(entry.items).forEach(([id, qty]) => { result[id] = (result[id] || 0) + qty; });
    });
    return result;
});

export const cartIds = computed(() => Object.keys(cart.value).map(Number));

export const subtotal = computed(() =>
    Object.entries(cart.value).reduce((sum, [id, qty]) => sum + (state.tickets[id] ? state.tickets[id].price * qty : 0), 0)
);

// Máximo de unidades para un ticket: su máximo por compra (sin pasar de los
// disponibles) y lo que queda del máximo por compra del evento.
// Lo que deja el máximo por compra del evento (en unidades). Con selección
// única se elige un solo tipo de ticket y manda el máximo del ticket.
function eventLimitFor(id) {
    return state.config.multiple ? state.config.maxSelection - (units.value - quantityOf(id)) : Infinity;
}

export function maxQuantityOf(id) {
    const ticket = state.tickets[id];
    if (!ticket) return 0;
    return Math.max(0, Math.min(ticket.maxQuantity, eventLimitFor(id)));
}

// Cambia las unidades de un ticket elegido, dentro de su máximo. Si lo que
// frena es el máximo por compra del evento, se muestra el aviso.
export function setQuantity(id, qty) {
    if (!isSelected(id)) return;
    const requested = Math.max(1, Math.floor(Number(qty)) || 1);
    const max = maxQuantityOf(id);
    const value = Math.min(requested, max);
    if (value > 1) state.quantities[id] = value;
    else delete state.quantities[id];
    pruneEntries();
    state.warning = requested > value && eventLimitFor(id) <= state.tickets[id].maxQuantity ? 'max' : null;
    state.error = null;
    clearCoupon();
}

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
    if (state.tickets[id] && state.tickets[id].soldOut) return;
    if (state.config.multiple) {
        if (isSelected(id)) {
            state.selected = state.selected.filter((s) => s !== id);
            delete state.quantities[id];
            pruneEntries();
        } else {
            if (units.value >= state.config.maxSelection) {
                state.warning = 'max';
                return;
            }
            state.selected = [...state.selected, id];
        }
    } else {
        if (isSelected(id)) return;
        state.selected = [id];
        state.quantities = {};
        state.entryItems = {};
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
    if (mandatory.length && !mandatory.some((t) => cartIds.value.includes(t.id))) {
        state.error = 'mandatory';
        return false;
    }

    state.error = null;
    return true;
}
