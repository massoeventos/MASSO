{{-- Carga una entrada del build de Vite (vite.config.mjs, public/build-vue).
     Uso: @include('common.vite-entry', ['entry' => 'resources/js/...']).
     Si falta el build (deploy sin `npm run production`), Vite lanzaría una
     excepción y la página quedaría en 500: se registra en el log y se omite. --}}
@if( file_exists(public_path('build-vue/manifest.json')) || file_exists(public_path('build-vue/hot')) )
    {{ \Illuminate\Support\Facades\Vite::useBuildDirectory('build-vue')->useHotFile(public_path('build-vue/hot'))->withEntryPoints([$entry]) }}
@else
    @php \Illuminate\Support\Facades\Log::error('Falta public/build-vue/manifest.json (correr npm run production): no se cargó ' . $entry); @endphp
@endif
