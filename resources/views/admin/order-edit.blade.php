@extends('layouts.store', ['title' => 'Редактирование заказа №'.$order->id.' — ZharZhar'])

@section('content')
@php
    $value = fn (string $key, mixed $default = '') => old($key, data_get($details, $key, $default));
    $programTimes = old('program_times', $details['program_times'] ?? ['17:00', '18:00', '19:00']);
    $copyFields = [
        'event_label' => ['Название события на обложке', false],
        'intro' => ['Обращение к гостям', true],
        'date_title' => ['Заголовок даты', false],
        'program' => ['Заголовок программы', false],
        'welcome' => ['Пункт программы №1', false],
        'ceremony' => ['Пункт программы №2', false],
        'celebration' => ['Пункт программы №3', false],
        'venue' => ['Заголовок места проведения', false],
        'map' => ['Текст кнопки карты', false],
        'countdown' => ['Заголовок обратного отсчёта', false],
        'days' => ['Подпись дней', false],
        'hours' => ['Подпись часов', false],
        'minutes' => ['Подпись минут', false],
        'seconds' => ['Подпись секунд', false],
        'hosts' => ['Заголовок организаторов', false],
        'rsvp' => ['Заголовок формы ответа', false],
        'hint' => ['Подсказка формы ответа', true],
        'name' => ['Поле имени гостя', false],
        'answer' => ['Вопрос об участии', false],
        'yes' => ['Ответ «Да»', false],
        'no' => ['Ответ «Нет»', false],
        'maybe' => ['Ответ «Позже»', false],
        'count' => ['Поле количества гостей', false],
        'message' => ['Поле пожелания', false],
        'send' => ['Кнопка отправки ответа', false],
        'closing' => ['Финальная фраза', true],
    ];
@endphp

<div class="shell narrow admin-order-page">
    <div class="page-heading order-head">
        <div>
            <a class="breadcrumb" href="{{ route('admin.store.index') }}#orders">← Все заказы</a>
            <p class="eyebrow">ПОЛНОЕ РЕДАКТИРОВАНИЕ</p>
            <h1>Заказ №{{ $order->id }}</h1>
            <p class="hint">Изменения оплаченного заказа сразу появятся в готовом приглашении.</p>
        </div>
        <span class="badge badge-{{ $order->status }}">{{ $order->statusLabel() }}</span>
    </div>

    @if($errors->any())<div class="error panel" role="alert"><strong>Не удалось сохранить.</strong><br>{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('admin.store.orders.update', $order) }}" class="stack admin-order-form">
        @csrf
        @method('PUT')

        <fieldset class="panel form-section">
            <legend>Заказ и клиент</legend>
            <div class="form-grid">
                <label class="field">Статус<select name="status" required>@foreach(['pending'=>'Ожидает оплаты','review'=>'На проверке','paid'=>'Приглашение готово','rejected'=>'Отклонён'] as $key=>$label)<option value="{{ $key }}" @selected(old('status', $order->status) === $key)>{{ $label }}</option>@endforeach</select></label>
                <label class="field">Дизайн<select name="template_id" required>@foreach($templates as $template)<option value="{{ $template->id }}" @selected((int)old('template_id', $order->template_id) === $template->id)>{{ $template->name }}{{ $template->is_active ? '' : ' · скрыт' }}</option>@endforeach</select></label>
                <label class="field">Имя клиента<input name="customer_name" value="{{ old('customer_name', $order->customer_name) }}" maxlength="120" required></label>
                <label class="field">Телефон клиента<input name="customer_phone" value="{{ old('customer_phone', $order->customer_phone) }}" maxlength="30" required></label>
                <label class="field">Исходная цена, ₸<input type="number" name="subtotal" min="0" max="10000000" value="{{ old('subtotal', $order->subtotal) }}" required></label>
                <label class="field">Скидка, ₸<input type="number" name="discount" min="0" max="10000000" value="{{ old('discount', $order->discount) }}" required></label>
                <label class="field">Итого, ₸<input type="number" name="total" min="0" max="10000000" value="{{ old('total', $order->total) }}" required></label>
                <label class="field">Промокод<input name="promo_code" value="{{ old('promo_code', $order->promo_code) }}" maxlength="40"></label>
                <label class="field wide">Сообщение об оплате<textarea name="payment_reference" maxlength="1000">{{ old('payment_reference', $order->payment_reference) }}</textarea></label>
                <label class="field wide">Заметка администратора<textarea name="admin_note" maxlength="1000">{{ old('admin_note', $order->admin_note) }}</textarea></label>
            </div>
        </fieldset>

        <fieldset class="panel form-section">
            <legend>Мероприятие</legend>
            <div class="form-grid">
                <label class="field">Категория<select name="event_type" required>@foreach(config('store.event_types') as $key=>$label)<option value="{{ $key }}" @selected($value('event_type', 'wedding') === $key)>{{ $label }}</option>@endforeach</select></label>
                <label class="field">Язык приглашения<select name="language" required><option value="kk" @selected($value('language', 'kk') === 'kk')>Қазақша</option><option value="ru" @selected($value('language') === 'ru')>Русский</option></select></label>
                <label class="field wide">Название мероприятия / имена<input name="names" value="{{ $value('names') }}" maxlength="160" required></label>
                <label class="field wide">Той иелері / организаторы<input name="hosts" value="{{ $value('hosts') }}" maxlength="240" required></label>
                <label class="field">Дата<input type="date" name="event_date" value="{{ $value('event_date') }}" required></label>
                <label class="field">Время<input type="time" name="event_time" value="{{ $value('event_time', '18:00') }}" required></label>
                <label class="field">Ресторан из каталога<select name="restaurant_id"><option value="">Не выбран</option>@foreach($restaurants as $restaurant)<option value="{{ $restaurant->id }}" @selected((int)$value('restaurant_id') === $restaurant->id)>{{ $restaurant->name }}{{ $restaurant->status === 'active' ? '' : ' · скрыт' }}</option>@endforeach</select></label>
                <label class="field">Музыка<select name="music_id"><option value="">Без музыки</option>@foreach($music as $track)<option value="{{ $track->id }}" @selected((int)old('music_id', $musicId) === $track->id)>{{ $track->name }}{{ $track->is_active ? '' : ' · скрыта' }}</option>@endforeach</select></label>
                <label class="field wide">Название ресторана или места<input name="venue_name" value="{{ $value('venue_name') }}" maxlength="160" required></label>
                <label class="field wide">Адрес<input name="venue_address" value="{{ $value('venue_address') }}" maxlength="255" required></label>
                <label class="field wide">Основной текст приглашения<textarea name="invitation_text" maxlength="2000">{{ $value('invitation_text') }}</textarea></label>
            </div>
        </fieldset>

        <fieldset class="panel form-section">
            <legend>Время программы</legend>
            <div class="form-grid program-time-grid">
                @foreach(['Сбор гостей','Церемония','Праздничный вечер'] as $index=>$label)<label class="field">{{ $label }}<input type="time" name="program_times[]" value="{{ $programTimes[$index] ?? ['17:00','18:00','19:00'][$index] }}" required></label>@endforeach
            </div>
        </fieldset>

        <fieldset class="panel form-section">
            <legend>Все тексты внутри приглашения</legend>
            <p class="hint">Можно изменить заголовки, подписи таймера, варианты ответа гостя и текст кнопок.</p>
            <div class="form-grid">
                @foreach($copyFields as $key => [$label, $wide])
                    <label class="field {{ $wide ? 'wide' : '' }}">{{ $label }}
                        @if($wide)<textarea name="copy[{{ $key }}]" maxlength="300" required>{{ old('copy.'.$key, $copy[$key] ?? '') }}</textarea>@else<input name="copy[{{ $key }}]" value="{{ old('copy.'.$key, $copy[$key] ?? '') }}" maxlength="300" required>@endif
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div class="admin-order-actions panel">
            <button class="button primary" type="submit">Сохранить все изменения</button>
            @if($order->invitation)
                <a class="button outline" href="{{ $order->publicUrl('i/'.$order->invitation->slug) }}" target="_blank" rel="noopener">Открыть приглашение ↗</a>
                <a class="button outline" href="{{ $order->publicUrl('responses/'.$order->responses_token) }}" target="_blank" rel="noopener">Ответы гостей ↗</a>
            @endif
        </div>
    </form>

    <section class="panel delete-order-panel">
        <h2>Удаление заказа</h2>
        <p class="hint">Будут удалены заказ, опубликованное приглашение и все ответы гостей. Это действие нельзя отменить.</p>
        <form method="POST" action="{{ route('admin.store.orders.destroy', $order) }}" onsubmit="return confirm('Удалить заказ №{{ $order->id }} вместе с приглашением и ответами гостей?')">
            @csrf
            @method('DELETE')
            <button class="button danger" type="submit">Удалить заказ навсегда</button>
        </form>
    </section>
</div>
@endsection
