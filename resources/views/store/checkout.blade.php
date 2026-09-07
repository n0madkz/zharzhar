@php
    $kk = app()->isLocale('kk');
    $eventLabels = $kk
        ? ['wedding' => 'Үйлену той', 'qyz_uzatu' => 'Қыз ұзату', 'anniversary' => 'Мерейтой', 'birthday' => 'Туған күн']
        : ['wedding' => 'Свадьба', 'qyz_uzatu' => 'Қыз ұзату', 'anniversary' => 'Юбилей', 'birthday' => 'День рождения'];
    $t = $kk ? [
        'title' => 'Шақыруды рәсімдеу — ZharZhar', 'back' => 'Барлық дизайндар',
        'heading' => 'Мерекеңіздің барлық мәліметі', 'progress' => '01 Дизайн таңдалды / 02 Мереке мәліметі / 03 Төлем',
        'errors' => 'Форма өрістерін тексеріңіз.', 'event' => '01. Мереке туралы', 'event_type' => 'Қандай мереке өткізесіз?',
        'invite_language' => 'Шақыру тілі', 'names' => 'Жас жұбайлардың немесе мерейтой иесінің есімі',
        'names_placeholder' => 'Алихан және Аружан / Айгүл', 'date' => 'Мереке күні', 'time' => 'Басталу уақыты',
        'hosts' => 'Той иелері', 'hosts_placeholder' => 'Ерлан – Айгүл, Марат – Сәуле',
        'venue' => '02. Кездесу орны', 'restaurant' => 'Каталогтағы мейрамхана', 'own_restaurant' => 'Мейрамхананы өзім көрсетемін',
        'restaurant_hint' => 'Мейрамхананы таңдағанда атауы мен мекенжайы толтырылады. Бұл залды брондау емес.',
        'venue_name' => 'Мейрамхана атауы', 'address' => 'Қала және мекенжай',
        'mood' => '03. Шақырудың көңіл күйі', 'music' => 'Музыка', 'without_music' => 'Музыкасыз',
        'music_hint' => 'Әуенді таңдап, алдын ала тыңдауға болады.', 'audio_label' => 'Таңдалған музыканы тыңдау',
        'invite_text' => 'Шақыру мәтіні', 'optional_text' => 'Қалауыңызша. Бос қалдырсаңыз, дайын мәтін қолданылады.',
        'text_placeholder' => 'Құрметті ағайын-туыс, бауырлар мен достар! Сіздерді қуанышымыздың қадірлі қонағы болуға шақырамыз…',
        'contact' => '04. Сізбен қалай байланысамыз', 'your_name' => 'Сіздің есіміңіз', 'phone' => 'Телефон / WhatsApp',
        'submit' => 'Рәсімдеу және төлемге өту', 'submit_hint' => 'Келесі қадамда нақты сома мен Kaspi деректері көрсетіледі. Шақыру төлем тексерілгеннен кейін жарияланады.',
        'choice' => 'СІЗДІҢ ТАҢДАУЫҢЫЗ', 'promo' => 'Промокод бар ма?', 'promo_placeholder' => 'Кодты енгізіңіз',
        'apply' => 'Қолдану', 'discount' => 'Жеңілдік', 'total' => 'Барлығы',
        'price_hint' => 'Kaspi арқылы бір реттік төлем. Қорытынды сома тапсырыс рәсімделгенде бекітіледі.',
    ] : [
        'title' => 'Оформить приглашение — ZharZhar', 'back' => 'Все дизайны',
        'heading' => 'Ваш праздник в деталях', 'progress' => '01 Дизайн выбран / 02 Данные события / 03 Оплата',
        'errors' => 'Проверьте поля формы.', 'event' => '01. О событии', 'event_type' => 'Какой у вас праздник?',
        'invite_language' => 'Язык приглашения', 'names' => 'Имена молодожёнов или имя именинника',
        'names_placeholder' => 'Алихан и Аружан / Айгүл', 'date' => 'Дата праздника', 'time' => 'Начало',
        'hosts' => 'Той иелері — хозяева торжества', 'hosts_placeholder' => 'Ерлан – Айгүл, Марат – Сәуле',
        'venue' => '02. Место встречи', 'restaurant' => 'Ресторан из каталога', 'own_restaurant' => 'Укажу свой ресторан',
        'restaurant_hint' => 'Выбор ресторана заполнит название и адрес. Это не бронирование зала.',
        'venue_name' => 'Название ресторана', 'address' => 'Город и адрес',
        'mood' => '03. Настроение приглашения', 'music' => 'Музыка', 'without_music' => 'Без музыки',
        'music_hint' => 'Выберите трек и прослушайте его заранее.', 'audio_label' => 'Прослушать выбранную музыку',
        'invite_text' => 'Текст приглашения', 'optional_text' => 'По желанию. Если оставить пустым, используем готовый текст.',
        'text_placeholder' => 'Дорогие родные и друзья! Будем рады видеть вас…',
        'contact' => '04. Как с вами связаться', 'your_name' => 'Ваше имя', 'phone' => 'Телефон / WhatsApp',
        'submit' => 'Оформить и перейти к оплате', 'submit_hint' => 'На следующем шаге получите точную сумму и реквизиты Kaspi. Приглашение опубликуем после проверки оплаты.',
        'choice' => 'ВАШ ВЫБОР', 'promo' => 'Есть промокод?', 'promo_placeholder' => 'Введите код',
        'apply' => 'Применить', 'discount' => 'Скидка', 'total' => 'Итого',
        'price_hint' => 'Разовая оплата через Kaspi. Итоговая сумма фиксируется при оформлении заказа.',
    ];
@endphp

@extends('layouts.store', ['title' => $t['title']])
@section('content')
<div class="shell"><div class="page-heading"><a class="breadcrumb" href="{{ route('store.catalog') }}#designs">← {{ $t['back'] }}</a><h1>{{ $t['heading'] }}</h1><p class="hint">{{ $t['progress'] }}</p></div>
@if($errors->any())<div class="error" role="alert">{{ $t['errors'] }} {{ $errors->first() }}</div>@endif
<form class="checkout-layout" method="POST" action="{{ route('store.order') }}" data-submit-once>@csrf
<input type="hidden" name="template_id" value="{{ $template->id }}"><input type="hidden" name="request_key" value="{{ old('request_key', $requestKey) }}">
<div class="panel">
<fieldset class="form-section"><legend>{{ $t['event'] }}</legend><div class="form-grid">
<label class="field">{{ $t['event_type'] }}<select name="event_type" required>@foreach($eventLabels as $key => $label)@if(!$template->event_type || $template->event_type === $key)<option value="{{ $key }}" @selected(old('event_type', $template->event_type) === $key)>{{ $label }}</option>@endif @endforeach</select>@error('event_type')<span class="error">{{ $message }}</span>@enderror</label>
<label class="field">{{ $t['invite_language'] }}<select name="language"><option value="kk" @selected(old('language', app()->getLocale()) === 'kk')>Қазақша</option><option value="ru" @selected(old('language', app()->getLocale()) === 'ru')>Русский</option></select></label>
<label class="field wide">{{ $t['names'] }}<input name="names" value="{{ old('names') }}" placeholder="{{ $t['names_placeholder'] }}" maxlength="160" required>@error('names')<span class="error">{{ $message }}</span>@enderror</label>
<label class="field">{{ $t['date'] }}<input type="date" name="event_date" value="{{ old('event_date') }}" min="{{ now()->format('Y-m-d') }}" required>@error('event_date')<span class="error">{{ $message }}</span>@enderror</label>
<label class="field">{{ $t['time'] }}<input type="time" name="event_time" value="{{ old('event_time', '18:00') }}" required>@error('event_time')<span class="error">{{ $message }}</span>@enderror</label>
<label class="field wide">{{ $t['hosts'] }}<input name="hosts" value="{{ old('hosts') }}" placeholder="{{ $t['hosts_placeholder'] }}" maxlength="240" required>@error('hosts')<span class="error">{{ $message }}</span>@enderror</label>
</div></fieldset>
<fieldset class="form-section"><legend>{{ $t['venue'] }}</legend><div class="form-grid">
<label class="field wide">{{ $t['restaurant'] }}<select name="restaurant_id" id="restaurant_id"><option value="">{{ $t['own_restaurant'] }}</option>@foreach($restaurants as $restaurant)<option value="{{ $restaurant->id }}" data-name="{{ $restaurant->name }}" data-address="{{ $restaurant->city }}, {{ $restaurant->address }}" @selected((string)old('restaurant_id') === (string)$restaurant->id)>{{ $restaurant->name }} · {{ $restaurant->city }}</option>@endforeach</select><small>{{ $t['restaurant_hint'] }}</small>@error('restaurant_id')<span class="error">{{ $message }}</span>@enderror</label>
<label class="field wide">{{ $t['venue_name'] }}<input id="venue_name" name="venue_name" value="{{ old('venue_name') }}" maxlength="160" required>@error('venue_name')<span class="error">{{ $message }}</span>@enderror</label>
<label class="field wide">{{ $t['address'] }}<input id="venue_address" name="venue_address" value="{{ old('venue_address') }}" maxlength="255" required>@error('venue_address')<span class="error">{{ $message }}</span>@enderror</label>
</div></fieldset>
<fieldset class="form-section"><legend>{{ $t['mood'] }}</legend><div class="form-grid">
<label class="field wide">{{ $t['music'] }}<select id="music_id" name="music_id"><option value="">{{ $t['without_music'] }}</option>@foreach($music as $track)<option value="{{ $track->id }}" data-url="{{ $track->audio_url }}" @selected((string)old('music_id') === (string)$track->id)>{{ $track->name }} · {{ $track->categoryLabel() }}</option>@endforeach</select><small>{{ $t['music_hint'] }}</small>@error('music_id')<span class="error">{{ $message }}</span>@enderror</label>
<audio class="wide" id="music-preview" controls preload="none" hidden aria-label="{{ $t['audio_label'] }}"></audio>
<label class="field wide">{{ $t['invite_text'] }} <small>{{ $t['optional_text'] }}</small><textarea name="invitation_text" maxlength="2000" placeholder="{{ $t['text_placeholder'] }}">{{ old('invitation_text') }}</textarea>@error('invitation_text')<span class="error">{{ $message }}</span>@enderror</label>
</div></fieldset>
<fieldset class="form-section"><legend>{{ $t['contact'] }}</legend><div class="form-grid">
<label class="field">{{ $t['your_name'] }}<input name="customer_name" value="{{ old('customer_name') }}" autocomplete="name" maxlength="120" required>@error('customer_name')<span class="error">{{ $message }}</span>@enderror</label>
<label class="field">{{ $t['phone'] }}<input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" autocomplete="tel" placeholder="+7 700 123 45 67" required>@error('customer_phone')<span class="error">{{ $message }}</span>@enderror</label>
</div></fieldset>
<button class="button primary full" type="submit">{{ $t['submit'] }} →</button><p class="hint">{{ $t['submit_hint'] }}</p>
</div>
<aside class="panel summary-panel"><p class="eyebrow">{{ $t['choice'] }}</p><div class="design-preview invite-card-{{ $template->config_json['theme'] ?? 'sage' }}"><img src="{{ $template->preview_image }}" alt=""><span class="card-shade"></span><strong>{{ $template->name }}</strong></div>
<div class="summary-row"><span>{{ $template->name }}</span><strong>{{ number_format($template->price, 0, ',', ' ') }} ₸</strong></div>
<label class="field" for="promo_code">{{ $t['promo'] }}</label><div class="promo-control field"><input id="promo_code" name="promo_code" value="{{ old('promo_code') }}" maxlength="40" placeholder="{{ $t['promo_placeholder'] }}" style="text-transform:uppercase"><button class="button outline" type="button" id="apply-promo" data-template="{{ $template->id }}" data-price="{{ $template->price }}" data-url="{{ route('store.quote') }}">{{ $t['apply'] }}</button></div>
<p class="hint" id="promo-result" aria-live="polite"></p>@error('promo_code')<p class="error">{{ $message }}</p>@enderror
<div class="summary-row"><span>{{ $t['discount'] }}</span><strong id="discount-value">0 ₸</strong></div><div class="summary-row total"><span>{{ $t['total'] }}</span><strong id="total-value">{{ number_format($template->price, 0, ',', ' ') }} ₸</strong></div>
<p class="hint">{{ $t['price_hint'] }}</p>
</aside></form></div>
@endsection
