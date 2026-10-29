<script setup>
import { ref } from 'vue';
import { form } from '../store';
import { t } from '../i18n';
import BaseModal from './BaseModal.vue';

// Casilla de términos (marcada por defecto, como antes) y el modal con las
// políticas del evento (HTML cargado desde el admin del evento).
const showTerms = ref(false);
</script>

<template>
    <div class="row">
        <div class="col-12">
            <br>
            <input type="checkbox" name="check" checked>
            <span style="cursor: pointer; color: #e7015e" @click="showTerms = true">{{ t('acceptTerms') }}</span>
        </div>
    </div>

    <BaseModal id="englishModal" :show="showTerms" size="lg" @close="showTerms = false">
        <div class="modal-header">
            <h5 class="modal-title">{{ t('termsTitle') }}</h5>
            <button type="button" class="close" :aria-label="t('close')" @click="showTerms = false"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body" v-html="form.event.termsHtml"></div>
    </BaseModal>
</template>
