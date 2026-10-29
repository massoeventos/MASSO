<script setup>
import { watch, onBeforeUnmount } from 'vue';

// Modal con el markup de Bootstrap 4, controlado por Vue (sin $.modal()).
// `persistent`: no se cierra con clic en el fondo ni con Escape.
const props = defineProps({
    id: { type: String, default: undefined },
    show: { type: Boolean, default: false },
    persistent: { type: Boolean, default: false },
    size: { type: String, default: '' }, // '', 'sm', 'lg'
    dialogClass: { type: String, default: '' },
});
const emit = defineEmits(['close']);

function onKeydown(e) {
    if (e.key === 'Escape' && !props.persistent) emit('close');
}

watch(
    () => props.show,
    (visible) => {
        document.body.classList.toggle('modal-open', visible);
        if (visible) document.addEventListener('keydown', onKeydown);
        else document.removeEventListener('keydown', onKeydown);
    },
    { immediate: true }
);

onBeforeUnmount(() => {
    document.body.classList.remove('modal-open');
    document.removeEventListener('keydown', onKeydown);
});

function onBackdropClick() {
    if (!props.persistent) emit('close');
}
</script>

<template>
    <Teleport to="body">
        <template v-if="show">
            <div :id="id" class="modal fade show d-block" tabindex="-1" role="dialog" aria-modal="true" @click.self="onBackdropClick">
                <div class="modal-dialog modal-dialog-centered" :class="[size ? 'modal-' + size : '', dialogClass]" role="document">
                    <div class="modal-content">
                        <slot />
                    </div>
                </div>
            </div>
            <div class="modal-backdrop fade show"></div>
        </template>
    </Teleport>
</template>
