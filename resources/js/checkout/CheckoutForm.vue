<script setup>
import { ref, computed, onMounted } from 'vue';
import { form } from './store';
import { t } from './i18n';
import { startIdentity } from './identity';
import { registerForm } from './submit';
import IdentificationModal from './components/IdentificationModal.vue';
import EmailField from './components/EmailField.vue';
import PersonalFields from './components/PersonalFields.vue';
import NationalityFields from './components/NationalityFields.vue';
import LocationFields from './components/LocationFields.vue';
import EventInputs from './components/EventInputs.vue';
import BillingFields from './components/BillingFields.vue';
import TicketSelector from './TicketSelector.vue';
import CartSummary from './CartSummary.vue';
import TermsField from './components/TermsField.vue';
import PaymentButtons from './components/PaymentButtons.vue';
import ConfirmationModal from './components/ConfirmationModal.vue';
import DuplicateModal from './components/DuplicateModal.vue';

// Raíz del formulario de compra (datos iniciales: PublicController::buildCheckoutPayload).
// El envío lo hace submit.js por fetch; el <form> agrupa los campos para FormData
// y la validación HTML5.
const formEl = ref(null);
const hasFieldErrors = computed(() => Object.keys(form.errors).length > 0);

// Errores del servidor que no tienen un campo propio en pantalla (tickets,
// documentos de ticket, medio de pago): se listan en el aviso general.
const generalErrors = computed(() =>
    Object.entries(form.errors)
        .filter(([key]) => /^(payment|ticket|check|coupon)/.test(key))
        .map(([, messages]) => messages[0])
);

onMounted(() => {
    registerForm(formEl.value);
    startIdentity({
        sessionIdentified: form.sessionIdentified,
        autofill: form.autofill,
        oldEmail: form.oldEmail,
    });
});
</script>

<template>
    <div class="row">
        <div v-if="form.flashError" class="col-lg-9 mx-auto">
            <!-- mensajes armados por PublicController::process() (pueden traer HTML) -->
            <div class="alert alert-danger flash-alert" v-html="form.flashError"></div>
        </div>
        <div v-else-if="hasFieldErrors" class="col-lg-9 mx-auto">
            <div class="alert alert-danger">
                {{ t('formHasErrors') }}
                <ul v-if="generalErrors.length" class="mb-0 mt-2">
                    <li v-for="message in generalErrors" :key="message">{{ message }}</li>
                </ul>
            </div>
        </div>

        <div class="newsletter-form col-lg-9 mx-auto">
            <form
                ref="formEl"
                method="POST"
                :action="form.urls.process"
                class="media align-items-end row"
                id="event-register-form"
                enctype="multipart/form-data"
                novalidate
                @submit.prevent
            >
                <input type="hidden" name="_token" :value="form.csrf">

                <EmailField />
                <PersonalFields />
                <NationalityFields />
                <LocationFields v-if="form.event.showLocationFields" />
                <EventInputs />
                <BillingFields />

                <TicketSelector />

                <div class="col-md-12 text-center">
                    <TermsField />
                    <hr>
                    <div class="row mb-3 row-total-and-coupon">
                        <CartSummary />
                    </div>
                    <PaymentButtons />
                </div>
            </form>
        </div>

        <IdentificationModal />
        <ConfirmationModal />
        <DuplicateModal />
    </div>
</template>
