@extends('layouts.store')
@section('content')
<section class="hero shell">
<div><p class="eyebrow">ОНЛАЙН-ПРИГЛАШЕНИЯ С ХАРАКТЕРОМ</p><h1>Большой день.<br><em>Красивое начало.</em></h1><p class="lead">Свадьба, юбилей или день рождения — начните свой праздник с приглашения, которое хочется сохранить.</p><div class="actions"><a class="button primary" href="#designs">Выбрать приглашение <span>↗</span></a><span class="price-note">от <strong>7 990 ₸</strong><small>Один праздник. Одна оплата.</small></span></div><p class="hero-footnote">Ваши имена · Любимая музыка · Ответы гостей</p></div>
<div class="hero-art" aria-label="Пример оформления свадебного приглашения"><span class="orbit-label">БЛИЗКИЕ ЛЮДИ. ОСОБЕННЫЕ МОМЕНТЫ.</span><div class="paper theme-sage"><span class="paper-kicker">ҮЙЛЕНУ ТОЙҒА ШАҚЫРУ</span><span class="ornament" aria-hidden="true">❧</span><span class="paper-script">Алихан<br><i>&</i> Аружан</span><span class="paper-date">12 · 09 · 2026</span><span class="paper-footer">БІЗДІҢ ҚУАНЫШЫМЫЗҒА ОРТАҚ БОЛЫҢЫЗ</span></div><span class="art-tag">С любовью, для ваших близких</span></div>
</section>
<div class="occasion-band"><span>Үйлену той</span><b>✦</b><span>Мерейтой</span><b>✦</b><span>Туған күн</span><b>✦</b><span>Ваш особенный день</span></div>
<section class="shell section" id="designs"><div class="section-title"><div><p class="eyebrow">КОЛЛЕКЦИЯ ПРИГЛАШЕНИЙ</p><h2>Найдите <em>свой дизайн</em></h2></div><p>Каждый дизайн — готовый сайт<br>с музыкой и ответами гостей.</p></div>
<nav class="filters" aria-label="Тип события"><a class="{{ !$category ? 'active' : '' }}" href="{{ route('store.catalog') }}#designs">Все события</a>@foreach(config('store.event_types') as $key => $label)<a class="{{ $category === $key ? 'active' : '' }}" href="{{ route('store.catalog', ['event' => $key]) }}#designs">{{ explode(' / ', $label)[0] }}</a>@endforeach</nav>
<div class="catalog-grid">@forelse($templates as $template)<article class="design-card">
<a href="{{ route('store.preview', $template) }}" class="design-preview theme-{{ $template->config_json['theme'] ?? 'sage' }}" aria-label="Посмотреть дизайн {{ $template->name }}">
@if($template->preview_image)<img src="{{ $template->preview_image }}" alt="{{ $template->name }}" loading="lazy">@else
<span class="mini-kicker">{{ $template->event_type === 'wedding' ? 'БІЗ ҮЙЛЕНЕМІЗ' : 'ОСОБЕННЫЙ ДЕНЬ' }}</span><span class="ornament" aria-hidden="true">❧</span><strong>{{ $template->event_type === 'wedding' ? 'Алихан & Аружан' : ($template->event_type === 'anniversary' ? 'Мерейтой' : 'Туған күн') }}</strong><span class="mini-date">12 / 09 / 2026</span>
@endif<span class="preview-pill">Посмотреть пример ↗</span></a>
<div class="design-info"><div><p class="eyebrow">{{ $template->event_type ? explode(' / ', config('store.event_types')[$template->event_type] ?? $template->event_type)[0] : 'Для любого события' }}</p><h3>{{ $template->name }}</h3></div><strong class="design-price">{{ number_format($template->price, 0, ',', ' ') }} ₸</strong></div><a class="button outline full" href="{{ route('store.checkout', $template) }}">Выбрать дизайн <span>→</span></a>
</article>@empty<div class="empty-state"><h3>Коллекция готовится</h3><p>Скоро здесь появятся дизайны для вашего праздника.</p><a href="tel:{{ preg_replace('/[^+0-9]/', '', config('store.kaspi_phone')) }}">Связаться с нами</a></div>@endforelse</div></section>
<section class="how-section" id="how"><div class="shell section"><p class="eyebrow">ОТ ИДЕИ ДО ПРИГЛАШЕНИЯ</p><h2>Всего несколько <em>простых шагов</em></h2><div class="steps-grid">
@foreach([['Выберите дизайн', 'Найдите настроение вашего праздника. Цена указана на каждой карточке.'], ['Расскажите о событии', 'Укажите имена, дату, ресторан, той иелері и выберите музыку.'], ['Оплатите через Kaspi', 'Переведите сумму по номеру телефона и сообщите об оплате на странице заказа.'], ['Пригласите близких', 'После нашей проверки получите приглашение и личную ссылку на ответы гостей.']] as $step)<article><span class="step-number">0{{ $loop->iteration }}</span><h3>{{ $step[0] }}</h3><p>{{ $step[1] }}</p></article>@endforeach
</div></div></section>
<section class="shell section faq" id="faq"><div><p class="eyebrow">ВСЁ, ЧТО НУЖНО ЗНАТЬ</p><h2>Маленькие детали.<br><em>Большой праздник.</em></h2></div><div>
<details><summary>Что входит в стоимость?</summary><p>Персональное приглашение по выбранному дизайну, музыка из библиотеки, информация о празднике и отдельная страница с ответами гостей.</p></details>
<details><summary>Как оплатить?</summary><p>Пока принимаем переводы Kaspi на {{ config('store.kaspi_phone') }}. Точная сумма и инструкция появятся после оформления. Оплату проверяет администратор.</p></details>
<details><summary>Где получить ссылки после оплаты?</summary><p>Сохраните личную ссылку на заказ. После подтверждения здесь появятся две ссылки: для гостей и для просмотра их ответов. Администратор также сможет скопировать их и отправить вам.</p></details>
<details><summary>Как применить промокод?</summary><p>Введите код при оформлении и нажмите «Применить». Скидку и итоговую сумму увидите до создания заказа.</p></details>
</div></section>
@endsection
