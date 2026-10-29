<script setup>
import { computed } from 'vue';
import { form, isLocked, clearFieldError, fieldError } from '../store';
import { t } from '../i18n';
import FieldError from './FieldError.vue';
import RutInput from './RutInput.vue';

// Nacionalidad chilena pide RUT; cualquier otra, DNI/pasaporte (mismo
// criterio que EnrollRequest). Solo se envía el campo que corresponde.
const isChilean = computed(() => String(form.values.nationality_country_id) === String(form.chileId));

function onNationalityChange() {
    clearFieldError('nationality_country_id');
    // al cambiar a mano, se limpia el documento que deja de aplicar
    if (isChilean.value) {
        if (!isLocked('passport')) form.values.passport = '';
    } else if (!isLocked('rut')) {
        form.values.rut = '';
    }
}
</script>

<template>
    <div class="col-md-6 form-group">
        <label>{{ t('nationality') }} *</label>
        <select
            v-model="form.values.nationality_country_id"
            name="nationality_country_id"
            class="form-control"
            :class="{ 'locked-field': isLocked('nationality_country_id'), 'is-invalid': fieldError('nationality_country_id') }"
            :disabled="isLocked('nationality_country_id')"
            required
            @change="onNationalityChange"
        >
            <option value="">{{ t('selectCountry') }}</option>
            <option v-for="country in form.countries" :key="country.id" :value="String(country.id)">{{ country.name }}</option>
        </select>
        <input v-if="isLocked('nationality_country_id')" type="hidden" name="nationality_country_id" :value="form.values.nationality_country_id">
        <FieldError name="nationality_country_id" />
    </div>

    <div v-if="isChilean" class="col-md-6 form-group">
        <label>{{ t('rut') }} *</label>
        <RutInput
            v-model="form.values.rut"
            name="rut"
            required
            :readonly="isLocked('rut')"
            :server-error="!!fieldError('rut')"
            @edited="clearFieldError('rut')"
        />
        <FieldError name="rut" />
    </div>

    <div v-else class="col-md-6 form-group">
        <label>{{ t('passport') }} *</label>
        <input
            v-model="form.values.passport"
            type="text"
            name="passport"
            class="form-control"
            :class="{ 'locked-field': isLocked('passport'), 'is-invalid': fieldError('passport') }"
            :readonly="isLocked('passport')"
            autocomplete="off"
            required
            @input="clearFieldError('passport')"
        >
        <FieldError name="passport" />
    </div>
</template>
