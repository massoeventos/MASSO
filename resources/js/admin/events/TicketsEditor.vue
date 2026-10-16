<script setup>
import { reactive, ref, computed } from 'vue';

// Categorías (tipos de entrada) y entradas del evento. Dibuja los inputs que
// procesa EventController::syncTicketsAndCategories():
//   ticket_categories[clave][name|name_eng|position|is_active]
//   tickets[clave][...|capacity|max_per_purchase|kind|is_active|category_ids[]|allowed_with[]]
// Las claves nuevas son temporales (numéricas); el controlador las crea y
// resuelve las categorías de cada entrada por esa clave.
// Los botones "Eliminar" no llevan la clase `.trash`: la vista conserva un
// handler jQuery delegado en `.ticket-wrapper .trash span` para las filas de
// "Formulario de Registro", que borraría estas filas por fuera de Vue.
const props = defineProps({
    categories: { type: Array, default: () => [] },
    tickets: { type: Array, default: () => [] },
    // reglas de compra que nombran cada categoría o sus entradas (por clave)
    ruleCounts: { type: Object, default: () => ({}) },
    rulesUrl: { type: String, default: null },
});

const rulesLabel = (key) => {
    const n = props.ruleCounts[key] || 0;
    return n ? ` · ${n} ${n === 1 ? 'regla' : 'reglas'}` : '';
};

const categories = reactive(props.categories.map((c) => ({ ...c })));
const tickets = reactive(props.tickets.map((t) => ({ ...t, allowed_keys: t.allowed_keys || [], price: formatPrice(t.price) })));

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

// --- Filtro por categoría (solo visual: todas las entradas se envían siempre)
const filter = ref('all'); // 'all' | 'none' | clave de categoría

const visibleTickets = computed(() => {
    if (filter.value === 'all') return tickets;
    if (filter.value === 'none') return tickets.filter((t) => t.category_keys.length === 0);
    return tickets.filter((t) => t.category_keys.includes(filter.value));
});

const countFor = (key) =>
    key === 'none'
        ? tickets.filter((t) => t.category_keys.length === 0).length
        : tickets.filter((t) => t.category_keys.includes(key)).length;

// --- Orden: con filtro, subir/bajar intercambia con la entrada visible vecina
function moveTicket(ticket, delta) {
    const visible = visibleTickets.value;
    const neighbor = visible[visible.indexOf(ticket) + delta];
    if (!neighbor) return;
    const a = tickets.indexOf(ticket);
    const b = tickets.indexOf(neighbor);
    [tickets[a], tickets[b]] = [tickets[b], tickets[a]];
}

function moveCategory(index, delta) {
    const target = index + delta;
    if (target < 0 || target >= categories.length) return;
    const [item] = categories.splice(index, 1);
    categories.splice(target, 0, item);
}

function addCategory() {
    categories.push({ key: newKey(), name: '', name_eng: '', is_active: true });
}

function removeCategory(index) {
    if (!confirm('¿Esta seguro? Las entradas dejarán de estar en esta categoría.')) return;
    const [removed] = categories.splice(index, 1);
    tickets.forEach((t) => {
        t.category_keys = t.category_keys.filter((key) => key !== removed.key);
    });
    if (filter.value === removed.key) filter.value = 'all';
}

function addTicket() {
    tickets.push({
        key: newKey(),
        name: '',
        name_eng: '',
        description: '',
        description_eng: '',
        // con un filtro de categoría activo, la entrada nueva nace en esa categoría
        category_keys: !['all', 'none'].includes(filter.value) ? [filter.value] : [],
        price: '',
        capacity: '',
        max_per_purchase: '1',
        sold: 0,
        from: today(),
        to: today(),
        is_mandatory: false,
        requires_document: false,
        is_active: true,
        kind: 'principal',
        allowed_keys: [],
    });
}

function removeTicket(ticket) {
    const message = ticket.sold > 0
        ? `Esta entrada tiene ${ticket.sold} vendidas. ¿Esta seguro de eliminarla?`
        : '¿Esta seguro?';
    if (!confirm(message)) return;
    tickets.splice(tickets.indexOf(ticket), 1);
    tickets.forEach((t) => toggleAllowed(t, ticket.key, false));
}

// Se modifica el arreglo en sí (no v-model): con v-model sobre un arreglo,
// dos cambios seguidos antes de re-renderizar pisaban el primero.
function toggleCategory(ticket, key, checked) {
    const index = ticket.category_keys.indexOf(key);
    if (checked && index === -1) ticket.category_keys.push(key);
    if (!checked && index !== -1) ticket.category_keys.splice(index, 1);
}

function toggleAllowed(ticket, key, checked) {
    const index = ticket.allowed_keys.indexOf(key);
    if (checked && index === -1) ticket.allowed_keys.push(key);
    if (!checked && index !== -1) ticket.allowed_keys.splice(index, 1);
}

// Entradas principales: las que pueden habilitar complementarios.
const principals = computed(() => tickets.filter((t) => t.kind !== 'complementario'));
const ticketName = (ticket) => ticket.name || '(sin nombre)';

const available = (ticket) => Math.max(0, (parseInt(ticket.capacity, 10) || 0) - ticket.sold);
const capacityTooLow = (ticket) => ticket.capacity !== '' && parseInt(ticket.capacity, 10) < ticket.sold;
const categoryName = (category) => category.name || '(sin nombre)';
</script>

<template>
    <div class="col-md-12">
        <label class="m-t-20 col-12">Tipos de Entrada / Categorías (opcional)</label>
        <small class="col-12" style="padding-bottom: 15px;display: block;text-align: justify;">* Opcional. Si define categorías, las entradas podrán agruparse bajo ellas en la página pública y el registro. Una entrada puede estar en varias categorías. Si no define ninguna, las entradas se mostrarán como hasta ahora.</small>

        <div v-for="(category, index) in categories" :key="category.key" class="row category-wrapper" :class="{ 'is-inactive': !category.is_active }">
            <div class="col-5">
                <small>Nombre Categoría</small>
                <input v-model="category.name" type="text" class="form-control" :name="`ticket_categories[${category.key}][name]`" placeholder="Ej: Médicos" required>
            </div>
            <div class="col-5">
                <small>Nombre Categoría Inglés</small>
                <input v-model="category.name_eng" type="text" class="form-control" :name="`ticket_categories[${category.key}][name_eng]`" placeholder="Ej: Physicians">
            </div>
            <div class="col-2">
                <small>Activa</small>
                <select v-model="category.is_active" class="form-control" :name="`ticket_categories[${category.key}][is_active]`">
                    <option :value="true">Sí</option>
                    <option :value="false">No</option>
                </select>
            </div>
            <div class="col-12 text-right category-order-actions">
                <input type="hidden" :name="`ticket_categories[${category.key}][position]`" :value="index">
                <button type="button" class="btn btn-xs btn-light" title="Subir categoría" :disabled="index === 0" @click="moveCategory(index, -1)"><i class="fa fa-arrow-up"></i></button>
                <button type="button" class="btn btn-xs btn-light" title="Bajar categoría" :disabled="index === categories.length - 1" @click="moveCategory(index, 1)"><i class="fa fa-arrow-down"></i></button>
            </div>
            <small class="col-12 text-right"><span class="btn btn-xs btn-danger" @click="removeCategory(index)"> <i class="fa fa-trash"></i> Eliminar</span></small>
        </div>

        <div class="col-12 mt-3 text-right">
            <span class="btn btn-dark" @click="addCategory">Añadir Categoría</span>
        </div>
    </div>

    <div class="col-md-12">
        <label class="m-t-20 col-12">Entradas / Tickets</label>
        <small class="col-12" style="padding-bottom: 15px;display: block;text-align: justify;"><template v-if="rulesUrl">Las restricciones entre entradas (requiere, no se combina, máximos) se configuran en <a :href="rulesUrl">Reglas de compra</a>. </template>* Puede ingresar los tipos de entrada disponibles. Puede especificar una fecha de inicio y término que es la ventana de tiempo que estará disponible dicha entrada. El cupo total es la cantidad máxima que se puede vender; los disponibles se calculan con lo ya vendido.</small>

        <div v-if="categories.length" class="col-12 tickets-filter">
            <small>Ver entradas de</small>
            <span class="tickets-filter-btn" :class="{ active: filter === 'all' }" @click="filter = 'all'">Todas ({{ tickets.length }})</span>
            <span
                v-for="category in categories"
                :key="'f-' + category.key"
                class="tickets-filter-btn"
                :class="{ active: filter === category.key, 'is-inactive': !category.is_active }"
                @click="filter = category.key"
            >{{ categoryName(category) }} ({{ countFor(category.key) }}){{ rulesLabel(category.key) }}</span>
            <span class="tickets-filter-btn" :class="{ active: filter === 'none' }" @click="filter = 'none'">Sin categoría ({{ countFor('none') }})</span>
        </div>

        <!-- v-show: las entradas filtradas siguen en el form y se envían -->
        <div
            v-for="ticket in tickets"
            v-show="visibleTickets.includes(ticket)"
            :key="ticket.key"
            class="row ticket-wrapper ticket-item"
            :class="{ 'is-inactive': !ticket.is_active }"
        >
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
            <div v-if="categories.length" class="col-12">
                <small>Categorías</small>
                <div class="ticket-categories">
                    <label v-for="category in categories" :key="'c-' + category.key" class="ticket-category-option">
                        <input
                            type="checkbox"
                            :name="`tickets[${ticket.key}][category_ids][]`"
                            :value="category.key"
                            :checked="ticket.category_keys.includes(category.key)"
                            @change="toggleCategory(ticket, category.key, $event.target.checked)"
                        >
                        {{ categoryName(category) }}
                    </label>
                    <span v-if="!ticket.category_keys.length" class="text-muted">Sin categoría / General</span>
                </div>
            </div>
            <div class="col-3">
                <small>Precio</small>
                <input :value="ticket.price" type="text" class="form-control" :name="`tickets[${ticket.key}][price]`" placeholder="Valor entrada" required @input="onPriceInput(ticket, $event)">
            </div>
            <div class="col-3">
                <small>Cupo total</small>
                <input v-model="ticket.capacity" type="number" min="0" class="form-control" :class="{ 'is-invalid': capacityTooLow(ticket) }" :name="`tickets[${ticket.key}][capacity]`" placeholder="Cupo" required>
                <span class="ticket-sales" :class="{ 'text-danger': capacityTooLow(ticket) }">
                    Vendidos {{ ticket.sold }} · Disponibles {{ available(ticket) }}
                </span>
            </div>
            <div class="col-3">
                <small>Máx. por compra</small>
                <input v-model="ticket.max_per_purchase" type="number" min="1" max="100" class="form-control" :name="`tickets[${ticket.key}][max_per_purchase]`" required>
                <span class="ticket-sales">{{ ticket.kind === 'complementario' ? 'Unidades por cada entrada' : 'Unidades de esta entrada en una compra' }}</span>
            </div>
            <div class="col-3">
                <small>Tipo</small>
                <select v-model="ticket.kind" class="form-control" :name="`tickets[${ticket.key}][kind]`">
                    <option value="principal">Principal (inscripción)</option>
                    <option value="complementario">Complementario (taller, cena…)</option>
                </select>
            </div>
            <div v-if="ticket.kind === 'complementario'" class="col-12">
                <small>Disponible con</small>
                <div class="ticket-categories">
                    <label v-for="principal in principals" :key="'a-' + principal.key" class="ticket-category-option">
                        <input
                            type="checkbox"
                            :name="`tickets[${ticket.key}][allowed_with][]`"
                            :value="principal.key"
                            :checked="ticket.allowed_keys.includes(principal.key)"
                            @change="toggleAllowed(ticket, principal.key, $event.target.checked)"
                        >
                        {{ ticketName(principal) }}
                    </label>
                    <span v-if="!principals.length" class="text-muted">No hay entradas principales.</span>
                    <span v-else-if="!ticket.allowed_keys.length" class="text-muted">Todas las entradas principales</span>
                </div>
                <span class="ticket-sales">Se elige dentro de cada entrada comprada (ej. 2 entradas, cada una con su taller).</span>
            </div>
            <div class="col-3">
                <small>Activa (a la venta)</small>
                <select v-model="ticket.is_active" class="form-control" :name="`tickets[${ticket.key}][is_active]`">
                    <option :value="true">Sí</option>
                    <option :value="false">No</option>
                </select>
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
                <input type="hidden" :name="`tickets[${ticket.key}][position]`" :value="tickets.indexOf(ticket)">
                <button type="button" class="btn btn-xs btn-light" title="Subir entrada" :disabled="visibleTickets.indexOf(ticket) === 0" @click="moveTicket(ticket, -1)"><i class="fa fa-arrow-up"></i></button>
                <button type="button" class="btn btn-xs btn-light" title="Bajar entrada" :disabled="visibleTickets.indexOf(ticket) === visibleTickets.length - 1" @click="moveTicket(ticket, 1)"><i class="fa fa-arrow-down"></i></button>
            </div>
            <small class="col-12 text-right"><span class="btn btn-xs btn-danger" @click="removeTicket(ticket)"> <i class="fa fa-trash"></i> Eliminar</span></small>
        </div>

        <div v-if="!visibleTickets.length" class="col-12 text-muted m-t-20">No hay entradas en esta categoría.</div>

        <div class="col-12 mt-3 text-right">
            <span class="btn btn-dark" @click="addTicket">Añadir Entrada</span>
        </div>
    </div>
</template>

<style>
.tickets-filter { margin-bottom: 10px; }
.tickets-filter small { display: block; text-transform: uppercase; font-size: 9px; padding: 0 4px 4px; }
.tickets-filter-btn {
    display: inline-block; cursor: pointer; margin: 0 6px 6px 0; padding: 3px 12px;
    border: 1px solid #ddd; border-radius: 14px; background: #f2f2f2; font-size: 12px;
}
.tickets-filter-btn.active { background: #e7015e; border-color: #e7015e; color: #fff; }
.ticket-categories { padding: 4px 4px 0; }
.ticket-category-option { margin-right: 14px; font-size: 13px; font-weight: normal; cursor: pointer; }
.ticket-category-option input { margin-right: 4px; vertical-align: middle; }
.ticket-sales { display: block; font-size: 11px; color: #6c757d; padding: 2px 4px 0; }
.is-inactive { opacity: .55; }
.tickets-filter-btn.active.is-inactive { opacity: .75; }
</style>
