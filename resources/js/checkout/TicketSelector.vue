<script setup>
import { computed } from 'vue';
import { state, fieldError, clearFieldError } from './store';
import { t } from './i18n';
import TicketRow from './components/TicketRow.vue';
import FieldError from './components/FieldError.vue';

// Tickets elegidos que piden documento de respaldo (uno por ticket, aunque el
// ticket aparezca en varias categorías).
const documentTickets = computed(() =>
    state.selected.map((id) => state.tickets[id]).filter((ticket) => ticket && ticket.requiresDocument)
);

// Índice global de fila para alternar el fondo (ticket-0 / ticket-1), igual
// que el $rowIndex del listado Blade anterior.
const rows = computed(() => {
    let index = 0;
    return state.groups.map((group) => ({
        ...group,
        rows: group.ticketIds.map((id) => ({ ticket: state.tickets[id], index: index++ })),
    }));
});

function selectGroup(key) {
    state.activeGroup = key;
}
</script>

<template>
    <div class="col-md-12">
        <h4>{{ t('heading') }}</h4>
        <p v-if="state.config.layout === 'flat'">{{ t('hintFlat') }}</p>
        <p v-else-if="state.config.layout === 'tabs'">{{ t('hintTabs') }}</p>
    </div>

    <div class="col-md-12 ticket-wrapper">
        <div v-if="state.warning === 'max'" class="alert alert-warning" role="alert">
            {{ t('maxSelection', state.config.maxSelection) }}
        </div>

        <template v-if="state.config.layout === 'tabs'">
            <div class="col-md-12 ticket-category-selector" role="tablist">
                <button
                    v-for="group in rows"
                    :key="group.key"
                    type="button"
                    class="btn ticket-category-btn"
                    :class="{ active: state.activeGroup === group.key }"
                    @click="selectGroup(group.key)"
                >
                    {{ group.label }}
                </button>
            </div>
            <div
                v-for="group in rows"
                v-show="state.activeGroup === group.key"
                :key="group.key"
                class="col-md-12 ticket-category-panel"
            >
                <TicketRow v-for="row in group.rows" :key="row.ticket.id" :ticket="row.ticket" :index="row.index" />
            </div>
        </template>

        <template v-else>
            <template v-for="group in rows" :key="group.key">
                <h5 v-if="state.config.layout === 'single'" class="ticket-category-title col-md-12">{{ group.label }}</h5>
                <TicketRow v-for="row in group.rows" :key="row.ticket.id" :ticket="row.ticket" :index="row.index" />
            </template>
        </template>

        <!-- lo que se envía: cada ticket elegido una sola vez -->
        <input v-for="id in state.selected" :key="'sel-' + id" type="hidden" name="ticket[]" :value="id">

        <div
            v-for="ticket in documentTickets"
            :key="'doc-' + ticket.id"
            class="col-md-12 ticket-document-wrapper"
            style="margin-top: 10px;"
        >
            <label style="font-size: 13px;">{{ t('documentLabel') }} ({{ ticket.name }}) *</label>
            <input
                type="file"
                class="form-control ticket-document-input"
                :class="{ 'is-invalid': fieldError('ticket_document.' + ticket.id) }"
                :name="'ticket_document[' + ticket.id + ']'"
                accept=".png,.jpg,.jpeg,.pdf"
                required
                @change="clearFieldError('ticket_document.' + ticket.id)"
            >
            <small class="text-muted" style="font-size: 12px;">{{ t('documentHint') }}</small>
            <FieldError :name="'ticket_document.' + ticket.id" />
        </div>

        <br>
        <div v-if="state.error === 'empty'" class="alert alert-danger ticket-error" role="alert">{{ t('errorEmpty') }}</div>
        <div v-if="state.error === 'mandatory'" class="alert alert-danger ticket-error" role="alert">{{ t('errorMandatory') }}</div>
    </div>
</template>
