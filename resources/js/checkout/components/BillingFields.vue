<script setup>
import { computed } from 'vue';
import { form, clearFieldError, fieldError } from '../store';
import { t } from '../i18n';
import FieldError from './FieldError.vue';
import RutInput from './RutInput.vue';

// Boleta o factura. Con factura se piden los datos de la empresa
// (invoice_data[*], obligatorios salvo la observación); con boleta no se
// envían. Mismas reglas que EnrollRequest.
const isInvoice = computed(() => form.billing.method === 'invoice');

// campos de texto que van después de razón social y RUT
const textFields = [
    { key: 'business_activity', label: 'businessActivity', maxlength: 100 },
    { key: 'address', label: 'address', maxlength: 200 },
    { key: 'city', label: 'invoiceCity', maxlength: 100 },
    { key: 'phone', label: 'phone', maxlength: 20 },
];

const errorKey = (key) => `invoice_data.${key}`;
</script>

<template>
    <div class="col-md-12 form-group">
        <label>{{ t('billingMethod') }} *</label>
        <div class="form-check px-4">
            <input
                id="receipt_input"
                v-model="form.billing.method"
                class="form-check-input"
                type="radio"
                name="billing_method"
                value="receipt"
                required
                @change="clearFieldError('billing_method')"
            >
            <label class="form-check-label pl-1" for="receipt_input">{{ t('receipt') }}</label>
        </div>
        <div class="form-check px-4">
            <input
                id="invoice_input"
                v-model="form.billing.method"
                class="form-check-input"
                type="radio"
                name="billing_method"
                value="invoice"
                required
                @change="clearFieldError('billing_method')"
            >
            <label class="form-check-label pl-1" for="invoice_input">{{ t('invoice') }}</label>
        </div>
        <FieldError name="billing_method" />
    </div>

    <template v-if="isInvoice">
        <div class="form-group col-md-6">
            <label>{{ t('businessName') }} *</label>
            <input
                v-model="form.billing.invoice.business_name"
                type="text"
                name="invoice_data[business_name]"
                class="form-control"
                :class="{ 'is-invalid': fieldError(errorKey('business_name')) }"
                maxlength="100"
                required
                @input="clearFieldError(errorKey('business_name'))"
            >
            <FieldError :name="errorKey('business_name')" />
        </div>

        <div class="form-group col-md-6">
            <label>{{ t('invoiceRut') }} *</label>
            <RutInput
                v-model="form.billing.invoice.rut"
                name="invoice_data[rut]"
                required
                :server-error="!!fieldError(errorKey('rut'))"
                @edited="clearFieldError(errorKey('rut'))"
            />
            <FieldError :name="errorKey('rut')" />
        </div>

        <div v-for="field in textFields" :key="field.key" class="form-group col-md-6">
            <label>{{ t(field.label) }} *</label>
            <input
                v-model="form.billing.invoice[field.key]"
                type="text"
                :name="`invoice_data[${field.key}]`"
                class="form-control"
                :class="{ 'is-invalid': fieldError(errorKey(field.key)) }"
                :maxlength="field.maxlength"
                required
                @input="clearFieldError(errorKey(field.key))"
            >
            <FieldError :name="errorKey(field.key)" />
        </div>

        <div class="form-group col-12">
            <label>{{ t('note') }}</label>
            <textarea
                v-model="form.billing.invoice.note"
                name="invoice_data[note]"
                class="form-control"
                rows="2"
                maxlength="400"
            ></textarea>
            <FieldError :name="errorKey('note')" />
        </div>
    </template>
</template>
