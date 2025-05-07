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
                    {!! Form::open(['url'=>route('public.process', $event->slug), 'files'=>true, 'method'=>'POST', 'class'=>'media align-items-end row']) !!}

                        <div class="col-md-6 form-group">
                            <label>{{ $lang == 'esp' ? 'Nombre' : 'First Name' }} *</label>
                            {!! Form::text('name', null, ['class'=>'form-control', 'autocomplete'=>'off', 'required'=>'required']) !!}
                        </div>

                        <div class="col-md-6 form-group">
                            <label>{{ $lang == 'esp' ? 'Apellido' : 'Last Name' }} *</label>
                            {!! Form::text('lastname', null, ['class'=>'form-control', 'autocomplete'=>'off', 'required'=>'required']) !!}
                        </div>

                        <div class="col-md-6 form-group">
                            <label>{{ $lang == 'esp' ? 'RUT' : 'RUT' }} <span id="required-rut">*</span></label>
                            {!! Form::text('rut', null, [
                                'class'=>'form-control',
                                'autocomplete'=>'off',
                                'id'=>'rut-input',
                                'oninput'=>'validarRUTInput()'
                            ]) !!}
                        </div>

                        <div class="col-md-6 form-group">
                            <label>{{ $lang == 'esp' ? 'Cédula Identidad / Pasaporte' : 'DNI / Passport' }} <span id="required-passport">*</span></label>
                            {!! Form::text('passport', null, ['id'=>'passport-input', 'class'=>'form-control', 'autocomplete'=>'off', 'oninput'=>'updatePassportOrDni()']) !!}
                        </div>

                        <div class="col-md-12 form-group">
                            <label>{{ $lang == 'esp' ? 'Correo Electrónico' : 'Email' }} *</label>
                            {!! Form::email('email', null, ['class'=>'form-control', 'autocomplete'=>'off', 'required'=>'required']) !!}
                        </div>

                        @if($event->show_location_fields)
                            <div class="col-md-12 form-group">
                                <label>{{ $lang == 'esp' ? 'País' : 'Country' }} *</label>
                                {!! Form::select('country_id', $countries, null, ['class' => 'form-control', 'required' => 'required', 'placeholder' => $lang == 'esp' ? 'Seleccione un país' : 'Select a country']) !!}
                            </div>

                            <div class="col-md-12 form-group">
                                <label>{{ $lang == 'esp' ? 'Región' : 'Region' }} *</label>
                                {!! Form::select('region_id', [], null, [
                                    'class' => 'form-control',
                                    'required' => 'required',
                                    'placeholder' => $lang == 'esp' ? 'Seleccione una región' : 'Select a region'
                                ]) !!}
                            </div>

                            <div class="col-md-12 form-group">
                                <label>{{ $lang == 'esp' ? 'Ciudad' : 'City' }} *</label>
                                {!! Form::select('city_id', [], null, [
                                    'class' => 'form-control',
                                    'required' => 'required',
                                    'placeholder' => $lang == 'esp' ? 'Seleccione una ciudad' : 'Select a city'
                                ]) !!}
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
                            <div class="alert alert-danger alert-error-rut-dni" style="display: none" role="alert">
                                @if ($lang == 'esp')
                                    Debe llenar el campo RUT o el campo Cédula Identidad / Pasaporte
                                @else
                                    You must fill in either the RUT field or the DNI / Passport field
                                @endif
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

                            <div class="row">
                                <div class="col-12">
                                    <p>
                                        {{ $lang == 'esp' ? 'Total a Pagar:' : 'Total to Pay:'}} <span class="total">-</span>
                                    </p>
                                </div>
                                <div class="col btn-not-free">
                                    <button value="transfer" type="submit" name="payment" class="btn submit-form">
                                        {{ ($lang == 'esp') ? "Pagar con Transferencia (CLP o USD)"  : "Pay Bank Transfer (CLP or USD)" }}
                                    </button>
                                    <small style="display: block">{{ $lang == 'esp' ? 'Los detalles para la tranferencia serán enviados a su correo.' : 'Details for the transfer will be sent to your mail.' }}</small>
                                </div>
                                <div class="col btn-not-free">
                                    <button value="webpay" type="submit" name="payment" class="btn submit-form">
                                        {{ $lang == 'esp' ? 'Pagar con Tarjetas' : 'Pay OnLine' }}
                                    </button>
                                    <small style="display: block">{{ $lang == 'esp' ? 'Será dirigido a la página de pago.' : 'It will be directed to the payment page.' }}</small>
                                    <img style="width: 200px" src="/images/visamaster.png">
                                </div>
                                <div class="col btn-free" style="display: none">
                                    <button value="free" type="submit" name="payment" class="btn submit-form">
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

@endsection

@section('footer')
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
        $(document).ready(function(){
            const maxSelectionTicket = parseInt('{{$event->max_selection_ticket}}');
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

            $('.ticket-input').click(function(event){
                const ticketChecked = $(".ticket-input:checked").length;
                if(ticketChecked > maxSelectionTicket) {
                    event.preventDefault();
                    $('.alert-warning-max-selection').show();
                    return false;
                } else {
                    $('.alert-warning-max-selection').hide();
                }

                let total = 0;
                $.each($(".ticket-input:checked"), function(){
                    total+=$(this).data('value');
                });
                $('.total').html('$'+number_format(total, 0, ',','.'));
                $('.alert-error-empty-selection').hide();

                showBtnBuy(total, ticketChecked === 0);
            });

            $('.submit-form').click(function(event){

                if( !($('#rut-input').val().trim() || $('#passport-input').val().trim())){
                    event.preventDefault();
                    $('.alert-error-rut-dni').show();
                    return false;
                }
                $('.alert-error-rut-dni').hide();

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
    function removeInvalidCharacters(input) {
        // Reemplazar cualquier cosa que no sea un número, 'K', 'k' o el guion '-'
        input.value = input.value.replace(/[^0-9Kk-]/g, '');

        // Si el RUT tiene más de 10 caracteres (incluyendo el guion), recortar el exceso
        if (input.value.length > 10) {
            input.value = input.value.slice(0, 10);
        }
    }

    function validarRUT(rut) {
        // Eliminar espacios y guiones
        rut = rut.replace(/\s/g, '').replace(/-/g, '');

        // Verifica que tenga 9 caracteres
        if (rut.length !== 9) {
            return false;
        }

        // Validar si el RUT tiene el formato correcto
        if (!/^\d{7,8}[0-9Kk]$/.test(rut)) {
            return false;
        }

        // Separar el RUT y el dígito verificador
        let cuerpo = rut.slice(0, -1); // Todos los números excepto el último
        let dv = rut.slice(-1).toUpperCase(); // El último dígito (verificador)
        
        // Calcular el dígito verificador
        let suma = 0;
        let multiplo = 2;

        // Iterar sobre el RUT y realizar las multiplicaciones
        for (let i = cuerpo.length - 1; i >= 0; i--) {
            suma += cuerpo.charAt(i) * multiplo;
            multiplo = multiplo === 7 ? 2 : multiplo + 1;
        }

        // Calcular el dígito verificador esperado
        let dvEsperado = 11 - (suma % 11);
        if (dvEsperado === 11) {
            dvEsperado = '0';
        } else if (dvEsperado === 10) {
            dvEsperado = 'K';
        } else {
            dvEsperado = dvEsperado.toString();
        }

        // Comparar el dígito verificador con el esperado
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

        updatePassportOrDni();
    }

    function updatePassportOrDni(){
        if(!($('#rut-input').val().trim() || $('#passport-input').val().trim())){
            $('#required-rut').show();
            $('#required-passport').show();
        }
        else{ // alguno de los 2
            if($('#rut-input').val().trim()){
                $('#required-passport').hide();
            }
            else{
                $('#required-rut').hide();
            }
        }
    }
    </script>

    @if($event->show_location_fields)    
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const countrySelect = document.querySelector('select[name="country_id"]');
                const regionSelect = document.querySelector('select[name="region_id"]');
                const citySelect = document.querySelector('select[name="city_id"]');
            
                // Función para limpiar y establecer placeholder
                function resetSelect(select, placeholder) {
                    select.innerHTML = '';
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = placeholder;
                    select.appendChild(option);
                }
            
                // Cargar regiones según el país
                countrySelect.addEventListener('change', function () {
                    const countryId = this.value;
                    resetSelect(regionSelect, "{{ $lang == 'esp' ? 'Seleccione una región' : 'Select a region' }}");
                    resetSelect(citySelect, "{{ $lang == 'esp' ? 'Seleccione una ciudad' : 'Select a city' }}");
            
                    if (!countryId) return;
            
                    fetch(`/get-regions/${countryId}?lang={{ $lang }}`)
                        .then(response => response.json())
                        .then(data => {
                            for (const id in data) {
                                const option = document.createElement('option');
                                option.value = id;
                                option.textContent = data[id];
                                regionSelect.appendChild(option);
                            }
                        })
                        .catch(error => console.error('Error cargando regiones:', error));
                });
            
                // Cargar ciudades según la región
                regionSelect.addEventListener('change', function () {
                    const regionId = this.value;
                    resetSelect(citySelect, "{{ $lang == 'esp' ? 'Seleccione una ciudad' : 'Select a city' }}");
            
                    if (!regionId) return;
                    fetch(`/get-cities/${regionId}?lang={{ $lang }}`)
                        .then(response => response.json())
                        .then(data => {
                            for (const id in data) {
                                const option = document.createElement('option');
                                option.value = id;
                                option.textContent = data[id];
                                citySelect.appendChild(option);
                            }
                        })
                        .catch(error => console.error('Error cargando ciudades:', error));
                });
            });
        </script>
    @endif
    
@endsection
