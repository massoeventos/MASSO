@extends('layouts.public')

{{-- Formulario de compra en Vue (resources/js/checkout/app.js). Los datos
     iniciales los arma PublicController::buildCheckoutPayload(). --}}

@section('content')
    <section id="ts-speakers-standard" class="ts-speakers-standard ts-speakers speaker-classic section-bg">
        <div class="container">
            <div class="row">
                <div class="col-lg-9 mx-auto">
                    <a class="navbar-brand" href="/"><img src="/images/logo.jpg" alt=""></a><br>
                </div>
                <div class="col-lg-9 mx-auto">
                    <h3>{{ $checkout['lang'] == 'esp' ? 'Formulario Registro:' : 'Registration Form:' }}<br>{{ $checkout['event']['name'] }}</h3>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-9 mx-auto mb-3">
                    <div class="alert alert-dark">
                        {{ $checkout['lang'] == 'esp'
                            ? 'Para procesar su registro llene todos los campos del formulario que aparece a continuación. El registro quedará confirmado una vez sea ratificado el pago, según corresponda. Puede volver a la ficha del evento'
                            : 'To process your registration, fill in all the fields on the form below. The registration will be confirmed once the payment is ratified. You can return to the event page doing' }}
                        <a href="{{ $checkout['urls']['event'] }}">{{ $checkout['lang'] == 'esp' ? 'haciendo clic acá.' : 'click here.' }}</a>
                    </div>
                </div>
            </div>

            <script type="application/json" id="checkout-data">@json($checkout)</script>
            {{-- Vue reemplaza este contenido al montar; si el JS no carga, queda el aviso --}}
            <div id="checkout-app">
                <div class="row"><div class="col-lg-9 mx-auto text-center text-muted py-5">
                    {{ $checkout['lang'] == 'esp' ? 'Cargando formulario…' : 'Loading form…' }}
                </div></div>
            </div>
        </div>
    </section>
@endsection

@section('footer')
    @if( file_exists(public_path('build-checkout/manifest.json')) || file_exists(public_path('build-checkout/hot')) )
        {{ \Illuminate\Support\Facades\Vite::useBuildDirectory('build-checkout')->useHotFile(public_path('build-checkout/hot'))->withEntryPoints(['resources/js/checkout/app.js']) }}
    @else
        @php \Illuminate\Support\Facades\Log::error('Checkout: falta public/build-checkout/manifest.json (correr npm run build:checkout)'); @endphp
    @endif
@endsection
