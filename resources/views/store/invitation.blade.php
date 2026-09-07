@php
$kk = ($details['language'] ?? 'ru') === 'kk';
$copy = $kk ? [
'kicker'=>'СІЗДІ ҚУАНЫШЫМЫЗҒА ШАҚЫРАМЫЗ','text'=>'Құрметті ағайын-туыс, бауырлар, дос-жарандар! Сіздерді қуанышымыздың қадірлі қонағы болуға шақырамыз.',
'hosts'=>'Той иелері','venue'=>'Кездесу орны','rsvp'=>'Сізді күтеміз!','hint'=>'Тойға қатысуыңызды растауыңызды сұраймыз.',
'name'=>'Аты-жөніңіз','answer'=>'Қатысасыз ба?','yes'=>'Қатысамын','no'=>'Өкінішке қарай, қатыса алмаймын','maybe'=>'Кейінірек айтамын',
'count'=>'Қанша адам (өзіңізді қоса есептегенде)?','message'=>'Тілегіңіз','send'=>'Жауап жіберу','music'=>'Мерекелік әуен',
] : [
'kicker'=>'ПРИГЛАШАЕМ РАЗДЕЛИТЬ НАШУ РАДОСТЬ','text'=>'Дорогие родные и друзья! Приглашаем вас разделить с нами радость этого особенного дня.',
'hosts'=>'Той иелері · Хозяева торжества','venue'=>'Место встречи','rsvp'=>'Будем ждать вас','hint'=>'Пожалуйста, сообщите, сможете ли вы прийти.',
'name'=>'Ваше имя','answer'=>'Вы придёте?','yes'=>'С удовольствием приду','no'=>'К сожалению, не смогу','maybe'=>'Сообщу позже',
'count'=>'Сколько человек, включая вас?','message'=>'Ваше пожелание','send'=>'Отправить ответ','music'=>'Музыка праздника',
];
@endphp
@extends('layouts.store', ['title' => $details['names'].' — приглашение', 'pageLanguage' => $kk ? 'kk' : 'ru'])
@section('content')
@if($preview)<div class="preview-banner">Пример дизайна «{{ $template->name }}». Имена, текст и место заменим на ваши.<a href="{{ route('store.checkout', $template) }}">Выбрать за {{ number_format($template->price, 0, ',', ' ') }} ₸ →</a></div>@endif
<article class="invitation-page theme-{{ $details['theme'] ?? 'sage' }}"><p class="eyebrow">{{ $copy['kicker'] }}</p><span class="ornament" aria-hidden="true">❧</span>
<h1>{{ $details['names'] }}</h1><div class="invite-divider"></div>
<p class="invite-body">{{ $details['invitation_text'] ?? $copy['text'] }}</p>
<p class="invite-date">{{ \Carbon\Carbon::parse($details['event_date'])->format('d.m.Y') }}<br>{{ $details['event_time'] }}</p>
<p class="eyebrow">{{ $copy['hosts'] }}</p><p class="invite-hosts">{{ $details['hosts'] }}</p><div class="invite-divider"></div>
<p class="eyebrow">{{ $copy['venue'] }}</p><h3>{{ $details['venue_name'] }}</h3><p>{{ $details['venue_address'] }}</p>
@if(!empty($details['music_url']))<div><p class="eyebrow">{{ $copy['music'] }} · {{ $details['music_name'] }}</p><audio controls loop preload="none" src="{{ $details['music_url'] }}" aria-label="{{ $copy['music'] }}"></audio></div>@endif
<section class="panel"><h2>{{ $copy['rsvp'] }}</h2><p class="hint">{{ $copy['hint'] }}</p>
@if($preview)<p class="hint">Форма ответов станет доступна в вашем приглашении после подтверждения заказа.</p>
@else
@if($errors->any())<p class="error" role="alert">{{ $kk ? 'Өрістерді тексеріңіз.' : 'Проверьте поля формы.' }}</p>@endif
<form method="POST" action="{{ route('store.rsvp', $invitation->slug) }}" class="stack" data-submit-once>@csrf
<label class="field">{{ $copy['name'] }}<input name="guest_name" value="{{ old('guest_name') }}" autocomplete="name" maxlength="120" required>@error('guest_name')<span class="error">{{ $message }}</span>@enderror</label>
<label class="field">{{ $copy['answer'] }}<select name="attendance_status" required>@foreach(['yes','no','maybe'] as $status)<option value="{{ $status }}" @selected(old('attendance_status') === $status)>{{ $copy[$status] }}</option>@endforeach</select></label>
<label class="field">{{ $copy['count'] }}<input type="number" name="guest_count" min="1" max="20" value="{{ old('guest_count', 1) }}" required>@error('guest_count')<span class="error">{{ $message }}</span>@enderror</label>
<label class="field">{{ $copy['message'] }}<textarea name="message" maxlength="1000">{{ old('message') }}</textarea>@error('message')<span class="error">{{ $message }}</span>@enderror</label>
<button class="button primary full" type="submit">{{ $copy['send'] }}</button>
</form>@endif</section></article>
@endsection
