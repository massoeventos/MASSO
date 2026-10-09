<script setup>
import { ref, computed } from 'vue';
import { state, total, hasSelection, clearCoupon } from './store';
import { t, money } from './i18n';
import { postJson } from './api';

const input = ref('');
const applied = computed(() => state.coupon.code !== '');

function onInput() {
    if (hasSelection.value) clearCoupon();
}

async function apply() {
    const code = input.value.trim();
    if (!code || applied.value || state.coupon.loading) return;

    if (!hasSelection.value) {
        state.coupon.feedback = t('couponNeedTicket');
        state.coupon.feedbackType = 'danger';
        return;
    }

    clearCoupon();
    state.coupon.loading = true;

    try {
        const { status, body } = await postJson(state.config.couponUrl, {
            event_id: state.config.eventId,
            coupon_code: code,
            tickets_ids: state.selected.map(String),
        });
        if (!body) throw new Error('respuesta no JSON');

        if (status === 200) {
            const percentage = Number(body.discount_percentage);
            if (isNaN(percentage)) throw new Error('discount_percentage no es un número válido');

            state.coupon.code = code;
            state.coupon.percentage = percentage;
            state.coupon.feedback = t('couponApplied', percentage);
            state.coupon.feedbackType = 'success';
        } else {
            let message = escapeHtml(body.message || t('couponError'));
            if (Array.isArray(body.invalid_tickets) && body.invalid_tickets.length) {
                message += '<br><span>' + body.invalid_tickets.map((n) => '• ' + escapeHtml(n)).join('<br>') + '</span>';
            }
            state.coupon.feedback = message;
            state.coupon.feedbackType = 'danger';
        }
    } catch (e) {
        clearCoupon();
        state.coupon.feedback = t('couponError');
        state.coupon.feedbackType = 'danger';
    } finally {
        state.coupon.loading = false;
    }
}

function cancel() {
    clearCoupon();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = String(text);
    return div.innerHTML;
}
</script>

<template>
    <div class="col-12 col-md-6 offset-md-3 form-group">
        <label for="coupon-code">{{ t('couponLabel') }}</label>
        <div class="input-group">
            <input
                id="coupon-code"
                v-model="input"
                type="text"
                class="form-control form-control-sm"
                autocomplete="off"
                :placeholder="t('couponPlaceholder')"
                :disabled="applied"
                @input="onInput"
                @keydown.enter.prevent="apply"
            >
            <div class="input-group-append">
                <button v-if="!state.coupon.loading" class="btn" type="button" :disabled="applied || input.trim() === ''" @click="apply">
                    {{ t('couponApply') }}
                </button>
                <button v-else class="btn" type="button">
                    <div class="spinner-border"></div>
                </button>
            </div>
        </div>
        <small
            id="coupon-feedback"
            :class="state.coupon.feedbackType ? 'text-' + state.coupon.feedbackType : 'text-muted'"
            v-html="state.coupon.feedback"
        ></small>

        <a v-if="applied" href="#" class="small text-danger" @click.prevent="cancel">{{ t('couponCancel') }}</a>

        <input type="hidden" name="coupon_code" :value="state.coupon.code">
    </div>

    <div class="col-12">
        <p class="mt-2 mb-4">
            {{ t('total') }} <span class="total">{{ hasSelection ? money(total) : '-' }}</span>
        </p>
    </div>
</template>
