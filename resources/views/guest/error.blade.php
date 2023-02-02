@extends('layouts.public')

@section('content')

      <section id="main-container" class="main-container">
         <div class="container">
            <div class="row">
               <div class="col-lg-6 mx-auto">
                  <div class="error-page text-center">
                     <div class="error-code">
                        <h2><strong>404</strong></h2>
                     </div>
                     <div class="error-message">
                        <h3>Página no encontrada!</h3>
                     </div>
                     <div class="error-body">
                        La página a la que intentas acceder no existe o no se encuentra disponible <br>
                        <a href="/" class="btn">Volver al Inicio</a>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </section>

@endsection