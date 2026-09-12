@extends('layouts.store', ['title' => 'Управление магазином — ZharZhar'])
@section('content')
<div class="shell"><div class="page-heading order-head"><div><p class="eyebrow">ZHARZHAR · АДМИНИСТРАТОР</p><h1>Магазин приглашений</h1></div><form method="POST" action="{{ route('logout') }}">@csrf<button class="button outline">Выйти</button></form></div>
@if($errors->any())<p class="error" role="alert">{{ $errors->first() }}</p>@endif
<nav class="admin-nav" aria-label="Разделы админки"><a href="#orders">Заказы и генерации</a><a href="#templates">Дизайны</a><a href="#music">Музыка</a><a href="#promos">Промокоды</a><a href="#restaurants">Рестораны</a><a href="{{ route('admin.dashboard') }}">Бронирования ↗</a></nav>
<div class="stats"><div class="panel"><strong>{{ $totals['review'] }}</strong><span>Оплат на проверке</span></div><div class="panel"><strong>{{ $totals['paid'] }}</strong><span>Создано приглашений</span></div><div class="panel"><strong>{{ number_format($totals['revenue'], 0, ',', ' ') }} ₸</strong><span>Подтверждено оплат</span></div></div>
<section id="orders" class="admin-section"><h2>Заказы <em>и приглашения</em></h2>
<form class="order-search" method="GET" action="{{ route('admin.store.index') }}#orders" role="search">
@if($status !== '')<input type="hidden" name="status" value="{{ $status }}">@endif
<label for="order-search">Поиск заказов</label><div><input id="order-search" name="q" type="search" value="{{ $search }}" placeholder="Имя, телефон, № заказа или ресторан"><button class="button primary" type="submit">Найти</button>@if($search !== '')<a class="button outline" href="{{ route('admin.store.index', array_filter(['status'=>$status])) }}#orders">Сбросить</a>@endif</div>
</form>
<nav class="filters" aria-label="Статус заказов">@foreach([''=>'Все','pending'=>'Ожидают оплаты','review'=>'На проверке','paid'=>'Готовы','rejected'=>'Отклонены'] as $key=>$label)<a class="{{ $status === $key ? 'active' : '' }}" href="{{ route('admin.store.index', array_filter(['status'=>$key,'q'=>$search])) }}#orders">{{ $label }}</a>@endforeach</nav>
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
<p><a class="button primary" href="{{ route('admin.store.orders.edit', $order) }}">Редактировать весь заказ →</a></p>
<details><summary>Личная страница заказа</summary><div class="copy-row"><input readonly aria-label="Страница заказа {{ $order->id }}" value="{{ $order->publicUrl('orders/'.$order->token) }}"><button type="button" class="button outline" data-copy="{{ $order->publicUrl('orders/'.$order->token) }}">Копировать</button></div></details>
</article>@empty<div class="empty-state">@if($search !== '')По запросу «{{ $search }}» заказов не найдено.@elseЗаказов с таким статусом пока нет.@endif</div>@endforelse
@if($orders->hasPages())<div class="pagination">@if($orders->previousPageUrl())<a href="{{ $orders->previousPageUrl() }}#orders">← Назад</a>@else<span></span>@endif<span>Страница {{ $orders->currentPage() }} из {{ $orders->lastPage() }}</span>@if($orders->nextPageUrl())<a href="{{ $orders->nextPageUrl() }}#orders">Далее →</a>@else<span></span>@endif</div>@endif
</section>
@php
$templateTextDefaults = [
    'title' => 'Жаңа шақыру',
    'event_label' => 'ҮЙЛЕНУ ТОЙЫ',
    'intro_title' => 'ҚҰРМЕТТІ АҒАЙЫН-ТУЫС, БАУЫРЛАР МЕН ДОСТАР!',
    'invitation_text' => 'Сіздерді қуанышымыздың қадірлі қонағы болуға шақырамыз.',
    'event_date' => now()->addMonths(2)->format('Y-m-d'),
    'event_time' => '18:00',
    'date_title' => 'Той салтанаты',
    'program_title' => 'Той бағдарламасы',
    'welcome_text' => 'Қонақтардың жиналуы',
    'ceremony_text' => 'Салтанатты рәсім',
    'celebration_text' => 'Мерекелік кеш',
    'venue_title' => 'Мекенжайымыз',
    'venue_name' => 'Салтанат сарайы',
    'venue_address' => 'Алматы қаласы, Абай даңғылы, 50',
    'countdown_title' => 'Салтанатқа дейін',
    'hosts_title' => 'Той иелері',
    'hosts_name' => 'Қуаныш иелері',
    'rsvp_title' => 'Сізді күтеміз!',
    'rsvp_hint' => 'Тойға қатысуыңызды растауыңызды сұраймыз.',
    'closing_text' => 'Қуанышымызға ортақ болыңыз!',
];
$groups = [
'templates' => ['title'=>'Дизайны приглашений','records'=>$templates,'route'=>'templates','hint'=>'Откройте любой дизайн справа, чтобы изменить обложку, оформление и все казахские тексты его демонстрационной страницы. Изменения не затрагивают уже оформленные заказы.','fields'=>[
'name'=>['label'=>'Название дизайна на казахском','required'=>true], 'slug'=>['label'=>'Код дизайна (латиница)','required'=>true],
'event_type'=>['label'=>'Событие','options'=>[''=>'Любое событие']+config('store.event_types')],
'price'=>['label'=>'Цена, ₸','type'=>'number','min'=>7990,'max'=>10000000,'default'=>7990,'required'=>true],
'theme'=>['label'=>'Оформление','options'=>config('store.themes')],
'preview_image'=>['label'=>'Ссылка или путь к обложке','hint'=>'Можно оставить текущий путь или указать HTTPS-ссылку.'],
'preview_image_file'=>['label'=>'Загрузить новую обложку с компьютера','type'=>'file','accept'=>'.jpg,.jpeg,.png,.webp,.avif,image/*','preview'=>'image','hint'=>'JPG, PNG, WebP или AVIF, не более 10 МБ.'],
'content_title'=>['label'=>'Главный заголовок превью','config'=>'content_kk.title','default'=>$templateTextDefaults['title'],'required'=>true],
'content_event_label'=>['label'=>'Название события над заголовком','config'=>'content_kk.event_label','default'=>$templateTextDefaults['event_label'],'required'=>true],
'content_intro_title'=>['label'=>'Обращение к гостям','config'=>'content_kk.intro_title','default'=>$templateTextDefaults['intro_title'],'type'=>'textarea','wide'=>true,'required'=>true,'maxlength'=>300],
'content_invitation_text'=>['label'=>'Текст приглашения','config'=>'content_kk.invitation_text','default'=>$templateTextDefaults['invitation_text'],'type'=>'textarea','wide'=>true,'required'=>true],
'content_event_date'=>['label'=>'Дата в превью','config'=>'content_kk.event_date','default'=>$templateTextDefaults['event_date'],'type'=>'date','required'=>true],
'content_event_time'=>['label'=>'Время в превью','config'=>'content_kk.event_time','default'=>$templateTextDefaults['event_time'],'type'=>'time','required'=>true],
'content_date_title'=>['label'=>'Заголовок блока даты','config'=>'content_kk.date_title','default'=>$templateTextDefaults['date_title'],'required'=>true],
'content_program_title'=>['label'=>'Заголовок программы','config'=>'content_kk.program_title','default'=>$templateTextDefaults['program_title'],'required'=>true],
'content_welcome_text'=>['label'=>'Пункт: сбор гостей','config'=>'content_kk.welcome_text','default'=>$templateTextDefaults['welcome_text'],'required'=>true],
'content_ceremony_text'=>['label'=>'Пункт: церемония','config'=>'content_kk.ceremony_text','default'=>$templateTextDefaults['ceremony_text'],'required'=>true],
'content_celebration_text'=>['label'=>'Пункт: праздничный вечер','config'=>'content_kk.celebration_text','default'=>$templateTextDefaults['celebration_text'],'required'=>true],
'content_venue_title'=>['label'=>'Заголовок места','config'=>'content_kk.venue_title','default'=>$templateTextDefaults['venue_title'],'required'=>true],
'content_venue_name'=>['label'=>'Название места в превью','config'=>'content_kk.venue_name','default'=>$templateTextDefaults['venue_name'],'required'=>true],
'content_venue_address'=>['label'=>'Адрес в превью','config'=>'content_kk.venue_address','default'=>$templateTextDefaults['venue_address'],'wide'=>true,'required'=>true],
'content_countdown_title'=>['label'=>'Заголовок таймера','config'=>'content_kk.countdown_title','default'=>$templateTextDefaults['countdown_title'],'required'=>true],
'content_hosts_title'=>['label'=>'Заголовок организаторов','config'=>'content_kk.hosts_title','default'=>$templateTextDefaults['hosts_title'],'required'=>true],
'content_hosts_name'=>['label'=>'Организаторы в превью','config'=>'content_kk.hosts_name','default'=>$templateTextDefaults['hosts_name'],'required'=>true],
'content_rsvp_title'=>['label'=>'Заголовок формы ответа','config'=>'content_kk.rsvp_title','default'=>$templateTextDefaults['rsvp_title'],'required'=>true],
'content_rsvp_hint'=>['label'=>'Подсказка формы ответа','config'=>'content_kk.rsvp_hint','default'=>$templateTextDefaults['rsvp_hint'],'type'=>'textarea','wide'=>true,'required'=>true,'maxlength'=>300],
'content_closing_text'=>['label'=>'Финальная фраза','config'=>'content_kk.closing_text','default'=>$templateTextDefaults['closing_text'],'wide'=>true,'required'=>true],
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
'address'=>['label'=>'Адрес','required'=>true],'two_gis_url'=>['label'=>'Ссылка 2GIS','type'=>'url','wide'=>true],'phone'=>['label'=>'Телефон','type'=>'tel'],
'bonus_percent'=>['label'=>'Бонус партнёру с оплаченного заказа, %','type'=>'number','min'=>0,'max'=>100,'step'=>'0.01','default'=>0,'hint'=>'Начисляется один раз после подтверждения оплаты приглашения.'],
]],
];
@endphp
@foreach($groups as $groupKey => $group)
<section class="admin-section" id="{{ $groupKey }}"><h2>{{ $group['title'] }}</h2><p class="hint">{{ $group['hint'] }}</p>
<div class="admin-grid"><div class="admin-editor"><h3>Добавить</h3><form method="POST" action="{{ route('admin.store.'.$group['route']) }}" @if(in_array($groupKey, ['templates', 'music'])) enctype="multipart/form-data" @endif>@csrf
@include('admin.store-fields', ['fields'=>$group['fields'],'record'=>null,'editor'=>$groupKey.'-new'])
</form></div><div class="stack">
@forelse($group['records'] as $record)<details class="admin-editor"><summary><span>{{ $record->name ?? $record->code }} @if($groupKey==='templates') · {{ number_format($record->price,0,',',' ') }} ₸ @endif @if($groupKey==='music') · {{ $record->categoryLabel() }} @endif @if($groupKey==='promos') · {{ $record->value }}{{ $record->type === 'percent' ? '%' : ' ₸' }} · {{ $record->uses }}/{{ $record->max_uses ?? '∞' }} @endif @if($groupKey==='restaurants' ? $record->status !== 'active' : !$record->is_active) · Неактивен @endif</span></summary>
@if($groupKey === 'templates')<div class="admin-template-preview">@if($record->preview_image)<img src="{{ str_starts_with($record->preview_image, '/') ? asset(ltrim($record->preview_image, '/')) : $record->preview_image }}" alt="Обложка {{ $record->name }}">@endif<div><strong>{{ $record->name }}</strong><a href="{{ route('store.preview', $record) }}" target="_blank" rel="noopener">Открыть превью ↗</a></div></div>@endif
<form method="POST" action="{{ route('admin.store.'.$group['route'], $record) }}" @if(in_array($groupKey, ['templates', 'music'])) enctype="multipart/form-data" @endif>@csrf
@include('admin.store-fields', ['fields'=>$group['fields'],'record'=>$record,'editor'=>$groupKey.'-'.$record->id])
</form></details>@empty<p class="hint">Записей пока нет.</p>@endforelse</div></div>
</section>@endforeach</div>
@endsection
