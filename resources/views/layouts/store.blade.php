@php
    $uiLocale = $pageLanguage ?? app()->getLocale();
    $kk = $uiLocale === 'kk';
    $layoutCopy = $kk ? [
        'title' => 'ZharZhar — ерекше күніңізге арналған шақырулар',
        'description' => 'Онлайн шақыру дизайнын таңдап, мерекеңіз туралы ақпаратты толтырыңыз. Бағасы 7 990 ₸ бастап.',
        'skip' => 'Мазмұнға өту', 'tagline' => 'ЕРЕКШЕ КҮН ОСЫНДА БАСТАЛАДЫ',
        'designs' => 'Дизайндар', 'how' => 'Қалай жұмыс істейді', 'faq' => 'Сұрақтар',
        'contact' => 'Байланысу', 'footer' => 'Жүректе қалатын мерекелер үшін.',
    ] : [
        'title' => 'ZharZhar — приглашения на ваши особенные события',
        'description' => 'Выберите дизайн онлайн-приглашения и расскажите о своём празднике. От 7 990 ₸.',
        'skip' => 'К содержимому', 'tagline' => 'СОБЫТИЯ НАЧИНАЮТСЯ ЗДЕСЬ',
        'designs' => 'Дизайны', 'how' => 'Как это работает', 'faq' => 'Вопросы',
        'contact' => 'Связаться', 'footer' => 'Для событий, которые останутся в сердце.',
    ];
@endphp
<!doctype html>
<html lang="{{ $uiLocale }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}"><meta name="referrer" content="no-referrer">
<title>{{ $title ?? $layoutCopy['title'] }}</title>
<meta name="description" content="{{ $layoutCopy['description'] }}">
<link rel="stylesheet" href="{{ asset('store.css') }}">
@if($invitationMode ?? false)<link rel="stylesheet" href="{{ asset('invitation.css') }}">@endif
<link rel="stylesheet" href="{{ asset('fonts.css') }}">
<script src="{{ asset('store.js') }}" defer></script>
@if($invitationMode ?? false)<script src="{{ asset('invitation.js') }}" defer></script>@endif
</head>
<body class="{{ ($invitationMode ?? false) ? 'invitation-shell' : '' }}">
<a class="skip-link" href="#main">{{ $layoutCopy['skip'] }}</a>
@unless($invitationMode ?? false)
<header class="store-header shell">
<a class="wordmark" href="{{ route('store.catalog') }}">zharzhar<span>●</span><small>{{ $layoutCopy['tagline'] }}</small></a>
<nav aria-label="{{ $kk ? 'Негізгі навигация' : 'Основная навигация' }}"><a href="{{ route('store.catalog') }}#designs">{{ $layoutCopy['designs'] }}</a><a href="{{ route('store.catalog') }}#how">{{ $layoutCopy['how'] }}</a><a href="{{ route('store.catalog') }}#faq">{{ $layoutCopy['faq'] }}</a></nav>
<div class="header-tools">
    <div class="language-switch" aria-label="{{ $kk ? 'Сайт тілі' : 'Язык сайта' }}">
        @foreach(['kk' => 'ҚАЗ', 'ru' => 'РУС'] as $locale => $label)
            <form method="POST" action="{{ route('store.language', $locale) }}">@csrf<button type="submit" class="{{ app()->getLocale() === $locale ? 'active' : '' }}" @if(app()->getLocale() === $locale) aria-current="true" @endif>{{ $label }}</button></form>
        @endforeach
    </div>
    <a class="header-contact" href="tel:{{ preg_replace('/[^+0-9]/', '', config('store.kaspi_phone')) }}">{{ $layoutCopy['contact'] }} ↗</a>
</div>
</header>
@endunless
<main id="main">@if(session('success'))<div class="shell"><p class="notice" role="status">{{ session('success') }}</p></div>@endif
@yield('content')
</main>
@unless($invitationMode ?? false)<footer class="store-footer shell"><a class="wordmark" href="{{ route('store.catalog') }}">zharzhar<span>●</span></a><p>{{ $layoutCopy['footer'] }}</p><span>© {{ date('Y') }} ZharZhar</span></footer>@endunless
</body>
</html>
