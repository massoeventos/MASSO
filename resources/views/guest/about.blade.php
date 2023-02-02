@extends('layouts.public')

@section('content')

    <div id="page-banner-area" class="page-banner-area little-area" style="background-image:url(/images/shap/subscribe_pattern.png)">
        <div class="page-banner-title">
        <div class="text-center">
        <h2>¿Quienes Somos?</h2>
        </div>
        </div>
    </div>

    <section class="ts-venue-feature" style="background-image:url(./images/speakers/speaker_bg.png)">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-title text-center">
                        <span>¿Quienes Somos?</span>
                        Massó Eventos
                    </h2>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 text-center">
                    <p>Somos una empresa dedicada a la producción de eventos en el área médica con 20 años de experiencia apoyando a sociedades científicas, organizaciones médicas, universidades, laboratorios y Clínicas.  Nos diferenciamos por ofrecer además asesoría profesional en las actividades complementarias referidas a los servicios turísticos y gastronómicos de congresos, seminarios, encuentros, simposios, talleres y cursos.</p>

                    <p>Nuestro objetivo es entregar flexibilidad en la producción para cumplir con lo esperado por los comités organizadores, con foco en la eficiencia presupuestaria y calidad del servicio que entregamos a través de un equipo de trabajo cohesionado y orientado hacia el cliente.</p>
                </div>
           </div> 
        </div>
        <div class="speaker-shap">
            <img class="shap2" src="images/shap/home_speaker_memphis2.png" alt="">
        </div>
    </section>

    <section class="ts-venue-feature gradient">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <h2 class="section-title white">
                        <span>Nuestra Experiencia</span>
                        Nuestros Servicios
                    </h2>
                </div>
            </div>
            <div class="row">
               <div class="col-lg-4">
                    <div class="single-venue-content text-center">
                        <i class="icon-school"></i>
                        <h3 class="ts-venue-title">Congresos</h3>
                        <p>
                            Cursos, talleres, seminarios, convenciones.
                        </p>
                    </div>
               </div>
               <div class="col-lg-4">
                    <div class="single-venue-content text-center">
                        <i class="icon-food"></i>
                        <h3 class="ts-venue-title">Catering</h3>
                        <p>
                            Asesoría Gastronómicas, elección de restaurantes, banqueteros, menús.
                        </p>
                    </div>
               </div>
               <div class="col-lg-4">
                    <div class="single-venue-content text-center">
                        <i class="icon-cycle"></i>
                        <h3 class="ts-venue-title">Turismo</h3>
                        <p>
                            Coordinación de reservas, áreas, hoteleras, gastronómicas, turísticas.
                        </p>
                    </div>
               </div>
            </div>
        </div>
    </section>  


    @if( !empty($members) )    
    <section class="ts-venue-feature" style="background-image:url(./images/speakers/speaker_bg.png)">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-title text-center">
                        <span>¿Quiénes Somos?</span>
                        Nuestro Equipo
                    </h2>
                </div>
            </div>
            <div class="row">
                @foreach( $members as $member)
                    <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-duration="1.5s" data-wow-delay="400ms">
                        <div class="ts-speaker">
                            <div class="speaker-img">
                                <img class="img-fluid" src="{{ $member->image }}" alt="{{ $member->name }}">
                            </div>
                            <div class="ts-speaker-info">
                                <h3 class="ts-title"><a href="#">{{ $member->name }}</a></h3>
                                <p>{{ nl2br($member->description) }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="speaker-shap">
            <img class="shap1" src="images/shap/home_speaker_memphis1.png" alt="">
            <img class="shap2" src="images/shap/home_speaker_memphis2.png" alt="">
        </div>
    </section>
    @endif

@endsection