import { watch } from 'vue';
import { state, form, cart, entries } from './store';
import { postJson } from './api';

// Reglas de compra (PublicController::validateCart): cada cambio de la
// selección (o del correo, por las compras anteriores) se valida con
// debounce; antes de pagar se espera la validación de lo que hay en
// pantalla. El servidor vuelve a validar al procesar la compra.

let lastKey = null;
let pending = null;
let timer = null;

function payload() {
    const data = {
        lang: state.config.lang,
        email: form.values.email || null,
        rut: form.values.rut || null,
    };
    if (state.config.entriesMode && entries.value.length) {
        data.entries = entries.value.map((entry) => ({ ticket: entry.ticket.id, items: entry.items }));
    } else {
        data.tickets = Object.keys(cart.value).map(Number);
        data.quantities = cart.value;
    }
    return data;
}

// Disponibles al día: si alguien compró mientras se llenaba el formulario.
function applyAvailable(available) {
    Object.entries(available || {}).forEach(([id, count]) => {
        const ticket = state.tickets[id];
        if (!ticket || ticket.available === count) return;
        ticket.available = count;
        ticket.maxQuantity = Math.min(ticket.maxPerPurchase, count);
        ticket.soldOut = count < 1;
    });
}

// Valida la selección actual (si no se validó ya) y devuelve las violaciones.
export function checkRules() {
    if (!state.config.hasRules || !form.urls.validateCart) return Promise.resolve([]);

    const data = payload();
    const key = JSON.stringify(data);
    if (key === lastKey) return pending || Promise.resolve(state.violations);
    lastKey = key;

    if (!Object.keys(cart.value).length) {
        state.violations = [];
        pending = null;
        return Promise.resolve([]);
    }

    const request = postJson(form.urls.validateCart, data)
        .then(({ status, body }) => {
            if (key !== lastKey) return pending; // llegó una más nueva
            if (status === 200 && body) {
                state.violations = body.violations || [];
                applyAvailable(body.available);
                if (!state.violations.length && state.error === 'rules') state.error = null;
            }
            return state.violations;
        })
        .catch(() => {
            lastKey = null; // se reintenta en el próximo cambio o al pagar
            return state.violations;
        });
    pending = request;
    return request;
}

export function watchRules() {
    if (!state.config.hasRules) return;
    watch(() => JSON.stringify(payload()), () => {
        clearTimeout(timer);
        timer = setTimeout(checkRules, 350);
    }, { immediate: true });
}
