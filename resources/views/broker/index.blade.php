@extends('layouts.zharzhar', ['title' => 'Банкетные залы — ZharZhar Broker'])
@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
<link rel="stylesheet" href="https://unpkg.com/maplibre-gl@5/dist/maplibre-gl.css">
<link rel="stylesheet" href="{{ asset('broker.css') }}">
<link rel="stylesheet" href="{{ asset('broker-extra.css') }}">

<header class="broker-topbar"><div><div class="brand">zharzhar · broker</div><small>{{ $city }}</small></div><button class="button secondary" id="locate" type="button">Определить моё место</button></header>
@if(session('success'))<p class="success">{{ session('success') }}</p>@endif
@if($errors->any())<div class="error" role="alert">{{ $errors->first() }}</div>@endif

<main class="broker-app">
    <section class="broker-screen is-active" data-screen="map">
        <div class="broker-heading"><div><p class="eyebrow">КАРТА</p><h1>Залы рядом</h1></div></div>
        <div class="card broker-tools" data-filters="map">
            <label>Поиск<input data-field="search" type="search" placeholder="Название, адрес или телефон"></label>
            <label>Район<select data-field="district"><option value="">Все районы</option></select></label>
            <label>Сделка<select data-field="status"><option value="">Все сделки</option><option value="open">В работе</option><option value="closed">Закрытые</option><option value="failed">Не состоялись</option></select></label>
            <label>Порядок<select data-field="sort"><option value="nearest">Сначала ближайшие</option><option value="farthest">Сначала дальние</option><option value="name">По названию</option></select></label>
        </div>
        <p data-location-note>Разрешите геолокацию или выберите начальную точку на карте.</p>
        <aside class="broker-map"><div id="map" aria-label="Карта банкетных залов"></div><p id="map-error" role="status" hidden>Карта не загрузилась. Список залов и ссылки на 2GIS доступны.</p><div class="broker-map-footer"><strong id="map-count"></strong><a id="route" class="button" target="_blank" rel="noopener noreferrer" hidden>Открыть маршрут</a><small id="route-note">Выберите район для маршрута объезда.</small></div></aside>
    </section>

    <section class="broker-screen" data-screen="venues" hidden>
        <div class="broker-heading"><div><p class="eyebrow">РЕСТОРАНЫ</p><h1>Список заведений</h1></div></div>
        <div class="card broker-tools" data-filters="venues">
            <label>Поиск<input data-field="search" type="search" placeholder="Название, адрес или телефон"></label>
            <label>Район<select data-field="district"><option value="">Все районы</option></select></label>
            <label>Сделка<select data-field="status"><option value="">Все сделки</option><option value="open">В работе</option><option value="closed">Закрытые</option><option value="failed">Не состоялись</option></select></label>
            <label>Порядок<select data-field="sort"><option value="nearest">Сначала ближайшие</option><option value="farthest">Сначала дальние</option><option value="name">По названию</option></select></label>
        </div>
        <p data-location-note>Список сортируется по расстоянию после определения геолокации.</p>
        <div class="broker-list-head"><strong id="venue-count"></strong><label><input type="checkbox" id="group-district"> Группировать по районам</label></div>
        <div id="venue-list"></div>
        <nav class="broker-pages" aria-label="Страницы залов"><button class="button secondary" id="prev" type="button">Назад</button><span id="page-label"></span><button class="button secondary" id="next" type="button">Далее</button></nav>
    </section>

    <section class="broker-screen" data-screen="deals" hidden>
        <div class="broker-heading"><div><p class="eyebrow">ПОДКЛЮЧЕНЫ</p><h1>Завершённые сделки</h1></div></div>
        <div class="card broker-tools broker-tools-compact" data-filters="deals">
            <label>Поиск<input data-field="search" type="search" placeholder="Название, адрес или телефон"></label>
            <label>Район<select data-field="district"><option value="">Все районы</option></select></label>
            <label>Порядок<select data-field="sort"><option value="nearest">Сначала ближайшие</option><option value="farthest">Сначала дальние</option><option value="name">По названию</option></select></label>
        </div>
        <div class="broker-list-head"><strong id="deal-count"></strong></div>
        <div id="deal-list"></div>
        <nav class="broker-pages" aria-label="Страницы сделок"><button class="button secondary" id="deal-prev" type="button">Назад</button><span id="deal-page-label"></span><button class="button secondary" id="deal-next" type="button">Далее</button></nav>
    </section>

    <section class="broker-screen" data-screen="settings" hidden>
        <div class="broker-heading"><div><p class="eyebrow">ПРОФИЛЬ</p><h1>Настройки</h1></div></div>
        <section class="card broker-settings">
            <h2>Город</h2>
            <form action="/broker/settings" method="post" class="broker-city-form">@csrf<label>Текущий город<select name="city" required>@foreach($cities as $option)<option value="{{ $option }}" @selected($option === $city)>{{ $option }}</option>@endforeach</select></label><button class="button">Сохранить город</button></form>
            @if($availableCities->isNotEmpty())
            <form action="/broker/cities" method="post" class="broker-add-city">@csrf<label>Добавить город<select name="city" required><option value="">Выберите город</option>@foreach($availableCities as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach</select></label><button class="button">Добавить и загрузить</button></form>
            @endif
            <form action="/broker/collect" method="post">@csrf<input type="hidden" name="city" value="{{ $city }}"><button class="button secondary">Обновить {{ $city }} из 2GIS</button></form>
            <p class="{{ $parserReady ? 'success' : 'error' }}">{{ $parserReady ? 'Парсер 2GIS установлен.' : 'Парсер 2GIS не установлен. Выполните в Artisan: broker:parser-install' }}</p>
            <small>Получение актуального списка из 2GIS может занять несколько минут.</small>
        </section>
        <form action="/logout" method="post" class="broker-logout">@csrf<button class="button secondary">Выйти из кабинета</button></form>
    </section>
</main>

<nav class="broker-bottom-nav" aria-label="Основная навигация">
    <button class="is-active" type="button" data-tab="map"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18-6 3V6l6-3 6 3 6-3v15l-6 3-6-3Zm0 0V3m6 18V6"/></svg><span>Карта</span></button>
    <button type="button" data-tab="venues"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 21V5l8-3 8 3v16M8 8h1m6 0h1M8 12h1m6 0h1M8 16h1m6 0h1M2 21h20"/></svg><span>Рестораны</span></button>
    <button type="button" data-tab="deals"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12l4 4L19 6M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2Z"/></svg><span>Сделки</span></button>
    <button type="button" data-tab="settings"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5ZM19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.83 2.83-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.55V21h-4v-.08A1.7 1.7 0 0 0 8.95 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06-2.83-2.83.06-.06A1.7 1.7 0 0 0 4.6 15 1.7 1.7 0 0 0 3.08 14H3v-4h.08A1.7 1.7 0 0 0 4.6 8.95a1.7 1.7 0 0 0-.34-1.88l-.06-.06 2.83-2.83.06.06A1.7 1.7 0 0 0 8.97 4.6 1.7 1.7 0 0 0 10 3.08V3h4v.08a1.7 1.7 0 0 0 1.03 1.52 1.7 1.7 0 0 0 1.88-.34l.06-.06 2.83 2.83-.06.06a1.7 1.7 0 0 0-.34 1.88A1.7 1.7 0 0 0 20.92 10H21v4h-.08A1.7 1.7 0 0 0 19.4 15Z"/></svg><span>Настройки</span></button>
</nav>

<dialog id="registration"><div class="broker-dialog-head"><h2>Подключить ресторан</h2><button type="button" id="close-dialog" aria-label="Закрыть">×</button></div><p id="registration-name"></p><form method="post" id="registration-form" class="form-stack">@csrf<label>Email для входа<input type="email" name="email" id="registration-email" required maxlength="255" autocomplete="off"></label><label>Телефон ресторана<input type="tel" name="phone" id="registration-phone" required maxlength="30"></label><label>Количество залов<input type="number" name="halls_count" id="halls-count" value="1" min="1" max="20" required></label><div id="hall-fields"></div><label>Пароль партнёра<input type="password" name="password" minlength="8" maxlength="128" required autocomplete="new-password"></label><label>Повторите пароль<input type="password" name="password_confirmation" minlength="8" maxlength="128" required autocomplete="new-password"></label><small>После регистрации передайте ресторану email и пароль для входа на partner.zharzhar.kz.</small><button class="button">Зарегистрировать партнёра</button></form></dialog>
<script>window.brokerData = {{ Illuminate\Support\Js::from(['venues' => $venues, 'city' => $city, 'csrf' => csrf_token()]) }};</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>
<script src="https://unpkg.com/maplibre-gl@5/dist/maplibre-gl.js"></script>
<script src="https://unpkg.com/@maplibre/maplibre-gl-leaflet/leaflet-maplibre-gl.js"></script>
<script src="{{ asset('broker.js') }}" defer></script>
@endsection
