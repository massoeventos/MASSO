@extends('layouts.public')

@section('content')


<section class="ts-faq-sec">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="faq-content mb-70">

                    @if( $lang == 'esp' ) <a href="?english" class="eng-button">View in english</a>
                    @else <a href="/{{ $event->slug }}" class="eng-button">Ver en español</a> @endif

                    <h2 class="column-title">
                        {{ $event->displayName($lang) }}
                    </h2>
                    @if($event->bannerImage())
                        <div class="event-banner" style="margin:15px 0;">
                            <img src="{{ $event->bannerImage()->path }}" alt="Banner {{ $event->displayName($lang) }}" style="max-width:100%;height:auto;">
                        </div>
                    @endif
                    <div class="panel-group faq-item" id="accordion" role="tablist" aria-multiselectable="true">

                        
                        <div class="panel">
                            {!! ($lang == 'esp') ? $event->description : $event->description_eng !!}
                        </div>

                        @if($event->footerImages()->count())
                            <div class="footer-gallery" style="text-align:center;margin:25px 0;">
                                @foreach($event->footerImages() as $img)
                                    <img src="{{ $img->path }}" alt="{{ $event->displayName($lang) }}" style="max-width:180px;width:100%;height:auto;margin:6px;display:inline-block;">
                                @endforeach
                            </div>
                        @endif

                        @if( !empty($event->location) )
                        <div class="location-wrapper">
                            <h3 class="subtitle">{{ ($lang == 'esp') ? 'Ubicación ' : 'Location' }}</h3>
                            @if( $event->location !== '100% ONLINE')
                                <div class="maps">
                                    <div style="width: 100%">
                                        <iframe
                                            scrolling="no"
                                            marginheight="0"
                                            marginwidth="0"
                                            src="https://maps.google.com/maps?width=100%25&amp;height=500&amp;hl=es&amp;q={{ $location_to_map }}&amp;t=&amp;z=14&amp;ie=UTF8&amp;iwloc=B&amp;output=embed"
                                            width="100%"
                                            height="500"
                                            frameborder="0">
                                        </iframe>
                                    </div>
                                </div>
                            @else
                                <div class="maps">
                                    <h4 class="subtitle">{{ $event->location }}</h4>
                                </div>
                            @endif
                        </div>
                        @endif

                        @if( $event->tickets()->count() > 0 )
                        <div class="tickets-wrapper">
                            <h3 class="subtitle">{{ ($lang == 'esp') ? 'Tickets' : 'Tickets' }}</h3>
                            @if( $lang == 'esp' )
                                <p>A continuación se muestran los tickets disponibles al dia de hoy. Podrá procesar su registro cliqueando en cualquiera de ellos. Los métodos disponibles de pago son transferencia moneda nacional, extranjera y tarjetas de crédito. <img style="width: 100px" src="/images/visamaster.png"></p>
                            @else
                                <p>The tickets available today are detailed below. You can process your registration by clicking on any of them. The available methods of payment are transfer of national or foreign currency and credit cards. <img style="width: 100px" src="/images/visamaster.png"></p>
                            @endif
                            <div class="tickets-container">
                                <?php $isAvailable = $event->hasTicketsAvailables() ?>
                                {{-- Agrupado igual que el checkout: Event::ticketGroups(); acá se muestran también
                                     los agotados o fuera de fecha (no los apagados). Un ticket en varias categorías
                                     aparece en cada una. --}}
                                <?php $ticketGroups = $event->ticketGroups(false); $hasCategories = $event->ticketCategories()->where('is_active', true)->exists(); ?>
                                @if( !$hasCategories )
                                    @foreach( $ticketGroups as $group )
                                        @foreach( $group['tickets'] as $ticket )
                                            @include('guest.common.ticket_card', ['ticket' => $ticket, 'lang' => $lang, 'event' => $event, 'isAvailable' => $isAvailable])
                                        @endforeach
                                    @endforeach
                                @else
                                    @foreach( $ticketGroups as $group )
                                    {{-- Abiertas por defecto: no todos notan la flecha para desplegarlas. --}}
                                    <div class="ticket-category-group is-open">
                                        <h4 class="ticket-category-title ticket-category-header">
                                            <span>{{ $group['category'] ? ($lang == 'esp' ? $group['category']->name : ($group['category']->name_eng ?: $group['category']->name)) : ($lang == 'esp' ? 'Otros' : 'Other') }}</span>
                                            <i class="fa fa-chevron-down accordion-arrow is-rotated" aria-hidden="true"></i>
                                        </h4>
                                        <div class="ticket-category-items">
                                            @foreach( $group['tickets'] as $ticket )
                                                @include('guest.common.ticket_card', ['ticket' => $ticket, 'lang' => $lang, 'event' => $event, 'isAvailable' => $isAvailable])
                                            @endforeach
                                        </div>
                                    </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        @endif

                    </div>



                </div>
            </div>
            <div class="col-lg-4">
                <div class="sidebar-widgets">
                    <div class="widget social-box">
                        <h4 class="widget-title">{{ ($lang == 'esp') ? 'Inscripciones' : "Registration" }}</h4>

                        @if( $event->hasTicketsAvailables() )
                            <p class="no-margin">{{ ($lang == 'esp') ? 'Las inscripciones se encuentran abiertas.' : "The registration is open." }}

                            <a href="{{ route('public.register', $lang == 'esp' ? ['id' => $event->slug] : ['id' => $event->slug, 'english' => 1]) }}" class="btn btn-xs btn-primary">{{ $lang == 'esp' ? 'Formulario de Registro' : 'Registration Form' }}</a>

                                <center>
                                    <small>Pago disponible con:</small> <img style="width: 200px" src="/images/visamaster.png">
                                </center>
                            </p>

                        @else
                            <p>{{ ($lang == 'esp') ? 'Las inscripciones se encuentran cerradas.' : "The registration is closed." }}</p>
                        @endif 
                    </div>

                    <div class="panel">
                        <img src="{{ $event->photo }}" class="img-responsive modal-image">
                    </div>
                    <div class="widget social-box">
                        <h4 class="widget-title">{{ ($lang == 'esp') ? 'Fechas' : 'Dates' }}</h4>
                        <ul>
                            <li><b>{{ ($lang == 'esp') ? 'Desde' : 'From' }}:</b> {{ date('d-m-Y', strtotime($event->date_init)) }}</li><br>
                            <li><b>{{ ($lang == 'esp') ? 'Hasta' : 'To' }}:</b> {{ date('d-m-Y', strtotime($event->date_finish)) }}</li>
                        </ul>
                    </div>

                    @if( !empty($event->organize) )
                    <div class="widget social-box">
                        <h4 class="widget-title">{{ ($lang == 'esp') ? 'Organiza' : 'Organized by' }}</h4>
                        <p>
                           {{ $event->organize }}
                        </p>
                    </div>
                    @endif

                    <div class="widget social-box">
                        <h4 class="widget-title">{{ ($lang == 'esp') ? 'Documentos' : 'Documents' }}</h4>
                        @if( $event->files()->count() > 0 or $event->isUC )
                        <ul>
                            @if( $event->files()->count() > 0 )
                                @foreach( $event->files()->get() as $file )
                            <li><a class="link" target="_BLANK" href="{{ $file->file }}"><i class="fa fa-file"></i> {{ $file->name }}</a></li>
                                @endforeach
                            @endif
                        </ul>
                        @else
                        <p>{{ ($lang == 'esp') ? 'No existen documentos asociados.' : "Doesn't exist attached files." }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@section('footer')
    @include('guest.ajaxmodal')

    @if( $event->ticketCategories->isNotEmpty() )
    <style type="text/css">
        .ticket-category-header {
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #e7015e;
        }
        .ticket-category-header .accordion-arrow {
            color: #e7015e;
            transition: transform 0.2s ease;
        }
        .ticket-category-header .accordion-arrow.is-rotated {
            transform: rotate(180deg);
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function(){
            // Cada categoría se pliega/despliega sola, sin cerrar las demás.
            $('.ticket-category-header').click(function(){
                var $group = $(this).closest('.ticket-category-group');
                var isOpen = $group.toggleClass('is-open').hasClass('is-open');

                $group.find('.ticket-category-items')[isOpen ? 'slideDown' : 'slideUp'](150);
                $(this).find('.accordion-arrow').toggleClass('is-rotated', isOpen);
            });
        });
    </script>
    @endif

    @if( !empty($event->location) )

        <!-- <script>
          var geocoder;
          var map;
          var address = "{{ $event->location }}";
          function initMap() {
            var map = new google.maps.Map(document.getElementById('map'), {
              zoom: 15,
              center: {lat: -34.397, lng: 150.644}
            });
            geocoder = new google.maps.Geocoder();
            codeAddress(geocoder, map);
          }

          function codeAddress(geocoder, map) {
            geocoder.geocode({'address': address}, function(results, status) {
              if (status === 'OK') {
                var infowindow = new google.maps.InfoWindow({
                  content: "{{ $event->location }}"
                });

                map.setCenter(results[0].geometry.location);
                var marker = new google.maps.Marker({
                  map: map,
                  position: results[0].geometry.location
                });

                marker.addListener('click', function() {
                  infowindow.open(map, marker);
                });

              }
            });
          }
        </script> -->
        <style type="text/css">
            a.btn.btn-xs.btn-primary {
                margin: 14px 0;
                display: block;
                padding: 10px 20px;
                font-size: 16px;
                line-height: 16px;
                height: unset!important;
                width: unset!important;
                border: 0;
            }
            .social-box {
                padding: 20px 20px!important;
            }
            .no-margin{
                margin: 0!important;
            }
            .faq-item .panel ul {
                padding-left: 20px;
            }
        </style>
        <!-- <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB487UUbGxH58M9mzUJZQOPA5x5DZqH8AI&callback=initMap"></script> -->

    @endif


@endsection
