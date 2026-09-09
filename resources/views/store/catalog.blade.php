@php
    $kk = app()->isLocale('kk');
    $eventLabels = $kk
        ? ['wedding' => 'Үйлену той', 'qyz_uzatu' => 'Қыз ұзату', 'anniversary' => 'Мерейтой', 'birthday' => 'Туған күн']
        : ['wedding' => 'Свадьба', 'qyz_uzatu' => 'Қыз ұзату', 'anniversary' => 'Юбилей', 'birthday' => 'День рождения'];
    $copy = $kk ? [
        'eyebrow' => 'ЕРЕКШЕ ОНЛАЙН ШАҚЫРУЛАР', 'hero' => 'Үлкен күн.', 'hero_em' => 'Әдемі бастама.',
        'lead' => 'Үйлену тойы, қыз ұзату, мерейтой немесе туған күн — мерекеңізді есте қалатын шақырудан бастаңыз.',
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
        'lead' => 'Свадьба, қыз ұзату, юбилей или день рождения — начните праздник с приглашения, которое хочется сохранить.',
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
<div class="hero-art" aria-label="{{ $copy['art_label'] }}"><span class="orbit-label">{{ $copy['moments'] }}</span><div class="paper theme-sage"><span class="paper-kicker">ҮЙЛЕНУ ТОЙЫНА ШАҚЫРУ</span><span class="ornament" aria-hidden="true">❧</span><span class="paper-script">Ақ інжу</span><span class="paper-date">08 · 11 · 2026</span><span class="paper-footer">БІЗДІҢ ҚУАНЫШЫМЫЗҒА ОРТАҚ БОЛЫҢЫЗ</span></div><span class="art-tag">{{ $copy['love'] }}</span></div>
</section>
<div class="occasion-band"><span>Үйлену той</span><b>✦</b><span>Қыз ұзату</span><b>✦</b><span>Мерейтой</span><b>✦</b><span>Туған күн</span><b>✦</b><span>{{ $copy['special'] }}</span></div>
<section class="shell section" id="designs"><div class="section-title"><div><p class="eyebrow">{{ $copy['collection'] }}</p><h2>{{ $copy['find'] }} <em>{{ $copy['find_em'] }}</em></h2></div><p>{{ $copy['design_note'] }}</p></div>
<nav class="filters" aria-label="{{ $kk ? 'Мереке түрі' : 'Тип события' }}"><a class="{{ !$category ? 'active' : '' }}" href="{{ route('store.catalog') }}#designs">{{ $copy['all'] }}</a>@foreach($eventLabels as $key => $label)<a class="{{ $category === $key ? 'active' : '' }}" href="{{ route('store.catalog', ['event' => $key]) }}#designs">{{ $label }}</a>@endforeach</nav>
<div class="catalog-grid" role="region" tabindex="0" aria-label="{{ $kk ? 'Дизайндар қатары. Көлденең жылжытыңыз.' : 'Ряд дизайнов. Прокручивайте горизонтально.' }}">
@forelse($templates as $template)
@php
    $theme = $template->config_json['theme'] ?? 'sage';
    $titleWords = preg_split('/\s+/u', trim($template->name), -1, PREG_SPLIT_NO_EMPTY);
    $titleFirstLine = array_shift($titleWords);
    $titleSecondLine = implode(' ', $titleWords);
@endphp
<article class="design-card design-card-{{ $theme }}">
<a href="{{ route('store.preview', $template) }}" class="design-preview invite-card-{{ $theme }} theme-{{ $theme }}" aria-label="{{ $copy['view'] }}: {{ $template->name }}">
@if($template->preview_image)<img src="{{ $template->preview_image }}" alt="" loading="lazy">@endif
<span class="card-shade" aria-hidden="true"></span>
<span class="card-theme-mark" aria-hidden="true"><i></i><i></i><b></b></span>
<span class="mini-kicker">{{ $template->config_json['content_kk']['event_label'] ?? ($template->event_type === 'qyz_uzatu' ? 'ҚЫЗ ҰЗАТУ' : ($template->event_type === 'anniversary' ? 'МЕРЕЙТОЙ' : ($template->event_type === 'birthday' ? 'ТУҒАН КҮН' : 'ҮЙЛЕНУ ТОЙЫ'))) }}</span>
<span class="card-ring" aria-hidden="true"></span>
<strong class="card-design-name"><span>{{ $titleFirstLine }}</span>@if($titleSecondLine)<span>{{ $titleSecondLine }}</span>@endif</strong>
<span class="mini-date">{{ \Carbon\Carbon::parse($template->config_json['content_kk']['event_date'] ?? '2026-11-08')->format('d / m / Y') }}</span>
<span class="preview-pill">{{ $copy['view'] }} ↗</span></a>
<div class="design-info"><div><p class="eyebrow">{{ $template->event_type ? ($eventLabels[$template->event_type] ?? $template->event_type) : $copy['any_event'] }}</p><h3 class="design-card-title"><span>{{ $titleFirstLine }}</span>@if($titleSecondLine)<span>{{ $titleSecondLine }}</span>@endif</h3></div><strong class="design-price">{{ number_format($template->price, 0, ',', ' ') }} ₸</strong></div><a class="button outline full" href="{{ route('store.checkout', $template) }}">{{ $copy['select_design'] }} <span>→</span></a>
</article>
@empty
<div class="empty-state"><h3>{{ $copy['empty'] }}</h3><p>{{ $copy['empty_text'] }}</p><a href="tel:{{ preg_replace('/[^+0-9]/', '', config('store.kaspi_phone')) }}">{{ $copy['contact'] }}</a></div>
@endforelse
</div></section>
<section class="how-section" id="how"><div class="shell section"><p class="eyebrow">{{ $copy['how_label'] }}</p><h2>{{ $copy['how'] }} <em>{{ $copy['how_em'] }}</em></h2><div class="steps-grid">@foreach($steps as $step)<article><span class="step-number">0{{ $loop->iteration }}</span><h3>{{ $step[0] }}</h3><p>{{ $step[1] }}</p></article>@endforeach</div></div></section>
<section class="shell section faq" id="faq"><div><p class="eyebrow">{{ $copy['faq_label'] }}</p><h2>{{ $copy['faq'] }}<br><em>{{ $copy['faq_em'] }}</em></h2></div><div>@foreach($faqs as $faq)<details><summary>{{ $faq[0] }}</summary><p>{{ $faq[1] }}</p></details>@endforeach</div></section>
@endsection
