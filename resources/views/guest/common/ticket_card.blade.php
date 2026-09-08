<div class="ticket">

    <?php
        $registerParams = ['id' => $event->slug];
        if ( $lang !== 'esp' ) { $registerParams['english'] = 1; }
        if ( $ticket->isAvailable() ) { $registerParams['ticket'] = $ticket->id; }
    ?>
    @if( $isAvailable ) <a href="{{ route('public.register', $registerParams) }}"> @endif

    <h5>{{ $lang == 'esp' ? $ticket->name : $ticket->name_eng }}</h5>
    <span class="price">CLP${{ number_format($ticket->price, 0,',','.') }}</span>

    <p>{{ ($lang == 'esp') ? $ticket->description : $ticket->description_eng }} </p>

    @if( $ticket->stock > 0 )
    <p>{{ ($lang == 'esp') ? 'Quedan '.$ticket->stock.' disponibles. '.$ticket->availableText() : $ticket->stock.' tickets left. '.$ticket->availableEngText() }}</p>
    @else
    <p>{{ ($lang == 'esp') ? 'Este ticket se encuentra agotado.' : 'This ticket is sold out' }}  </p>
    @endif

    @if( $isAvailable ) </a> @endif
</div>
