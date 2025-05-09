@extends('layouts.public')

@section('content')

<section id="main-container" class="main-container">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 mx-auto">
                <div class="error-page text-center">
                    <div class="error-code">
                        <h2><strong>{{ $title === 'Error 404' ? '404' : '' }}</strong></h2>
                    </div>
                    <div class="error-message">
                        <h3>
                            {{ $title === 'Error 404' ? 'Página no encontrada!' : 'Ocurrió un error interno!' }}
                        </h3>
                    </div>
                    <div class="error-body">
                        @if ($title === 'Error 404')
                            La página a la que intentas acceder no existe o no se encuentra disponible.
                        @else
                            Algo salió mal, por favor inténtalo más tarde.
                        @endif
                        <br>
                        <a href="/" class="btn">Volver al Inicio</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
