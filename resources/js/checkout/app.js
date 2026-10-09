import { createApp } from 'vue';
import { initForm } from './store';
import CheckoutForm from './CheckoutForm.vue';
import './checkout.css';

// Entrada del formulario de compra (guest/register.blade.php).
// Datos iniciales: PublicController::buildCheckoutPayload().
const dataEl = document.getElementById('checkout-data');
const mountEl = document.getElementById('checkout-app');

if (dataEl && mountEl) {
    initForm(JSON.parse(dataEl.textContent));
    createApp(CheckoutForm).mount(mountEl);
}
