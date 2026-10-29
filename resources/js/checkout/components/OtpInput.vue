<script setup>
import { ref, nextTick } from 'vue';

// Código de 6 dígitos en casillas separadas: avanza solo, Backspace vuelve a
// la anterior, acepta pegar el código completo y emite `complete` al llenar
// las 6 casillas. v-model = el código como string.
const props = defineProps({
    modelValue: { type: String, default: '' },
    length: { type: Number, default: 6 },
});
const emit = defineEmits(['update:modelValue', 'complete', 'submit']);

const digits = ref(Array.from({ length: props.length }, (_, i) => props.modelValue[i] || ''));
const boxes = ref([]);

function sync() {
    const code = digits.value.join('');
    emit('update:modelValue', code);
    if (code.length === props.length) emit('complete', code);
}

function onInput(i, e) {
    const value = e.target.value.replace(/\D/g, '').slice(-1);
    digits.value[i] = value;
    e.target.value = value;
    if (value && i < props.length - 1) boxes.value[i + 1].focus();
    sync();
}

function onKeydown(i, e) {
    if (e.key === 'Backspace' && !digits.value[i] && i > 0) {
        boxes.value[i - 1].focus();
    } else if (e.key === 'Enter') {
        e.preventDefault();
        emit('submit');
    }
}

function onPaste(e) {
    e.preventDefault();
    const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, props.length);
    if (!pasted) return;
    digits.value = Array.from({ length: props.length }, (_, i) => pasted[i] || '');
    boxes.value[Math.min(pasted.length, props.length) - 1].focus();
    sync();
}

function clear() {
    digits.value = Array.from({ length: props.length }, () => '');
    sync();
}

async function focusFirst() {
    await nextTick();
    if (boxes.value[0]) boxes.value[0].focus();
}

defineExpose({ clear, focusFirst });
</script>

<template>
    <div class="otp-input-group">
        <input
            v-for="(digit, i) in digits"
            :key="i"
            :ref="(el) => (boxes[i] = el)"
            :value="digit"
            type="text"
            class="otp-box"
            inputmode="numeric"
            maxlength="1"
            autocomplete="one-time-code"
            @input="onInput(i, $event)"
            @keydown="onKeydown(i, $event)"
            @paste="onPaste"
        >
    </div>
</template>
