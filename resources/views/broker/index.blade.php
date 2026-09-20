@extends('layouts.zharzhar', ['title' => 'Банкетные залы — ZharZhar Broker'])
@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="anonymous">
<link rel="stylesheet" href="{{ asset('broker.css') }}">
<header><div><div class="brand">zharzhar · broker</div><small>Знакомьте рестораны с сервисом и подключайте партнёров</small></div><form action="/logout" method="post">@csrf<button class="button secondary">Выйти</button></form></header>
@if(session('success'))<p class="success">{{ session('success') }}</p>@endif
@if($errors->any())<div class="error" role="alert">{{ $errors->first() }}</div>@endif
<div class="broker-heading"><div><p class="eyebrow">КАБИНЕТ СОТРУДНИКА</p><h1>Банкетные залы</h1></div><button class="button" id="locate" type="button">Рядом со мной</button></div>
<section class="card broker-tools">
<form class="form-stack" action="/broker/settings" method="post">@csrf<label>Город в настройках<input name="city" list="cities" value="{{ $city }}" required maxlength="100" placeholder="Например, Алматы"></label><datalist id="cities">@foreach($cities as $option)<option value="{{ $option }}">@endforeach</datalist><button class="button secondary">Сохранить город</button></form>
<label>Поиск по названию, адресу или телефону<input id="search" type="search" placeholder="Введите часть названия или номера"></label>
<label>Район<select id="district"><option value="">Все районы</option></select></label>
<label>Подключение<select id="connection"><option value="">Все залы</option><option value="new">Не подключены</option><option value="partner">Уже партнёры</option><option value="completed">Сделка завершена</option></select></label>
</section>
<p id="location-note" class="muted" role="status">Разрешите геолокацию или нажмите на карту, чтобы выбрать начало маршрута. Расстояния указаны по прямой.</p>
<div class="broker-workspace">
<section><div class="broker-list-head"><strong id="count"></strong><label><input type="checkbox" id="group-district"> Группировать по районам</label></div><div id="venue-list"></div><nav class="broker-pages" aria-label="Страницы залов"><button class="button secondary" id="prev" type="button">Назад</button><span id="page-label"></span><button class="button secondary" id="next" type="button">Далее</button></nav></section>
<aside class="broker-map"><div id="map" aria-label="Карта банкетных залов"></div><p id="map-error" role="status" hidden>Карта не загрузилась. Список залов и ссылки на 2GIS доступны.</p><div class="broker-map-footer"><a id="route" class="button" target="_blank" rel="noopener noreferrer" hidden>Открыть маршрут</a><small id="route-note">Выберите район для маршрута объезда.</small></div></aside>
</div>
<details class="card broker-import"><summary>Загрузить банкетные залы из 2GIS</summary>
@if(config('broker.parser_python') && array_key_exists($city, config('broker.cities')))
<form action="/broker/collect" method="post">@csrf<input type="hidden" name="city" value="{{ $city }}"><p>Собрать актуальные банкетные залы в городе {{ $city }}. Сбор может занять несколько минут.</p><button class="button">Обновить из 2GIS</button></form>
<p role="status">{{ Illuminate\Support\Facades\Cache::get('broker-import:'.hash('sha256', $city)) }}</p>
@endif
<p class="muted">Выберите в parser-2gis город и категорию «Банкетные залы», сохраните результат в JSON. Повторный импорт обновит контакты и сохранит подключённые рестораны и завершённые сделки.</p><form class="form-stack" action="/broker/import" method="post" enctype="multipart/form-data">@csrf<label>Город<input name="city" value="{{ $city }}" required maxlength="100"></label><label>JSON от parser-2gis<input type="file" name="file" accept=".json,application/json" required></label><button class="button">Загрузить залы</button></form></details>
<dialog id="registration"><div class="broker-dialog-head"><h2>Подключить ресторан</h2><button type="button" id="close-dialog" aria-label="Закрыть">×</button></div><p id="registration-name"></p><form method="post" id="registration-form" class="form-stack">@csrf<label>Email для входа<input type="email" name="email" id="registration-email" required maxlength="255" autocomplete="off"></label><label>Телефон ресторана<input type="tel" name="phone" id="registration-phone" required maxlength="30"></label><label>Пароль партнёра<input type="password" name="password" minlength="8" maxlength="128" required autocomplete="new-password"></label><label>Повторите пароль<input type="password" name="password_confirmation" minlength="8" maxlength="128" required autocomplete="new-password"></label><small>После регистрации передайте ресторану email и пароль для входа на partner.zharzhar.kz.</small><button class="button">Зарегистрировать партнёра</button></form></dialog>
<script>window.brokerData = {{ Illuminate\Support\Js::from(['venues' => $venues, 'city' => $city, 'csrf' => csrf_token()]) }};</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin="anonymous"></script>
<script src="{{ asset('broker.js') }}" defer></script>
@endsection
