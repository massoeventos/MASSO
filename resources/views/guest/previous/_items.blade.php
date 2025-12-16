@foreach($events as $event)
<div class="col-md-4 event-card">
    <div class="ts-speaker">
        <div class="speaker-img">
            <img class="img-fluid" onerror="this.src='/images/shap/news_memphis2.png'" src="{{ $event->photo }}" alt="">
        </div>
        <div class="ts-speaker-info">
            <h3 class="ts-title"><span>{{ $event->name }}</span></h3>
            <p>
               <i class="fa fa-map-marker"></i> {{ $event->location }}
            </p>
            <p>
               <i class="fa fa-calendar"></i> {{ $event->getDateString() }}
            </p>
        </div>
    </div>
</div>
@endforeach
