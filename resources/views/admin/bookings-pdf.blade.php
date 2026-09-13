<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 22px 20px; }
        body { color: #17212f; font-family: "DejaVu Sans", sans-serif; font-size: 8px; }
        h1 { margin: 0 0 4px; font-size: 20px; }
        .meta { margin: 0 0 14px; color: #667085; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th { background: #eaf0ff; color: #14213d; font-size: 7px; text-align: left; }
        th, td { border: 1px solid #ccd5e3; padding: 5px 4px; vertical-align: top; word-wrap: break-word; }
        tbody tr:nth-child(even) { background: #f8faff; }
        .num { text-align: right; white-space: nowrap; }
        .footer { position: fixed; right: 0; bottom: -11px; color: #7c8799; font-size: 7px; }
    </style>
</head>
<body>
<h1>Все бронирования ZharZhar</h1>
<p class="meta">Всего: {{ $bookings->count() }} · Сформировано: {{ now()->format('d.m.Y H:i') }}</p>
<table>
    <thead>
    <tr>
        <th style="width:3%">№</th><th style="width:7%">Дата</th><th style="width:10%">Ресторан</th>
        <th style="width:10%">Посетитель</th><th style="width:9%">Телефон</th><th style="width:8%">Мероприятие</th>
        <th style="width:9%">Пакет</th><th style="width:7%">Период</th><th style="width:5%">Гостей</th>
        <th style="width:7%">Цена/гость</th><th style="width:7%">Предоплата</th><th style="width:7%">Итого</th>
        <th style="width:7%">Статус</th><th style="width:11%">Примечание</th>
    </tr>
    </thead>
    <tbody>
    @forelse($bookings as $booking)
        <tr>
            <td>{{ $booking->id }}</td>
            <td>{{ $booking->booking_date->format('d.m.Y') }}</td>
            <td>{{ $booking->restaurant?->name ?? '—' }}</td>
            <td>{{ $booking->visitor_name }}</td>
            <td>{{ $booking->phone ?: '—' }}</td>
            <td>{{ $booking->event_type ?: '—' }}</td>
            <td>{{ $booking->tariff?->name ?: '—' }}</td>
            <td>{{ $booking->slot?->label ?: '—' }}</td>
            <td class="num">{{ $booking->guest_count }}</td>
            <td class="num">{{ number_format((float) $booking->price_per_guest, 0, ',', ' ') }} ₸</td>
            <td class="num">{{ number_format((float) $booking->prepayment, 0, ',', ' ') }} ₸</td>
            <td class="num">{{ number_format((float) $booking->total_amount, 0, ',', ' ') }} ₸</td>
            <td>{{ $booking->statusLabel() }}</td>
            <td>{{ $booking->note ?: '—' }}</td>
        </tr>
    @empty
        <tr><td colspan="14">Бронирований пока нет.</td></tr>
    @endforelse
    </tbody>
</table>
<div class="footer">ZharZhar · единый реестр бронирований</div>
</body>
</html>
