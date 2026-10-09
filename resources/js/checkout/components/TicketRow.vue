<script setup>
import { computed } from 'vue';
import { state, isSelected, toggleTicket, fieldError, clearFieldError } from '../store';
import { t, money } from '../i18n';
import FieldError from './FieldError.vue';

const props = defineProps({
    ticket: { type: Object, required: true },
    index: { type: Number, required: true },
});

const selected = computed(() => isSelected(props.ticket.id));
const documentKey = computed(() => `ticket_document.${props.ticket.id}`);

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
                        name="ticket[]"
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

                    <div v-if="ticket.requiresDocument" v-show="selected" class="ticket-document-wrapper" style="margin-top: 10px;">
                        <label style="font-size: 13px;">{{ t('documentLabel') }} *</label>
                        <input
                            type="file"
                            class="form-control ticket-document-input"
                            :class="{ 'is-invalid': fieldError(documentKey) }"
                            :name="'ticket_document[' + ticket.id + ']'"
                            accept=".png,.jpg,.jpeg,.pdf"
                            :required="selected"
                            @change="clearFieldError(documentKey)"
                        >
                        <small class="text-muted" style="font-size: 12px;">{{ t('documentHint') }}</small>
                        <FieldError :name="documentKey" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
