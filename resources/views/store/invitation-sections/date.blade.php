    <section class="invite-section date-section" data-reveal>
        <p class="invite-script">{{ $copy['date_title'] }}</p>
        <div class="date-orb">
            <span>{{ $monthNames[$eventDate->month - 1] }}</span>
            <strong>{{ $eventDate->format('d') }}</strong>
            <span>{{ $weekdayNames[$eventDate->dayOfWeekIso - 1] }}</span>
        </div>
        <p class="invite-time">{{ $details['event_time'] }}</p>
    </section>
