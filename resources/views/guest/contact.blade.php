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
                        <span>¿Nos deseas contactar?</span>
                        Escríbenos o Llámanos 
                    </h2>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4 offset-lg-4">
                    <div class="single-intro-text single-contact-feature">
                        <h3 class="ts-title">Información General</h3>
                        <p>
                            <strong>Nombre:</strong> Masso Eventos
                        </p>
                        <p>
                            <strong>Teléfono:</strong> <a href="tel:+569 9092 8575">+569 9092 8575</a>
                        </p>
                        <p>
                            <strong>Email:</strong> <a href="mailto:claudio@massoeventos.cl">claudio@massoeventos.cl</a>
                        </p>
                        <span class="count-number fa fa-paper-plane"></span>
                    </div>
                    <div class="border-shap left"></div>
                </div>

            </div>
        </div>

        <div class="speaker-shap">
            <img class="shap2" src="images/shap/home_schedule_memphis1.png" alt="">
        </div>
    </section>
      <section class="ts-contact-map no-padding">
         <div class="container-fluid">
            <div class="row">
               <div class="col-lg-12 no-padding">
                  <div class="mapouter">
                     <div class="gmap_canvas">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3329.1662982550997!2d-70.60472968471193!3d-33.444973680775504!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x9662cf996a59d1cf%3A0x918554d321801d8e!2sDr+Pedro+Lautaro+Ferrer+2701-2809%2C+Providencia%2C+Regi%C3%B3n+Metropolitana!5e0!3m2!1ses-419!2scl!4v1561314134993!5m2!1ses-419!2scl" width="100%" height="450" frameborder="0" style="border:0" allowfullscreen></iframe>
                  </div>
               </div>
            </div>
         </div>
      </section>

@endsection