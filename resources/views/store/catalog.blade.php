@php
    $kk = app()->isLocale('kk');
    $eventLabels = $kk
        ? ['wedding' => 'Үйлену той', 'qyz_uzatu' => 'Қыз ұзату', 'anniversary' => 'Мерейтой', 'birthday' => 'Туған күн', 'besik_toi' => 'Бесік той']
        : ['wedding' => 'Свадьба', 'qyz_uzatu' => 'Қыз ұзату', 'anniversary' => 'Юбилей', 'birthday' => 'День рождения', 'besik_toi' => 'Бесік той'];
    $copy = $kk ? [
        'eyebrow' => 'ЕРЕКШЕ ОНЛАЙН ШАҚЫРУЛАР', 'hero' => 'Үлкен күн.', 'hero_em' => 'Әдемі бастама.',
        'lead' => 'Үйлену тойы, қыз ұзату, мерейтой, туған күн немесе бесік той — мерекеңізді есте қалатын шақырудан бастаңыз.',
        'choose' => 'Шақыруды таңдау', 'from' => 'бастап', 'payment' => 'Kaspi Pay арқылы төлем',
        'features' => 'Сіздің есімдеріңіз · Сүйікті әуен · Қонақтардың жауабы',
        'art_label' => 'Үйлену тойына арналған шақыру үлгісі', 'moments' => 'ЖАҚЫН ЖАНДАР. ЕРЕКШЕ СӘТТЕР.',
        'love' => 'Жақындарыңызға ізгі ниетпен', 'special' => 'Сіздің ерекше күніңіз',
        'collection' => 'ШАҚЫРУЛАР ЖИНАҒЫ', 'find' => 'Өз дизайныңызды', 'find_em' => 'таңдаңыз',
        'design_note' => 'Әр дизайн — музыкасы және қонақтардың жауаптары бар дайын сайт.',
        'all' => 'Барлық мерекелер', 'view' => 'Үлгіні көру', 'select_design' => 'Дизайнды таңдау',
        'any_event' => 'Кез келген мерекеге', 'empty' => 'Жинақ дайындалып жатыр',
        'empty_text' => 'Жақында мерекеңізге арналған жаңа дизайндар пайда болады.', 'contact' => 'Бізбен байланысу',
        'how_label' => 'ОЙДАН ДАЙЫН ШАҚЫРУҒА ДЕЙІН', 'how' => 'Бар болғаны', 'how_em' => 'төрт қадам',
        'faq_label' => 'БІЛУ КЕРЕК БАРЛЫҚ АҚПАРАТ', 'faq' => 'Шағын мәлімет.', 'faq_em' => 'Үлкен мереке.',
    ] : [
        'eyebrow' => 'ОНЛАЙН-ПРИГЛАШЕНИЯ С ХАРАКТЕРОМ', 'hero' => 'Большой день.', 'hero_em' => 'Красивое начало.',
        'lead' => 'Свадьба, қыз ұзату, юбилей, день рождения или бесік той — начните праздник с приглашения, которое хочется сохранить.',
        'choose' => 'Выбрать приглашение', 'from' => 'от', 'payment' => 'Оплата по Kaspi Pay',
        'features' => 'Ваши имена · Любимая музыка · Ответы гостей',
        'art_label' => 'Пример свадебного приглашения', 'moments' => 'БЛИЗКИЕ ЛЮДИ. ОСОБЕННЫЕ МОМЕНТЫ.',
        'love' => 'С любовью, для ваших близких', 'special' => 'Ваш особенный день',
        'collection' => 'КОЛЛЕКЦИЯ ПРИГЛАШЕНИЙ', 'find' => 'Найдите', 'find_em' => 'свой дизайн',
        'design_note' => 'Каждый дизайн — готовый сайт с музыкой и ответами гостей.',
        'all' => 'Все события', 'view' => 'Посмотреть пример', 'select_design' => 'Выбрать дизайн',
        'any_event' => 'Для любого события', 'empty' => 'Коллекция готовится',
        'empty_text' => 'Скоро здесь появятся новые дизайны для вашего праздника.', 'contact' => 'Связаться с нами',
        'how_label' => 'ОТ ИДЕИ ДО ПРИГЛАШЕНИЯ', 'how' => 'Всего', 'how_em' => 'четыре шага',
        'faq_label' => 'ВСЁ, ЧТО НУЖНО ЗНАТЬ', 'faq' => 'Маленькие детали.', 'faq_em' => 'Большой праздник.',
    ];
    $steps = $kk ? [
        ['Дизайнды таңдаңыз', 'Мерекеңіздің көңіл күйіне сай дизайнды табыңыз. Әр карточкада нақты бағасы көрсетілген.'],
        ['Мәліметті толтырыңыз', 'Есімдерді, күнді, мейрамхананы, той иелерін көрсетіп, музыканы таңдаңыз.'],
        ['Kaspi Pay арқылы төлеңіз', 'Жақында Kaspi Pay төлемін қосамыз. Әзірге төлем тәсілі тапсырыс рәсімделгеннен кейін көрсетіледі.'],
        ['Жақындарыңызды шақырыңыз', 'Төлем расталған соң шақыру сілтемесі мен қонақ жауаптарының жеке сілтемесін алыңыз.'],
    ] : [
        ['Выберите дизайн', 'Найдите настроение вашего праздника. Цена указана на каждой карточке.'],
        ['Расскажите о событии', 'Укажите имена, дату, ресторан, той иелері и выберите музыку.'],
        ['Оплата по Kaspi Pay', 'Скоро подключим Kaspi Pay. Пока способ оплаты показывается после оформления приглашения.'],
        ['Пригласите близких', 'После подтверждения получите приглашение и личную ссылку на ответы гостей.'],
    ];
    $faqs = $kk ? [
        ['Бағаға не кіреді?', 'Таңдалған дизайндағы жеке шақыру, музыка, мереке туралы мәлімет және қонақтардың жауаптары жиналатын жеке парақша.'],
        ['Қалай төлеуге болады?', 'Жақында Kaspi Pay қосылады. Әзірге уақытша төлем деректері шақыруды рәсімдегеннен кейін жеке тапсырыс парақшасында көрсетіледі.'],
        ['Төлемнен кейін сілтемелерді қайдан аламын?', 'Тапсырыстың жеке сілтемесін сақтаңыз. Төлем расталғаннан кейін шақыру мен жауаптар парақшасының сілтемелері сонда пайда болады.'],
        ['Промокодты қалай қолданамын?', 'Тапсырыс рәсімдегенде кодты енгізіп, «Қолдану» батырмасын басыңыз. Жеңілдік бірден есептеледі.'],
    ] : [
        ['Что входит в стоимость?', 'Персональное приглашение, музыка, информация о празднике и отдельная страница с ответами гостей.'],
        ['Как оплатить?', 'Скоро подключим Kaspi Pay. Пока временные реквизиты появляются на личной странице после оформления приглашения.'],
        ['Где получить ссылки после оплаты?', 'Сохраните личную ссылку на заказ. После подтверждения там появятся приглашение и страница ответов.'],
        ['Как применить промокод?', 'Введите код при оформлении и нажмите «Применить». Скидка рассчитается сразу.'],
    ];
@endphp

@extends('layouts.store')
@section('content')
<section class="hero shell">
<div><p class="eyebrow">{{ $copy['eyebrow'] }}</p><h1>{{ $copy['hero'] }}<br><em>{{ $copy['hero_em'] }}</em></h1><p class="lead">{{ $copy['lead'] }}</p><div class="actions"><a class="button primary" href="#designs">{{ $copy['choose'] }} <span>↗</span></a><span class="price-note">{{ $copy['from'] }} <strong>7 990 ₸</strong><small>{{ $copy['payment'] }}</small></span></div><p class="hero-footnote">{{ $copy['features'] }}</p></div>
<div class="hero-art" aria-label="{{ $copy['art_label'] }}"><span class="orbit-label">{{ $copy['moments'] }}</span><div class="paper theme-sage"><span class="paper-kicker">ҮЙЛЕНУ ТОЙЫНА ШАҚЫРУ</span><span class="hero-ornament" aria-hidden="true"><i></i><i></i><b></b></span><span class="paper-script">Ақ інжу</span><span class="paper-date">08 · 11 · 2026</span><span class="paper-footer">БІЗДІҢ ҚУАНЫШЫМЫЗҒА ОРТАҚ БОЛЫҢЫЗ</span></div><span class="art-tag">{{ $copy['love'] }}</span></div>
</section>
<div class="occasion-band"><span>Үйлену той</span><b>✦</b><span>Қыз ұзату</span><b>✦</b><span>Мерейтой</span><b>✦</b><span>Туған күн</span><b>✦</b><span>Бесік той</span><b>✦</b><span>{{ $copy['special'] }}</span></div>
<section class="shell section" id="designs"><div class="section-title"><div><p class="eyebrow">{{ $copy['collection'] }}</p><h2>{{ $copy['find'] }} <em>{{ $copy['find_em'] }}</em></h2></div><p>{{ $copy['design_note'] }}</p></div>
<nav class="filters" data-catalog-filters aria-label="{{ $kk ? 'Мереке түрі' : 'Тип события' }}"><a class="{{ !$category ? 'active' : '' }}" data-event-filter="" href="{{ route('store.catalog') }}#designs" @if(!$category) aria-current="true" @endif>{{ $copy['all'] }}</a>@foreach($eventLabels as $key => $label)<a class="{{ $category === $key ? 'active' : '' }}" data-event-filter="{{ $key }}" href="{{ route('store.catalog', ['event' => $key]) }}#designs" @if($category === $key) aria-current="true" @endif>{{ $label }}</a>@endforeach</nav>
<div class="catalog-grid" data-catalog-grid role="region" aria-label="{{ $kk ? 'Дизайндар каталогы' : 'Каталог дизайнов' }}" aria-live="polite">
@forelse($templates as $template)
@php
    $theme = $template->config_json['theme'] ?? 'sage';
    $titleWords = preg_split('/\s+/u', trim($template->name), -1, PREG_SPLIT_NO_EMPTY);
    $titleFirstLine = array_shift($titleWords);
    $titleSecondLine = implode(' ', $titleWords);
    $designSignature = config('invitation_styles.'.$template->slug.'.'.($kk ? 'kk' : 'ru'));
    $offer = config('invitation_offers.'.$template->slug.'.'.($kk ? 'kk' : 'ru'), []);
@endphp
<article class="design-card design-card-{{ $theme }} design-template-{{ $template->slug }}" data-event-type="{{ $template->event_type }}" @if($category && $template->event_type && $template->event_type !== $category) hidden @endif>
<a href="{{ route('store.preview', $template) }}" class="design-preview invite-card-{{ $theme }} theme-{{ $theme }} event-card-{{ $template->event_type }}" aria-label="{{ $copy['view'] }}: {{ $template->name }}">
@if($template->preview_image)<img src="{{ $template->preview_image }}{{ $template->slug === 'altyn-nomad' ? '?v=2' : '' }}" alt="" loading="lazy">@endif
@if(data_get($template->config_json, 'format') === 'video')<span class="video-template-badge">{{ $kk ? 'БЕЙНЕ' : 'ВИДЕО' }}</span>@endif
<span class="card-shade" aria-hidden="true"></span>
<span class="card-theme-mark" aria-hidden="true"><i></i><i></i><b></b></span>
<span class="mini-kicker">{{ $template->config_json['content_kk']['event_label'] ?? ($template->event_type === 'qyz_uzatu' ? 'ҚЫЗ ҰЗАТУ' : ($template->event_type === 'anniversary' ? 'МЕРЕЙТОЙ' : ($template->event_type === 'birthday' ? 'ТУҒАН КҮН' : 'ҮЙЛЕНУ ТОЙЫ'))) }}</span>
<span class="card-ring" aria-hidden="true"></span>
<strong class="card-design-name"><span>{{ $titleFirstLine }}</span>@if($titleSecondLine)<span>{{ $titleSecondLine }}</span>@endif</strong>
<span class="mini-date">{{ \Carbon\Carbon::parse($template->config_json['content_kk']['event_date'] ?? '2026-11-08')->format('d / m / Y') }}</span>
<span class="preview-pill">{{ $copy['view'] }} ↗</span></a>
<div class="design-info"><div><p class="eyebrow">{{ $template->event_type ? ($eventLabels[$template->event_type] ?? $template->event_type) : $copy['any_event'] }}</p><h3 class="design-card-title"><span>{{ $titleFirstLine }}</span>@if($titleSecondLine)<span>{{ $titleSecondLine }}</span>@endif</h3></div><strong class="design-price">{{ number_format($template->price, 0, ',', ' ') }} ₸</strong></div>
@if($designSignature)<p class="design-signature">{{ $designSignature }}</p>@endif
@if($offer)
<div class="design-offer">
    <p class="design-offer-label">{{ $kk ? 'ОСЫ БАҒАҒА КІРЕДІ' : 'ЧТО ВХОДИТ В ЦЕНУ' }}</p>
    <ul>@foreach($offer as $feature)<li>{{ $feature }}</li>@endforeach</ul>
</div>
@endif
<a class="button outline full" href="{{ route('store.checkout', $template) }}">{{ $copy['select_design'] }} <span>→</span></a>
</article>
@empty
<div class="empty-state"><h3>{{ $copy['empty'] }}</h3><p>{{ $copy['empty_text'] }}</p><a href="tel:{{ preg_replace('/[^+0-9]/', '', config('store.kaspi_phone')) }}">{{ $copy['contact'] }}</a></div>
@endforelse
<div class="empty-state" data-catalog-empty hidden><h3>{{ $copy['empty'] }}</h3><p>{{ $copy['empty_text'] }}</p></div>
</div>
<nav class="catalog-pagination" data-catalog-pagination hidden aria-label="{{ $kk ? 'Дизайндар беттері' : 'Страницы дизайнов' }}">
<button type="button" data-page-action="previous"><span aria-hidden="true">←</span> {{ $kk ? 'Артқа' : 'Назад' }}</button>
<span class="catalog-page-numbers" data-page-numbers></span>
<button type="button" data-page-action="next">{{ $kk ? 'Келесі' : 'Далее' }} <span aria-hidden="true">→</span></button>
</nav>
</section>
<section class="how-section" id="how"><div class="shell section"><p class="eyebrow">{{ $copy['how_label'] }}</p><h2>{{ $copy['how'] }} <em>{{ $copy['how_em'] }}</em></h2><div class="steps-grid">@foreach($steps as $step)<article><span class="step-number">0{{ $loop->iteration }}</span><h3>{{ $step[0] }}</h3><p>{{ $step[1] }}</p></article>@endforeach</div></div></section>
<section class="shell section faq" id="faq"><div><p class="eyebrow">{{ $copy['faq_label'] }}</p><h2>{{ $copy['faq'] }}<br><em>{{ $copy['faq_em'] }}</em></h2></div><div>@foreach($faqs as $faq)<details><summary>{{ $faq[0] }}</summary><p>{{ $faq[1] }}</p></details>@endforeach</div></section>
<nav class="mobile-store-nav" data-mobile-store-nav aria-label="{{ $kk ? 'Мобильді навигация' : 'Мобильная навигация' }}">
    <a href="#main" data-mobile-tab="home" aria-current="page"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1z"/></svg><span>{{ $kk ? 'Басты бет' : 'Главная' }}</span></a>
    <a href="#designs" data-mobile-tab="designs"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="8" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/><rect x="13" y="13" width="8" height="8" rx="1.5"/></svg><span>{{ $kk ? 'Дизайндар' : 'Дизайны' }}</span></a>
    <button type="button" data-mobile-tab="categories" data-category-open aria-haspopup="dialog" aria-controls="mobile-categories"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/><circle cx="7" cy="6" r="1.5"/><circle cx="16" cy="12" r="1.5"/><circle cx="10" cy="18" r="1.5"/></svg><span>{{ $kk ? 'Санаттар' : 'Категории' }}</span></button>
    <a href="#faq" data-mobile-tab="faq"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.5 2.5 0 0 1 5 0c0 2-2.5 2-2.5 4M12 17h.01"/></svg><span>{{ $kk ? 'Сұрақтар' : 'Вопросы' }}</span></a>
</nav>
<dialog class="mobile-category-sheet" id="mobile-categories" data-category-sheet aria-labelledby="mobile-category-title">
    <div class="mobile-category-handle" aria-hidden="true"></div>
    <div class="mobile-category-heading"><div><p class="eyebrow">{{ $kk ? 'ШАҚЫРУ ТҮРЛЕРІ' : 'ТИПЫ ПРИГЛАШЕНИЙ' }}</p><h2 id="mobile-category-title">{{ $kk ? 'Мерекені таңдаңыз' : 'Выберите событие' }}</h2></div><button type="button" class="mobile-category-close" data-category-close aria-label="{{ $kk ? 'Жабу' : 'Закрыть' }}">×</button></div>
    <div class="mobile-category-options">
        <a href="{{ route('store.catalog') }}#designs" data-mobile-event-filter="" @if(!$category) aria-current="true" @endif><span>{{ $copy['all'] }}</span><b>{{ $templates->count() }}</b></a>
        @foreach($eventLabels as $key => $label)
        <a href="{{ route('store.catalog', ['event' => $key]) }}#designs" data-mobile-event-filter="{{ $key }}" @if($category === $key) aria-current="true" @endif><span>{{ $label }}</span><b>{{ $templates->filter(fn ($template) => !$template->event_type || $template->event_type === $key)->count() }}</b></a>
        @endforeach
    </div>
</dialog>
@endsection
