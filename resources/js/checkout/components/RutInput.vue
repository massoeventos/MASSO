<script setup>
import { ref, watch, onMounted } from 'vue';
import { cleanRutInput, isValidRut } from '../rut';
import { t } from '../i18n';

// Input de RUT: limpia caracteres no válidos al escribir y marca el campo
// inválido con setCustomValidity, así reportValidity() bloquea el envío.
const props = defineProps({
    modelValue: { type: String, default: '' },
    name: { type: String, required: true },
    required: { type: Boolean, default: false },
    readonly: { type: Boolean, default: false },
    serverError: { type: Boolean, default: false },
});
const emit = defineEmits(['update:modelValue', 'edited']);

const el = ref(null);
const invalid = ref(false);

function check(value) {
    invalid.value = !!value && !isValidRut(value);
    if (el.value) el.value.setCustomValidity(invalid.value ? t('rutInvalid') : '');
}

function onInput(e) {
    const cleaned = cleanRutInput(e.target.value);
    e.target.value = cleaned;
    emit('update:modelValue', cleaned);
    emit('edited');
    check(cleaned);
}

watch(() => props.modelValue, (value) => check(value));
onMounted(() => check(props.modelValue));
</script>

<template>
    <input
        ref="el"
        :value="modelValue"
        type="text"
        :name="name"
        class="form-control"
        :class="{ 'is-invalid': invalid || serverError, 'locked-field': readonly }"
        :readonly="readonly"
        :required="required"
        autocomplete="off"
        @input="onInput"
    >
</template>
