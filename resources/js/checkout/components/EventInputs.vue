<script setup>
import { form, clearFieldError, fieldError } from '../store';
import FieldError from './FieldError.vue';

// Campos propios de cada evento (events_inputs: texto o archivo). El nombre
// del campo es el del input con espacios cambiados por "_", igual que espera
// PublicController::storeEventInputValues().
</script>

<template>
    <div v-for="input in form.inputs" :key="input.key" class="col-md-12 form-group">
        <label>{{ input.label }}<template v-if="input.required"> *</template></label>

        <input
            v-if="input.type === 'text'"
            v-model="form.inputValues[input.key]"
            type="text"
            :name="input.key"
            class="form-control"
            :class="{ 'is-invalid': fieldError(input.key) }"
            autocomplete="off"
            :required="input.required"
            @input="clearFieldError(input.key)"
        >
        <input
            v-else
            type="file"
            :name="input.key"
            class="form-control"
            :class="{ 'is-invalid': fieldError(input.key) }"
            accept=".png,.jpg,.jpeg,.pdf"
            :required="input.required"
            @change="clearFieldError(input.key)"
        >
        <FieldError :name="input.key" />
    </div>
</template>
