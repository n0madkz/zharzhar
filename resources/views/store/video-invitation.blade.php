@php
    $kk = ($details['language'] ?? 'kk') === 'kk';
    $eventDate = \Carbon\Carbon::parse($details['event_date']);
    $videoPath = data_get($template->config_json, 'video_url');
    $videoUrl = $videoPath && str_starts_with($videoPath, '/') ? asset(ltrim($videoPath, '/')) : $videoPath;
    $posterPath = $template->preview_image;
    $posterUrl = $posterPath && str_starts_with($posterPath, '/') ? asset(ltrim($posterPath, '/')) : $posterPath;
    $endSeconds = max(2, min(20, (int) data_get($template->config_json, 'video_end_seconds', 6)));
    $finalText = $details['video_final_text'] ?? data_get($template->config_json, 'content_kk.closing_text') ?? ($kk ? 'Қуанышымызға ортақ болыңыз!' : 'Разделите с нами этот счастливый день!');
    $copy = $kk ? [
        'back' => 'Шаблондарға қайту', 'choose' => 'Осы дизайнды таңдау',
        'play' => 'Видеоны ойнату', 'unsupported' => 'Браузеріңіз видеоны қолдамайды.',
        'video_missing' => 'Видео әлі жүктелмеген.', 'event' => 'Мереке туралы',
        'date' => 'Күні мен уақыты', 'venue' => 'Мекенжай', 'map' => '2GIS-те ашу',
        'rsvp' => 'Сізді күтеміз!', 'hint' => 'Тойға қатысуыңызды растауыңызды сұраймыз.',
        'name' => 'Аты-жөніңіз', 'answer' => 'Тойға қатысасыз ба?', 'yes' => 'Иә', 'no' => 'Жоқ',
        'count' => 'Қонақ саны', 'message' => 'Ақ тілегіңіз', 'send' => 'Жауап жіберу',
        'preview_form' => 'Дайын шақыруда қонақтар осы жерден жауабын жібереді.',
    ] : [
        'back' => 'Назад к шаблонам', 'choose' => 'Выбрать этот дизайн',
        'play' => 'Воспроизвести видео', 'unsupported' => 'Ваш браузер не поддерживает видео.',
        'video_missing' => 'Видео пока не загружено.', 'event' => 'О событии',
        'date' => 'Дата и время', 'venue' => 'Место проведения', 'map' => 'Открыть в 2GIS',
        'rsvp' => 'Будем ждать вас!', 'hint' => 'Пожалуйста, сообщите, сможете ли вы прийти.',
        'name' => 'Ваше имя', 'answer' => 'Вы придёте?', 'yes' => 'Да', 'no' => 'Нет',
        'count' => 'Количество гостей', 'message' => 'Ваше пожелание', 'send' => 'Отправить ответ',
        'preview_form' => 'В готовом приглашении гости отправят ответ здесь.',
    ];
    $providedTwoGisUrl = $details['two_gis_url'] ?? null;
    $twoGisUrl = is_string($providedTwoGisUrl) && str_starts_with($providedTwoGisUrl, 'https://')
        ? $providedTwoGisUrl
        : 'https://2gis.kz/search/'.rawurlencode(trim(($details['venue_name'] ?? '').' '.($details['venue_address'] ?? '')));
@endphp

@extends('layouts.store', [
    'title' => $details['names'].' — '.$template->name,
    'pageLanguage' => $kk ? 'kk' : 'ru',
    'invitationMode' => true,
])

@section('content')
@if($preview)
<nav class="preview-toolbar" aria-label="Предпросмотр шаблона">
    <a class="preview-back" href="{{ route('store.catalog') }}#designs"><span aria-hidden="true">←</span> {{ $copy['back'] }}</a>
    <span class="preview-template">{{ $template->name }}</span>
    <a class="preview-choose" href="{{ route('store.checkout', $template) }}">{{ $copy['choose'] }} · {{ number_format($template->price, 0, ',', ' ') }} ₸</a>
</nav>
@endif

<article class="video-invite-page">
    <section class="video-invite-stage" data-video-invitation data-end-seconds="{{ $endSeconds }}">
        @if($videoUrl)
        <video controls playsinline preload="metadata" @if($posterUrl) poster="{{ $posterUrl }}" @endif>
            <source src="{{ $videoUrl }}">
            {{ $copy['unsupported'] }}
        </video>
        @else
        <div class="video-invite-placeholder" @if($posterUrl) style="background-image:url('{{ $posterUrl }}')" @endif><span>{{ $copy['video_missing'] }}</span></div>
        @endif
        <div class="video-final-overlay" data-video-final aria-hidden="true">
            <div>
                <p>{{ $eventDate->format('d · m · Y') }}</p>
                <h1>{{ $details['names'] }}</h1>
                <strong>{{ $finalText }}</strong>
            </div>
        </div>
    </section>

    <section class="video-event-card">
        <p class="video-section-label">{{ $copy['event'] }}</p>
        <h2>{{ $details['names'] }}</h2>
        @if(!empty($details['invitation_text']))<p class="video-invitation-message">{{ $details['invitation_text'] }}</p>@endif
        <dl>
            <div><dt>{{ $copy['date'] }}</dt><dd>{{ $eventDate->format('d.m.Y') }} · {{ $details['event_time'] }}</dd></div>
            <div><dt>{{ $copy['venue'] }}</dt><dd><strong>{{ $details['venue_name'] }}</strong><span>{{ $details['venue_address'] }}</span></dd></div>
        </dl>
        <a class="video-map-link" href="{{ $twoGisUrl }}" target="_blank" rel="noopener">{{ $copy['map'] }} <span aria-hidden="true">↗</span></a>
    </section>

    <section class="video-rsvp-card">
        <h2>{{ $copy['rsvp'] }}</h2><p>{{ $copy['hint'] }}</p>
        @if($preview)
            <div class="video-rsvp-preview"><span>{{ $copy['name'] }}</span><span>{{ $copy['yes'] }} / {{ $copy['no'] }}</span><button type="button" disabled>{{ $copy['send'] }}</button></div>
            <small>{{ $copy['preview_form'] }}</small>
        @else
            @if($errors->any())<p class="error" role="alert">{{ $errors->first() }}</p>@endif
            <form method="POST" action="{{ route('store.rsvp', $invitation->slug) }}" class="video-rsvp-form" data-submit-once>
                @csrf
                <label>{{ $copy['name'] }}<input name="guest_name" value="{{ old('guest_name') }}" autocomplete="name" maxlength="120" required></label>
                <fieldset><legend>{{ $copy['answer'] }}</legend><div>@foreach(['yes','no'] as $status)<label><input type="radio" name="attendance_status" value="{{ $status }}" @checked(old('attendance_status', 'yes') === $status) required><span>{{ $copy[$status] }}</span></label>@endforeach</div></fieldset>
                <label data-guest-count>{{ $copy['count'] }}<input type="number" name="guest_count" min="1" max="20" value="{{ old('guest_count', 1) }}" required></label>
                <label>{{ $copy['message'] }}<textarea name="message" maxlength="1000">{{ old('message') }}</textarea></label>
                <button type="submit">{{ $copy['send'] }}</button>
            </form>
        @endif
    </section>
    <footer class="video-invite-footer">ZharZhar · {{ $eventDate->format('Y') }}</footer>
</article>

<script>
(() => {
    const stage = document.querySelector('[data-video-invitation]');
    const video = stage?.querySelector('video');
    const final = stage?.querySelector('[data-video-final]');
    if (!video || !final) return;
    const endSeconds = Number(stage.dataset.endSeconds || 6);
    const updateFinal = () => {
        const show = Number.isFinite(video.duration) && video.duration > 0 && video.duration - video.currentTime <= endSeconds;
        final.classList.toggle('is-visible', show);
        final.setAttribute('aria-hidden', show ? 'false' : 'true');
    };
    video.addEventListener('timeupdate', updateFinal);
    video.addEventListener('loadedmetadata', updateFinal);
    video.addEventListener('seeked', updateFinal);
    video.addEventListener('ended', () => {
        final.classList.add('is-visible');
        final.setAttribute('aria-hidden', 'false');
    });
})();
</script>
@endsection
