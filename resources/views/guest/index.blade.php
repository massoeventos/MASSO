@extends('layouts.public')

@section('content')

    <div id="page-banner-area" class="page-banner-area" style="background-image:url(/images/shap/subscribe_pattern.png)">
        <div class="page-banner-title">
        <div class="text-center">
        <h2>Masso Eventos</h2>
        <h3>Producción de Eventos - Servicios Turísticos.</h3>
        </div>
        </div>
    </div>

    <section id="ts-speakers-standard" class="ts-speakers-standard ts-speakers speaker-classic section-bg">
        <div class="container">

            @if( !empty($eventsUC) )
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-title text-center">
                        Próximos Cursos UC
                    </h2>
                </div>
            </div>

            <div class="row">
                @if( !empty($eventsUC) && sizeof($eventsUC) > 0 )
                    @foreach( $eventsUC as $event )
                        <div class="col-md-4">
                            <div class="ts-speaker">
                                <div class="speaker-img">
                                    <a href="/{{ $event->slug }}">
                                        <img class="img-fluid" onerror="this.src='/images/shap/news_memphis2.png'" src="{{ $event->photo }}" alt="">
                                    </a>
                                </div>
                                <div class="ts-speaker-info">
                                    <h3 class="ts-title"><a href="/{{ $event->slug }}" >{{ $event->name }} </a></h3>
                                    <p>
                                       <i class="fa fa-map-marker"></i> {{ $event->location }}
                                    </p>
                                    <p>
                                       <i class="fa fa-calendar"></i> {{ $event->getDateString() }}
                                    </p>
                                    @if( !empty($event->organize))
                                    <p>
                                       <i class="fa fa-cogs"></i> Organiza: {{ $event->organize }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col-12">
                        <div class="text-center events-empty">
                            <p>En estos momentos no se encuentran eventos disponibles.<br>Si gustas puedes visitar los eventos que hemos gestionado con anterioridad.</p>
                        </div>
                    </div>
                @endif
            </div>

            @endif

            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h2 class="section-title text-center">
                        Próximos Eventos
                    </h2>
                </div>
            </div>

            <div class="row">
                @if( !empty($events) && sizeof($events) > 0 )
                    @foreach( $events as $event )
                        <div class="col-md-4">
                            <div class="ts-speaker">
                                <div class="speaker-img">
                                    <a href="/{{ $event->slug }}">
                                        <img class="img-fluid" onerror="this.src='/images/shap/news_memphis2.png'" src="{{ $event->photo }}" alt="">
                                    </a>
                                </div>
                                <div class="ts-speaker-info">
                                    <h3 class="ts-title"><a href="/{{ $event->slug }}" >{{ $event->name }} </a></h3>
                                    <p>
                                       <i class="fa fa-map-marker"></i> {{ $event->location }}
                                    </p>
                                    <p>
                                       <i class="fa fa-calendar"></i> {{ $event->getDateString() }}
                                    </p>
                                    @if( !empty($event->organize))
                                    <p>
                                       <i class="fa fa-cogs"></i> Organiza: {{ $event->organize }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="col-12">
                        <div class="text-center events-empty">
                            <p>En estos momentos no se encuentran eventos disponibles.<br>Si gustas puedes visitar los eventos que hemos gestionado con anterioridad.</p>
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

@endsection