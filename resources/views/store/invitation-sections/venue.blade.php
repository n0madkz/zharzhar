    <section class="invite-section venue-section" data-reveal>
        @if($template->event_type === 'wedding')<span class="invite-wedding-rings invite-wedding-rings--section" aria-hidden="true"><i></i><i></i></span>@else<span class="venue-rings" aria-hidden="true"></span>@endif
        <p class="invite-overline">{{ $copy['venue'] }}</p>
        <h2>{{ $details['venue_name'] }}</h2>
        <a class="venue-location" href="{{ $twoGisUrl }}" target="_blank" rel="noopener" aria-label="{{ $copy['map'] }}: {{ $details['venue_address'] }}">
            <span class="two-gis-logo" aria-hidden="true">2GIS</span>
            <span class="venue-location-copy"><small>{{ $copy['address_label'] }}</small><strong>{{ $details['venue_address'] }}</strong><span>{{ $copy['map'] }}</span></span>
            <span class="venue-location-arrow" aria-hidden="true">↗</span>
        </a>
    </section>
