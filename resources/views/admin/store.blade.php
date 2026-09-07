@extends('layouts.store', ['title' => 'Управление магазином — ZharZhar'])
@section('content')
<div class="shell"><div class="page-heading order-head"><div><p class="eyebrow">ZHARZHAR · АДМИНИСТРАТОР</p><h1>Магазин приглашений</h1></div><form method="POST" action="{{ route('logout') }}">@csrf<button class="button outline">Выйти</button></form></div>
@if($errors->any())<p class="error" role="alert">{{ $errors->first() }}</p>@endif
<nav class="admin-nav" aria-label="Разделы админки"><a href="#orders">Заказы и генерации</a><a href="#templates">Дизайны</a><a href="#music">Музыка</a><a href="#promos">Промокоды</a><a href="#restaurants">Рестораны</a><a href="{{ route('admin.dashboard') }}">Бронирования ↗</a></nav>
<div class="stats"><div class="panel"><strong>{{ $totals['review'] }}</strong><span>Оплат на проверке</span></div><div class="panel"><strong>{{ $totals['paid'] }}</strong><span>Создано приглашений</span></div><div class="panel"><strong>{{ number_format($totals['revenue'], 0, ',', ' ') }} ₸</strong><span>Подтверждено оплат</span></div></div>
<section id="orders" class="admin-section"><h2>Заказы <em>и приглашения</em></h2>
<nav class="filters" aria-label="Статус заказов">@foreach([''=>'Все','pending'=>'Ожидают оплаты','review'=>'На проверке','paid'=>'Готовы','rejected'=>'Отклонены'] as $key=>$label)<a class="{{ $status === $key ? 'active' : '' }}" href="{{ route('admin.store.index', ['status'=>$key]) }}#orders">{{ $label }}</a>@endforeach</nav>
@forelse($orders as $order)<article class="panel order-card"><div class="order-head"><div><p class="eyebrow">ЗАКАЗ №{{ $order->id }} · {{ $order->created_at->format('d.m.Y H:i') }}</p><h3>{{ $order->details['names'] }}</h3><p class="hint">{{ $order->customer_name }} · {{ $order->customer_phone }}</p></div><div><strong>{{ number_format($order->total, 0, ',', ' ') }} ₸</strong><br><span class="badge badge-{{ $order->status }}">{{ $order->statusLabel() }}</span></div></div>
<p class="hint">{{ $order->details['template_name'] }} · {{ $order->details['event_date'] }} {{ $order->details['event_time'] }} · {{ $order->details['venue_name'] }}<br>Той иелері: {{ $order->details['hosts'] }}<br>{{ $order->details['venue_address'] }} · {{ $order->details['music_name'] ?? 'Без музыки' }}</p>
@if($order->promo_code)<p class="hint">Промокод <strong>{{ $order->promo_code }}</strong> · скидка {{ number_format($order->discount, 0, ',', ' ') }} ₸ · исходная цена {{ number_format($order->subtotal, 0, ',', ' ') }} ₸</p>@endif
@if($order->payment_reference)<p class="notice">Сообщение об оплате: {{ $order->payment_reference }}</p>@endif
@if($order->status === 'paid')
<p class="hint">Подтверждено {{ $order->paid_at->format('d.m.Y H:i') }} · администратор #{{ $order->confirmed_by }}</p>
<p class="field">Приглашение для гостей</p><div class="copy-row"><input readonly aria-label="Приглашение заказа {{ $order->id }}" value="{{ $order->publicUrl('i/'.$order->invitation->slug) }}"><button type="button" class="button outline" data-copy="{{ $order->publicUrl('i/'.$order->invitation->slug) }}">Копировать</button></div>
<p class="field">Личная ссылка на ответы</p><div class="copy-row"><input readonly aria-label="Ответы заказа {{ $order->id }}" value="{{ $order->publicUrl('responses/'.$order->responses_token) }}"><button type="button" class="button outline" data-copy="{{ $order->publicUrl('responses/'.$order->responses_token) }}">Копировать</button></div>
@elseif(in_array($order->status, ['pending','review']))
<form method="POST" action="{{ route('admin.store.confirm', $order) }}" class="stack" data-submit-once>@csrf<p class="hint">Проверьте поступление {{ number_format($order->total, 0, ',', ' ') }} ₸ в Kaspi. Подтверждение опубликует приглашение и откроет ссылки клиенту.</p><button type="submit" class="button primary">Подтвердить оплату и создать приглашение</button></form>
<details><summary>Отклонить заказ</summary><form method="POST" action="{{ route('admin.store.reject', $order) }}" class="stack">@csrf<label class="field">Причина для клиента<textarea name="admin_note" required maxlength="1000"></textarea></label><button class="button danger" type="submit">Отклонить заказ</button></form></details>
@endif
@if($order->admin_note)<p class="hint">{{ $order->admin_note }}</p>@endif
<details><summary>Личная страница заказа</summary><div class="copy-row"><input readonly aria-label="Страница заказа {{ $order->id }}" value="{{ $order->publicUrl('orders/'.$order->token) }}"><button type="button" class="button outline" data-copy="{{ $order->publicUrl('orders/'.$order->token) }}">Копировать</button></div></details>
</article>@empty<div class="empty-state">Заказов с таким статусом пока нет.</div>@endforelse
<div class="pagination">@if($orders->previousPageUrl())<a href="{{ $orders->previousPageUrl() }}">← Назад</a>@endif<span>Страница {{ $orders->currentPage() }}</span>@if($orders->nextPageUrl())<a href="{{ $orders->nextPageUrl() }}">Далее →</a>@endif</div>
</section>
@php
$groups = [
'templates' => ['title'=>'Дизайны приглашений','records'=>$templates,'route'=>'templates','hint'=>'Добавляйте дизайны на основе доступных оформлений. Цена от 7 990 ₸. Изменения не меняют уже оформленные заказы.','fields'=>[
'name'=>['label'=>'Название','required'=>true], 'slug'=>['label'=>'Код дизайна (латиница)','required'=>true],
'event_type'=>['label'=>'Событие','options'=>[''=>'Любое событие']+config('store.event_types')],
'price'=>['label'=>'Цена, ₸','type'=>'number','min'=>7990,'max'=>10000000,'default'=>7990,'required'=>true],
'theme'=>['label'=>'Оформление','options'=>config('store.themes')],
'preview_image'=>['label'=>'Ссылка на обложку HTTPS','type'=>'url','hint'=>'Необязательно. Без ссылки показываем оформление дизайна.'],
]],
'music' => ['title'=>'Музыкальная библиотека','records'=>$music,'route'=>'music','hint'=>'Загрузите аудиофайл с компьютера и отметьте события, для которых он подходит. Один трек можно добавить сразу в несколько категорий.','fields'=>[
'name'=>['label'=>'Название трека','required'=>true],
'categories'=>['label'=>'Категории','multiple_options'=>config('store.music_categories'),'hint'=>'Выберите одну или несколько категорий.'],
'audio_file'=>['label'=>'Аудиофайл','type'=>'file','accept'=>'.mp3,.m4a,.mp4,.wav,.ogg,.webm,audio/*','required'=>true,'hint'=>'MP3, M4A, WAV, OGG или WebM, не более 30 МБ.'],
]],
'promos' => ['title'=>'Промокоды','records'=>$promos,'route'=>'promos','hint'=>'Пустой код будет сгенерирован автоматически. Лимит включает оформленные и оплаченные заказы; при отклонении резерв освобождается.','fields'=>[
'code'=>['label'=>'Промокод','hint'=>'Латиница, цифры и дефис. Пусто = сгенерировать.'],
'type'=>['label'=>'Тип скидки','options'=>['percent'=>'Процент','fixed'=>'Сумма в тенге']],
'value'=>['label'=>'Размер скидки','type'=>'number','min'=>1,'required'=>true],
'max_uses'=>['label'=>'Лимит применений','type'=>'number','min'=>1,'hint'=>'Пусто = без ограничения'],
'expires_at'=>['label'=>'Действует до (время Казахстана)','type'=>'datetime-local'],
]],
'restaurants' => ['title'=>'Каталог ресторанов','records'=>$restaurants,'route'=>'restaurants','hint'=>'Справочник мест для приглашений. Кабинеты партнёров и бронирования доступны в отдельном разделе.','fields'=>[
'name'=>['label'=>'Название','required'=>true],'city'=>['label'=>'Город','required'=>true],
'address'=>['label'=>'Адрес','required'=>true],'phone'=>['label'=>'Телефон','type'=>'tel'],
]],
];
@endphp
@foreach($groups as $groupKey => $group)
<section class="admin-section" id="{{ $groupKey }}"><h2>{{ $group['title'] }}</h2><p class="hint">{{ $group['hint'] }}</p>
<div class="admin-grid"><div class="admin-editor"><h3>Добавить</h3><form method="POST" action="{{ route('admin.store.'.$group['route']) }}" @if($groupKey === 'music') enctype="multipart/form-data" @endif>@csrf
@include('admin.store-fields', ['fields'=>$group['fields'],'record'=>null,'editor'=>$groupKey.'-new'])
</form></div><div class="stack">
@forelse($group['records'] as $record)<details class="admin-editor"><summary><span>{{ $record->name ?? $record->code }} @if($groupKey==='templates') · {{ number_format($record->price,0,',',' ') }} ₸ @endif @if($groupKey==='music') · {{ $record->categoryLabel() }} @endif @if($groupKey==='promos') · {{ $record->value }}{{ $record->type === 'percent' ? '%' : ' ₸' }} · {{ $record->uses }}/{{ $record->max_uses ?? '∞' }} @endif @if($groupKey==='restaurants' ? $record->status !== 'active' : !$record->is_active) · Неактивен @endif</span></summary>
<form method="POST" action="{{ route('admin.store.'.$group['route'], $record) }}" @if($groupKey === 'music') enctype="multipart/form-data" @endif>@csrf
@include('admin.store-fields', ['fields'=>$group['fields'],'record'=>$record,'editor'=>$groupKey.'-'.$record->id])
</form></details>@empty<p class="hint">Записей пока нет.</p>@endforelse</div></div>
</section>@endforeach</div>
@endsection
