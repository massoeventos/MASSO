<script setup>
import { computed } from 'vue';
import { form, state, cart, entries, subtotal, total, hasSelection } from '../store';
import { t, money } from '../i18n';
import { payment, submit } from '../submit';
import BaseModal from './BaseModal.vue';

// "Confirmar detalles del pago": evento, tickets elegidos (por entrada, con
// sus actividades), cupón aplicado y total antes de enviar.
const amount = computed(() => (hasSelection.value ? money(total.value) : '$0'));

const lines = computed(() => {
    if (state.config.entriesMode) {
        return entries.value.map((entry) => ({
            key: entry.key,
            label: `${t('entry', entry.number)} · ${entry.ticket.name}`,
            amount: entry.ticket.price,
            items: Object.entries(entry.items).map(([id, qty]) => ({
                id,
                label: (qty > 1 ? `${qty} × ` : '') + state.tickets[id].name,
                amount: state.tickets[id].price * qty,
            })),
        }));
    }
    return Object.entries(cart.value).map(([id, qty]) => ({
        key: id,
        label: (qty > 1 ? `${qty} × ` : '') + state.tickets[id].name,
        amount: state.tickets[id].price * qty,
        items: [],
    }));
});

const coupon = computed(() => (state.coupon.code && state.coupon.percentage !== null ? state.coupon : null));

function close() {
    if (!payment.submitting) payment.confirmOpen = false;
}
</script>

<template>
    <BaseModal id="paymentConfirmationModal" :show="payment.confirmOpen" @close="close">
        <div class="modal-header bg-light">
            <h5 class="modal-title mb-0">{{ t('confirmTitle') }}</h5>
            <button type="button" class="close" :aria-label="t('close')" @click="close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
            <div class="mb-3 confirmation-label">{{ t('confirmSummary') }}</div>
            <div class="p-3 mb-3 confirmation-box">
                <div class="mt-2">
                    <div class="confirmation-key"><strong>{{ t('confirmEvent') }}</strong></div>
                    <div class="confirmation-value">{{ form.event.name || '—' }}</div>
                </div>
                <div v-if="lines.length" class="mt-3">
                    <div class="confirmation-key"><strong>{{ t('confirmTickets') }}</strong></div>
                    <ul class="confirmation-lines">
                        <li v-for="line in lines" :key="line.key">
                            <div class="confirmation-line"><span>{{ line.label }}</span><span>{{ money(line.amount) }}</span></div>
                            <div v-for="item in line.items" :key="item.id" class="confirmation-line confirmation-item">
                                <span>+ {{ item.label }}</span><span>{{ money(item.amount) }}</span>
                            </div>
                        </li>
                    </ul>
                    <template v-if="coupon">
                        <div class="confirmation-line confirmation-subtotal"><span>{{ t('confirmSubtotal') }}</span><span>{{ money(subtotal) }}</span></div>
                        <div class="confirmation-line confirmation-discount">
                            <span>{{ t('confirmCoupon', coupon.code, coupon.percentage) }}</span><span>−{{ money(subtotal - total) }}</span>
                        </div>
                    </template>
                </div>
                <div class="mt-3">
                    <div class="confirmation-key"><strong>{{ t('confirmTotal') }}</strong></div>
                    <div style="display: flex; align-items: baseline; gap: 8px;">
                        <span class="confirmation-amount">{{ amount }}</span>
                        <span class="confirmation-currency">CLP</span>
                    </div>
                </div>
            </div>

            <div v-if="payment.method === 'transfer'">
                <div class="confirmation-label">{{ t('confirmTransferTitle') }}</div>
                <div class="border p-3 mt-2 confirmation-transfer">
                    {{ t('confirmTransferText') }}
                    <a :href="'mailto:' + form.paymentContactEmail">{{ form.paymentContactEmail }}</a>.
                </div>
            </div>
        </div>
        <div class="modal-footer d-flex flex-column flex-sm-row justify-content-center align-items-stretch">
            <button type="button" class="btn btn-outline-secondary bg-secondary mb-2 mb-sm-0" :disabled="payment.submitting" @click="close">{{ t('cancel') }}</button>
            <button type="button" class="btn" :disabled="payment.submitting" @click="submit">
                {{ payment.submitting ? t('processing') : t('confirmPay') }}
            </button>
        </div>
    </BaseModal>
</template>
