import { reactive } from 'vue';
import { form } from './store';
import { postJson } from './api';
import { t } from './i18n';

// Paso de identificación del checkout: el correo se pide primero (modal);
// si el cliente ya tiene datos guardados se confirma con un código (OTP) y
// esos datos se autocompletan bloqueados (se corrigen desde Mi Perfil).
// Mismos endpoints que el formulario anterior:
// PublicController::identifyCustomer / verifyCustomerCode / resendCustomerCode.

const PERSONAL_FIELDS = ['name', 'lastname', 'gender', 'nationality_country_id', 'rut', 'passport'];
const LOCATION_FIELDS = ['country_id', 'region_id', 'city_id', 'custom_city'];

export const feedback = reactive({
    email: { text: '', type: '' },
    code: { text: '', type: '' },
});

export function startIdentity({ sessionIdentified, autofill, oldEmail }) {
    const sameAsSession = sessionIdentified
        && String(oldEmail).trim().toLowerCase() === String(autofill.email || '').trim().toLowerCase();

    if (oldEmail && !sameAsSession) {
        // Vuelve de un envío con errores hecho con otro correo (sin sesión, o
        // usó "Usar otro correo"): se respeta lo enviado, sin autocompletar.
        form.values.email = oldEmail;
        form.identity.identified = false;
    } else if (sessionIdentified) {
        // Sesión activa (/mi-cuenta o una identificación previa): no se vuelve
        // a pedir nada. Si vuelve de un envío con el mismo correo, los campos
        // bloqueados traen el mismo valor que se envió.
        resolveIdentification(autofill.email || '', autofill, true);
    } else {
        openModal();
    }
}

export function openModal() {
    form.identity.step = 'email';
    form.identity.modalOpen = true;
}

const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export async function identify(rawEmail) {
    const email = rawEmail.trim();
    setFeedback('email', '', '');
    if (!email) return;
    if (!EMAIL_RE.test(email)) {
        setFeedback('email', t('invalidEmail'), 'danger');
        return;
    }

    try {
        const { status, body } = await postJson(form.urls.identify, { email });
        if (status === 422 || !body) {
            setFeedback('email', t('invalidEmail'), 'danger');
        } else if (body.status === 'existing') {
            form.identity.email = email;
            form.identity.canSms = !!body.can_sms;
            form.identity.step = 'code';
            setFeedback('code', '', '');
        } else {
            // correo nuevo (o sin datos guardados): pasa directo, sin código
            resolveIdentification(email, {}, false);
        }
    } catch (e) {
        setFeedback('email', t('genericError'), 'danger');
    }
}

export async function verifyCode(code) {
    setFeedback('code', '', '');
    if (!code) {
        setFeedback('code', t('enterCode'), 'danger');
        return;
    }

    try {
        const { body } = await postJson(form.urls.verify, { email: form.identity.email, code });
        if (body && body.verified) {
            // verify-customer-code inicia sesión del cliente y rota el token
            if (body.csrf_token) form.csrf = body.csrf_token;
            form.sessionEmail = form.identity.email;
            resolveIdentification(form.identity.email, body.autofill || {}, false);
        } else {
            setFeedback('code', (body && body.message) || t('invalidCode'), 'danger');
        }
    } catch (e) {
        setFeedback('code', t('genericError'), 'danger');
    }
}

export async function resendCode(channel) {
    try {
        const { body } = await postJson(form.urls.resend, { email: form.identity.email, channel });
        setFeedback('code', (body && body.message) || '', body && body.sent ? 'success' : 'danger');
    } catch (e) {
        setFeedback('code', t('resendError'), 'danger');
    }
}

// "Usar otro correo" dentro del modal: vuelve al paso del correo.
export function backToEmailStep() {
    form.identity.step = 'email';
    setFeedback('email', '', '');
    setFeedback('code', '', '');
}

// "Usar otro correo" desde el formulario: deja todo como sin identificar.
export function changeEmail() {
    [...PERSONAL_FIELDS, ...LOCATION_FIELDS].forEach((field) => {
        form.values[field] = '';
    });
    form.values.email = '';
    form.locked = {};
    Object.assign(form.identity, {
        email: '',
        identified: false,
        viaSession: false,
        hasData: false,
        canSms: false,
    });
    setFeedback('email', '', '');
    setFeedback('code', '', '');
    openModal();
}

function resolveIdentification(email, autofill, viaSession) {
    form.values.email = email;
    form.locked = { email: true };
    Object.assign(form.identity, {
        identified: true,
        viaSession,
        hasData: !!(autofill.name || autofill.lastname || autofill.rut || autofill.passport),
        modalOpen: false,
    });
    applyAutofill(autofill);
}

// Completa y bloquea solo los campos que el cliente ya tiene guardados; los
// que le faltan quedan editables (bloqueo campo por campo).
function applyAutofill(autofill) {
    ['name', 'lastname', 'gender', 'nationality_country_id'].forEach((field) => lockWith(field, autofill[field]));

    if (autofill.rut) {
        lockWith('rut', autofill.rut);
    } else if (autofill.passport) {
        lockWith('passport', autofill.passport);
    }

    // Ubicación como grupo. Residencia en Chile se guarda solo como city_id
    // (country_id queda null), así que Chile se deduce de la ciudad.
    const chileId = String(form.chileId);
    const livesInChile = !!autofill.city_id || String(autofill.country_id || '') === chileId;

    if (livesInChile) {
        form.values.country_id = chileId;
        if (autofill.city_id) {
            form.values.region_id = String(autofill.region_id || '');
            form.values.city_id = String(autofill.city_id);
            ['country_id', 'region_id', 'city_id'].forEach((f) => (form.locked[f] = true));
        }
    } else if (autofill.custom_city) {
        form.values.country_id = String(autofill.country_id || '');
        form.values.custom_city = autofill.custom_city;
        form.locked.country_id = true;
        form.locked.custom_city = true;
    }
}

function lockWith(field, value) {
    if (value === null || value === undefined || value === '') return;
    form.values[field] = String(value);
    form.locked[field] = true;
}

function setFeedback(kind, text, type) {
    feedback[kind].text = text;
    feedback[kind].type = type;
}

export function hasLockedData() {
    return Object.keys(form.locked).some((field) => field !== 'email');
}
