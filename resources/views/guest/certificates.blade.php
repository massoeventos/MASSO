@extends('layouts.public')

@section('content')

    <div id="page-banner-area" class="page-banner-area little-area" style="background-image:url(/images/shap/subscribe_pattern.png)">
        <div class="page-banner-title">
        <div class="text-center">
        <h2>{{ $title }}</h2>
        </div>
        </div>
    </div>

    <section class="ts-contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-title text-center">
                        <span>Descarga tu Certificado de Asistencia.</span>
                        <small>Llena el campo que aparece a continuación con tu RUN o Nº de Pasaporte.</small>
                    </h2>
                </div>

                @if(Session::has('error_alert'))
                    <div class="col-lg-9 mx-auto"><div class="alert alert-danger flash-alert">{!! Session::get('error_alert') !!}</div></div>
                @endif

                @if(Session::has('success_alert'))
                    <div class="col-lg-9 mx-auto"><div class="alert alert-success" style="color: black;">{!! Session::get('success_alert') !!}</div></div>
                @endif

                <div class="newsletter-form col-lg-9 mx-auto">
                    <form method="POST" action="{{ route('public.search') }}" class="media align-items-end">
                        @csrf
                        <div class="email-form-group media-body">
                            <input type="text" name="run" value="{{ old('run') }}" class="form-control" placeholder="RUN o Pasaporte sin puntos ni guión" autocomplete="off" required>
                        </div>
                        <div class="d-flex ts-submit-btn">
                            <button class="btn" type="submit">Buscar</button>
                        </div>
                    </form>
                </div>
                <div class="col-lg-9 mx-auto mt-4">
                    <div class="alert alert-dark">Al ingresar los datos requeridos, el sistema mostrará los certificados disponibles asociados a su documento de identificación. En caso de problemas, póngase en contacto con nosotros.</small>
                </div>
            </div>
        </div>
        <div class="speaker-shap">
            <img class="shap2" src="images/shap/home_schedule_memphis1.png" alt="">
        </div>
    </section>

     

@endsection