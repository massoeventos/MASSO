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
                    {!! Form::open(['url'=>route('public.payment2'), 'method'=>'POST', 'class'=>'media align-items-end row', 'id'=>'group-payment-form']) !!}
                        <input type="hidden" name="payment" id="payment-method-input" value="">
                        <div class="col-md-12">
                            <h4>Datos del Participante</h4>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Nombres</label>
                            {!! Form::text('name', null, ['class'=>'form-control', 'placeholder'=>'Indica tus nombres acá.', 'autocomplete'=>'off', 'required'=>'required']) !!}
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Apellidos</label>
                            {!! Form::text('lastname', null, ['class'=>'form-control', 'placeholder'=>'Indica tus apellidos acá.', 'autocomplete'=>'off', 'required'=>'required']) !!}
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Email</label>
                            {!! Form::email('email', null, ['class'=>'form-control', 'placeholder'=>'Indica acá tu correo electrónico.', 'autocomplete'=>'off', 'required'=>'required']) !!}
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
                            {!! Form::text('amount', null, ['class'=>'form-control currency', 'placeholder'=>'$0', 'autocomplete'=>'off', 'required'=>'required']) !!}
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Observación (Opcional)</label>
                            {!! Form::text('user_observation', null, ['class'=>'form-control', 'placeholder'=>'Agrega alguna nota de referencia', 'autocomplete'=>'off']) !!}
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
                    {!! Form::close() !!}
                </div>
            </div>
        </div>

        <div class="speaker-shap">
            <img class="shap1" src="images/shap/home_speaker_memphis1.png" alt="">
            <img class="shap2" src="images/shap/home_speaker_memphis1.png" alt="">
        </div>

    </section>    

    <div class="modal fade align-items-center" id="paymentConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="payment-confirmation-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 id="payment-confirmation-title" class="modal-title mb-0">Confirmar pago</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Revisa los datos antes de continuar.</p>
                    <p class="mb-2"><strong>Curso:</strong> <span id="confirmation-course-name">—</span></p>
                    <p class="mb-0"><strong>Monto:</strong> <span id="confirmation-amount">—</span></p>
                </div>
                <div class="modal-footer d-flex flex-column flex-sm-row justify-content-center align-items-stretch">
                    <button type="button" class="btn bg-secondary mb-2 mb-sm-0" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn" id="confirmation-modal-confirm">Confirmar y pagar</button>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        #paymentConfirmationModal .modal-body p {
            color: initial;
        }
        #paymentConfirmationModal .modal-footer .btn { 
            font-size: 12px;
            height: 30px;
            line-height: 30px;
        }
    </style>

    <script>
        const form = document.getElementById('group-payment-form');
        const eventSelect = document.getElementById('event-select');
        const ticketSelect = document.getElementById('ticket-select');
        const transferBtn = document.querySelector('button[name="payment"][value="transfer"]');
        const paymentButtons = document.querySelectorAll('.payment-trigger');
        const paymentMethodInput = document.getElementById('payment-method-input');
        const amountInput = document.querySelector('input[name="amount"]');

        const confirmationModalElement = document.getElementById('paymentConfirmationModal');
        const confirmationModal = window.jQuery ? window.jQuery(confirmationModalElement) : null;
        const confirmationCourse = document.getElementById('confirmation-course-name');
        const confirmationAmount = document.getElementById('confirmation-amount');
        const confirmationModalConfirm = document.getElementById('confirmation-modal-confirm');
        const confirmationDismissTriggers = confirmationModalElement ? confirmationModalElement.querySelectorAll('[data-dismiss="modal"]') : [];
        let fallbackBackdrop = null;

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
    </script>
    
    <script>
        
        paymentButtons.forEach(button => {
            button.addEventListener('click', () => {
                paymentMethodInput.value = button.value;

                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                showConfirmation();
            });
        });

        confirmationModalConfirm.addEventListener('click', () => {
            closeConfirmationModal();
            form.submit();
        });

        confirmationDismissTriggers.forEach(trigger => {
            trigger.addEventListener('click', () => {
                if (!(confirmationModal && confirmationModal.modal)) {
                    closeConfirmationModal();
                }
            });
        });


        function openConfirmationModal() {
            if (confirmationModal && confirmationModal.modal) {
                confirmationModal.modal('show');
            } else if (confirmationModalElement) {
                confirmationModalElement.classList.add('show');
                confirmationModalElement.style.display = 'flex';
                document.body.classList.add('modal-open');

                fallbackBackdrop = document.createElement('div');
                fallbackBackdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(fallbackBackdrop);
            }
        }

        function closeConfirmationModal() {
            if (confirmationModal && confirmationModal.modal) {
                confirmationModal.modal('hide');
            } else if (confirmationModalElement) {
                confirmationModalElement.classList.remove('show');
                confirmationModalElement.style.display = 'none';
                document.body.classList.remove('modal-open');

                if (fallbackBackdrop) {
                    fallbackBackdrop.parentNode.removeChild(fallbackBackdrop);
                    fallbackBackdrop = null;
                }
            }
        }

        function showConfirmation() {
            const selectedOption = eventSelect.options[eventSelect.selectedIndex];
            const eventName = selectedOption && selectedOption.value ? selectedOption.textContent.trim() : 'Sin evento seleccionado';
            const amountValue = amountInput ? amountInput.value.trim() : '';

            confirmationCourse.textContent = eventName || '—';
            confirmationAmount.textContent = amountValue || '$0';
            openConfirmationModal();
        }

    </script>
@endsection