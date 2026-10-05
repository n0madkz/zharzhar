@php
    $eventDate = \Carbon\Carbon::parse($details['event_date']);
    $templateCopy = $template->config_json['content_kk'] ?? [];
    $copy = array_replace($templateCopy, $details['template_copy'] ?? [], $details['copy'] ?? []);
    $jubileeAge = $details['jubilee_age'] ?? ($preview ? ($copy['jubilee_age'] ?? $copy['jubilee_number'] ?? null) : null);
    $invitationText = ($details['invitation_text'] ?? null) ?: ($copy['invitation_text'] ?? 'Мерейлі күнімізге шақырамыз!');
    $closingText = $copy['closing'] ?? $copy['closing_text'] ?? 'Ақ тілегіңіз — тойымыздың ең қымбат сыйы.';
    $monthNames = ['қаңтар', 'ақпан', 'наурыз', 'сәуір', 'мамыр', 'маусым', 'шілде', 'тамыз', 'қыркүйек', 'қазан', 'қараша', 'желтоқсан'];
    $twoGisUrl = ! empty($details['two_gis_url']) && str_starts_with($details['two_gis_url'], 'https://')
        ? $details['two_gis_url']
        : 'https://2gis.kz/search/'.rawurlencode(trim($details['venue_name'].' '.$details['venue_address']));
@endphp

@extends('layouts.store', [
    'title' => $details['names'].' — Мерейтой',
    'pageLanguage' => 'kk',
    'invitationMode' => true,
    'jubileeMode' => true,
])

@section('content')
@if($preview)
<nav class="preview-toolbar" aria-label="Шаблонды алдын ала қарау">
    <a class="preview-back" href="{{ route('store.catalog') }}#designs"><span aria-hidden="true">←</span> Шаблондарға қайту</a>
    <span class="preview-template">{{ $details['names'] }}</span>
    <a class="preview-choose" href="{{ route('store.checkout', $template) }}">Осы дизайнды таңдау · {{ number_format($template->price, 0, ',', ' ') }} ₸</a>
</nav>
@endif

<article class="jubilee-page">
    <header class="jubilee-cover">
        <div class="jubilee-topline"><span>ZHARZHAR / МЕРЕЙТОЙ ШАҚЫРУЫ</span><span>01 — ӨМІР ӨРНЕГІ</span></div>
        <button class="music-orb music-theme-jubilee-tumar jubilee-music" type="button" data-invite-music @if(empty($details['music_url'])) data-preview-tone @endif aria-label="Музыканы қосу" aria-pressed="false" data-play-label="Музыканы қосу" data-pause-label="Музыканы тоқтату"><span class="music-kazakh-ornament" aria-hidden="true"></span><span class="music-control-icon" aria-hidden="true"><span class="music-play-icon"></span><span class="music-pause-icon"><i></i><i></i></span></span></button>
        <div class="jubilee-cover-main">
            <span class="jubilee-event-label">МЕРЕЙТОЙ</span>
            <span class="jubilee-cover-overline">ҒҰМЫРДЫҢ ҚЫМБАТ БЕЛЕСІ</span>
            <h1>{{ $details['names'] }}</h1>
            @if($jubileeAge)<p class="jubilee-age"><strong>{{ $jubileeAge }}</strong><span> жас</span></p>@endif
            <p class="jubilee-cover-date">{{ $eventDate->format('d.m.Y') }}</p>
            <p>мерейлі тойы</p>
        </div>
        <div class="jubilee-cover-tail"><span class="jubilee-cover-ornament" aria-hidden="true"></span><p>Сізге арналған<br>ақжарма шақыру</p><a href="#jubilee-letter">Шақыруды ашу <span aria-hidden="true">↓</span></a></div>
    </header>

    <section class="jubilee-letter" id="jubilee-letter" data-reveal>
        <p class="jubilee-section-label">01 / ІЗГІ ШАҚЫРУ</p>
        <h2>Қадірлі<br><em>жақындар!</em></h2>
        <p class="jubilee-letter-text">{{ $invitationText }}</p>
        <div class="jubilee-signature"><span>Ізгі ниетпен,</span><strong>{{ $details['hosts'] }}</strong></div>
    </section>

    <section class="jubilee-date" data-reveal>
        <div class="jubilee-date-heading"><span>02 / МЕРЕЙЛІ КҮН</span><h2>Бірге болайық</h2></div>
        <div class="jubilee-date-ticket"><div><span>КҮНІ</span><strong>{{ $eventDate->format('d') }}</strong></div><div><span>АЙЫ</span><strong>{{ $monthNames[$eventDate->month - 1] }}</strong></div><div><span>УАҚЫТЫ</span><strong>{{ $details['event_time'] }}</strong></div></div>
        <p class="jubilee-year">{{ $eventDate->format('Y') }} жыл</p>
        <div class="jubilee-countdown" data-countdown="{{ $eventDate->format('Y-m-d').'T'.$details['event_time'] }}"><p>Кездескенше</p><div><span><strong data-countdown-part="days">00</strong><small>күн</small></span><span><strong data-countdown-part="hours">00</strong><small>сағат</small></span><span><strong data-countdown-part="minutes">00</strong><small>минут</small></span><span><strong data-countdown-part="seconds">00</strong><small>секунд</small></span></div></div>
    </section>

    <section class="jubilee-place" data-reveal>
        <p class="jubilee-section-label">03 / КЕЗДЕСУ ОРНЫ</p>
        <h2>Ақ дастархан<br><em>басында</em></h2>
        <div class="jubilee-place-card"><span class="jubilee-place-icon" aria-hidden="true"></span><strong>{{ $details['venue_name'] }}</strong><p>{{ $details['venue_address'] }}</p><a href="{{ $twoGisUrl }}" target="_blank" rel="noopener">2GIS картасынан қарау <span aria-hidden="true">↗</span></a></div>
    </section>

    <section class="jubilee-rsvp" id="jubilee-rsvp" data-reveal>
        <p class="jubilee-section-label">04 / АҚ ТІЛЕК КІТАБЫ</p>
        <h2>Ақ тілек<br><em>кітабы</em></h2>
        <p class="jubilee-rsvp-intro">Келетініңізді белгілеңіз. Жылы лебізіңізді қалдырсаңыз, мерейтой естелігіне айналады.</p>
        @if($preview)
            <div class="jubilee-form" aria-label="Жауап формасының үлгісі"><label>Аты-жөніңіз<span class="jubilee-preview-input">Аты-жөніңізді жазыңыз</span></label><fieldset class="attendance-choice" disabled><legend>Мерейтойға келесіз бе?</legend><label><input type="radio" checked><b>Иә, келемін</b></label><label><input type="radio"><b>Жоқ</b></label></fieldset><label>Ақ тілегіңіз<span class="jubilee-preview-input jubilee-preview-message">Ізгі тілегіңізді жазыңыз</span></label><button type="button" disabled>Жауап жіберу</button></div>
            <p class="jubilee-form-note">Дайын шақыруда жауаптар жеке парақшаңызға жиналады.</p>
        @else
            @if($errors->any())<p class="error" role="alert">Өрістерді тексеріңіз: {{ $errors->first() }}</p>@endif
            <form class="jubilee-form" method="POST" action="{{ route('store.rsvp', $invitation->slug) }}" data-submit-once>@csrf
                <label>Аты-жөніңіз<input name="guest_name" value="{{ old('guest_name') }}" autocomplete="name" maxlength="120" required></label>
                <fieldset class="attendance-choice"><legend>Мерейтойға келесіз бе?</legend><label><input type="radio" name="attendance_status" value="yes" @checked(old('attendance_status', 'yes') === 'yes') required><b>Иә, келемін</b></label><label><input type="radio" name="attendance_status" value="no" @checked(old('attendance_status') === 'no') required><b>Жоқ</b></label></fieldset>
                <label data-guest-count>Қонақ саны<input type="number" name="guest_count" min="1" max="20" value="{{ old('guest_count', 1) }}" required></label>
                <label>Ақ тілегіңіз<textarea name="message" maxlength="1000">{{ old('message') }}</textarea></label>
                <button type="submit">Жауап жіберу</button>
            </form>
        @endif
    </section>

    <footer class="jubilee-end"><span class="jubilee-end-ornament" aria-hidden="true"></span><p>{{ $closingText }}</p><small>zharzhar · {{ $eventDate->format('Y') }}</small></footer>
</article>
@if(!empty($details['music_url']))<audio id="invite-audio" loop preload="none" src="{{ \App\Models\Music::playbackUrlFor($details['music_url']) }}"></audio>@endif

@endsection
