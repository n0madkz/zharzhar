<!doctype html>
<html lang="{{ $pageLanguage ?? 'ru' }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}"><meta name="referrer" content="no-referrer">
<title>{{ $title ?? 'ZharZhar — приглашения на ваши особенные события' }}</title>
<meta name="description" content="Выберите дизайн онлайн-приглашения, расскажите о своём празднике и пригласите близких. От 7 990 ₸.">
<link rel="stylesheet" href="{{ asset('store.css') }}">
<script src="{{ asset('store.js') }}" defer></script>
</head>
<body>
<a class="skip-link" href="#main">К содержимому</a>
<header class="store-header shell">
<a class="wordmark" href="{{ route('store.catalog') }}">zharzhar<span>●</span><small>СОБЫТИЯ НАЧИНАЮТСЯ ЗДЕСЬ</small></a>
<nav aria-label="Основная навигация"><a href="{{ route('store.catalog') }}#designs">Дизайны</a><a href="{{ route('store.catalog') }}#how">Как это работает</a><a href="{{ route('store.catalog') }}#faq">Вопросы</a></nav>
<a class="header-contact" href="tel:{{ preg_replace('/[^+0-9]/', '', config('store.kaspi_phone')) }}">Связаться ↗</a>
</header>
<main id="main">@if(session('success'))<div class="shell"><p class="notice" role="status">{{ session('success') }}</p></div>@endif
@yield('content')
</main>
<footer class="store-footer shell"><a class="wordmark" href="{{ route('store.catalog') }}">zharzhar<span>●</span></a><p>Для событий, которые останутся в сердце.</p><span>© {{ date('Y') }} ZharZhar</span></footer>
</body>
</html>
