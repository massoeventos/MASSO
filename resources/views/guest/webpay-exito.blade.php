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
        <h2>{{ $payment->managment == 'transfer' ? 'Inscripción Recibida / Registration Received' : 'Pago Recibido / Payment received' }}</h2>
        </div>
        </div>
    </div>

    <section id="ts-speakers-standard" class="ts-speakers-standard ts-speakers speaker-classic section-bg">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-title text-center">
                        <span>Hemos recibido tu @if( $payment->managment == 'transfer' ) inscripción @else pago @endif</span>
                        @if( $payment->managment == 'transfer' ) Inscripción Exitosa @else Transacción Exitosa @endif
                    </h2>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-9 mx-auto"><div class="alert alert-success flash-alert">
                    @if( $payment->managment == 'transfer' )
                        Tu inscripción ha sido procesada exitosamente. Los datos para ejecutar la transferencia se muestran a continuación, y será enviado a tu correo electrónico.<br><br>Your registration has been successfully processed. The data to execute the transfer are shown below, and will be sent to your email.
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
