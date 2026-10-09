<script setup>
import { computed } from 'vue';
import { form, hasSelection, subtotal } from '../store';
import { t } from '../i18n';
import { payment, startPayment } from '../submit';

// "Registrar" (gratis) solo cuando hay tickets elegidos y su subtotal es $0;
// si no, Transferencia (si el evento la permite) y Webpay.
const isFree = computed(() => hasSelection.value && subtotal.value === 0);
</script>

<template>
    <div class="row">
        <template v-if="!isFree">
            <div v-if="form.event.allowBankTransfer" class="col btn-not-free">
                <button type="button" class="btn submit-form" :disabled="payment.submitting" @click="startPayment('transfer')">
                    {{ t('payTransfer') }}
                </button>
                <small style="display: block">{{ t('payTransferNote') }}</small>
            </div>
            <div class="col btn-not-free">
                <button type="button" class="btn submit-form" :disabled="payment.submitting" @click="startPayment('webpay')">
                    {{ t('payWebpay') }}
                </button>
                <small style="display: block">{{ t('payWebpayNote') }}</small>
                <img style="width: 200px" src="/images/visamaster.png" alt="">
            </div>
        </template>
        <div v-else class="col btn-free">
            <button type="button" class="btn submit-form" :disabled="payment.submitting" @click="startPayment('free')">
                {{ payment.submitting ? t('processing') : t('payFree') }}
            </button>
        </div>
    </div>
</template>
