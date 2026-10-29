<script setup>
import { computed } from 'vue';
import { form, isLocked, clearFieldError, fieldError } from '../store';
import { t } from '../i18n';
import { changeEmail, hasLockedData } from '../identity';
import FieldError from './FieldError.vue';

// Correo del comprador: lo fija el paso de identificación (IdentificationModal).
const status = computed(() => {
    if (!form.identity.identified) return '';
    if (form.identity.viaSession) return t('statusSession');
    return form.identity.hasData ? t('statusConfirmed') : '';
});

// "Usar otro correo" no cierra la sesión del cliente (ej. compra para otra
// persona): la compra se vincula por el correo del formulario
// (PublicController::linkCustomerToPurchase), así que se avisa a qué cuenta va.
const normalize = (email) => String(email || '').trim().toLowerCase();
const otherEmailNote = computed(() => {
    const email = form.values.email;
    if (!form.sessionEmail || !email || normalize(email) === normalize(form.sessionEmail)) return '';
    return t('otherEmailNote', form.sessionEmail, email.trim());
});
</script>

<template>
    <div class="col-md-12 form-group">
        <label>{{ t('email') }} *</label>
        <input
            v-model="form.values.email"
            type="email"
            name="email"
            class="form-control"
            :class="{ 'locked-field': isLocked('email'), 'is-invalid': fieldError('email') }"
            :readonly="isLocked('email')"
            autocomplete="off"
            required
            @input="clearFieldError('email')"
        >
        <FieldError name="email" />
        <small v-if="status" class="d-block mt-1 text-success">{{ status }}</small>
        <small v-if="otherEmailNote" class="d-block mt-1 text-muted">{{ otherEmailNote }}</small>
        <small v-if="hasLockedData()" class="d-block mt-1">
            {{ t('lockedBanner') }}
            <a :href="form.urls.profile" target="_blank" class="customer-link">{{ t('lockedBannerLink') }}</a>.
        </small>
        <a v-if="form.identity.identified" href="#" class="customer-link small d-block mt-1" @click.prevent="changeEmail">
            {{ t('useAnotherEmail') }}
        </a>
    </div>
</template>
