<script setup>
import { computed } from 'vue';
import { form, isLocked, clearFieldError, fieldError } from '../store';
import { t } from '../i18n';
import FieldError from './FieldError.vue';

const genderOptions = computed(() => Object.entries(t('genders')));
</script>

<template>
    <div class="col-md-6 form-group">
        <label>{{ t('name') }} *</label>
        <input
            v-model="form.values.name"
            type="text"
            name="name"
            class="form-control"
            :class="{ 'locked-field': isLocked('name'), 'is-invalid': fieldError('name') }"
            :readonly="isLocked('name')"
            autocomplete="off"
            required
            @input="clearFieldError('name')"
        >
        <FieldError name="name" />
    </div>

    <div class="col-md-6 form-group">
        <label>{{ t('lastname') }} *</label>
        <input
            v-model="form.values.lastname"
            type="text"
            name="lastname"
            class="form-control"
            :class="{ 'locked-field': isLocked('lastname'), 'is-invalid': fieldError('lastname') }"
            :readonly="isLocked('lastname')"
            autocomplete="off"
            required
            @input="clearFieldError('lastname')"
        >
        <FieldError name="lastname" />
    </div>

    <div class="col-md-12 form-group">
        <label>{{ t('gender') }} *</label>
        <!-- un select deshabilitado no se envía: el valor viaja en el hidden -->
        <select
            v-model="form.values.gender"
            name="gender"
            class="form-control"
            :class="{ 'locked-field': isLocked('gender'), 'is-invalid': fieldError('gender') }"
            :disabled="isLocked('gender')"
            required
            @change="clearFieldError('gender')"
        >
            <option value="">{{ t('selectOption') }}</option>
            <option v-for="[value, label] in genderOptions" :key="value" :value="value">{{ label }}</option>
        </select>
        <input v-if="isLocked('gender')" type="hidden" name="gender" :value="form.values.gender">
        <FieldError name="gender" />
    </div>
</template>
