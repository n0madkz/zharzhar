@php
    $eventDate = \Carbon\Carbon::parse($details['event_date']);
    $templateCopy = $template->config_json['content_kk'] ?? [];
    $copy = array_replace($templateCopy, $details['template_copy'] ?? [], $details['copy'] ?? []);
    $invitationText = ($details['invitation_text'] ?? null) ?: ($copy['invitation_text'] ?? 'Сүйікті бөбегіміздің бесік тойына шақырамыз!');
    $closingText = $copy['closing'] ?? $copy['closing_text'] ?? 'Ақ бесігімізге ақ батаңызды арнаңыз!';
    $monthNames = ['қаңтар', 'ақпан', 'наурыз', 'сәуір', 'мамыр', 'маусым', 'шілде', 'тамыз', 'қыркүйек', 'қазан', 'қараша', 'желтоқсан'];
    $twoGisUrl = ! empty($details['two_gis_url']) && str_starts_with($details['two_gis_url'], 'https://')
        ? $details['two_gis_url']
        : 'https://2gis.kz/search/'.rawurlencode(trim($details['venue_name'].' '.$details['venue_address']));
@endphp

@extends('layouts.store', [
    'title' => $details['names'].' — Бесік той',
    'pageLanguage' => 'kk',
    'invitationMode' => true,
    'besikMode' => true,
])

@section('content')
@if($preview)
    <nav class="preview-toolbar" aria-label="Шаблонды алдын ала қарау">
        <a class="preview-back" href="{{ route('store.catalog') }}#designs"><span aria-hidden="true">←</span> Шаблондарға қайту</a>
        <span class="preview-template">{{ $details['names'] }}</span>
        <a class="preview-choose" href="{{ route('store.checkout', $template) }}">Осы дизайнды таңдау · {{ number_format($template->price, 0, ',', ' ') }} ₸</a>
    </nav>
@endif

<article class="besik-page">
    <header class="besik-hero">
        <div class="besik-hero-top"><span>БАЛАПАНЫМЫЗДЫҢ АЛҒАШҚЫ ТОЙЫ</span><span class="besik-star" aria-hidden="true"></span></div>
        <p class="besik-hero-kicker">Ақ бесікке ақ тілек</p>
        <h1><span>{{ $details['names'] }}</span><small>бесік тойы</small></h1>
        <p class="besik-hero-date">{{ $eventDate->format('d.m.Y') }} <span aria-hidden="true">·</span> {{ $details['event_time'] }}</p>
        <img class="besik-hero-art" src="{{ asset('invitation-assets/besik-toi.svg') }}" alt="Қазақ бесігінің иллюстрациясы" fetchpriority="high">
        <a class="besik-hero-jump" href="#besik-invitation">Шақыруды ашу <span aria-hidden="true">↓</span></a>
    </header>

    <section class="besik-letter" id="besik-invitation" data-reveal>
        <span class="besik-section-number">01 / АҚ ТІЛЕК</span>
        <span class="besik-embroidered" aria-hidden="true"></span>
        <h2>Қадірлі<br>жақындар!</h2>
        <p>{{ $invitationText }}</p>
        <div class="besik-letter-signature"><span>Той иелері</span><strong>{{ $details['hosts'] }}</strong></div>
    </section>

    <section class="besik-details" data-reveal>
        <span class="besik-section-number">02 / КЕЗДЕСЕТІН КҮН</span>
        <h2>Бірге қуанайық!</h2>
        <div class="besik-date-strip">
            <div><span>КҮНІ</span><strong>{{ $eventDate->format('d') }}</strong></div>
            <div><span>АЙЫ</span><strong>{{ $monthNames[$eventDate->month - 1] }}</strong></div>
            <div><span>УАҚЫТЫ</span><strong>{{ $details['event_time'] }}</strong></div>
        </div>
        <div class="besik-countdown" data-countdown="{{ $eventDate->format('Y-m-d').'T'.$details['event_time'] }}">
            <p>Бесік тойға дейін</p>
            <div class="besik-countdown-grid"><span><strong data-countdown-part="days">00</strong><small>күн</small></span><span><strong data-countdown-part="hours">00</strong><small>сағат</small></span><span><strong data-countdown-part="minutes">00</strong><small>минут</small></span><span><strong data-countdown-part="seconds">00</strong><small>секунд</small></span></div>
        </div>
    </section>

    <section class="besik-place" data-reveal>
        <span class="besik-section-number">03 / МЕКЕН-ЖАЙЫМЫЗ</span>
        <h2>Сізді күтеміз!</h2>
        <p class="besik-place-name">{{ $details['venue_name'] }}</p>
        <p class="besik-place-address">{{ $details['venue_address'] }}</p>
        <a class="besik-map-link" href="{{ $twoGisUrl }}" target="_blank" rel="noopener">2GIS картасынан қарау <span aria-hidden="true">↗</span></a>
    </section>

    <section class="besik-rsvp" id="besik-rsvp" data-reveal>
        <span class="besik-section-number">04 / ЖАУАП ПЕН АҚ ТІЛЕК</span>
        <h2>Ақ тілегіңізді<br>қалдырыңыз</h2>
        <p>Бесік тойға қатысатыныңызды белгілеңіз. Бөпеге арнаған ақ тілегіңіз біз үшін қымбат.</p>
        @if($preview)
            <div class="besik-form" aria-label="Жауап формасының үлгісі">
                <label>Аты-жөніңіз<span class="besik-preview-input">Аты-жөніңізді жазыңыз</span></label>
                <fieldset class="attendance-choice" disabled><legend>Тойға қатысасыз ба?</legend><label><input type="radio" checked><b>Иә, келемін</b></label><label><input type="radio"><b>Жоқ</b></label></fieldset>
                <label>Бөпеге ақ тілегіңіз<span class="besik-preview-input besik-preview-message">Ақ тілегіңізді жазыңыз</span></label>
                <button type="button" disabled>Жауап жіберу</button>
            </div>
            <p class="besik-form-note">Дайын шақыруда қонақтардың жауаптары жеке парақшаңызда жиналады.</p>
        @else
            @if($errors->any())<p class="error" role="alert">Өрістерді тексеріңіз: {{ $errors->first() }}</p>@endif
            <form class="besik-form" method="POST" action="{{ route('store.rsvp', $invitation->slug) }}" data-submit-once>
                @csrf
                <label>Аты-жөніңіз<input name="guest_name" value="{{ old('guest_name') }}" autocomplete="name" maxlength="120" required></label>
                <fieldset class="attendance-choice"><legend>Тойға қатысасыз ба?</legend><label><input type="radio" name="attendance_status" value="yes" @checked(old('attendance_status', 'yes') === 'yes') required><b>Иә, келемін</b></label><label><input type="radio" name="attendance_status" value="no" @checked(old('attendance_status') === 'no') required><b>Жоқ</b></label></fieldset>
                <label data-guest-count>Қонақ саны<input type="number" name="guest_count" min="1" max="20" value="{{ old('guest_count', 1) }}" required></label>
                <label>Бөпеге ақ тілегіңіз<textarea name="message" maxlength="1000">{{ old('message') }}</textarea></label>
                <button type="submit">Жауап жіберу</button>
            </form>
        @endif
    </section>

    <footer class="besik-ending"><span class="besik-embroidered" aria-hidden="true"></span><p>{{ $closingText }}</p><small>zharzhar · 2026</small></footer>
</article>

@if(!empty($details['music_url']))
    <audio id="invite-audio" loop preload="none" src="{{ \App\Models\Music::playbackUrlFor($details['music_url']) }}"></audio>
@endif
<button class="music-orb music-theme-besik-story music-template-besik-toi besik-music" type="button" data-invite-music @if(empty($details['music_url'])) data-preview-tone @endif aria-label="Музыканы қосу" aria-pressed="false" data-play-label="Музыканы қосу" data-pause-label="Музыканы тоқтату">
    <span class="music-kazakh-ornament" aria-hidden="true"></span>
    <span class="music-control-icon" aria-hidden="true"><span class="music-play-icon"></span><span class="music-pause-icon"><i></i><i></i></span></span>
</button>
@endsection
