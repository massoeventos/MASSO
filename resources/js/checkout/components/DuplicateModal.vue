<script setup>
import { t, money } from '../i18n';
import { payment, continueAfterDuplicate, resendLastPayment } from '../submit';
import BaseModal from './BaseModal.vue';

// Compra previa desde este dispositivo con el mismo correo y tickets
// (PublicController::checkDuplicatePayment): reenviar el correo de esa
// compra, o seguir con una nueva.
function close() {
    payment.duplicateOpen = false;
}
</script>

<template>
    <BaseModal id="duplicatePaymentModal" :show="payment.duplicateOpen" @close="close">
        <div class="modal-header bg-warning">
            <h5 class="modal-title mb-0">{{ t('duplicateTitle') }}</h5>
            <button type="button" class="close" :aria-label="t('close')" @click="close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div v-if="payment.duplicate" class="modal-body">
            <p class="mb-3">{{ t('duplicateText') }}</p>
            <div class="p-3 mb-3" style="background:#f8f9fa; border-radius:6px;">
                <div class="d-flex justify-content-between"><span class="text-muted">{{ t('dupEmail') }}</span><strong>{{ payment.duplicate.email || '—' }}</strong></div>
                <div class="d-flex justify-content-between mt-2"><span class="text-muted">{{ t('dupAmount') }}</span><strong>{{ payment.duplicate.amount ? money(payment.duplicate.amount) : '—' }}</strong></div>
                <div class="d-flex justify-content-between mt-2"><span class="text-muted">{{ t('dupStatus') }}</span><strong>{{ payment.duplicate.status || '—' }}</strong></div>
                <div class="d-flex justify-content-between mt-2"><span class="text-muted">{{ t('dupMethod') }}</span><strong>{{ payment.duplicate.managment || '—' }}</strong></div>
                <div class="d-flex justify-content-between mt-2"><span class="text-muted">{{ t('dupDate') }}</span><strong>{{ payment.duplicate.created_at || '—' }}</strong></div>
            </div>
            <div>
                <div class="text-muted mb-1">{{ t('dupTickets') }}</div>
                <ul class="pl-3 mb-0" style="list-style: disc;">
                    <li v-for="ticket in payment.duplicate.tickets || []" :key="ticket.id">
                        {{ ticket.name || 'Ticket' }}<template v-if="ticket.price"> - {{ money(ticket.price) }}</template>
                    </li>
                    <li v-if="!(payment.duplicate.tickets || []).length">{{ t('dupNoTickets') }}</li>
                </ul>
            </div>
            <small v-if="payment.duplicateFeedback.text" class="d-block mt-3" :class="'text-' + payment.duplicateFeedback.type">
                {{ payment.duplicateFeedback.text }}
            </small>
        </div>
        <div class="modal-footer d-flex flex-column align-items-stretch">
            <button type="button" class="btn btn-outline-secondary bg-secondary mb-2 small-btn" @click="close">{{ t('close') }}</button>
            <button type="button" class="btn bg-warning mb-2 small-btn" @click="continueAfterDuplicate">{{ t('dupContinue') }}</button>
            <button type="button" class="btn small-btn" :disabled="payment.resending" @click="resendLastPayment">
                {{ payment.resending ? t('sending') : t('dupResend') }}
            </button>
        </div>
    </BaseModal>
</template>
