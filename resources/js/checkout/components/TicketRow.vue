<script setup>
import { computed } from 'vue';
import { state, isSelected, toggleTicket } from '../store';
import { t, money } from '../i18n';

// Una fila de ticket. El input solo marca la selección (sin `name`): un
// ticket puede repetirse en varias categorías, así que lo que se envía lo
// arma TicketSelector una sola vez por ticket (ticket[] y su documento).
const props = defineProps({
    ticket: { type: Object, required: true },
    index: { type: Number, required: true },
});

const selected = computed(() => isSelected(props.ticket.id));

function onChange(e) {
    toggleTicket(props.ticket.id);
    // si la selección fue bloqueada (máximo alcanzado), revertir el check
    e.target.checked = isSelected(props.ticket.id);
}
</script>

<template>
    <div class="form-group row">
        <div class="ticket-row col-md-12" :class="'ticket-' + (index % 2)">
            <div class="row">
                <div class="col-md-1">
                    <input
                        :type="state.config.multiple ? 'checkbox' : 'radio'"
                        :value="ticket.id"
                        class="ticket-input"
                        :checked="selected"
                        @change="onChange"
                    >
                </div>
                <div class="col-md-11">
                    <p class="ticket-name">
                        {{ ticket.name }}
                        <i v-if="ticket.isMandatory">({{ t('mandatory') }})</i>
                        <b>CLP{{ money(ticket.price) }}</b>
                    </p>
                    <p class="ticket-description">{{ ticket.description }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
