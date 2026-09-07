@php
    $kk = app()->isLocale('kk');
    $t = $kk ? [
        'title' => 'Қонақтардың жауаптары — ZharZhar', 'label' => 'ЖАУАПТАРДЫҢ ЖЕКЕ ПАРАҚШАСЫ',
        'hint' => 'Бұл сілтеме жауаптарды көруге мүмкіндік береді. Оны жарияламаңыз және қонақтарға жібермеңіз.',
        'open' => 'Шақыруды ашу', 'yes_count' => 'адам қатысады', 'maybe_count' => '«мүмкін» деп жауап берді',
        'no_count' => 'қатыса алмайды', 'guest' => 'ҚОНАҚ', 'answer' => 'ЖАУАП', 'people' => 'АДАМ', 'message' => 'ХАБАРЛАМА',
        'yes' => 'Қатысады', 'no' => 'Қатыспайды', 'maybe' => 'Мүмкін',
        'empty' => 'Әзірге жауап жоқ. Қонақтарға шақыру сілтемесін жіберіңіз.', 'back' => 'Артқа', 'page' => 'Бет', 'next' => 'Келесі',
    ] : [
        'title' => 'Ответы гостей — ZharZhar', 'label' => 'ЛИЧНАЯ СТРАНИЦА ОТВЕТОВ',
        'hint' => 'Эта ссылка открывает доступ к ответам. Не публикуйте её и не отправляйте гостям.',
        'open' => 'Открыть приглашение', 'yes_count' => 'гостей придут', 'maybe_count' => 'ответили «возможно»',
        'no_count' => 'не смогут прийти', 'guest' => 'ГОСТЬ', 'answer' => 'ОТВЕТ', 'people' => 'ЧЕЛОВЕК', 'message' => 'СООБЩЕНИЕ',
        'yes' => 'Придёт', 'no' => 'Не придёт', 'maybe' => 'Возможно',
        'empty' => 'Ответов пока нет. Отправьте гостям ссылку на приглашение.', 'back' => 'Назад', 'page' => 'Страница', 'next' => 'Далее',
    ];
@endphp
@extends('layouts.store', ['title' => $t['title']])
@section('content')
<div class="shell section"><p class="eyebrow">{{ $t['label'] }}</p><h2>{{ $order->details['names'] }}</h2><p class="hint">{{ $t['hint'] }}</p><a class="button outline" href="{{ $order->publicUrl('i/'.$order->invitation->slug) }}">{{ $t['open'] }} ↗</a>
<div class="stats"><div class="panel"><strong>{{ $counts['yes'] }}</strong><span>{{ $t['yes_count'] }}</span></div><div class="panel"><strong>{{ $counts['maybe'] }}</strong><span>{{ $t['maybe_count'] }}</span></div><div class="panel"><strong>{{ $counts['no'] }}</strong><span>{{ $t['no_count'] }}</span></div></div>
<div class="panel table-scroll"><table><thead><tr><th>{{ $t['guest'] }}</th><th>{{ $t['answer'] }}</th><th>{{ $t['people'] }}</th><th>{{ $t['message'] }}</th></tr></thead><tbody>@forelse($rsvps as $rsvp)<tr><td>{{ $rsvp->guest_name }}</td><td>{{ ['yes'=>$t['yes'],'no'=>$t['no'],'maybe'=>$t['maybe']][$rsvp->attendance_status] }}</td><td>{{ $rsvp->guest_count }}</td><td>{{ $rsvp->message ?: '—' }}</td></tr>@empty<tr><td colspan="4">{{ $t['empty'] }}</td></tr>@endforelse</tbody></table></div>
<div class="pagination">@if($rsvps->previousPageUrl())<a href="{{ $rsvps->previousPageUrl() }}">← {{ $t['back'] }}</a>@endif<span>{{ $t['page'] }} {{ $rsvps->currentPage() }}</span>@if($rsvps->nextPageUrl())<a href="{{ $rsvps->nextPageUrl() }}">{{ $t['next'] }} →</a>@endif</div></div>
@endsection
