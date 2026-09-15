<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 22px 20px; }
        body { color: #172033; font-family: "DejaVu Sans", sans-serif; font-size: 8px; }
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
<h1>{{ __('partner.reports.title') }} · {{ $restaurant->name }}</h1>
<p class="meta">
    {{ __('partner.reports.total_records', ['count' => $bookings->count()]) }} ·
    {{ __('partner.reports.range', ['from' => $from ? \Carbon\Carbon::parse($from)->format('d.m.Y') : '—', 'to' => $to ? \Carbon\Carbon::parse($to)->format('d.m.Y') : '—']) }} ·
    {{ __('partner.reports.generated', ['date' => now()->format('d.m.Y H:i')]) }}
</p>
<table>
    <thead><tr>
        <th style="width:3%">№</th><th style="width:7%">{{ __('partner.reports.date') }}</th><th style="width:9%">{{ __('partner.reports.event') }}</th>
        <th style="width:9%">{{ __('partner.packages.package') }}</th><th style="width:11%">{{ __('partner.reports.visitor') }}</th><th style="width:10%">{{ __('partner.reports.phone') }}</th>
        <th style="width:8%">{{ __('partner.reports.period') }}</th><th style="width:5%">{{ __('partner.reports.guests') }}</th><th style="width:8%">{{ __('partner.reports.price') }}</th>
        <th style="width:8%">{{ __('partner.reports.prepayment') }}</th><th style="width:8%">{{ __('partner.reports.total') }}</th><th style="width:8%">{{ __('partner.reports.status') }}</th>
        <th style="width:12%">{{ __('partner.reports.notes') }}</th>
    </tr></thead>
    <tbody>
    @forelse($bookings as $booking)
        <tr>
            <td>{{ $booking->id }}</td><td>{{ $booking->booking_date->format('d.m.Y') }}</td><td>{{ $booking->event_type ?: '—' }}</td>
            <td>{{ $booking->tariff?->name ?: '—' }}</td><td>{{ $booking->visitor_name }}</td><td>{{ $booking->phone ?: '—' }}</td>
            <td>{{ $booking->slot?->label ?: '—' }}</td><td class="num">{{ $booking->guest_count }}</td>
            <td class="num">{{ number_format((float)$booking->price_per_guest, 0, ',', ' ') }} ₸</td>
            <td class="num">{{ number_format((float)$booking->prepayment, 0, ',', ' ') }} ₸</td>
            <td class="num">{{ number_format((float)$booking->total_amount, 0, ',', ' ') }} ₸</td>
            <td>{{ $booking->statusLabel() }}</td><td>{{ $booking->note ?: '—' }}</td>
        </tr>
    @empty<tr><td colspan="13">{{ __('partner.reports.empty') }}</td></tr>@endforelse
    </tbody>
</table>
<div class="footer">ZharZhar · {{ $restaurant->name }}</div>
</body>
</html>
