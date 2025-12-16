@extends('layouts.public')

@section('content')

    <div id="page-banner-area" class="page-banner-area little-area" style="background-image:url(/images/shap/subscribe_pattern.png)">
        <div class="page-banner-title">
        <div class="text-center">
        <h2>Eventos Anteriores</h2>
        </div>
        </div>
    </div>

    <section id="ts-speakers-standard" class="ts-speakers-standard ts-speakers speaker-classic section-bg">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-title text-center">
                        <span>Estos eventos hemos gestionado</span>
                        Eventos Previos
                    </h2>
                </div>
            </div>

            <div class="row">
                @if( !empty($events) && $events->count() > 0 )
                    <div class="events-container row w-100">
                        @include('guest.previous._items', ['events' => $events])
                    </div>
                    <div class="page-load-status col-12 text-center py-3" style="display:none;">
                        <div class="infinite-scroll-request">Cargando más eventos...</div>
                        <div class="infinite-scroll-last" style="display:none;">No hay más eventos.</div>
                        <div class="infinite-scroll-error" style="display:none;">No se pudo cargar más eventos.</div>
                    </div>
                @else
                    <div class="col-12">
                        <div class="text-center events-empty">
                            <p>En estos momentos el listado está vacío.<br>Pronto cargaremos lo necesario.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="speaker-shap">
            <img class="shap1" src="images/shap/home_speaker_memphis1.png" alt="">
            <img class="shap2" src="images/shap/home_speaker_memphis1.png" alt="">
        </div>

    </section>    

    
    @if( !empty($events) && $events->count() > 0 )
    <script src="https://unpkg.com/infinite-scroll@5/dist/infinite-scroll.pkgd.min.js"></script>
    <script>
        (function(){
            const container = document.querySelector('.events-container');
            const status = document.querySelector('.page-load-status');
            let nextPageUrl = '{{ $events->nextPageUrl() }}';

            if (!container || !nextPageUrl) {
                return;
            }

            const infScroll = new InfiniteScroll(container, {
                path() {
                    return nextPageUrl;
                },
                responseBody: 'json',
                history: false,
                checkLastPage: false,
                status,
                fetchOptions: {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                },
                scrollThreshold: 300
            });

            infScroll.on('load', function(body) {
                const html = body && body.html ? body.html : '';
                if (html) {
                    const temp = document.createElement('div');
                    temp.innerHTML = html;
                    const items = Array.from(temp.children);
                    infScroll.appendItems(items);
                }

                nextPageUrl = body ? body.next_page_url : null;

                if (!nextPageUrl) {
                    infScroll.destroy();
                    if (status) {
                        const last = status.querySelector('.infinite-scroll-last');
                        if (last) last.style.display = 'block';
                    }
                }
            });

            infScroll.on('error', function() {
                if (status) {
                    const errorEl = status.querySelector('.infinite-scroll-error');
                    if (errorEl) errorEl.style.display = 'block';
                }
            });
        })();
    </script>
    @endif

@endsection