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
            <button class="button" type="submit">Добавить ресторан</button>
        </form>
    </section>

    <section>
        <h2>Рестораны в системе</h2>
        @forelse($restaurants as $restaurant)
            <article class="card restaurant-card">
                <div class="card-head"><div><h3>{{ $restaurant->name }}</h3><p>{{ $restaurant->city }} · {{ $restaurant->address ?? 'Адрес не указан' }}</p></div><span class="status status-{{ $restaurant->status }}">{{ $restaurant->status === 'active' ? 'Активен' : 'Отключён' }}</span></div>
                <p>@if($restaurant->phone)<a href="tel:{{ $restaurant->phone }}">{{ $restaurant->phone }}</a>@else Телефон не указан @endif · {{ $restaurant->max_seats !== null ? $restaurant->max_seats.' мест' : 'Количество мест не указано' }}</p>
                <p class="muted">{{ $restaurant->partner?->email ?? 'Email не указан' }} · {{ $restaurant->bookings_count }} бронирований</p>
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

<div class="section-head"><div><h2>Все бронирования</h2><p class="muted">Нажмите на имя гостя, чтобы открыть бронирование на этом сайте.</p></div></div>
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
                <td><div class="row-actions"><a href="{{ route('admin.bookings.show', $booking) }}">Просмотр</a><a href="{{ route('admin.bookings.pdf', $booking) }}">PDF</a></div></td>
            </tr>
        @empty
            <tr><td colspan="8">Бронирований пока нет.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
