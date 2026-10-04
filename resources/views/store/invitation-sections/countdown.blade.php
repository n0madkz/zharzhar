    <section class="invite-section countdown-section" data-countdown="{{ $eventDate->format('Y-m-d').'T'.($details['event_time'] ?? '18:00') }}" data-reveal>
        @if($storyCountdownImage)<img class="story-section-photo" src="{{ $storyCountdownImage }}" alt="" loading="lazy">@endif
        <p class="invite-script">{{ $copy['countdown'] }}</p>
        <div class="countdown-grid">
            @foreach([['days', $copy['days']], ['hours', $copy['hours']], ['minutes', $copy['minutes']], ['seconds', $copy['seconds']]] as [$part, $label])
                <div><strong data-countdown-part="{{ $part }}">00</strong><span>{{ $label }}</span></div>
            @endforeach
        </div>
    </section>
