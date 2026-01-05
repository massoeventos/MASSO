@extends('layouts.public')

@section('content')

    <div id="page-banner-area" class="page-banner-area little-area" style="background-image:url(/images/shap/subscribe_pattern.png)">
        <div class="page-banner-title">
        <div class="text-center">
        <h2>Pagos grupales</h2>
        </div>
        </div>
    </div>

    <section id="ts-speakers-standard" class="ts-speakers-standard ts-speakers speaker-classic section-bg">
        <div class="container">
            <div class="row">
                <div class="col-lg-9 mx-auto mb-3">
                    <div class="alert alert-dark">En esta sección podrá enviar pagos grupales personalizados, en caso de gestionar una inscripción a un evento, favor revisar <a href="/" class="">acá</a> los eventos que se encuentran disponibles.</small></div>
                </div>
            </div>

            <div class="row">
                @if(Session::has('error_alert'))
                    <div class="col-lg-9 mx-auto"><div class="alert alert-danger flash-alert">{!! Session::get('error_alert') !!}</div></div>
                @endif

                @if ($errors->any())
                    <div class="col-lg-9 mx-auto">
                        <div class="alert alert-danger flash-alert">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
                <div class="newsletter-form col-lg-9 mx-auto">
                    <form method="POST" action="{{ route('public.payment2') }}" class="media align-items-end row" id="group-payment-form" enctype="multipart/form-data">
                        @csrf

                        @php
                            $autofill = (isset($autofill) && is_array($autofill)) ? $autofill : [];
                            $initialPoMode = old('po_input_mode', 'number');
                        @endphp

                        <input type="hidden" name="payment" id="payment-method-input" value="">
                        <div class="col-md-12">
                            <h4>Datos del Participante</h4>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Nombres</label>
                            <input type="text" name="name" value="{{ old('name', $autofill['name'] ?? null) }}" class="form-control" placeholder="Indica tus nombres acá." autocomplete="off" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Apellidos</label>
                            <input type="text" name="lastname" value="{{ old('lastname', $autofill['lastname'] ?? null) }}" class="form-control" placeholder="Indica tus apellidos acá." autocomplete="off" required>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="{{ old('email', $autofill['email'] ?? null) }}" class="form-control" placeholder="Indica acá tu correo electrónico." autocomplete="off" required>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Evento</label>
                            <select id="event-select" class="form-control" autocomplete="off" required>
                                <option value="">Selecciona un evento</option>
                                @foreach($events as $event)
                                    <option value="{{ $event->id }}" data-allow-transfer="{{ $event->allow_bank_transfer ? '1' : '0' }}">{{ $event->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12 form-group">
                            <label>Tipo de Ticket</label>
                            <select name="ticket_id" id="ticket-select" class="form-control" autocomplete="off" required disabled>
                                <option value="">Selecciona un ticket</option>
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Monto a Pagar</label>
                            <input type="text" name="amount" value="{{ old('amount') }}" class="form-control currency" placeholder="$0" autocomplete="off" required>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Observación (Opcional)</label>
                            <input type="text" name="user_observation" value="{{ old('user_observation') }}" class="form-control" placeholder="Agrega alguna nota de referencia" autocomplete="off">
                        </div>
                        <div class="col-md-12 form-group">
                            <label class="d-block">Orden de compra / documento asociado</label>
                            <div class="btn-group btn-group-toggle mb-2" data-toggle="buttons">
                                <label class="btn btn-outline-primary {{ $initialPoMode === 'number' ? 'active' : '' }}" id="po-option-number">
                                    <input type="radio" name="po_input_mode" value="number" autocomplete="off" {{ $initialPoMode === 'number' ? 'checked' : '' }}> Número
                                </label>
                                <label class="btn btn-outline-primary {{ $initialPoMode === 'file' ? 'active' : '' }}" id="po-option-file">
                                    <input type="radio" name="po_input_mode" value="file" autocomplete="off" {{ $initialPoMode === 'file' ? 'checked' : '' }}> Documento
                                </label>
                            </div>
                            <div id="po-number-wrapper" class="mt-1">
                                <input type="text" name="purchase_order_number" value="{{ old('purchase_order_number') }}" class="form-control" placeholder="Ingresa el número de la orden de compra">
                            </div>
                            <div id="po-file-wrapper" class="mt-2 d-none">
                                <input type="file" name="purchase_order_file" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="form-text text-muted">PDF o imagen de la orden.</small>
                            </div>
                        </div>

                        <div class="col-md-12 form-group">
                            <label>Participantes (Excel opcional)</label>
                            <input type="file" name="participants_excel" class="form-control-file" accept=".xlsx,.xls,.csv">
                            <small class="form-text text-muted">Adjunta un Excel con el listado de participantes según el formato (se validan los campos obligatorios marcados con *).</small>
                        </div>
                        <div class="col-md-12 text-center mt-3">
                            <div class="row text-center">
                                <div class="col btn-not-free">
                                    <button value="transfer" type="button" name="payment" class="btn submit-form payment-trigger">
                                        {{ ($lang == 'esp') ? "Pagar con Transferencia (CLP o USD)"  : "Pay Bank Transfer (CLP or USD)" }}
                                    </button>
                                    <small style="display: block">{{ $lang == 'esp' ? 'Los detalles para la tranferencia serán enviados a su correo.' : 'Details for the transfer will be sent to your mail.' }}</small>
                                </div>
                                <div class="col btn-not-free">
                                    <button value="webpay" type="button" name="payment" class="btn submit-form payment-trigger">
                                        {{ $lang == 'esp' ? 'Pagar con Tarjetas' : 'Pay OnLine' }}
                                    </button>
                                <small style="display: block">{{ $lang == 'esp' ? 'Será dirigido a la página de pago.' : 'It will be directed to the payment page.' }}</small>
                                <img style="width: 200px" src="/images/visamaster.png">
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="speaker-shap">
            <img class="shap1" src="images/shap/home_speaker_memphis1.png" alt="">
            <img class="shap2" src="images/shap/home_speaker_memphis1.png" alt="">
        </div>

    </section>    

    @include('guest.common.payment_confirmation_modal')
    
    <style>
        
        /* Toggle de modo de pago */
        .btn-group-toggle .btn {
            color: inherit;
            background: inherit;
            height: 30px;
            line-height: 30px;
            box-shadow: none !important;
            text-transform: none;
        }

        .btn-group-toggle .btn-outline-primary{
            border-color: #3b1d82;
        }

        .btn-outline-primary:not([disabled]):not(.disabled).active, .btn-outline-primary:not([disabled]):not(.disabled):active, .show>.btn-outline-primary.dropdown-toggle {
            color: #fff;
            background-color: #3b1d82;
            border-color: #3b1d82;
        }
        /* FIN- Toggle de modo de pago */
    </style>

    <script>
        const form = document.getElementById('group-payment-form');
        const eventSelect = document.getElementById('event-select');
        const ticketSelect = document.getElementById('ticket-select');
        const transferBtn = document.querySelector('button[name="payment"][value="transfer"]');
        const paymentButtons = document.querySelectorAll('.payment-trigger');
        const paymentMethodInput = document.getElementById('payment-method-input');
        const amountInput = document.querySelector('input[name="amount"]');

        function toggleTransferButton(option) {
            if (!transferBtn) return;
            const allow = option ? option.getAttribute('data-allow-transfer') : null;
            const enabled = allow === '1';
            transferBtn.disabled = !enabled;
            transferBtn.closest('.col').classList.toggle('d-none', !enabled);
        }

        eventSelect.addEventListener('change', function () {
            const eventId = this.value;
            const selectedOption = this.options[this.selectedIndex];

            toggleTransferButton(selectedOption);

            ticketSelect.innerHTML = '<option value="">Cargando tickets...</option>';
            ticketSelect.disabled = true;

            if (eventId) {
                fetch(`/event-tickets/${eventId}`)
                    .then(response => response.json())
                    .then(data => {
                        ticketSelect.innerHTML = '<option value="">Selecciona un ticket</option>';
                        data.forEach(ticket => {
                            const option = document.createElement('option');
                            option.value = ticket.id;
                            option.textContent = ticket.name;
                            ticketSelect.appendChild(option);
                        });
                        ticketSelect.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error al cargar tickets:', error);
                        ticketSelect.innerHTML = '<option value="">Error al cargar</option>';
                        ticketSelect.disabled = true;
                    });
            } else {
                ticketSelect.innerHTML = '<option value="">Selecciona un ticket</option>';
                ticketSelect.disabled = true;
            }
        });

        // Estado inicial (por si hay valor precargado)
        toggleTransferButton(eventSelect.options[eventSelect.selectedIndex]);

        // Manejo de orden de compra
        const poOptionNumber = document.getElementById('po-option-number');
        const poOptionFile = document.getElementById('po-option-file');
        const poNumberWrapper = document.getElementById('po-number-wrapper');
        const poFileWrapper = document.getElementById('po-file-wrapper');
        const poNumberInput = document.querySelector('input[name="purchase_order_number"]');
        const poFileInput = document.querySelector('input[name="purchase_order_file"]');
        
        function setPurchaseOrderMode(mode) {
            const useNumber = mode === 'number';

            poNumberWrapper.classList.toggle('d-none', !useNumber);
            poFileWrapper.classList.toggle('d-none', useNumber);

            if (poNumberInput) {
                poNumberInput.required = useNumber;
                if (!useNumber) poNumberInput.value = '';
            }
            if (poFileInput) {
                poFileInput.required = !useNumber;
                if (useNumber) poFileInput.value = '';
            }
        }

        if (poOptionNumber && poOptionFile) {
            poOptionNumber.addEventListener('click', () => setPurchaseOrderMode('number'));
            poOptionFile.addEventListener('click', () => setPurchaseOrderMode('file'));
            // Initial state based on selected radio
            const selectedMode = document.querySelector('input[name="po_input_mode"]:checked');
            setPurchaseOrderMode(selectedMode ? selectedMode.value : 'number');
        }

        document.addEventListener('DOMContentLoaded', function () {
            window.bindPaymentConfirmationModal({
                form: form,
                triggerSelector: '.payment-trigger',
                onTriggerClick: function (button) {
                    if (paymentMethodInput) {
                        paymentMethodInput.value = button.value;
                    }
                },
                getCourseName: function () {
                    const selectedOption = eventSelect ? eventSelect.options[eventSelect.selectedIndex] : null;
                    return selectedOption && selectedOption.value ? selectedOption.textContent.trim() : 'Sin evento seleccionado';
                },
                getAmountText: function () {
                    return amountInput ? amountInput.value.trim() : '$0';
                }
            });
        });
    </script>
@endsection