<script setup>
import { reactive } from 'vue';

// Categorías (tipos de entrada) y entradas del evento. Dibuja los mismos
// inputs que el formulario Blade anterior (ticket_categories[clave][...],
// tickets[clave][...]); EventController::store()/update() resuelve las claves
// nuevas (temporales) y la categoría de cada entrada vía category_id = clave.
// Los botones "Eliminar" no llevan la clase `.trash`: la vista conserva un
// handler jQuery delegado en `.ticket-wrapper .trash span` para las filas de
// "Formulario de Registro", que borraría estas filas por fuera de Vue.
const props = defineProps({
    categories: { type: Array, default: () => [] },
    tickets: { type: Array, default: () => [] },
});

const categories = reactive(props.categories.map((c) => ({ ...c })));
const tickets = reactive(props.tickets.map((t) => ({ ...t, price: formatPrice(t.price) })));

// Claves numéricas para lo nuevo (como el Date.now() anterior): no chocan con
// ids reales y el controlador las trata como registros nuevos.
let seq = 0;
const newKey = () => String(Date.now() + seq++);
const today = () => new Date().toISOString().slice(0, 10);

function formatPrice(value) {
    const digits = String(value ?? '').replace(/\D/g, '');
    return digits ? '$' + Number(digits).toLocaleString('es-CL') : '';
}

function onPriceInput(ticket, e) {
    ticket.price = formatPrice(e.target.value);
    e.target.value = ticket.price;
}

function move(list, index, delta) {
    const target = index + delta;
    if (target < 0 || target >= list.length) return;
    const [item] = list.splice(index, 1);
    list.splice(target, 0, item);
}

function addCategory() {
    categories.push({ key: newKey(), name: '', name_eng: '' });
}

function removeCategory(index) {
    if (!confirm('¿Esta seguro? Las entradas asociadas a esta categoría quedarán sin categoría.')) return;
    const [removed] = categories.splice(index, 1);
    tickets.forEach((t) => {
        if (t.category_key === removed.key) t.category_key = '';
    });
}

function addTicket() {
    tickets.push({
        key: newKey(),
        name: '',
        name_eng: '',
        description: '',
        description_eng: '',
        category_key: '',
        price: '',
        stock: '',
        from: today(),
        to: today(),
        is_mandatory: false,
        requires_document: false,
    });
}

function removeTicket(index) {
    if (confirm('¿Esta seguro?')) tickets.splice(index, 1);
}
</script>

<template>
    <div class="col-md-12">
        <label class="m-t-20 col-12">Tipos de Entrada / Categorías (opcional)</label>
        <small class="col-12" style="padding-bottom: 15px;display: block;text-align: justify;">* Opcional. Si define categorías, las entradas podrán agruparse bajo ellas en la página pública y el registro. Si no define ninguna, las entradas se mostrarán como hasta ahora.</small>

        <div v-for="(category, index) in categories" :key="category.key" class="row category-wrapper">
            <div class="col-6">
                <small>Nombre Categoría</small>
                <input v-model="category.name" type="text" class="form-control" :name="`ticket_categories[${category.key}][name]`" placeholder="Ej: Médicos" required>
            </div>
            <div class="col-6">
                <small>Nombre Categoría Inglés</small>
                <input v-model="category.name_eng" type="text" class="form-control" :name="`ticket_categories[${category.key}][name_eng]`" placeholder="Ej: Physicians">
            </div>
            <div class="col-12 text-right category-order-actions">
                <input type="hidden" :name="`ticket_categories[${category.key}][position]`" :value="index">
                <button type="button" class="btn btn-xs btn-light" title="Subir categoría" :disabled="index === 0" @click="move(categories, index, -1)"><i class="fa fa-arrow-up"></i></button>
                <button type="button" class="btn btn-xs btn-light" title="Bajar categoría" :disabled="index === categories.length - 1" @click="move(categories, index, 1)"><i class="fa fa-arrow-down"></i></button>
            </div>
            <small class="col-12 text-right"><span class="btn btn-xs btn-danger" @click="removeCategory(index)"> <i class="fa fa-trash"></i> Eliminar</span></small>
        </div>

        <div class="col-12 mt-3 text-right">
            <span class="btn btn-dark" @click="addCategory">Añadir Categoría</span>
        </div>
    </div>

    <div class="col-md-12">
        <label class="m-t-20 col-12">Entradas / Tickets</label>
        <small class="col-12" style="padding-bottom: 15px;display: block;text-align: justify;">* Puede ingresar los tipos de entrada disponibles. Puede especificar una fecha de inicio y término que es la ventana de tiempo que estará disponible dicha entrada. Si deja vacío estos campos, se entiende que no existirán restricciones.</small>

        <div v-for="(ticket, index) in tickets" :key="ticket.key" class="row ticket-wrapper ticket-item">
            <div class="col-6">
                <small>Entrada</small>
                <input v-model="ticket.name" type="text" class="form-control" :name="`tickets[${ticket.key}][name]`" placeholder="Nombre entrada" required>
            </div>
            <div class="col-6">
                <small>Entrada Inglés</small>
                <input v-model="ticket.name_eng" type="text" class="form-control" :name="`tickets[${ticket.key}][name_eng]`" placeholder="Nombre entrada en inglés">
            </div>
            <div class="col-12">
                <small>Descripción</small>
                <input v-model="ticket.description" type="text" class="form-control" :name="`tickets[${ticket.key}][description]`" placeholder="Descripción de la entrada">
            </div>
            <div class="col-12">
                <small>Descripción Inglés</small>
                <input v-model="ticket.description_eng" type="text" class="form-control" :name="`tickets[${ticket.key}][description_eng]`" placeholder="Descripción de la entrada (inglés)">
            </div>
            <div class="col-6">
                <small>Categoría</small>
                <select v-model="ticket.category_key" class="form-control" :name="`tickets[${ticket.key}][category_id]`">
                    <option value="">Sin categoría / General</option>
                    <option v-for="category in categories" :key="category.key" :value="category.key">{{ category.name || '(sin nombre)' }}</option>
                </select>
            </div>
            <div class="col-3">
                <small>Precio</small>
                <input :value="ticket.price" type="text" class="form-control" :name="`tickets[${ticket.key}][price]`" placeholder="Valor entrada" required @input="onPriceInput(ticket, $event)">
            </div>
            <div class="col-3">
                <small>Stock</small>
                <input v-model="ticket.stock" type="number" class="form-control" :name="`tickets[${ticket.key}][stock]`" placeholder="Stock" required>
            </div>
            <div class="col-3">
                <small>Desde</small>
                <input v-model="ticket.from" type="date" class="form-control" :name="`tickets[${ticket.key}][from]`" required>
            </div>
            <div class="col-3">
                <small>Hasta</small>
                <input v-model="ticket.to" type="date" class="form-control" :name="`tickets[${ticket.key}][to]`" required>
            </div>
            <div class="col-3">
                <small>Ticket Obligatorio</small>
                <select v-model="ticket.is_mandatory" class="form-control" :name="`tickets[${ticket.key}][is_mandatory]`" required>
                    <option :value="false">No</option>
                    <option :value="true">Si</option>
                </select>
            </div>
            <div class="col-3">
                <small>Requiere Documento</small>
                <select v-model="ticket.requires_document" class="form-control" :name="`tickets[${ticket.key}][requires_document]`" required>
                    <option :value="false">No</option>
                    <option :value="true">Si</option>
                </select>
            </div>
            <div class="col-12 text-right ticket-order-actions">
                <input type="hidden" :name="`tickets[${ticket.key}][position]`" :value="index">
                <button type="button" class="btn btn-xs btn-light" title="Subir entrada" :disabled="index === 0" @click="move(tickets, index, -1)"><i class="fa fa-arrow-up"></i></button>
                <button type="button" class="btn btn-xs btn-light" title="Bajar entrada" :disabled="index === tickets.length - 1" @click="move(tickets, index, 1)"><i class="fa fa-arrow-down"></i></button>
            </div>
            <small class="col-12 text-right"><span class="btn btn-xs btn-danger" @click="removeTicket(index)"> <i class="fa fa-trash"></i> Eliminar</span></small>
        </div>

        <div class="col-12 mt-3 text-right">
            <span class="btn btn-dark" @click="addTicket">Añadir Entrada</span>
        </div>
    </div>
</template>
