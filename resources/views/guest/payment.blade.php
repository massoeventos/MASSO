@extends('layouts.public')

@section('content')

    <div id="page-banner-area" class="page-banner-area little-area" style="background-image:url(/images/shap/subscribe_pattern.png)">
        <div class="page-banner-title">
        <div class="text-center">
        <h2>Pagos</h2>
        </div>
        </div>
    </div>

    <section id="ts-speakers-standard" class="ts-speakers-standard ts-speakers speaker-classic section-bg">
        <div class="container">
            <div class="row">
                <div class="col-lg-9 mx-auto mb-3">
                    <div class="alert alert-dark">En esta sección podrá enviar pagos personalizados, en caso de gestionar una inscripción a un evento, favor revisar <a href="/" class="">acá</a> los eventos que se encuentran disponibles.</small></div>
                </div>
            </div>

            <div class="row">
                @if(Session::has('error_alert'))
                    <div class="col-lg-9 mx-auto"><div class="alert alert-danger flash-alert">{!! Session::get('error_alert') !!}</div></div>
                @endif
                <div class="newsletter-form col-lg-9 mx-auto">
                    {!! Form::open(['url'=>route('public.payment'), 'method'=>'POST', 'class'=>'media align-items-end row']) !!}
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
                            {!! Form::text('email', null, ['class'=>'form-control', 'placeholder'=>'Indica acá tu correo electrónico.', 'autocomplete'=>'off', 'required'=>'required']) !!}
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Evento y/o descripción del pago</label>
                            {!! Form::select('description', $select_options, null, ['class'=>'form-control', 'autocomplete'=>'off', 'required'=>'required']) !!}
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Monto a Pagar</label>
                            {!! Form::text('amount', null, ['class'=>'form-control currency', 'placeholder'=>'$0', 'autocomplete'=>'off', 'required'=>'required']) !!}
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Observación (Opcional)</label>
                            {!! Form::text('user_observation', null, ['class'=>'form-control', 'placeholder'=>'Agrega alguna nota de referencia', 'autocomplete'=>'off', 'required'=>'']) !!}
                        </div>
                        <div class="col-md-12 text-center mt-3">
                            <div class="row text-center">
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