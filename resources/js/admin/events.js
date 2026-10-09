import { createApp } from 'vue';
import TicketsEditor from './events/TicketsEditor.vue';

// Panel: editor de categorías y entradas del evento (crear/editar evento).
// Datos iniciales: EventController::ticketsEditorData(). Se envía con el
// form del evento de forma nativa, con los mismos nombres de campo de siempre.
const dataEl = document.getElementById('tickets-editor-data');
const mountEl = document.getElementById('tickets-editor');

if (dataEl && mountEl) {
    createApp(TicketsEditor, JSON.parse(dataEl.textContent)).mount(mountEl);
}
