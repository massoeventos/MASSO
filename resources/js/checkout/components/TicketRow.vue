<script setup>
import { computed } from 'vue';
import { state, isSelected, toggleTicket, quantityOf, setQuantity } from '../store';
import { t, money } from '../i18n';

// Una fila de ticket. El input solo marca la selección (sin `name`): un
// ticket puede repetirse en varias categorías, así que lo que se envía lo
// arma TicketSelector una sola vez por ticket (ticket[], cantidad y documento).
const props = defineProps({
    ticket: { type: Object, required: true },
    index: { type: Number, required: true },
});

const selected = computed(() => isSelected(props.ticket.id));
const quantity = computed(() => quantityOf(props.ticket.id));
// el selector de cantidad aparece solo si el ticket admite más de una unidad
const showQuantity = computed(() => selected.value && props.ticket.maxQuantity > 1);

function onChange(e) {
    toggleTicket(props.ticket.id);
    // si la selección fue bloqueada (máximo alcanzado), revertir el check
    e.target.checked = isSelected(props.ticket.id);
}
</script>

<template>
    <div class="form-group row">
        <div class="ticket-row col-md-12" :class="['ticket-' + (index % 2), { 'is-sold-out': ticket.soldOut }]">
            <div class="row">
                <div class="col-md-1">
                    <input
                        :type="state.config.multiple ? 'checkbox' : 'radio'"
                        :value="ticket.id"
                        class="ticket-input"
                        :checked="selected"
                        :disabled="ticket.soldOut"
                        @change="onChange"
                    >
                </div>
                <div class="col-md-11">
                    <p class="ticket-name">
                        {{ ticket.name }}
                        <i v-if="ticket.isMandatory">({{ t('mandatory') }})</i>
                        <span v-if="ticket.soldOut" class="ticket-sold-out">{{ t('soldOut') }}</span>
                        <b>CLP{{ money(ticket.price) }}</b>
                    </p>
                    <p class="ticket-description">{{ ticket.description }}</p>
                    <div v-if="showQuantity" class="ticket-quantity">
                        <span>{{ t('quantity') }}</span>
                        <button
                            type="button"
                            class="btn btn-sm"
                            :aria-label="t('less')"
                            :disabled="quantity <= 1"
                            @click="setQuantity(ticket.id, quantity - 1)"
                        >−</button>
                        <span class="ticket-quantity-value">{{ quantity }}</span>
                        <button
                            type="button"
                            class="btn btn-sm"
                            :aria-label="t('more')"
                            :disabled="quantity >= ticket.maxQuantity"
                            @click="setQuantity(ticket.id, quantity + 1)"
                        >+</button>
                        <span v-if="quantity > 1" class="ticket-quantity-total">= CLP{{ money(ticket.price * quantity) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
