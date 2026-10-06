@extends('layouts.public')

@section('content')

    <style type="text/css">
        .page-banner-area.little-area .page-banner-title h2 {
    margin-top: -1%;
    font-size: 28px;
}
    </style>

    <div id="page-banner-area" class="page-banner-area little-area" style="background-image:url(/images/shap/subscribe_pattern.png)">
        <div class="page-banner-title">
        <div class="text-center">
        <h2>{{ $payment->managment == 'transfer' ? 'Solicitud Recibida / Request Received' : 'Pago Recibido / Payment received' }}</h2>
        </div>
        </div>
    </div>

    <section id="ts-speakers-standard" class="ts-speakers-standard ts-speakers speaker-classic section-bg">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-title text-center">
                        <span>Hemos recibido tu @if( $payment->managment == 'transfer' ) solicitud de inscripción @else pago @endif</span>
                        @if( $payment->managment == 'transfer' ) Pendiente de Pago @else Transacción Exitosa @endif
                    </h2>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-9 mx-auto"><div class="alert {{ $payment->managment == 'transfer' ? 'alert-warning' : 'alert-success' }} flash-alert">
                    @if( $payment->managment == 'transfer' )
                        Hemos recibido tu solicitud de inscripción N° <b>#{{ $payment->id }}</b>. <b>Tu inscripción quedará confirmada una vez que recibamos el pago.</b> Te enviaremos los datos para realizar la transferencia; al pagar, incluso si lo hace un tercero o una institución, indica el N° de orden <b>#{{ $payment->id }}</b> en el comentario y envía el comprobante a <a href="mailto:{{ \Masso\Payment::PAYMENT_CONTACT_EMAIL }}">{{ \Masso\Payment::PAYMENT_CONTACT_EMAIL }}</a>. Si tienes problemas para realizar tu pago, comunícate con nosotros a ese mismo correo.<br><br>We have received your registration request N° <b>#{{ $payment->id }}</b>. <b>Your registration will be confirmed once we receive the payment.</b> We will send you the details to make the transfer; when paying, even if a third party or an institution pays, include the order N° <b>#{{ $payment->id }}</b> in the comment and send the receipt to <a href="mailto:{{ \Masso\Payment::PAYMENT_CONTACT_EMAIL }}">{{ \Masso\Payment::PAYMENT_CONTACT_EMAIL }}</a>. If you have trouble making your payment, please contact us at that same address.
                    @else
                        Tu pago ha sido recepcionado exitosamente. Recibirás un correo como comprobante de esta transacción. <br>Your payment has been successfully received. You will receive an email as proof of this transaction.
                    @endif


                </div></div>

                <div class="col-lg-9 mx-auto">
                    <table class="table table-hover table-stripped table-striped">
                        <tbody>
                            <tr>
                                <td>Nombre / Name</td>
                                <td>{{ $payment->name }}</td>
                            </tr>
                            <tr>
                                <td>Apellido / Lastname</td>
                                <td>{{ $payment->lastname }}</td>
                            </tr>
                            <tr>
                                <td>Correo / Email</td>
                                <td>{{ $payment->email }}</td>
                            </tr>
                            <tr>
                                <td>Pago / Payment</td>
                                <td>
                                    {{ $payment->description }}
                                    <?php
                                    if(is_array($events) && count($events) > 0) {
                                        foreach ($events as $event){
                                            echo "<br> &#9679; {$event}";
                                        }
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Monto / Amount</td>
                                <td>CLP${{ number_format($payment->amount,0,',','.') }}</td>
                            </tr>
                            @if( $payment->managment == 'webpay' )
                            <tr>
                                <td>Cód. Aut. / Auth Code</td>
                                <td>{{ $payment->success->auth_code }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="col-lg-9 mx-auto">
                    <p class="text-center">*En caso de dudas o consultas, no dudes en contactarnos.<br>* In case of doubts or questions, do not hesitate to contact us.</p>
                </div>
            </div>
        </div>

        <div class="speaker-shap">
            <img class="shap1" src="/images/shap/home_speaker_memphis1.png" alt="">
            <img class="shap2" src="/images/shap/home_speaker_memphis1.png" alt="">
        </div>

    </section>

@endsection
