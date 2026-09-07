@extends('layouts.store', ['title' => 'Оформить приглашение — ZharZhar'])
@section('content')
<div class="shell"><div class="page-heading"><a class="breadcrumb" href="{{ route('store.catalog') }}#designs">← Все дизайны</a><h1>Ваш праздник <em>в деталях</em></h1><p class="hint">01 Дизайн выбран &nbsp; / &nbsp; <strong>02 Данные события</strong> &nbsp; / &nbsp; 03 Оплата</p></div>
@if($errors->any())<div class="error" role="alert">Проверьте поля формы. {{ $errors->first() }}</div>@endif
<form class="checkout-layout" method="POST" action="{{ route('store.order') }}" data-submit-once>@csrf
<input type="hidden" name="template_id" value="{{ $template->id }}"><input type="hidden" name="request_key" value="{{ old('request_key', $requestKey) }}">
<div class="panel">
<fieldset class="form-section"><legend>01. О событии</legend><div class="form-grid">
<label class="field">Какой у вас праздник?<select name="event_type" required>@foreach(config('store.event_types') as $key => $label)@if(!$template->event_type || $template->event_type === $key)<option value="{{ $key }}" @selected(old('event_type', $template->event_type) === $key)>{{ $label }}</option>@endif @endforeach</select>@error('event_type')<span class="error">{{ $message }}</span>@enderror</label>
<label class="field">Язык приглашения<select name="language"><option value="ru" @selected(old('language') === 'ru')>Русский</option><option value="kk" @selected(old('language') === 'kk')>Қазақша</option></select></label>
<label class="field wide">Имена молодожёнов или имя именинника<input name="names" value="{{ old('names') }}" placeholder="Алихан и Аружан / Айгүл" maxlength="160" required>@error('names')<span class="error">{{ $message }}</span>@enderror</label>
<label class="field">Дата праздника<input type="date" name="event_date" value="{{ old('event_date') }}" min="{{ now()->format('Y-m-d') }}" required>@error('event_date')<span class="error">{{ $message }}</span>@enderror</label>
<label class="field">Начало (время Казахстана)<input type="time" name="event_time" value="{{ old('event_time', '18:00') }}" required>@error('event_time')<span class="error">{{ $message }}</span>@enderror</label>
<label class="field wide">Той иелері — хозяева торжества<input name="hosts" value="{{ old('hosts') }}" placeholder="Ерлан – Айгүл, Марат – Сәуле" maxlength="240" required>@error('hosts')<span class="error">{{ $message }}</span>@enderror</label>
</div></fieldset>
<fieldset class="form-section"><legend>02. Место встречи</legend><div class="form-grid">
<label class="field wide">Ресторан из каталога<select name="restaurant_id" id="restaurant_id"><option value="">Укажу свой ресторан</option>@foreach($restaurants as $restaurant)<option value="{{ $restaurant->id }}" data-name="{{ $restaurant->name }}" data-address="{{ $restaurant->city }}, {{ $restaurant->address }}" @selected((string)old('restaurant_id') === (string)$restaurant->id)>{{ $restaurant->name }} · {{ $restaurant->city }}</option>@endforeach</select><small>Выбор ресторана заполняет название и адрес. Это не бронирование зала.</small>@error('restaurant_id')<span class="error">{{ $message }}</span>@enderror</label>
<label class="field wide">Название ресторана<input id="venue_name" name="venue_name" value="{{ old('venue_name') }}" maxlength="160" required>@error('venue_name')<span class="error">{{ $message }}</span>@enderror</label>
<label class="field wide">Город и адрес<input id="venue_address" name="venue_address" value="{{ old('venue_address') }}" maxlength="255" required>@error('venue_address')<span class="error">{{ $message }}</span>@enderror</label>
</div></fieldset>
<fieldset class="form-section"><legend>03. Настроение приглашения</legend><div class="form-grid">
<label class="field wide">Музыка<select id="music_id" name="music_id"><option value="">Без музыки</option>@foreach($music as $track)<option value="{{ $track->id }}" data-url="{{ $track->audio_url }}" @selected((string)old('music_id') === (string)$track->id)>{{ $track->name }} · {{ $track->categoryLabel() }}</option>@endforeach</select><small>Выберите трек и нажмите воспроизведение, чтобы послушать.</small>@error('music_id')<span class="error">{{ $message }}</span>@enderror</label>
<audio class="wide" id="music-preview" controls preload="none" hidden aria-label="Прослушать выбранную музыку"></audio>
<label class="field wide">Текст приглашения <small>По желанию. Если оставить пустым, используем готовый текст.</small><textarea name="invitation_text" maxlength="2000" placeholder="Дорогие родные и друзья! Будем рады видеть вас…">{{ old('invitation_text') }}</textarea>@error('invitation_text')<span class="error">{{ $message }}</span>@enderror</label>
</div></fieldset>
<fieldset class="form-section"><legend>04. Как с вами связаться</legend><div class="form-grid">
<label class="field">Ваше имя<input name="customer_name" value="{{ old('customer_name') }}" autocomplete="name" maxlength="120" required>@error('customer_name')<span class="error">{{ $message }}</span>@enderror</label>
<label class="field">Телефон / WhatsApp<input type="tel" name="customer_phone" value="{{ old('customer_phone') }}" autocomplete="tel" placeholder="+7 700 123 45 67" required>@error('customer_phone')<span class="error">{{ $message }}</span>@enderror</label>
</div></fieldset>
<button class="button primary full" type="submit">Оформить и перейти к оплате →</button><p class="hint">На следующем шаге получите точную сумму и реквизиты Kaspi. Приглашение опубликуем после проверки оплаты.</p>
</div>
<aside class="panel summary-panel"><p class="eyebrow">ВАШ ВЫБОР</p><div class="design-preview theme-{{ $template->config_json['theme'] ?? 'sage' }}"><span class="ornament" aria-hidden="true">❧</span><strong>{{ $template->name }}</strong></div><h3>{{ $template->name }}</h3><p class="hint">Персональное приглашение<br>Музыка и информация о событии<br>Личная страница ответов гостей</p>
<div class="summary-row"><span>Стоимость дизайна</span><strong>{{ number_format($template->price, 0, ',', ' ') }} ₸</strong></div>
<label class="field" for="promo_code">Есть промокод?</label><div class="promo-control field"><input id="promo_code" name="promo_code" value="{{ old('promo_code') }}" maxlength="40" placeholder="Введите код" style="text-transform:uppercase"><button class="button outline" type="button" id="apply-promo" data-template="{{ $template->id }}" data-price="{{ $template->price }}" data-url="{{ route('store.quote') }}">Применить</button></div>
<p class="hint" id="promo-result" aria-live="polite"></p>@error('promo_code')<p class="error">{{ $message }}</p>@enderror
<div class="summary-row"><span>Скидка</span><strong id="discount-value">0 ₸</strong></div><div class="summary-row total"><span>Итого</span><strong id="total-value">{{ number_format($template->price, 0, ',', ' ') }} ₸</strong></div>
<p class="hint">Разовая оплата через Kaspi. Итоговая сумма фиксируется при оформлении заказа.</p>
</aside></form></div>
@endsection
