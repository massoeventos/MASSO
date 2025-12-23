@extends('layouts.public')

@section('content')

    <style type="text/css">
        body.register-page {
    background-color: #f9fafc;
}
body header, body footer{
    display: none;
}
#englishModal ul {
    padding-left: 20px;
}
span.total {
    color: black;
    font-weight: bold;
    font-size: 20px;
    line-height: 11px;
}
body.register-page a.navbar-brand {
    margin-top: -20px;
    margin-bottom: 10px;
}
.ticket-wrapper .col-md-1 {
    text-align: center;
}
.ticket-row.ticket-0 .row {
    margin: 0;
    background-color: rgba(0,0,0,.03);
}
.ticket-row.ticket-1 .row {
    margin: 0;
    background-color: rgba(0,0,0,.06);
}
.ticket-row p {
    margin: 0;
}
p.ticket-description {
    font-size: 14px;
    line-height: 14px;
}
p.ticket-name b {
    color: #e7015e;
    float: right;
    letter-spacing: 0px;

}
.ticket-row .row {
    margin: 0;
    background-color: rgba(0,0,0,.03);
    padding: 15px 5px;
}
.ticket-wrapper .form-group.row {
    margin-bottom: 0;
}
p.ticket-name {
    color: #1b1e21;
    font-size: 15px;
    line-height: 15px;
    padding-bottom: 4px;
}

    .is-invalid {
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25); /* opcional */
    }
    
    .row-total-and-coupon .input-group .form-control,
    .row-total-and-coupon .input-group .btn{
        height: 32px;
    }
    
    .row-total-and-coupon .input-group .btn{
        line-height: 28px;
        font-size: 0.8em;
    }
    
    
    @keyframes spinner-border {
    to { transform: rotate(360deg); }
    }

    .spinner-border {
        display: inline-block;
        width: 15px;
        height: 15px;
        vertical-align: text-bottom;
        border: 3px solid white;
        border-right-color: transparent;
        border-radius: 50%;
        animation: spinner-border .75s linear infinite;
    }
    #coupon-feedback{
        display: block;
        margin-top: 10px;
    }
    </style>

    <section id="ts-speakers-standard" class="ts-speakers-standard ts-speakers speaker-classic section-bg">
        <div class="container">

            <div class="row">
                <div class="col-lg-9 mx-auto">
                    <a class="navbar-brand" href="/">
                     <img src="/images/logo.jpg" alt="">
                  </a><br>
                </div>
                <div class="col-lg-9 mx-auto">
                    <h3>{{ $lang == 'esp' ? 'Formulario Registro:' : 'Registration Form:' }}<br>{{ $event->name }}</h3>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-9 mx-auto mb-3">
                    <div class="alert alert-dark">{{ $lang == 'esp' ? 'Para procesar su registro llene todos los campos del formulario que aparece a continuación. El registro quedará confirmado una vez sea ratificado el pago, según corresponda.     Puede volver a la ficha del evento' : 'To process your registration, fill in all the fields on the form below. The registration will be confirmed once the payment is ratified. You can return to the event page doing ' }} <a href="{{ route('public.event', $event->slug) }}" class="">{{ $lang == 'esp' ? 'haciendo clic acá.' : 'click here.' }}</a></small></div>
                </div>
            </div>

            <div class="row">
                @if(Session::has('error_alert'))
                    <div class="col-lg-9 mx-auto"><div class="alert alert-danger flash-alert">{!! Session::get('error_alert') !!}</div></div>
                @endif
                <div class="newsletter-form col-lg-9 mx-auto">
                    {!! Form::open(['url'=>route('public.process', $event->slug), 'files'=>true, 'method'=>'POST', 'class'=>'media align-items-end row', 'id'=>'event-register-form']) !!}

                        @php
                            $autofill = (isset($autofill) && is_array($autofill)) ? $autofill : [];
                        @endphp

                        <input type="hidden" name="payment" id="payment-method-input" value="">

                        <div class="col-md-6 form-group">
                            <label>{{ $lang == 'esp' ? 'Nombre' : 'First Name' }} *</label>
                            {!! Form::text('name', old('name', $autofill['name'] ?? null), ['class'=>'form-control', 'autocomplete'=>'off', 'required'=>'required']) !!}
                        </div>

                        <div class="col-md-6 form-group">
                            <label>{{ $lang == 'esp' ? 'Apellido' : 'Last Name' }} *</label>
                            {!! Form::text('lastname', old('lastname', $autofill['lastname'] ?? null), ['class'=>'form-control', 'autocomplete'=>'off', 'required'=>'required']) !!}
                        </div>

                        <div class="col-md-12 form-group">
                            <label>{{ $lang == 'esp' ? 'Correo Electrónico' : 'Email' }} *</label>
                            {!! Form::email('email', old('email', $autofill['email'] ?? null), ['class'=>'form-control', 'autocomplete'=>'off', 'required'=>'required']) !!}
                        </div>

                        <div class="col-md-6 form-group">
                            <label>{{ $lang == 'esp' ? 'Nacionalidad' : 'Nationality' }} *</label>
                            {!! Form::select('nationality_country_id', $countries, old('nationality_country_id', $autofill['nationality_country_id'] ?? null), [
                                'id' => 'nationality_country_id',
                                'class' => 'form-control',
                                'required' => 'required',
                                'placeholder' => $lang == 'esp' ? 'Seleccione un país' : 'Select a country',
                            ]) !!}
                        </div>

                         <div class="col-md-6 form-group" id="rut-group" style="display: none;">
                            <label>{{ $lang == 'esp' ? 'RUT' : 'RUT' }} *</label>
                            {!! Form::text('rut', old('rut', $autofill['rut'] ?? null), [
                                'class'=>'form-control',
                                'autocomplete'=>'off',
                                'id'=>'rut-input',
                                'oninput'=>'validarRUTInput()'
                            ]) !!}
                        </div>

                        <div class="col-md-6 form-group" id="passport-group">
                            <label>{{ $lang == 'esp' ? 'DNI / Pasaporte' : 'DNI / Passport' }} *</label>
                            {!! Form::text('passport', old('passport', $autofill['passport'] ?? null), [
                                'id'=>'passport-input',
                                'class'=>'form-control',
                                'autocomplete'=>'off',
                                'required' => 'required',
                            ]) !!}
                        </div>

                        @if($event->show_location_fields)
                            <div class="col-md-12 form-group">
                                <label>{{ $lang == 'esp' ? 'País de residencia' : 'Country of residence' }} *</label>
                                {!! Form::select('country_id', $countries, old('country_id', $autofill['country_id'] ?? null), [
                                    'class' => 'form-control',
                                    'required' => 'required',
                                    'placeholder' => $lang == 'esp' ? 'Seleccione un país' : 'Select a country'
                                ]) !!}
                            </div>

                            <div class="col-md-12 form-group" id="region-container">
                                <label>{{ $lang == 'esp' ? 'Región' : 'Region' }} *</label>
                                {!! Form::select('region_id', [], old('region_id', $autofill['region_id'] ?? null), [
                                    'class' => 'form-control',
                                    'required' => 'required',
                                    'placeholder' => $lang == 'esp' ? 'Seleccione una región' : 'Select a region',
                                    'disabled' =>'disabled',
                                    'data-initial' => old('region_id', $autofill['region_id'] ?? ''),
                                ]) !!}
                            </div>

                            <div class="col-md-12 form-group" id="city-select-container">
                                <label>{{ $lang == 'esp' ? 'Ciudad' : 'City' }} *</label>
                                {!! Form::select('city_id', [], old('city_id', $autofill['city_id'] ?? null), [
                                    'class' => 'form-control',
                                    'required' => 'required',
                                    'placeholder' => $lang == 'esp' ? 'Seleccione una ciudad' : 'Select a city',
                                    'disabled' =>'disabled',
                                    'data-initial' => old('city_id', $autofill['city_id'] ?? ''),
                                ]) !!}
                            </div>
                        
                            <div class="col-md-12 form-group d-none" id="city-input-container">
                                <label>{{ $lang == 'esp' ? 'Ciudad' : 'City' }} *</label>
                                <input type="text" name="custom_city" class="form-control" placeholder="{{ $lang == 'esp' ? 'Ingrese su ciudad' : 'Enter your city' }}" maxlength="100" value="{{ old('custom_city', $autofill['custom_city'] ?? '') }}">
                            </div>
                        @endif
                        @if( $event->inputs()->count() > 0 )
                        @foreach( $event->inputs as $input )
                        <div class="col-md-12 form-group">
                            <label>{{ $lang == 'esp' ? $input->name : $input->name_eng }} @if( $input->required )* @endif</label>

                            @if( ($required = ($input->required) ? 'required' : 'req') && $input->type == 'text' )
                            {!! Form::text(str_replace([' '], ['_'], $input->name), null, ['class'=>'form-control', 'autocomplete'=>'off', $required=>$input->required]) !!}
                            @else
                            {!! Form::file( str_replace([' '], ['_'], $input->name), ['class'=>'form-control', 'autocomplete'=>'off', $required=>$input->required, 'accept'=>'.png,.jpg,.pdf']) !!}
                            @endif
                        </div>
                        @endforeach
                        @endif
                        
                        <div class="col-md-12 form-group">
                            <label>Método de facturación *</label>
                            <div class="form-check px-4">
                                <input class="form-check-input" type="radio" name="billing_method" id="receipt_input" value="receipt" required {{ old('billing_method', $autofill['billing_method'] ?? '') === 'receipt' ? 'checked' : '' }}>
                                <label class="form-check-label pl-1" for="receipt_input">Boleta</label>
                            </div>
                            <div class="form-check px-4">
                                <input class="form-check-input" type="radio" name="billing_method" id="invoice_input" value="invoice" required {{ old('billing_method', $autofill['billing_method'] ?? '') === 'invoice' ? 'checked' : '' }}>
                                <label class="form-check-label pl-1" for="invoice_input">Factura</label>
                            </div>
                        </div>

                        <div class="form-group col-md-6 invoice-item" style="display:none;">
                            <label>{{ $lang == 'esp' ? 'Razón Social' : 'Business Name' }} *</label>
                            <input type="text" name="invoice_data[business_name]" class="form-control" maxlength="100" value="{{ old('invoice_data.business_name', $autofill['invoice_data']['business_name'] ?? '') }}">
                        </div>
                        <div class="form-group col-md-6 invoice-item" style="display:none;">
                            <label>{{ $lang == 'esp' ? 'RUT' : 'Tax ID (RUT)' }} *</label>
                            <input type="text" name="invoice_data[rut]" id="invoice-rut-input" class="form-control" oninput="validarInvoiceRUTInput()" value="{{ old('invoice_data.rut', $autofill['invoice_data']['rut'] ?? '') }}">
                        </div>
                        <div class="form-group col-md-6 invoice-item" style="display:none;">
                            <label>{{ $lang == 'esp' ? 'Giro' : 'Business Activity' }} *</label>
                            <input type="text" name="invoice_data[business_activity]" class="form-control" maxlength="100" value="{{ old('invoice_data.business_activity', $autofill['invoice_data']['business_activity'] ?? '') }}">
                        </div>
                        <div class="form-group col-md-6 invoice-item" style="display:none;">
                            <label>{{ $lang == 'esp' ? 'Dirección' : 'Address' }} *</label>
                            <input type="text" name="invoice_data[address]" class="form-control" maxlength="200" value="{{ old('invoice_data.address', $autofill['invoice_data']['address'] ?? '') }}">
                        </div>
                        <div class="form-group col-md-6 invoice-item" style="display:none;">
                            <label>{{ $lang == 'esp' ? 'Ciudad' : 'City' }} *</label>
                            <input type="text" name="invoice_data[city]" class="form-control" maxlength="100" value="{{ old('invoice_data.city', $autofill['invoice_data']['city'] ?? '') }}">
                        </div>
                        <div class="form-group col-md-6 invoice-item" style="display:none;">
                            <label>{{ $lang == 'esp' ? 'Teléfono' : 'Phone' }} *</label>
                            <input type="text" name="invoice_data[phone]" class="form-control" maxlength="20" value="{{ old('invoice_data.phone', $autofill['invoice_data']['phone'] ?? '') }}">
                        </div>
                        <div class="form-group col-12 invoice-item" style="display:none;">
                            <label>{{ $lang == 'esp' ? 'Observación' : 'Note' }}</label>
                            <textarea name="invoice_data[note]" class="form-control" rows="2" maxlength="400">{{ old('invoice_data.note', $autofill['invoice_data']['note'] ?? '') }}</textarea>
                        </div>

                        <div class="col-md-12">
                            <h4>Tickets</h4>
                            <p>{{ $lang == 'esp' ? 'Seleccione el ticket / categoría para su registro' : 'Select the ticket / category for your registration' }}.</p>
                        </div>
                        <div class="col-md-12 ticket-wrapper">
                            @if( $event->tickets()->count() > 0 )
                            <?php
                                $warning_max_selection = "Ya seleccionaste el máximo de categorías permitidas ({$event->max_selection_ticket})";
                                $warning_max_selection_eng = "You have already selected the maximum allowed categories ({$event->max_selection_ticket})";
                            ?>
                            <div class="alert alert-warning alert-warning-max-selection" style="display: none" role="alert">
                                {{ $lang == 'esp' ? $warning_max_selection : $warning_max_selection_eng }}
                            </div>
                            @foreach( $event->tickets as $key => $ticket )
                            @if( $ticket->isAvailable() )
                            <div class="form-group row" >
                                <div class="ticket-row ticket-{{ $key%2 }} col-md-12">
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
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                            @endforeach
                            <div class="alert alert-warning alert-warning-max-selection" style="display: none" role="alert">
                                {{ $lang == 'esp' ? $warning_max_selection : $warning_max_selection_eng }}
                            </div>
                            <br/>
                            <div class="alert alert-danger alert-error-empty-selection" style="display: none" role="alert">
                                {{ $lang == 'esp' ? 'Debe seleccionar una categoría' : 'You must select a category' }}
                            </div>
                            <div class="alert alert-danger alert-error-ticket-mandatory" style="display: none" role="alert">
                                {{ $lang == 'esp' ? 'Debe seleccionar los tickets obligatorios' : 'You must select the mandatory tickets' }}
                            </div>
                            @endif
                        </div>


                        <div class="col-md-12 text-center">
                            <div class="row">
                                <div class="col-12">
                                    <br>
                                    <input type="checkbox" name="check" checked>
                                        <span data-toggle="modal" data-target="#englishModal" style="cursor: pointer; color: #e7015e">
                                            {{ $lang == 'esp' ? 'Acepto términos y condiciones.' : 'I agree to term and conditions:'}}
                                        </span>
                                </div>
                            </div>
                            <hr>

                            <div class="row mb-3 row-total-and-coupon">
                                <div class="col-12 col-md-6 offset-md-3 form-group">
                                    <label for="coupon-code">
                                        {{ $lang == 'esp' ? '¿Tienes un cupón de descuento?' : 'Do you have a discount coupon?' }}
                                    </label>
                                    <div class="input-group">
                                        {!! Form::text('', null, [
                                            'class' => 'form-control form-control-sm',
                                            'autocomplete' => 'off',
                                            'id' => 'coupon-code',
                                            'placeholder' => $lang == 'esp' ? 'Introduce el código aquí...' : 'Enter the code here...',
                                        ]) !!}
                                        <div class="input-group-append">
                                            <button class="btn" type="button" id="apply-coupon-btn" disabled>
                                                {{ $lang == 'esp' ? 'Aplicar' : 'Apply' }}
                                            </button>
                                            <button class="btn d-none" type="button" id="loading-coupon-btn">
                                                <div class="spinner-border"></div>
                                            </button>
                                        </div>
                                    </div>
                                    <small id="coupon-feedback" class="text-muted"></small>


                                    <a href="#" class="small text-danger d-none" id="cancel-coupon">Cancelar</a>
                                    

                                    <input type="text" name="coupon_code" id="coupon_code_send" class="d-none">
                                </div>
                                
                                <div class="col-12">
                                    <p class="mt-2 mb-4">
                                        {{ $lang == 'esp' ? 'Total a Pagar:' : 'Total to Pay:'}} <span class="total">-</span>
                                    </p>
                                </div>

                            </div>
                            <div class="row">
                                @if($event->allow_bank_transfer)
                                <div class="col btn-not-free">
                                    <button value="transfer" type="button" class="btn submit-form payment-trigger">
                                        {{ ($lang == 'esp') ? "Pagar con Transferencia (CLP o USD)"  : "Pay Bank Transfer (CLP or USD)" }}
                                    </button>
                                    <small style="display: block">{{ $lang == 'esp' ? 'Los detalles para la tranferencia serán enviados a su correo.' : 'Details for the transfer will be sent to your mail.' }}</small>
                                </div>
                                @endif
                                <div class="col btn-not-free">
                                    <button value="webpay" type="button" class="btn submit-form payment-trigger">
                                        {{ $lang == 'esp' ? 'Pagar con Tarjetas' : 'Pay OnLine' }}
                                    </button>
                                    <small style="display: block">{{ $lang == 'esp' ? 'Será dirigido a la página de pago.' : 'It will be directed to the payment page.' }}</small>
                                    <img style="width: 200px" src="/images/visamaster.png">
                                </div>
                                <div class="col btn-free" style="display: none">
                                    <button value="free" type="button" class="btn submit-form payment-trigger">
                                        {{ $lang == 'esp' ? 'Registrar' : 'Register' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>

        <div class="speaker-shap">
            <img class="shap1" src="images/shap/home_speaker_memphis1.png" alt="">
            <img class="shap2" src="images/shap/home_speaker_memphis1.png" alt="">
        </div>

    </section>

    @include('guest.common.payment_confirmation_modal')

@endsection

@section('footer')
    <div class="modal fade" id="duplicatePaymentModal" tabindex="-1" role="dialog" aria-labelledby="duplicate-payment-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 id="duplicate-payment-title" class="modal-title mb-0">Posible compra duplicada</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Detectamos que este dispositivo ya tiene un registro previo con el mismo correo y tipos de tickets. Revisa los datos antes de continuar.</p>
                    <div class="p-3 mb-3" style="background:#f8f9fa; border-radius:6px;">
                        <div class="d-flex justify-content-between"><span class="text-muted">Correo</span><strong id="duplicate-email">—</strong></div>
                        <div class="d-flex justify-content-between mt-2"><span class="text-muted">Monto</span><strong id="duplicate-amount">—</strong></div>
                        <div class="d-flex justify-content-between mt-2"><span class="text-muted">Estado</span><strong id="duplicate-status">—</strong></div>
                        <div class="d-flex justify-content-between mt-2"><span class="text-muted">Medio</span><strong id="duplicate-method">—</strong></div>
                        <div class="d-flex justify-content-between mt-2"><span class="text-muted">Fecha</span><strong id="duplicate-date">—</strong></div>
                    </div>
                    <div>
                        <div class="text-muted mb-1">Tickets seleccionados</div>
                        <ul id="duplicate-tickets" class="pl-3 mb-0" style="list-style: disc;"></ul>
                    </div>
                    <small id="duplicate-feedback" class="d-block mt-3"></small>
                </div>
                <div class="modal-footer d-flex flex-column align-items-stretch">
                    <button type="button" class="btn btn-outline-secondary bg-secondary mb-2    small-btn" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn bg-warning mb-2 small-btn" id="duplicate-continue-btn">Continuar con nueva compra</button>
                    <button type="button" class="btn small-btn" id="duplicate-resend-btn">Reenviar datos por correo</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade bd-example-modal-lg" id="englishModal" tabindex="-1" role="dialog" aria-labelledby="englishModal" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content ">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">RETURN, CERTIFICATION AND PAYMENT POLICY</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
              {!! $lang == 'esp' ? html_entity_decode($event->terms_and_conditions) : html_entity_decode($event->terms_and_conditions_eng) !!}
          </div>
        </div>
      </div>
    </div>
    <script type="text/javascript">
        function getTotalTickets(){
            let total = 0;
            $.each($(".ticket-input:checked"), function(){
                total+=$(this).data('value');
            });
            return total;
        }
        
        function printTotalTickets(total){
            $('.total').html('$'+number_format(total, 0, ',','.'));
        }
        
        var number_format = function(number,decimals,dec_point,thousands_sep) {
            number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
            var n = !isFinite(+number) ? 0 : +number,
                prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
                dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
                s = '',
                toFixedFix = function (n, prec) {
                    var k = Math.pow(10, prec);
                    return '' + Math.round(n * k) / k;
                };
            // Fix for IE parseFloat(0.55).toFixed(0) = 0;
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
            }

        let duplicateModalBypassOnce = false;
        let duplicateTriggerButton = null;
        let duplicateFallbackBackdrop = null;
        let duplicateData = null;

        const normalizeIds = (list) => list.map(id => parseInt(id, 10)).filter(Boolean).sort((a, b) => a - b);

        function fillDuplicateModal() {
            if (!duplicateData) return;

            const emailEl = document.getElementById('duplicate-email');
            const amountEl = document.getElementById('duplicate-amount');
            const statusEl = document.getElementById('duplicate-status');
            const methodEl = document.getElementById('duplicate-method');
            const dateEl = document.getElementById('duplicate-date');
            const ticketsEl = document.getElementById('duplicate-tickets');

            if (emailEl) emailEl.textContent = duplicateData.email || '—';
            if (amountEl) amountEl.textContent = duplicateData.amount ? '$' + number_format(duplicateData.amount, 0, ',', '.') : '—';
            if (statusEl) statusEl.textContent = duplicateData.status || '—';
            if (methodEl) methodEl.textContent = duplicateData.managment || '—';
            if (dateEl) dateEl.textContent = duplicateData.created_at || '—';

            if (ticketsEl) {
                ticketsEl.innerHTML = '';
                const tickets = (duplicateData.tickets || []);
                if (tickets.length === 0) {
                    const li = document.createElement('li');
                    li.textContent = 'Sin tickets asociados';
                    ticketsEl.appendChild(li);
                } else {
                    tickets.forEach(t => {
                        const li = document.createElement('li');
                        const price = t.price ? ' - $' + number_format(t.price, 0, ',', '.') : '';
                        li.textContent = (t.name || 'Ticket') + price;
                        ticketsEl.appendChild(li);
                    });
                }
            }
        }

        function openDuplicateModal() {
            if (!duplicateData) return;
            fillDuplicateModal();

            const modalEl = document.getElementById('duplicatePaymentModal');
            const jqModal = window.jQuery ? window.jQuery(modalEl) : null;

            if (jqModal && jqModal.modal) {
                jqModal.modal('show');
            } else if (modalEl) {
                modalEl.classList.add('show');
                modalEl.style.display = 'block';
                document.body.classList.add('modal-open');

                duplicateFallbackBackdrop = document.createElement('div');
                duplicateFallbackBackdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(duplicateFallbackBackdrop);
            }
        }

        function closeDuplicateModal() {
            const modalEl = document.getElementById('duplicatePaymentModal');
            const jqModal = window.jQuery ? window.jQuery(modalEl) : null;

            if (jqModal && jqModal.modal) {
                jqModal.modal('hide');
            } else if (modalEl) {
                modalEl.classList.remove('show');
                modalEl.style.display = 'none';
                document.body.classList.remove('modal-open');
                if (duplicateFallbackBackdrop) {
                    duplicateFallbackBackdrop.parentNode.removeChild(duplicateFallbackBackdrop);
                    duplicateFallbackBackdrop = null;
                }
            }
        }

        $(document).ready(function(){
            const maxSelectionTicket = parseInt('{{$event->max_selection_ticket}}');
            const duplicateFeedback = document.getElementById('duplicate-feedback');
            const duplicateResendBtn = document.getElementById('duplicate-resend-btn');
            const duplicateContinueBtn = document.getElementById('duplicate-continue-btn');

            const updateTicketDocuments = () => {
                const checked = {};
                $('.ticket-input:checked').each(function(){
                    checked[$(this).val()] = true;
                });

                $('.ticket-document-wrapper').each(function(){
                    const ticketId = String($(this).data('ticket-id'));
                    const shouldShow = !!checked[ticketId];
                    $(this).toggle(shouldShow);

                    const input = $(this).find('input[type="file"]');
                    if (shouldShow) {
                        input.attr('required', 'required');
                    } else {
                        input.removeAttr('required');
                    }
                });
            };

            updateTicketDocuments();

            // Elegir tipo de ticket
            $('.ticket-input').click(function(event){
                const ticketChecked = $(".ticket-input:checked").length;
                if(ticketChecked > maxSelectionTicket) {
                    event.preventDefault();
                    $('.alert-warning-max-selection').show();
                    return false;
                } else {
                    $('.alert-warning-max-selection').hide();
                }

                const total = setTotalWithoutCoupon();

                $('.alert-error-empty-selection').hide();

                showBtnBuy(total, ticketChecked === 0);

                updateTicketDocuments();
            });

            $('.submit-form').click(function(event){

                if($(".ticket-input:checked").length  === 0){
                    event.preventDefault();
                    $('.alert-error-empty-selection').show();
                    return false;
                }
                $('.alert-error-empty-selection').hide();

                if($('.ticket-input[data-is_mandatory="1"]').length > 0 && $('.ticket-input[data-is_mandatory="1"]:checked').length === 0) {
                    event.preventDefault();
                    $('.alert-error-ticket-mandatory').show();
                  return false;
                }
               $('.alert-error-ticket-mandatory').hide();
            });

            const form = document.getElementById('event-register-form');
            const paymentMethodInput = document.getElementById('payment-method-input');
            let lastClickedButton = null;

            window.bindPaymentConfirmationModal({
                form: form,
                triggerSelector: '.payment-trigger',
                onTriggerClick: function (button) {
                    lastClickedButton = button;
                    if (paymentMethodInput) {
                        paymentMethodInput.value = button.value;
                    }
                },
                validateBeforeOpen: function () {
                    if ($(".ticket-input:checked").length === 0) {
                        $('.alert-error-empty-selection').show();
                        return false;
                    }
                    $('.alert-error-empty-selection').hide();

                    if ($('.ticket-input[data-is_mandatory="1"]').length > 0 && $('.ticket-input[data-is_mandatory="1"]:checked').length === 0) {
                        $('.alert-error-ticket-mandatory').show();
                        return false;
                    }
                    $('.alert-error-ticket-mandatory').hide();

                    if (duplicateModalBypassOnce) {
                        duplicateModalBypassOnce = false;
                        return true;
                    }

                    const emailInput = document.querySelector('input[name="email"]');
                    const email = emailInput && emailInput.value ? emailInput.value.trim() : '';
                    const tickets = getTicketInputs();

                    if (!email || !tickets.length) {
                        return true;
                    }

                    duplicateTriggerButton = lastClickedButton;

                    return fetch('{{ route('public.checkDuplicatePayment', $event->slug) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ email: email, tickets: tickets })
                    })
                    .then(res => res.json().then(body => ({ status: res.status, body })))
                    .then(({ body }) => {
                        if (body && body.duplicate && body.payment) {
                            duplicateData = body.payment;
                            openDuplicateModal();
                            return false;
                        }
                        duplicateData = null;
                        return true;
                    })
                    .catch(() => true);
                },
                shouldSkipModal: function (button) {
                    return button && button.value === 'free';
                },
                getCourseName: function () {
                    return '{{ $event->name }}';
                },
                getAmountText: function () {
                    const totalEl = document.querySelector('.total');
                    const totalText = totalEl ? totalEl.textContent.trim() : '';
                    return totalText && totalText !== '-' ? totalText : '$0';
                }
            });

            if (duplicateResendBtn) {
                duplicateResendBtn.addEventListener('click', function () {
                    if (!duplicateData) {
                        return;
                    }

                    duplicateResendBtn.disabled = true;
                    duplicateResendBtn.textContent = 'Enviando...';
                    if (duplicateFeedback) {
                        duplicateFeedback.textContent = '';
                        duplicateFeedback.className = '';
                    }

                    fetch('{{ route('public.resendLastPayment', $event->slug) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ payment_id: duplicateData ? duplicateData.id : null })
                    })
                    .then(res => res.json().then(body => ({ status: res.status, body })))
                    .then(({ status, body }) => {
                        if (duplicateFeedback) {
                            duplicateFeedback.textContent = body && body.message ? body.message : 'Listo.';
                            duplicateFeedback.className = status === 200 ? 'text-success' : 'text-danger';
                        }
                    })
                    .catch(() => {
                        if (duplicateFeedback) {
                            duplicateFeedback.textContent = 'No pudimos reenviar el correo, intenta nuevamente.';
                            duplicateFeedback.className = 'text-danger';
                        }
                    })
                    .finally(() => {
                        duplicateResendBtn.disabled = false;
                        duplicateResendBtn.textContent = 'Reenviar datos por correo';
                    });
                });
            }

            if (duplicateContinueBtn) {
                duplicateContinueBtn.addEventListener('click', function () {
                    duplicateModalBypassOnce = true;
                    closeDuplicateModal();
                    if (duplicateTriggerButton) {
                        duplicateTriggerButton.click();
                    }
                });
            }

            const duplicateDismissButtons = document.querySelectorAll('#duplicatePaymentModal [data-dismiss="modal"]');
            duplicateDismissButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    closeDuplicateModal();
                });
            });
        });

        const showBtnBuy = (amount, showForce = false) => {
            if (amount === 0 && !showForce) {
                $('.btn-not-free').hide();
                $('.btn-free').show();
                return;
            }

            $('.btn-not-free').show();
            $('.btn-free').hide();
        }
    </script>
    
    <script>               

    const CHILE_ID = '{{ $chile->id }}';

    function removeInvalidCharacters(input) {
        // Reemplazar cualquier cosa que no sea un número, 'K', 'k' o el guion '-'
        input.value = input.value.replace(/[^0-9Kk-]/g, '');

        // Si el RUT tiene más de 11 caracteres (incluyendo el guion), recortar el exceso
        if (input.value.length > 11) {
            input.value = input.value.slice(0, 11);
        }
    }

    function validarRUT(rut) {
        // Eliminar espacios y guiones
        rut = rut.replace(/\s|-/g, '');

        // Validar si el RUT tiene entre 7 y 9 dígitos numéricos más el verificador
        if (!/^\d{7,9}[0-9Kk]$/.test(rut)) {
            return false;
        }

        // Separar el cuerpo del dígito verificador
        const cuerpo = rut.slice(0, -1);
        const dv = rut.slice(-1).toUpperCase();

        // Calcular el dígito verificador
        let suma = 0;
        let multiplo = 2;

        // Iterar sobre el RUT y realizar las multiplicaciones
        for (let i = cuerpo.length - 1; i >= 0; i--) {
            suma += parseInt(cuerpo.charAt(i), 10) * multiplo;
            multiplo = multiplo === 7 ? 2 : multiplo + 1;
        }

        let dvEsperado = 11 - (suma % 11);
        dvEsperado = dvEsperado === 11 ? '0' : dvEsperado === 10 ? 'K' : dvEsperado.toString();

        return dv === dvEsperado;
    }

    function validarRUTInput() {
        const rutInput = document.getElementById('rut-input');

        // Llamar a la función que limpia el input
        removeInvalidCharacters(rutInput);
        
        // Validar el RUT
        const isValid = rutInput.value ? validarRUT(rutInput.value) : true;
        
        // Mostrar mensaje de error si el RUT no es válido
        if (isValid) {
            rutInput.classList.remove('is-invalid');
            rutInput.setCustomValidity(''); // Limpiar el mensaje de validación personalizado
        } else {
            rutInput.classList.add('is-invalid');
            rutInput.setCustomValidity('El RUT es inválido'); // Para evitar que se envíe si no es válido
        }

    }

    function validarInvoiceRUTInput() {
        const rutInput = document.getElementById('invoice-rut-input');

        removeInvalidCharacters(rutInput);

        const isValid = rutInput.value ? validarRUT(rutInput.value) : true;

        if (isValid) {
            rutInput.classList.remove('is-invalid');
            rutInput.setCustomValidity('');
        } else {
            rutInput.classList.add('is-invalid');
            rutInput.setCustomValidity('El RUT es inválido');
        }
    }

    </script>
    <script>
        // Activar RUT o DNI/Pasaporte según nacionalidad
        document.addEventListener('DOMContentLoaded', function () {
            const nationalitySelect = document.querySelector('select[name="nationality_country_id"]');
            const rutGroup = document.getElementById('rut-group');
            const passportGroup = document.getElementById('passport-group');
            const rutInput = document.getElementById('rut-input');
            const passportInput = document.getElementById('passport-input');

            function toggleRutPassportFields(nationalityCountryId) {
                const isChile = nationalityCountryId == CHILE_ID;

                if (isChile) {
                    rutGroup.style.display = 'block';
                    rutInput.setAttribute('required', 'required');
                    passportGroup.style.display = 'none';
                    passportInput.removeAttribute('required');

                    // limpiar passport
                    passportInput.value = '';
                } else {
                    rutGroup.style.display = 'none';
                    rutInput.removeAttribute('required');
                    passportGroup.style.display = 'block';
                    passportInput.setAttribute('required', 'required');

                    // limpiar RUT
                    rutInput.value = '';
                    rutInput.classList.remove('is-invalid');
                    rutInput.setCustomValidity('');
                }
            }

            // Ejecutar al cargar si ya hay un valor seleccionado
            if (nationalitySelect.value) {
                toggleRutPassportFields(nationalitySelect.value);
            }

            nationalitySelect.addEventListener('change', function () {
                toggleRutPassportFields(this.value);
            });
        });
    </script>

    {{-- aplicar cupones --}}
    <script>

    /**
     * Aplica valores sin descuentos
    */
    function setTotalWithoutCoupon(){
        setAppliedCoupon('');
        const total = getTotalTickets();
        printTotalTickets(total);

        const feedback = document.getElementById('coupon-feedback');
        feedback.textContent = '';
        feedback.classList.remove('text-success', 'text-danger');
        
        return total;
    }

    /**
     * Guarda en input oculto el código de cupón aplicado, ajusta UI
    */
    function setAppliedCoupon(newValue){
        const inputCouponCodeSend = document.getElementById('coupon_code_send');
        const inputCouponCode = document.getElementById('coupon-code');

        inputCouponCodeSend.value = newValue;
        
        const cancelCouponBtn = document.getElementById('cancel-coupon');
        const applyCouponbutton = document.getElementById('apply-coupon-btn');

        // Se agregó cupón
        if(newValue && newValue != ''){
            cancelCouponBtn.classList.remove('d-none');
            inputCouponCode.disabled = true;
            applyCouponbutton.disabled = true;
        }
        else{
            // Se removió cupón
            cancelCouponBtn.classList.add('d-none');
            inputCouponCode.disabled = false;
            applyCouponbutton.disabled = false;
        }
    }
    
    function getTicketInputs(){
        const ticketInputs = document.querySelectorAll('.ticket-input:checked');
        const tickets = Array.from(ticketInputs).map(el => el.value);
        return tickets;
    }
        
    document.addEventListener('DOMContentLoaded', function () {
        const input = document.getElementById('coupon-code');
        const applyCouponbutton = document.getElementById('apply-coupon-btn');
        const feedback = document.getElementById('coupon-feedback');
        const loadingCouponBtn = document.getElementById('loading-coupon-btn')
        const cancelCouponBtn = document.getElementById('cancel-coupon')

        input.addEventListener('input', function () {
            const tickets = getTicketInputs();
            
            if(tickets.length){
                setTotalWithoutCoupon();
            }
            
            applyCouponbutton.disabled = input.value.trim() === '';
        });

        applyCouponbutton.addEventListener('click', function () {
            const couponCode = input.value.trim();
            const tickets = getTicketInputs()

            if (tickets.length === 0) {
                feedback.textContent = '{{ $lang == "esp" ? "Debe seleccionar al menos un ticket." : "Please select at least one ticket." }}';
                feedback.className = 'text-danger';
                return;
            }

            applyCouponbutton.classList.add('d-none');
            loadingCouponBtn.classList.remove('d-none');
            
            setTotalWithoutCoupon()

            fetch('{{ url("/validate-coupon") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    event_id: {{ $event->id }},
                    coupon_code: couponCode,
                    tickets_ids: tickets
                })
            })
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(({ status, body }) => {
                try {
                    if (status === 200) {
                        feedback.innerHTML = '{{ $lang == "esp" ? "Cupón del " : "" }}' + body.discount_percentage + '% {{ $lang == "esp" ? "de descuento aplicado." : "coupon discount applied." }}';
                        feedback.className = 'text-success';

                        const subtotal = getTotalTickets();

                        // Revisa que discount_percentage exista y sea número
                        const discountPercentage = Number(body.discount_percentage);

                        if (isNaN(discountPercentage)) {
                            throw new Error('discount_percentage no es un número válido');
                        }

                        const discountFactor = (100 - discountPercentage) / 100;
                        const total = subtotal * discountFactor;

                        setAppliedCoupon(couponCode);                        
                        printTotalTickets(total);
                    } else {
                        let errorMessage = body.message;

                        if (Array.isArray(body.invalid_tickets) && body.invalid_tickets.length > 0) {
                            const list = body.invalid_tickets.map(name => `• ${name}`).join('<br>');
                            errorMessage += `<br><span>${list}</span>`;
                        }

                        setTotalWithoutCoupon();
                        feedback.innerHTML = errorMessage;
                        feedback.className = 'text-danger';
                        
                    }
                    applyCouponbutton.classList.remove('d-none');
                    loadingCouponBtn.classList.add('d-none');    
                } catch (error) {
                    console.error('Error dentro del then:', error);
                    throw error; // para que llegue al catch global
                }
            })
            .catch(() => {
                setTotalWithoutCoupon();
                feedback.textContent = '{{ $lang == "esp" ? "Error al validar el cupón." : "Error validating coupon." }}';
                feedback.className = 'text-danger';

                applyCouponbutton.classList.remove('d-none');
                loadingCouponBtn.classList.add('d-none');
            });
        });
        
        
        document.getElementById('coupon-code').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault(); // Evita el submit del formulario
                document.getElementById('apply-coupon-btn').click(); // Simula clic en el botón aplicar
            }
        });
        
        cancelCouponBtn.addEventListener('click', function (e) {
            e.preventDefault();
            setTotalWithoutCoupon();
        })

    });
    </script>
    @if($event->show_location_fields)    
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const countrySelect = document.querySelector('select[name="country_id"]');
                const regionSelect = document.querySelector('select[name="region_id"]');
                const citySelect = document.querySelector('select[name="city_id"]');
                const regionContainer = document.getElementById('region-container');
                const citySelectContainer = document.getElementById('city-select-container');
                const cityInputContainer = document.getElementById('city-input-container');

                let initialRegionId = (regionSelect && regionSelect.dataset) ? (regionSelect.dataset.initial || '') : '';
                let initialCityId = (citySelect && citySelect.dataset) ? (citySelect.dataset.initial || '') : '';

                function resetSelect(select, placeholder, isLoading = false) {
                    select.innerHTML = '';
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = isLoading ? '{{ $lang == "esp" ? "Cargando..." : "Loading..." }}' : placeholder;
                    select.appendChild(option);
                    select.disabled = true;
                }

                function toggleChileMode(isChile) {
                    if (isChile) {
                        // Mostrar selects de región y ciudad
                        regionContainer.classList.remove('d-none');
                        citySelectContainer.classList.remove('d-none');
                        cityInputContainer.classList.add('d-none');

                        regionSelect.setAttribute('required', 'required');
                        citySelect.setAttribute('required', 'required');
                        cityInputContainer.querySelector('input').removeAttribute('required');
                    } else {
                        // Mostrar input libre de ciudad
                        regionContainer.classList.add('d-none');
                        citySelectContainer.classList.add('d-none');
                        cityInputContainer.classList.remove('d-none');

                        regionSelect.removeAttribute('required');
                        citySelect.removeAttribute('required');
                        cityInputContainer.querySelector('input').setAttribute('required', 'required');
                    }
                }

                countrySelect.addEventListener('change', function () {
                    const countryId = this.value;
                    const isChile = countryId == CHILE_ID;
                    toggleChileMode(isChile);

                    resetSelect(regionSelect, "{{ $lang == 'esp' ? 'Seleccione una región' : 'Select a region' }}");
                    resetSelect(citySelect, "{{ $lang == 'esp' ? 'Seleccione una ciudad' : 'Select a city' }}");

                    if (!countryId) return;

                    if (isChile) {
                        resetSelect(regionSelect, '', true); // Mostrar "Cargando..."
                        fetch(`/get-regions/${countryId}?lang={{ $lang }}`)
                            .then(response => response.json())
                            .then(data => {
                                resetSelect(regionSelect, "{{ $lang == 'esp' ? 'Seleccione una región' : 'Select a region' }}");
                                for (const id in data) {
                                    const option = document.createElement('option');
                                    option.value = id;
                                    option.textContent = data[id];
                                    regionSelect.appendChild(option);
                                }
                                regionSelect.disabled = false;

                                // Apply initial region (autofill) once
                                if (initialRegionId) {
                                    regionSelect.value = initialRegionId;
                                    regionSelect.dataset.initial = '';
                                    initialRegionId = '';
                                    regionSelect.dispatchEvent(new Event('change'));
                                }
                            })
                            .catch(error => console.error('Error cargando regiones:', error));
                    }
                });

                regionSelect.addEventListener('change', function () {
                    const regionId = this.value;
                    resetSelect(citySelect, '', true); // Mostrar "Cargando..."

                    if (!regionId) return;

                    fetch(`/get-cities/${regionId}?lang={{ $lang }}`)
                        .then(response => response.json())
                        .then(data => {
                            resetSelect(citySelect, "{{ $lang == 'esp' ? 'Seleccione una ciudad' : 'Select a city' }}");
                            for (const id in data) {
                                const option = document.createElement('option');
                                option.value = id;
                                option.textContent = data[id];
                                citySelect.appendChild(option);
                            }
                            citySelect.disabled = false;

                            // Apply initial city (autofill) once
                            if (initialCityId) {
                                citySelect.value = initialCityId;
                                citySelect.dataset.initial = '';
                                initialCityId = '';
                            }
                        })
                        .catch(error => console.error('Error cargando ciudades:', error));
                });

                // Trigger initial load if country already selected (autofill)
                if (countrySelect && countrySelect.value) {
                    countrySelect.dispatchEvent(new Event('change'));
                }
            });
        </script>
    @endif
    
    <script>
        // radio facturacion
        $(document).ready(function () {
            function toggleFacturaFields() {
                const isFactura = $('input[name="billing_method"]:checked').val() === 'invoice';

                if (isFactura) {
                    $('.invoice-item').slideDown();

                    // Agregar required a todos los campos menos observación
                    $('.invoice-item input, .invoice-item textarea').each(function () {
                        if ($(this).attr('name') !== 'invoice_data[note]') {
                            $(this).attr('required', true);
                        }
                    });
                } else {
                    $('.invoice-item').slideUp();

                    // Quitar required de todos los campos
                    $('.invoice-item input, .invoice-item textarea').removeAttr('required');

                    // Si el RUT de factura es inválido, limpiar su valor
                    const rutInput = $('#invoice-rut-input');
                    if (rutInput.length) {
                        const rutVal = rutInput.val();
                        rutInput.removeClass('is-invalid');
                        rutInput[0].setCustomValidity('');

                        if (rutVal && !validarRUT(rutVal)) {
                            rutInput.val('');
                        }
                    }
                }
            }

            // Ejecutar al cargar por si hay una opción ya marcada
            toggleFacturaFields();

            // Escuchar cambios en los radio buttons
            $('input[name="billing_method"]').on('change', toggleFacturaFields);
        });
    </script>
@endsection
