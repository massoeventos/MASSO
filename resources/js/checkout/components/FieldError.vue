<script setup>
import { computed } from 'vue';
import { fieldError } from '../store';

// Error de validación del servidor para un campo (clave de Laravel).
const props = defineProps({
    name: { type: String, required: true },
});

const message = computed(() => fieldError(props.name));
</script>

<template>
    <small v-if="message" class="text-danger d-block mt-1">{{ message }}</small>
</template>
