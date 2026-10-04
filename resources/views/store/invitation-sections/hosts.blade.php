    <section class="invite-section hosts-section" data-reveal>
        @if($template->event_type === 'wedding')<span class="invite-wedding-rings invite-wedding-rings--section" aria-hidden="true"><i></i><i></i></span>@else<span class="joined-rings" aria-hidden="true"><i></i><i></i></span>@endif
        <p class="invite-overline">{{ $copy['hosts'] }}</p>
        <h2>{{ $details['hosts'] }}</h2>
    </section>
