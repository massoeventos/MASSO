<script setup>
import { ref, watch, nextTick } from 'vue';
import { form } from '../store';
import { t } from '../i18n';
import { feedback, identify, verifyCode, resendCode, backToEmailStep } from '../identity';
import BaseModal from './BaseModal.vue';
import OtpInput from './OtpInput.vue';

// Modal obligatorio (no se cierra con el fondo ni con Escape) que pide el
// correo antes de usar el formulario; con datos guardados, pide el código.
const email = ref('');
const code = ref('');
const busy = ref(false);
const resending = ref(''); // canal en envío: 'email' | 'sms' | ''
const emailInput = ref(null);
const otp = ref(null);

watch(
    () => [form.identity.modalOpen, form.identity.step],
    async ([open, step]) => {
        if (!open) return;
        await nextTick();
        if (step === 'email') {
            email.value = email.value || form.identity.email;
            emailInput.value && emailInput.value.focus();
        } else {
            code.value = '';
            otp.value && otp.value.clear();
            otp.value && otp.value.focusFirst();
        }
    },
    { immediate: true }
);

async function submitEmail() {
    if (busy.value) return;
    busy.value = true;
    await identify(email.value);
    busy.value = false;
}

async function submitCode() {
    if (busy.value) return;
    busy.value = true;
    await verifyCode(code.value);
    busy.value = false;
}

async function resend(channel) {
    if (resending.value) return;
    resending.value = channel;
    await resendCode(channel);
    resending.value = '';
}
</script>

<template>
    <BaseModal id="identificationModal" :show="form.identity.modalOpen" persistent>
        <div class="modal-header">
            <div class="modal-icon">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="5" width="18" height="14" rx="2"></rect>
                    <path d="M3 7l9 6 9-6"></path>
                </svg>
            </div>
            <h5 class="modal-title mb-0">{{ t('idTitle') }}</h5>
        </div>

        <div class="modal-body">
            <div v-if="form.identity.step === 'email'">
                <p class="mb-3">{{ t('idIntro') }}</p>
                <div class="form-group">
                    <label>{{ t('email') }} *</label>
                    <input
                        ref="emailInput"
                        v-model="email"
                        type="email"
                        class="form-control"
                        autocomplete="off"
                        :placeholder="t('idPlaceholder')"
                        @keydown.enter.prevent="submitEmail"
                    >
                </div>
                <button type="button" class="btn btn-brand" :disabled="busy" @click="submitEmail">
                    {{ t('idContinue') }} <span aria-hidden="true">→</span>
                </button>
                <small class="d-block mt-2" :class="feedback.email.type ? 'text-' + feedback.email.type : ''">{{ feedback.email.text }}</small>
                <p class="privacy-note">🔒 {{ t('idPrivacy') }}</p>
            </div>

            <div v-else>
                <p class="mb-3">{{ t('idCodeIntro') }}</p>
                <OtpInput ref="otp" v-model="code" @complete="submitCode" @submit="submitCode" />
                <button type="button" class="btn btn-brand mt-3" :disabled="busy" @click="submitCode">{{ t('idVerify') }}</button>
                <small class="d-block mt-2" :class="feedback.code.type ? 'text-' + feedback.code.type : ''">{{ feedback.code.text }}</small>

                <a href="#" class="customer-link small d-block mt-3" @click.prevent="resend('email')">
                    {{ resending === 'email' ? t('sending') : t('idResendEmail') }}
                </a>
                <a v-if="form.identity.canSms" href="#" class="customer-link small d-block mt-1" @click.prevent="resend('sms')">
                    {{ resending === 'sms' ? t('sending') : t('idResendSms') }}
                </a>
                <a href="#" class="customer-link small d-block mt-1" @click.prevent="backToEmailStep">{{ t('useAnotherEmail') }}</a>
            </div>
        </div>
    </BaseModal>
</template>
