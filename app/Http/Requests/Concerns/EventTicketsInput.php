<?php

namespace Masso\Http\Requests\Concerns;

use Masso\EventTicket;

/**
 * Categorías y entradas del formulario de evento (crear/editar), tal como
 * las envía el editor Vue (resources/js/admin/events/TicketsEditor.vue).
 * Compartido por EventStoreRequest y EventUpdateRequest.
 */
trait EventTicketsInput
{
    protected function normalizeTicketsInput(array $input): array
    {
        $toFlag = fn ($value, $default = 1) => $value === null ? $default : (in_array($value, [1, '1', true, 'true'], true) ? 1 : 0);

        foreach ($input['ticket_categories'] ?? [] as $key => $category) {
            $input['ticket_categories'][$key]['is_active'] = $toFlag($category['is_active'] ?? null);
        }

        foreach ($input['tickets'] ?? [] as $key => $ticket) {
            $input['tickets'][$key]['price'] = str_replace(['$', 'e', ',', '.'], '', $ticket['price'] ?? '');
            $input['tickets'][$key]['is_mandatory'] = $toFlag($ticket['is_mandatory'] ?? null, 0);
            $input['tickets'][$key]['requires_document'] = $toFlag($ticket['requires_document'] ?? null, 0);
            $input['tickets'][$key]['is_active'] = $toFlag($ticket['is_active'] ?? null);
            $input['tickets'][$key]['kind'] = $ticket['kind'] ?? EventTicket::KIND_PRINCIPAL;
            // claves de categoría (ids reales o temporales de categorías nuevas)
            $input['tickets'][$key]['category_ids'] = array_values(array_filter((array) ($ticket['category_ids'] ?? [])));
            // claves de las entradas principales que habilitan este complementario
            $input['tickets'][$key]['allowed_with'] = array_values(array_filter((array) ($ticket['allowed_with'] ?? [])));
        }

        return $input;
    }

    protected function ticketRules(): array
    {
        return [
            'ticket_categories'                => 'nullable|array',
            'ticket_categories.*.name'         => 'required|string',
            'ticket_categories.*.name_eng'     => 'nullable|string',
            'ticket_categories.*.position'     => 'nullable|integer',
            'ticket_categories.*.is_active'    => 'required|in:0,1',

            'tickets'                    => 'nullable|array',
            'tickets.*.name'             => 'required|string',
            'tickets.*.name_eng'         => 'nullable|string',
            'tickets.*.description'      => 'nullable|string',
            'tickets.*.description_eng'  => 'nullable|string',
            'tickets.*.price'            => 'required|numeric|min:0',
            'tickets.*.capacity'         => 'required|integer|min:0',
            'tickets.*.max_per_purchase' => 'required|integer|min:1|max:100',
            'tickets.*.from'             => 'required|date',
            'tickets.*.to'               => 'required|date|after_or_equal:tickets.*.from',
            'tickets.*.is_mandatory'     => 'required|in:0,1',
            'tickets.*.requires_document'=> 'required|in:0,1',
            'tickets.*.is_active'        => 'required|in:0,1',
            'tickets.*.kind'             => 'required|in:' . implode(',', EventTicket::KINDS),
            'tickets.*.category_ids'     => 'array',
            'tickets.*.category_ids.*'   => 'integer',
            'tickets.*.allowed_with'     => 'array',
            'tickets.*.allowed_with.*'   => 'integer',
            'tickets.*.position'         => 'nullable|integer',
        ];
    }

    protected function ticketAttributes(): array
    {
        return [
            'ticket_categories.*.name' => 'nombre de la categoría',
            'tickets.*.name' => 'nombre de la entrada',
            'tickets.*.price' => 'precio',
            'tickets.*.capacity' => 'cupo total',
            'tickets.*.max_per_purchase' => 'máximo por compra',
            'tickets.*.from' => 'fecha desde',
            'tickets.*.to' => 'fecha hasta',
        ];
    }
}
