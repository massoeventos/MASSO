<script setup>
import { state, canAddFor, addFor } from '../store';
import { t } from '../i18n';

// Reglas de compra no cumplidas (de la compra o de una entrada), con el
// botón "Agregar {ticket}" cuando la regla se resuelve agregando un ticket.
defineProps({
    violations: { type: Array, required: true },
    // dentro de la tarjeta de una entrada, sin "Entrada n (...): "
    inEntry: { type: Boolean, default: false },
});
</script>

<template>
    <div v-if="violations.length" class="alert alert-warning rule-violations" role="alert">
        <div v-for="(violation, i) in violations" :key="i" class="rule-violation">
            <span>{{ inEntry ? violation.text : violation.message }}</span>
            <button
                v-if="violation.addTicketId && canAddFor(violation)"
                type="button"
                class="btn btn-sm rule-add"
                @click="addFor(violation)"
            >{{ t('addTicket', state.tickets[violation.addTicketId].name) }}</button>
        </div>
    </div>
</template>
