@extends('layouts.zharzhar', ['title' => 'Бронирование №'.$booking->id.' — ZharZhar'])

@section('content')
<header>
    <div><a class="text-link" href="{{ route('admin.dashboard') }}">← Все бронирования</a><div class="brand">Бронирование №{{ $booking->id }}</div></div>
    <span class="status status-{{ $booking->status }}">{{ $booking->statusLabel() }}</span>
</header>
<div class="booking-detail-grid">
    <section class="card booking-main-card">
        <p class="eyebrow">ПОСЕТИТЕЛЬ</p><h1>{{ $booking->visitor_name }}</h1>
        @if($booking->whatsappUrl())<a class="button whatsapp" href="{{ $booking->whatsappUrl() }}" target="_blank" rel="noopener">Написать {{ $booking->phone }} в WhatsApp ↗</a>@else<p class="muted">Номер телефона не указан.</p>@endif
        <dl class="detail-list">
            <div><dt>Мероприятие</dt><dd>{{ $booking->event_type ?: 'Не указано' }}</dd></div>
            <div><dt>Дата</dt><dd>{{ $booking->booking_date->format('d.m.Y') }}</dd></div>
            <div><dt>Период</dt><dd>{{ $booking->slot?->label ?? 'Не выбран' }} @if($booking->slot)· {{ $booking->slot->start_time }}–{{ $booking->slot->end_time }}@endif</dd></div>
            <div><dt>Количество гостей</dt><dd>{{ $booking->guest_count }}</dd></div>
            <div><dt>Цена за гостя</dt><dd>{{ number_format((float)$booking->price_per_guest, 0, ',', ' ') }} ₸</dd></div>
            <div><dt>Предоплата</dt><dd>{{ number_format((float)$booking->prepayment, 0, ',', ' ') }} ₸</dd></div>
            <div><dt>Итоговая сумма</dt><dd><strong>{{ number_format($booking->total_amount, 0, ',', ' ') }} ₸</strong></dd></div>
            <div><dt>Примечание</dt><dd>{{ $booking->note ?: 'Нет' }}</dd></div>
            <div><dt>Создано</dt><dd>{{ $booking->created_at?->format('d.m.Y H:i') }}</dd></div>
        </dl>
    </section>
    <aside class="card">
        <p class="eyebrow">РЕСТОРАН</p><h2>{{ $booking->restaurant?->name ?? 'Не указан' }}</h2>
        <p>{{ $booking->restaurant?->city }} · {{ $booking->restaurant?->address }}</p>
        @if($booking->restaurant?->two_gis_url)<p><a class="text-link" href="{{ $booking->restaurant->two_gis_url }}" target="_blank" rel="noopener">Открыть в 2GIS ↗</a></p>@endif
        <a class="button secondary full" href="{{ route('admin.bookings.pdf', $booking) }}">Скачать PDF</a>
    </aside>
</div>
@endsection
