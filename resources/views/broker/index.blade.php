@extends('layouts.zharzhar', ['title' => 'Банкетные залы — ZharZhar Broker'])
@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
<link rel="stylesheet" href="https://unpkg.com/maplibre-gl@5/dist/maplibre-gl.css">
<link rel="stylesheet" href="{{ asset('broker.css') }}">
<link rel="stylesheet" href="{{ asset('broker-extra.css') }}">
<header><div><div class="brand">zharzhar · broker</div><small>Знакомьте рестораны с сервисом и подключайте партнёров</small></div><form action="/logout" method="post">@csrf<button class="button secondary">Выйти</button></form></header>
@if(session('success'))<p class="success">{{ session('success') }}</p>@endif
@if($errors->any())<div class="error" role="alert">{{ $errors->first() }}</div>@endif

<div class="broker-heading"><div><p class="eyebrow">КАБИНЕТ СОТРУДНИКА</p><h1>Банкетные залы</h1></div><button class="button" id="locate" type="button">Рядом со мной</button></div>

<details class="card broker-city-panel">
    <summary><strong>Настройки города</strong><span>{{ $city }}</span></summary>
    <form action="/broker/settings" method="post" class="broker-city-form">@csrf<label>Текущий город<select name="city" required>@foreach($cities as $option)<option value="{{ $option }}" @selected($option === $city)>{{ $option }}</option>@endforeach</select></label><button class="button">Показать город</button></form>
    <form action="/broker/collect" method="post">@csrf<input type="hidden" name="city" value="{{ $city }}"><button class="button secondary">Обновить {{ $city }} из 2GIS</button></form>
    @if($availableCities->isNotEmpty())
    <form action="/broker/cities" method="post" class="broker-add-city">@csrf<label>Добавить город<select name="city" required><option value="">Выберите город</option>@foreach($availableCities as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach</select></label><button class="button">Добавить и загрузить из 2GIS</button></form>
    @endif
    <p class="{{ $parserReady ? 'success' : 'error' }}">{{ $parserReady ? 'Парсер 2GIS установлен.' : 'Парсер 2GIS не установлен. В Laravel Toolkit → Artisan один раз выполните: broker:parser-install' }}</p>
    <small>При добавлении или обновлении города сервер получает актуальный список через interlark/parser-2gis. Это может занять несколько минут.</small>
</details>

<section class="card broker-tools">
    <label>Поиск по названию, адресу или телефону<input id="search" type="search" placeholder="Введите часть названия или номера"></label>
    <label>Район<select id="district"><option value="">Все районы</option></select></label>
    <label>Состояние сделки<select id="connection"><option value="">Все сделки</option><option value="open">В работе</option><option value="closed">Закрытые сделки</option><option value="failed">Не состоявшиеся</option><option value="partner">Уже партнёр</option></select></label>
    <label>Сортировка<select id="sort-order"><option value="nearest">Сначала ближайшие</option><option value="farthest">Сначала дальние</option><option value="name">По названию</option></select></label>
</section>
<div class="broker-view-controls" role="group" aria-label="Вид результатов"><button class="button secondary" id="view-split" type="button">Список и карта</button><button class="button secondary" id="view-list" type="button">Только список</button><button class="button secondary" id="view-map" type="button">Только карта</button></div>
<p id="location-note">Разрешите геолокацию или выберите начальную точку на карте. Расстояния считаются по прямой.</p>
<div class="broker-workspace" id="broker-workspace" data-view="split">
    <section><div class="broker-list-head"><strong id="count"></strong><label><input type="checkbox" id="group-district"> Группировать по районам</label></div><div id="venue-list"></div><nav class="broker-pages" aria-label="Страницы залов"><button class="button secondary" id="prev" type="button">Назад</button><span id="page-label"></span><button class="button secondary" id="next" type="button">Далее</button></nav></section>
    <aside class="broker-map"><div id="map" aria-label="Карта банкетных залов"></div><p id="map-error" role="status" hidden>Карта не загрузилась. Список залов и ссылки на 2GIS доступны.</p><div class="broker-map-footer"><a id="route" class="button" target="_blank" rel="noopener noreferrer" hidden>Открыть маршрут</a><small id="route-note">Выберите район для маршрута объезда.</small></div></aside>
</div>
<dialog id="registration"><div class="broker-dialog-head"><h2>Подключить ресторан</h2><button type="button" id="close-dialog" aria-label="Закрыть">×</button></div><p id="registration-name"></p><form method="post" id="registration-form" class="form-stack">@csrf<label>Email для входа<input type="email" name="email" id="registration-email" required maxlength="255" autocomplete="off"></label><label>Телефон ресторана<input type="tel" name="phone" id="registration-phone" required maxlength="30"></label><label>Пароль партнёра<input type="password" name="password" minlength="8" maxlength="128" required autocomplete="new-password"></label><label>Повторите пароль<input type="password" name="password_confirmation" minlength="8" maxlength="128" required autocomplete="new-password"></label><small>После регистрации передайте ресторану email и пароль для входа на partner.zharzhar.kz.</small><button class="button">Зарегистрировать партнёра</button></form></dialog>
<script>window.brokerData = {{ Illuminate\Support\Js::from(['venues' => $venues, 'city' => $city, 'csrf' => csrf_token()]) }};</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>
<script src="https://unpkg.com/maplibre-gl@5/dist/maplibre-gl.js"></script>
<script src="https://unpkg.com/@maplibre/maplibre-gl-leaflet/leaflet-maplibre-gl.js"></script>
<script src="{{ asset('broker.js') }}" defer></script>
@endsection
