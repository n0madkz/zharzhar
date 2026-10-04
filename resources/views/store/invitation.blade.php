@php
    $kk = ($details['language'] ?? 'kk') === 'kk';
    $theme = $details['theme'] ?? 'pearl';
    $isPhotoStory = $template->slug === 'mahabbat-hikayasy';
    $isBesikToi = $template->slug === 'besik-toi';
    $eventType = $details['event_type'] ?? 'wedding';
    $eventDate = \Carbon\Carbon::parse($details['event_date']);
    $image = $template->preview_image
        ? (str_starts_with($template->preview_image, '/') ? asset(ltrim($template->preview_image, '/')) : $template->preview_image)
        : null;
    $supportsPhotos = (bool) data_get($template->config_json, 'supports_photos', false);
    $photoGallery = $preview
        ? collect(data_get($template->config_json, 'sample_photos', []))->map(
            fn ($path) => str_starts_with($path, '/') ? asset(ltrim($path, '/')) : $path
        )->all()
        : collect($details['photo_paths'] ?? [])->map(
            fn ($path) => route('store.photo', ['filename' => basename($path)])
        )->all();
    if ($supportsPhotos && $photoGallery) {
        $image = $photoGallery[0];
    }
    $storyCountdownImage = $isPhotoStory ? ($photoGallery[1] ?? $photoGallery[0] ?? $image) : null;
    $storyFinalImage = $isPhotoStory ? ($photoGallery[2] ?? $photoGallery[0] ?? $image) : null;
    $eventLabels = [
        'wedding' => 'ҮЙЛЕНУ ТОЙЫ',
        'qyz_uzatu' => 'ҚЫЗ ҰЗАТУ',
        'anniversary' => 'МЕРЕЙТОЙ',
        'birthday' => 'ТУҒАН КҮН',
        'besik_toi' => 'БЕСІК ТОЙ',
    ];
    $copy = $kk ? [
        'intro' => 'ҚҰРМЕТТІ АҒАЙЫН-ТУЫС, БАУЫРЛАР, ҚҰДА-ЖЕКЖАТ, ДОС-ЖАРАНДАР!',
        'date_title' => 'Той салтанаты',
        'venue' => 'Мекенжайымыз',
        'address_label' => 'Мекенжай',
        'map' => '2GIS-те ашу',
        'countdown' => 'Салтанатқа дейін',
        'days' => 'күн', 'hours' => 'сағат', 'minutes' => 'минут', 'seconds' => 'секунд',
        'hosts' => 'Той иелері',
        'rsvp' => 'Сізді күтеміз!',
        'hint' => 'Тойға қатысуыңызды растауыңызды сұраймыз.',
        'name' => 'Аты-жөніңіз', 'answer' => 'Тойға қатысасыз ба?',
        'yes' => 'Иә', 'no' => 'Жоқ', 'maybe' => 'Кейінірек айтамын',
        'count' => 'Қонақ саны', 'message' => 'Ақ тілегіңіз', 'send' => 'Жауап жіберу',
        'preview_form' => 'Дайын шақыруда қонақтар осы жерден жауабын жібереді. Барлық жауап сіздің жеке парақшаңызда жиналады.',
        'back' => 'Шаблондарға қайту', 'choose' => 'Осы дизайнды таңдау',
        'music_play' => 'Музыканы қосу', 'music_pause' => 'Музыканы тоқтату',
        'closing' => 'Қуанышымызға ортақ болыңыз!',
        'story_chapter' => 'Біздің хикаямыз', 'story_moment' => 'Есте қалар сәттер',
    ] : [
        'intro' => 'ДОРОГИЕ РОДНЫЕ И ДРУЗЬЯ!',
        'date_title' => 'Дата торжества',
        'venue' => 'Место проведения', 'address_label' => 'Адрес', 'map' => 'Открыть в 2GIS', 'countdown' => 'До торжества',
        'days' => 'дней', 'hours' => 'часов', 'minutes' => 'минут', 'seconds' => 'секунд',
        'hosts' => 'Хозяева торжества', 'rsvp' => 'Будем ждать вас!',
        'hint' => 'Пожалуйста, сообщите, сможете ли вы прийти.',
        'name' => 'Ваше имя', 'answer' => 'Вы придёте?', 'yes' => 'Да',
        'no' => 'Нет', 'maybe' => 'Сообщу позже', 'count' => 'Количество гостей',
        'message' => 'Ваше пожелание', 'send' => 'Отправить ответ',
        'preview_form' => 'В готовом приглашении гости отправят ответ здесь. Все ответы будут собраны на вашей личной странице.',
        'back' => 'Назад к шаблонам', 'choose' => 'Выбрать этот дизайн',
        'music_play' => 'Включить музыку', 'music_pause' => 'Остановить музыку',
        'closing' => 'Разделите с нами этот счастливый день!',
        'story_chapter' => 'Наша история', 'story_moment' => 'Моменты, которые останутся с нами',
    ];
    $templateCopy = $kk ? array_replace($template->config_json['content_kk'] ?? [], $details['template_copy'] ?? []) : [];
    $customCopy = array_replace($templateCopy, $details['copy'] ?? []);
    $chapters = config('invitation_layouts.'.$template->slug, [['kind' => 'letter', 'sections' => ['intro', 'photos']], ['kind' => 'calendar', 'sections' => ['date', 'venue']], ['kind' => 'ribbon', 'sections' => ['countdown', 'hosts']], ['kind' => 'reply', 'sections' => ['rsvp']]]);
    if ($customCopy) {
        $copy = array_replace($copy, array_filter([
            'event_label' => $customCopy['event_label'] ?? null,
            'intro' => $customCopy['intro_title'] ?? ($customCopy['intro'] ?? null),
            'date_title' => $customCopy['date_title'] ?? null,
            'venue' => $customCopy['venue_title'] ?? ($customCopy['venue'] ?? null),
            'map' => $customCopy['map'] ?? null,
            'countdown' => $customCopy['countdown_title'] ?? ($customCopy['countdown'] ?? null),
            'days' => $customCopy['days'] ?? null,
            'hours' => $customCopy['hours'] ?? null,
            'minutes' => $customCopy['minutes'] ?? null,
            'seconds' => $customCopy['seconds'] ?? null,
            'hosts' => $customCopy['hosts_title'] ?? ($customCopy['hosts'] ?? null),
            'rsvp' => $customCopy['rsvp_title'] ?? ($customCopy['rsvp'] ?? null),
            'hint' => $customCopy['rsvp_hint'] ?? ($customCopy['hint'] ?? null),
            'name' => $customCopy['name'] ?? null,
            'answer' => $customCopy['answer'] ?? null,
            'yes' => $customCopy['yes'] ?? null,
            'no' => $customCopy['no'] ?? null,
            'maybe' => $customCopy['maybe'] ?? null,
            'count' => $customCopy['count'] ?? null,
            'message' => $customCopy['message'] ?? null,
            'send' => $customCopy['send'] ?? null,
            'closing' => $customCopy['closing_text'] ?? ($customCopy['closing'] ?? null),
        ], fn ($value) => filled($value)));
    }
    if ($kk && preg_match('/ата.?анасы/ui', $copy['hosts'])) {
        $copy['hosts'] = 'Той иелері';
    }
    $copy['map'] = $kk ? '2GIS-те ашу' : 'Открыть в 2GIS';
    $copy['event_label'] ??= $templateCopy['event_label'] ?? ($eventLabels[$eventType] ?? $eventLabels['wedding']);
    $invitationText = ($details['invitation_text'] ?? null) ?: ($templateCopy['invitation_text'] ?? ($kk ? 'Сіздерді қуанышымыздың қадірлі қонағы болуға шақырамыз.' : 'Приглашаем вас разделить с нами этот счастливый день.'));
    $galleryTitle = $templateCopy['gallery_title'] ?? ($kk ? 'Біздің ерекше сәттеріміз' : 'Наши особенные моменты');
    $monthNames = $kk
        ? ['қаңтар', 'ақпан', 'наурыз', 'сәуір', 'мамыр', 'маусым', 'шілде', 'тамыз', 'қыркүйек', 'қазан', 'қараша', 'желтоқсан']
        : ['январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь'];
    $weekdayNames = $kk
        ? ['дүйсенбі', 'сейсенбі', 'сәрсенбі', 'бейсенбі', 'жұма', 'сенбі', 'жексенбі']
        : ['понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота', 'воскресенье'];
    $nameParts = preg_split('/\s*(?:&|·|\bи\b|\bжәне\b)\s*/ui', $details['names'], -1, PREG_SPLIT_NO_EMPTY);
    $jubileeAge = $details['jubilee_age'] ?? ($preview ? ($customCopy['jubilee_age'] ?? $customCopy['jubilee_number'] ?? null) : null);
    $monogram = collect($nameParts)->take(2)->map(fn ($name) => mb_strtoupper(mb_substr(trim($name), 0, 1)))->implode(' · ');
    $displayNameLines = $nameParts;
    if ($preview) {
        $previewNameWords = preg_split('/\s+/u', trim($details['names']), -1, PREG_SPLIT_NO_EMPTY);
        $displayNameLines = count($previewNameWords) > 1
            ? [array_shift($previewNameWords), implode(' ', $previewNameWords)]
            : $previewNameWords;
    }
    $providedTwoGisUrl = $details['two_gis_url'] ?? null;
    $twoGisUrl = is_string($providedTwoGisUrl) && str_starts_with($providedTwoGisUrl, 'https://')
        ? $providedTwoGisUrl
        : 'https://2gis.kz/search/'.rawurlencode(trim($details['venue_name'].' '.$details['venue_address']));
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

<article class="invite-mobile invite-theme-{{ $theme }} invite-template-{{ $template->slug }} invite-event-{{ $eventType }}" data-invite-theme="{{ $theme }}" data-template-slug="{{ $template->slug }}">
    <header class="invite-cover">
        @if($image)<img class="invite-cover-image" src="{{ $image }}" alt="" fetchpriority="high">@endif
        <span class="invite-cover-shade" aria-hidden="true"></span>
        @if($template->event_type === 'wedding' && $template->price >= 10990)
            <span class="invite-butterfly invite-butterfly--one" aria-hidden="true"></span>
            <span class="invite-butterfly invite-butterfly--two" aria-hidden="true"></span>
        @endif
        <span class="invite-kazakh-mark" aria-hidden="true"></span>
        @if($isPhotoStory)<span class="story-cover-frame" aria-hidden="true"></span>@endif
        <div class="invite-cover-copy" data-reveal>
            <h1 class="invite-name{{ $preview ? ' invite-name-preview' : '' }}">@foreach($displayNameLines as $nameLine)<span>{{ $nameLine }}</span>@endforeach</h1>
            @if($eventType === 'anniversary' && $jubileeAge)<span class="jubilee-number">{{ $jubileeAge }} <small>жас</small></span>@else<span class="invite-monogram" aria-hidden="true">{{ $monogram }}</span>@endif
            @if($template->event_type === 'wedding')<span class="invite-wedding-rings" aria-hidden="true"><i></i><i></i></span>@endif
            @if($isPhotoStory)<span class="story-chapter-label">{{ $copy['story_chapter'] }}</span>@endif
            <p class="invite-overline">{{ $copy['event_label'] }}</p>
            <p class="invite-cover-date">{{ $eventDate->translatedFormat('d · m · Y') }}</p>
        </div>
    </header>

    @if($isBesikToi)<a class="besik-wish-link" href="#besik-rsvp">Бөпеге ақ тілек қалдыру <span aria-hidden="true">↓</span></a>@endif

    @foreach($chapters as $chapter)
        <div class="invite-chapter invite-chapter--{{ $chapter['kind'] }}" data-chapter="{{ $loop->iteration }}">
            <span class="invite-chapter-index" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
            @foreach($chapter['sections'] as $section)
                @include('store.invitation-sections.'.$section)
            @endforeach
        </div>
    @endforeach

    <footer class="invite-finale" data-reveal>
        @if($storyFinalImage)<img class="story-section-photo" src="{{ $storyFinalImage }}" alt="" loading="lazy">@endif
        <div class="finale-circle"><p>{{ $copy['closing'] }}</p></div>
        <small>ZharZhar · {{ $eventDate->format('Y') }}</small>
    </footer>

</article>

@if(!empty($details['music_url']))
    <audio id="invite-audio" loop preload="none" src="{{ \App\Models\Music::playbackUrlFor($details['music_url']) }}"></audio>
@endif
<button class="music-orb music-theme-{{ $theme }} music-template-{{ $template->slug }}" type="button" data-invite-music @if(empty($details['music_url'])) data-preview-tone @endif aria-label="{{ $copy['music_play'] }}" aria-pressed="false" data-play-label="{{ $copy['music_play'] }}" data-pause-label="{{ $copy['music_pause'] }}">
    <span class="music-kazakh-ornament" aria-hidden="true"></span>
    <span class="music-control-icon" aria-hidden="true">
        <span class="music-play-icon"></span>
        <span class="music-pause-icon"><i></i><i></i></span>
    </span>
</button>
@endsection
