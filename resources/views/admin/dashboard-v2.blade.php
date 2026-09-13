@extends('layouts.zharzhar', ['title' => 'Рестораны и бронирования — ZharZhar'])

@section('content')
<header>
    <div><div class="brand">Жар-Жар · Администратор</div><div class="muted">Управление ресторанами и бронированиями</div></div>
    <div class="actions"><a class="button secondary" href="{{ route('admin.store.index') }}">Магазин приглашений</a><form method="POST" action="{{ route('logout') }}">@csrf<button class="button" type="submit">Выйти</button></form></div>
</header>
@if(session('success'))<p class="success">{{ session('success') }}</p>@endif
@if($errors->any())<div class="error card">{{ $errors->first() }}</div>@endif

<h1>Рестораны</h1>
<div class="admin-columns">
    <section class="card">
        <h2>Добавить ресторан</h2>
        <form method="POST" action="{{ route('admin.restaurants.store') }}" class="form-stack">
            @csrf
            <label>Название<input name="name" value="{{ old('name') }}" required></label>
            <label>Город<input name="city" value="{{ old('city') }}" required></label>
            <label>Адрес<input name="address" value="{{ old('address') }}"></label>
            <label>Ссылка на ресторан в 2GIS<input name="two_gis_url" type="url" value="{{ old('two_gis_url') }}" placeholder="https://2gis.kz/..."></label>
            <label>Телефон ресторана<input name="phone" value="{{ old('phone') }}" required placeholder="+7 700 000 00 00"></label>
            <label>Email для входа<input name="email" type="email" value="{{ old('email') }}" required></label>
            <label>Пароль кабинета<input name="password" type="password" required minlength="8"></label>
            <label>Количество мест<input name="max_seats" type="number" min="0" value="{{ old('max_seats') }}" placeholder="Можно указать 0"></label>
            <label>Бонус с оплаченного приглашения, %<input name="bonus_percent" type="number" min="0" max="100" step="0.01" value="{{ old('bonus_percent', 0) }}"><small>Начисляется ресторану после подтверждения оплаты заказа.</small></label>
            <button class="button" type="submit">Добавить ресторан</button>
        </form>
    </section>

    <section>
        <h2>Рестораны в системе</h2>
        @forelse($restaurants as $restaurant)
            <article class="card restaurant-card">
                <div class="card-head"><div><h3>{{ $restaurant->name }}</h3><p>{{ $restaurant->city }} · {{ $restaurant->address ?? 'Адрес не указан' }}</p></div><span class="status status-{{ $restaurant->status }}">{{ $restaurant->status === 'active' ? 'Активен' : 'Отключён' }}</span></div>
                <p>@if($restaurant->phone)<a href="tel:{{ $restaurant->phone }}">{{ $restaurant->phone }}</a>@else Телефон не указан @endif · {{ $restaurant->max_seats !== null ? $restaurant->max_seats.' мест' : 'Количество мест не указано' }}</p>
                <p class="muted">{{ $restaurant->partner?->email ?? 'Email не указан' }} · {{ $restaurant->bookings_count }} бронирований · бонус {{ rtrim(rtrim(number_format((float)$restaurant->bonus_percent, 2, '.', ''), '0'), '.') }}%</p>
                @if($restaurant->two_gis_url)<p><a class="text-link" href="{{ $restaurant->two_gis_url }}" target="_blank" rel="noopener">Открыть ресторан в 2GIS ↗</a></p>@endif
                <details>
                    <summary>Изменить ресторан и доступ</summary>
                    <form method="POST" action="{{ route('admin.restaurants.update', $restaurant) }}" class="form-stack edit-restaurant-form">
                        @csrf @method('PUT')
                        <label>Название<input name="name" value="{{ $restaurant->name }}" required></label>
                        <label>Город<input name="city" value="{{ $restaurant->city }}" required></label>
                        <label>Адрес<input name="address" value="{{ $restaurant->address }}"></label>
                        <label>Ссылка на ресторан в 2GIS<input name="two_gis_url" type="url" value="{{ $restaurant->two_gis_url }}" placeholder="https://2gis.kz/..."></label>
                        <label>Телефон<input name="phone" value="{{ $restaurant->phone }}" required></label>
                        <label>Email партнёра<input name="email" type="email" value="{{ $restaurant->partner?->email }}" required></label>
                        <label>Новый пароль<input name="password" type="password" minlength="8"><small>Оставьте пустым, чтобы сохранить текущий пароль.</small></label>
                        <label>Количество мест<input name="max_seats" type="number" min="0" value="{{ $restaurant->max_seats }}"></label>
                        <label>Бонус с оплаченного приглашения, %<input name="bonus_percent" type="number" min="0" max="100" step="0.01" value="{{ $restaurant->bonus_percent }}"><small>Можно установить отдельный процент для каждого ресторана.</small></label>
                        <label class="check"><input name="is_active" type="checkbox" value="1" @checked($restaurant->status === 'active')> Активен</label>
                        <button class="button" type="submit">Сохранить изменения</button>
                    </form>
                </details>
            </article>
        @empty
            <div class="card">Ресторанов пока нет.</div>
        @endforelse
    </section>
</div>

<div class="section-head"><div><h2>Все бронирования</h2><p class="muted">Нажмите на имя гостя, чтобы открыть бронирование на этом сайте.</p></div><a class="button secondary" href="{{ route('admin.bookings.pdf') }}">Скачать все в PDF</a></div>
<form class="booking-filters card" method="GET" action="{{ route('admin.dashboard') }}">
    <label class="filter-search">Имя или телефон<input name="q" type="search" value="{{ $search }}" placeholder="Введите часть имени или номера"></label>
    <label>Ресторан<input name="restaurant" type="search" list="admin-restaurant-options" value="{{ $filters['restaurant'] ?? $restaurants->firstWhere('id', (int) ($filters['restaurant_id'] ?? 0))?->name }}" placeholder="Начните вводить название" autocomplete="off"><datalist id="admin-restaurant-options">@foreach($restaurants as $restaurant)<option value="{{ $restaurant->name }}">{{ $restaurant->city }}</option>@endforeach</datalist><small>Можно ввести часть названия или выбрать подсказку.</small></label>
    <label>Период<select name="period"><option value="">Все периоды</option>@foreach($periods as $period)<option value="{{ $period->slot_key }}" @selected(($filters['period'] ?? '') === $period->slot_key)>{{ $period->label }} · {{ $period->start_time }}–{{ $period->end_time }}</option>@endforeach</select></label>
    <label>Мероприятие<select name="event_type"><option value="">Все мероприятия</option>@foreach($eventTypes as $eventType)<option value="{{ $eventType }}" @selected(($filters['event_type'] ?? '') === $eventType)>{{ $eventType }}</option>@endforeach</select></label>
    <label>Дата от<input name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}"></label>
    <label>Дата до<input name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}"></label>
    <div class="booking-filter-actions"><button class="button" type="submit">Применить</button>@if(collect($filters)->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty())<a class="button secondary" href="{{ route('admin.dashboard') }}">Сбросить</a>@endif</div>
</form>
<div class="table-scroll">
    <table>
        <thead><tr><th>Посетитель</th><th>Телефон / WhatsApp</th><th>Ресторан</th><th>Дата</th><th>Гостей</th><th>Сумма</th><th>Статус</th><th>Действия</th></tr></thead>
        <tbody>
        @forelse($bookings as $booking)
            <tr>
                <td><a class="booking-name" href="{{ route('admin.bookings.show', $booking) }}">{{ $booking->visitor_name }}</a><br><small>{{ $booking->event_type ?: 'Мероприятие' }}</small></td>
                <td>@if($booking->whatsappUrl())<a class="whatsapp-link" href="{{ $booking->whatsappUrl() }}" target="_blank" rel="noopener">{{ $booking->phone }} ↗</a>@else<span class="muted">Не указан</span>@endif</td>
                <td>{{ $booking->restaurant?->name ?? '—' }}</td>
                <td>{{ $booking->booking_date->format('d.m.Y') }}<br><small>{{ $booking->slot?->label ?? 'Период не выбран' }}</small></td>
                <td>{{ $booking->guest_count }}</td>
                <td>{{ number_format($booking->total_amount, 0, ',', ' ') }} ₸</td>
                <td><span class="status status-{{ $booking->status }}">{{ $booking->statusLabel() }}</span></td>
                <td><div class="row-actions"><a href="{{ route('admin.bookings.show', $booking) }}">Просмотр</a></div></td>
            </tr>
        @empty
            <tr><td colspan="8">Бронирований пока нет.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@if($bookings->hasPages())<div class="booking-pagination">@if($bookings->previousPageUrl())<a class="button secondary" href="{{ $bookings->previousPageUrl() }}">← Назад</a>@else<span></span>@endif<span>Страница {{ $bookings->currentPage() }} из {{ $bookings->lastPage() }}</span>@if($bookings->nextPageUrl())<a class="button secondary" href="{{ $bookings->nextPageUrl() }}">Далее →</a>@else<span></span>@endif</div>@endif
@endsection
