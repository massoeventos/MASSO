<div class="form-group row" >
    <div class="ticket-row ticket-{{ $rowIndex%2 }} col-md-12">
        <div class="row">
            <div class="col-md-1">
                <input
                    @if( $event->is_multiple_selection_ticket === 1)
                        type="checkbox"
                    @else
                        type="radio"
                    @endif
                    name="ticket[]"
                    value="{{ $ticket->id }}"
                    data-value="{{ $ticket->price }}"
                    data-is_mandatory="{{ $ticket->is_mandatory }}"
                    data-requires_document="{{ $ticket->requires_document }}"
                    class="ticket-input"
                    {{ (string) request('ticket') === (string) $ticket->id ? 'checked' : '' }}
                >
            </div>
            <div class="col-md-11">
                <p class="ticket-name">
                    {{ $lang == 'esp' ? $ticket->name : $ticket->name_eng }}
                    @if($ticket->is_mandatory === 1)
                    <i>({{ $lang == 'esp' ? 'Ticket Obligatorio' : 'Mandatory Ticket' }})</i>
                    @endif
                    <b>CLP${{ number_format($ticket->price, 0, ',', '.') }}</b>
                </p>
                <p class="ticket-description">{{ $lang == 'esp' ? $ticket->description : $ticket->description_eng }}</p>

                @if(!empty($ticket->requires_document))
                <div class="ticket-document-wrapper" data-ticket-id="{{ $ticket->id }}" style="display:none; margin-top: 10px;">
                    <label style="font-size: 13px;">
                        {{ $lang == 'esp' ? 'Adjunte documento que acredite esta categoría' : 'Attach document that proves this category' }} *
                    </label>
                    <input type="file" class="form-control ticket-document-input" name="ticket_document[{{ $ticket->id }}]" accept=".png,.jpg,.jpeg,.pdf">
                    <small class="text-muted" style="font-size: 12px;">
                        {{ ($lang == 'esp'
                            ? 'Formatos permitidos: PDF, JPG o PNG. Tamaño máximo: 5 MB.'
                            : 'Allowed formats: PDF, JPG or PNG. Max size: 5 MB.')
                        }}
                    </small>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
