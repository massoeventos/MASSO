@extends('layouts.public')

@section('content')

    <div id="page-banner-area" class="page-banner-area little-area" style="background-image:url(/images/shap/subscribe_pattern.png)">
        <div class="page-banner-title">
        <div class="text-center">
        <h2>Pago Rechazado</h2>
        </div>
        </div>
    </div>

    <section id="ts-speakers-standard" class="ts-speakers-standard ts-speakers speaker-classic section-bg">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-title text-center">
                        <span>Error en la Transacción</span>
                        Transacción Fallida
                    </h2>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-9 mx-auto"><div class="alert alert-danger flash-alert">Tu pago ha sido rechazado por Transbank o por tu entidad bancaria, inténtalo nuevamente.</div></div>

                <div class="col-lg-9 mx-auto">
                    <p>Transbank ha indicado que tu transacción ha sido rechazada, el mótivo de este rechazo pueden ser:
                    </p>
                    <div class="col-lg-12">
                        <ul>
                            <li>Tarjeta no habilitada por sistema financiero.</li>
                            <li>Tarjeta no tiene cupo y/o saldo para la transacción.</li>
                            <li>La entidad bancaria tiene un bloqueo temporal asociado a tu tarjeta.</li>
                            <li>Error temporal.</li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-9 mx-auto">
                    <p class="text-center mt-4"><a href="/pagos" class="btn ">Intentar Pago Nuevamente</a></p>
                </div>
                <div class="col-lg-9 mx-auto">
                    <p class="text-center mt-4">*En caso de dudas o consultas, no dudes en contactarnos. </p>
                </div>
            </div>
        </div>

        <div class="speaker-shap">
            <img class="shap1" src="/images/shap/home_speaker_memphis1.png" alt="">
            <img class="shap2" src="/images/shap/home_speaker_memphis1.png" alt="">
        </div>

    </section>    

@endsection