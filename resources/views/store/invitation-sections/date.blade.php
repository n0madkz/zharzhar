    <section class="invite-section date-section" data-reveal>
        <p class="invite-script">{{ $copy['date_title'] }}</p>
        <div class="date-orb">
            <span>{{ $monthNames[$eventDate->month - 1] }}</span>
            <strong>{{ $eventDate->format('d') }}</strong>
            <span>{{ $weekdayNames[$eventDate->dayOfWeekIso - 1] }}</span>
        </div>
        <p class="invite-time">{{ $details['event_time'] }}</p>
        @if($template->price >= 10990)
            @php
                $calendarStart = $eventDate->copy()->setTimezone('Asia/Almaty')->setTimeFromTimeString($details['event_time'])->utc();
                $calendarUrl = 'https://calendar.google.com/calendar/render?'.http_build_query([
                    'action' => 'TEMPLATE',
                    'text' => $details['names'].' — '.$template->name,
                    'dates' => $calendarStart->format('Ymd\THis\Z').'/'.$calendarStart->copy()->addHours(3)->format('Ymd\THis\Z'),
                    'details' => $invitationText,
                    'location' => $details['venue_name'].', '.$details['venue_address'],
                ], '', '&', PHP_QUERY_RFC3986);
            @endphp
            <a class="invite-calendar-link" href="{{ $calendarUrl }}" target="_blank" rel="noopener noreferrer">{{ $kk ? 'Күнтізбеге қосу' : 'Добавить в календарь' }} <span aria-hidden="true">↗</span></a>
        @endif
    </section>
