<script setup>
import { computed } from 'vue';
import { state, complementsFor, itemQuantity, maxItemQuantity, setItemQuantity } from '../store';
import { t, money } from '../i18n';
import RuleViolations from './RuleViolations.vue';

// Una entrada comprada (una unidad de entrada principal) con los
// complementarios que habilita. Lo que se envía lo arma TicketSelector.
const props = defineProps({
    entry: { type: Object, required: true },
});

const complements = computed(() => complementsFor(props.entry.ticket.id));
const violations = computed(() => state.violations.filter((v) => v.entry === props.entry.number));
const qty = (id) => itemQuantity(props.entry.key, id);
const max = (id) => maxItemQuantity(props.entry.key, id);

function onToggle(ticket, e) {
    setItemQuantity(props.entry.key, ticket.id, e.target.checked ? 1 : 0);
    e.target.checked = qty(ticket.id) > 0;
}
</script>

<template>
    <div v-if="complements.length" class="entry-card">
        <p class="entry-card-title">
            {{ t('entry', entry.number) }} · <b>{{ entry.ticket.name }}</b>
        </p>
        <div
            v-for="ticket in complements"
            :key="ticket.id"
            class="entry-item"
            :class="{ 'is-sold-out': ticket.soldOut }"
        >
            <label class="entry-item-label">
                <input
                    type="checkbox"
                    :checked="qty(ticket.id) > 0"
                    :disabled="ticket.soldOut || (qty(ticket.id) === 0 && max(ticket.id) < 1)"
                    @change="onToggle(ticket, $event)"
                >
                <span class="entry-item-name">{{ ticket.name }}</span>
                <span v-if="ticket.soldOut" class="ticket-sold-out">{{ t('soldOut') }}</span>
                <b>CLP{{ money(ticket.price) }}</b>
            </label>
            <p v-if="ticket.description" class="entry-item-description">{{ ticket.description }}</p>
            <div v-if="qty(ticket.id) > 0 && ticket.maxQuantity > 1" class="ticket-quantity">
                <span>{{ t('quantity') }}</span>
                <button
                    type="button"
                    class="btn btn-sm"
                    :aria-label="t('less')"
                    :disabled="qty(ticket.id) <= 1"
                    @click="setItemQuantity(entry.key, ticket.id, qty(ticket.id) - 1)"
                >−</button>
                <span class="ticket-quantity-value">{{ qty(ticket.id) }}</span>
                <button
                    type="button"
                    class="btn btn-sm"
                    :aria-label="t('more')"
                    :disabled="qty(ticket.id) >= max(ticket.id)"
                    @click="setItemQuantity(entry.key, ticket.id, qty(ticket.id) + 1)"
                >+</button>
                <span v-if="qty(ticket.id) > 1" class="ticket-quantity-total">= CLP{{ money(ticket.price * qty(ticket.id)) }}</span>
            </div>
        </div>
        <RuleViolations :violations="violations" in-entry />
    </div>
</template>
