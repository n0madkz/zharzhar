@php
    $kk = ($details['language'] ?? 'kk') === 'kk';
    $theme = $details['theme'] ?? 'pearl';
    $eventType = $details['event_type'] ?? 'wedding';
    $eventDate = \Carbon\Carbon::parse($details['event_date']);
    $image = $template->preview_image
        ? (str_starts_with($template->preview_image, '/') ? asset(ltrim($template->preview_image, '/')) : $template->preview_image)
        : null;
    $eventLabels = [
        'wedding' => 'ҮЙЛЕНУ ТОЙЫ',
        'qyz_uzatu' => 'ҚЫЗ ҰЗАТУ',
        'anniversary' => 'МЕРЕЙТОЙ',
        'birthday' => 'ТУҒАН КҮН',
    ];
    $copy = $kk ? [
        'intro' => 'ҚҰРМЕТТІ АҒАЙЫН-ТУЫС, БАУЫРЛАР, ҚҰДА-ЖЕКЖАТ, ДОС-ЖАРАНДАР!',
        'date_title' => 'Той салтанаты',
        'program' => 'Той бағдарламасы',
        'welcome' => 'Қонақтардың жиналуы',
        'ceremony' => $eventType === 'qyz_uzatu' ? 'Қыз ұзату рәсімі' : 'Салтанатты рәсім',
        'celebration' => 'Мерекелік кеш',
        'venue' => 'Мекенжайымыз',
        'map' => 'Картадан көру',
        'countdown' => 'Салтанатқа дейін',
        'days' => 'күн', 'hours' => 'сағат', 'minutes' => 'минут', 'seconds' => 'секунд',
        'hosts' => 'Той иелері',
        'rsvp' => 'Сізді күтеміз!',
        'hint' => 'Тойға қатысуыңызды растауыңызды сұраймыз.',
        'name' => 'Аты-жөніңіз', 'answer' => 'Тойға қатысасыз ба?',
        'yes' => 'Иә, қуана қатысамын', 'no' => 'Өкінішке қарай, қатыса алмаймын', 'maybe' => 'Кейінірек айтамын',
        'count' => 'Қонақ саны', 'message' => 'Ақ тілегіңіз', 'send' => 'Жауап жіберу',
        'preview_form' => 'Дайын шақыруда қонақтар осы жерден жауабын жібереді. Барлық жауап сіздің жеке парақшаңызда жиналады.',
        'back' => 'Шаблондарға қайту', 'choose' => 'Осы дизайнды таңдау',
        'music_play' => 'Музыканы қосу', 'music_pause' => 'Музыканы тоқтату',
        'closing' => 'Қуанышымызға ортақ болыңыз!',
    ] : [
        'intro' => 'ДОРОГИЕ РОДНЫЕ И ДРУЗЬЯ!',
        'date_title' => 'Дата торжества', 'program' => 'Программа вечера',
        'welcome' => 'Сбор гостей', 'ceremony' => 'Торжественная церемония', 'celebration' => 'Праздничный вечер',
        'venue' => 'Место проведения', 'map' => 'Посмотреть на карте', 'countdown' => 'До торжества',
        'days' => 'дней', 'hours' => 'часов', 'minutes' => 'минут', 'seconds' => 'секунд',
        'hosts' => 'Хозяева торжества', 'rsvp' => 'Будем ждать вас!',
        'hint' => 'Пожалуйста, сообщите, сможете ли вы прийти.',
        'name' => 'Ваше имя', 'answer' => 'Вы придёте?', 'yes' => 'С удовольствием приду',
        'no' => 'К сожалению, не смогу', 'maybe' => 'Сообщу позже', 'count' => 'Количество гостей',
        'message' => 'Ваше пожелание', 'send' => 'Отправить ответ',
        'preview_form' => 'В готовом приглашении гости отправят ответ здесь. Все ответы будут собраны на вашей личной странице.',
        'back' => 'Назад к шаблонам', 'choose' => 'Выбрать этот дизайн',
        'music_play' => 'Включить музыку', 'music_pause' => 'Остановить музыку',
        'closing' => 'Разделите с нами этот счастливый день!',
    ];
    $templateCopy = $preview ? ($details['template_copy'] ?? []) : [];
    if ($templateCopy) {
        $copy = array_replace($copy, array_filter([
            'intro' => $templateCopy['intro_title'] ?? null,
            'date_title' => $templateCopy['date_title'] ?? null,
            'program' => $templateCopy['program_title'] ?? null,
            'welcome' => $templateCopy['welcome_text'] ?? null,
            'ceremony' => $templateCopy['ceremony_text'] ?? null,
            'celebration' => $templateCopy['celebration_text'] ?? null,
            'venue' => $templateCopy['venue_title'] ?? null,
            'countdown' => $templateCopy['countdown_title'] ?? null,
            'hosts' => $templateCopy['hosts_title'] ?? null,
            'rsvp' => $templateCopy['rsvp_title'] ?? null,
            'hint' => $templateCopy['rsvp_hint'] ?? null,
            'closing' => $templateCopy['closing_text'] ?? null,
        ], fn ($value) => filled($value)));
    }
    $times = [
        ['17:00', $copy['welcome']],
        ['18:00', $copy['ceremony']],
        ['19:00', $copy['celebration']],
    ];
    $monthNames = $kk
        ? ['қаңтар', 'ақпан', 'наурыз', 'сәуір', 'мамыр', 'маусым', 'шілде', 'тамыз', 'қыркүйек', 'қазан', 'қараша', 'желтоқсан']
        : ['январь', 'февраль', 'март', 'апрель', 'май', 'июнь', 'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь'];
    $weekdayNames = $kk
        ? ['дүйсенбі', 'сейсенбі', 'сәрсенбі', 'бейсенбі', 'жұма', 'сенбі', 'жексенбі']
        : ['понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота', 'воскресенье'];
    $nameParts = preg_split('/\s*(?:&|·|\bи\b|\bжәне\b)\s*/ui', $details['names'], -1, PREG_SPLIT_NO_EMPTY);
    $monogram = $eventType === 'anniversary'
        ? '60'
        : collect($nameParts)->take(2)->map(fn ($name) => mb_strtoupper(mb_substr(trim($name), 0, 1)))->implode(' · ');
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

<article class="invite-mobile invite-theme-{{ $theme }}" data-invite-theme="{{ $theme }}">
    <header class="invite-cover">
        @if($image)<img class="invite-cover-image" src="{{ $image }}" alt="" fetchpriority="high">@endif
        <span class="invite-cover-shade" aria-hidden="true"></span>
        <div class="invite-cover-copy" data-reveal>
            <p class="invite-overline">{{ $templateCopy['event_label'] ?? ($eventLabels[$eventType] ?? $eventLabels['wedding']) }}</p>
            <p class="invite-cover-date">{{ $eventDate->translatedFormat('d · m · Y') }}</p>
            @if($eventType === 'anniversary')<span class="jubilee-number" aria-hidden="true">60</span>@endif
            <h1>{{ $details['names'] }}</h1>
            <span class="invite-monogram" aria-hidden="true">{{ $monogram }}</span>
        </div>
    </header>

    <section class="invite-section invite-intro" data-reveal>
        <div class="orbit-mark" aria-hidden="true"><i></i><i></i><span>♥</span></div>
        <p class="invite-small-title">{{ $copy['intro'] }}</p>
        <p class="invite-message">{{ $details['invitation_text'] ?? '' }}</p>
    </section>

    <section class="invite-section date-section" data-reveal>
        <p class="invite-script">{{ $copy['date_title'] }}</p>
        <div class="date-orb">
            <span>{{ $monthNames[$eventDate->month - 1] }}</span>
            <strong>{{ $eventDate->format('d') }}</strong>
            <span>{{ $weekdayNames[$eventDate->dayOfWeekIso - 1] }}</span>
        </div>
        <p class="invite-time">{{ $details['event_time'] }}</p>
    </section>

    <section class="invite-section program-section" data-reveal>
        <p class="invite-overline">{{ $copy['program'] }}</p>
        <div class="program-list">
            @foreach($times as [$time, $label])
                <div class="program-item"><time>{{ $time }}</time><span class="program-dot"></span><p>{{ $label }}</p></div>
            @endforeach
        </div>
    </section>

    <section class="invite-section venue-section" data-reveal>
        <span class="venue-rings" aria-hidden="true"></span>
        <p class="invite-overline">{{ $copy['venue'] }}</p>
        <h2>{{ $details['venue_name'] }}</h2>
        <p>{{ $details['venue_address'] }}</p>
        <a class="round-map" href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($details['venue_name'].' '.$details['venue_address']) }}" target="_blank" rel="noopener">{{ $copy['map'] }} <span aria-hidden="true">↗</span></a>
    </section>

    <section class="invite-section countdown-section" data-countdown="{{ $eventDate->format('Y-m-d').'T'.($details['event_time'] ?? '18:00') }}" data-reveal>
        <p class="invite-script">{{ $copy['countdown'] }}</p>
        <div class="countdown-grid">
            @foreach([['days', $copy['days']], ['hours', $copy['hours']], ['minutes', $copy['minutes']], ['seconds', $copy['seconds']]] as [$part, $label])
                <div><strong data-countdown-part="{{ $part }}">00</strong><span>{{ $label }}</span></div>
            @endforeach
        </div>
    </section>

    <section class="invite-section hosts-section" data-reveal>
        <span class="joined-rings" aria-hidden="true"><i></i><i></i></span>
        <p class="invite-overline">{{ $copy['hosts'] }}</p>
        <h2>{{ $details['hosts'] }}</h2>
    </section>

    <section class="invite-section rsvp-section" data-reveal>
        <p class="invite-script">{{ $copy['rsvp'] }}</p>
        <p>{{ $copy['hint'] }}</p>
        @if($preview)
            <div class="rsvp-preview">
                <span>{{ $copy['name'] }}</span><span>{{ $copy['answer'] }}</span><button type="button" disabled>{{ $copy['send'] }}</button>
            </div>
            <p class="rsvp-note">{{ $copy['preview_form'] }}</p>
        @else
            @if($errors->any())<p class="error" role="alert">{{ $kk ? 'Өрістерді тексеріңіз.' : 'Проверьте поля формы.' }}</p>@endif
            <form method="POST" action="{{ route('store.rsvp', $invitation->slug) }}" class="invite-form" data-submit-once>
                @csrf
                <label>{{ $copy['name'] }}<input name="guest_name" value="{{ old('guest_name') }}" autocomplete="name" maxlength="120" required></label>
                <label>{{ $copy['answer'] }}<select name="attendance_status" required>@foreach(['yes','no','maybe'] as $status)<option value="{{ $status }}" @selected(old('attendance_status') === $status)>{{ $copy[$status] }}</option>@endforeach</select></label>
                <label>{{ $copy['count'] }}<input type="number" name="guest_count" min="1" max="20" value="{{ old('guest_count', 1) }}" required></label>
                <label>{{ $copy['message'] }}<textarea name="message" maxlength="1000">{{ old('message') }}</textarea></label>
                <button type="submit">{{ $copy['send'] }}</button>
            </form>
        @endif
    </section>

    <footer class="invite-finale" data-reveal>
        <div class="finale-circle"><span aria-hidden="true">♥</span><p>{{ $copy['closing'] }}</p></div>
        <small>ZharZhar · {{ $eventDate->format('Y') }}</small>
    </footer>

    @if(!empty($details['music_url']))
        <audio id="invite-audio" loop preload="none" src="{{ $details['music_url'] }}"></audio>
    @endif
    <button class="music-orb" type="button" data-invite-music @if(empty($details['music_url'])) data-preview-tone @endif aria-label="{{ $copy['music_play'] }}" data-play-label="{{ $copy['music_play'] }}" data-pause-label="{{ $copy['music_pause'] }}">
        <span class="music-note" aria-hidden="true">♪</span><span class="music-bars" aria-hidden="true"><i></i><i></i><i></i></span>
    </button>
</article>
@endsection
