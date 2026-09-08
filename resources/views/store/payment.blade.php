@php
    $kk = app()->isLocale('kk');
    $t = $kk ? [
        'title' => 'Тапсырыс №', 'catalog' => 'Каталог', 'ready' => 'Мерекеге бәрі дайын!', 'almost' => 'Шақыруыңыз дайын болуға жақын',
        'order' => 'Тапсырыс', 'save' => 'Тапсырыс сілтемесін сақтаңыз',
        'save_hint' => 'Осы жерден мәртебені тексеріп, дайын сілтемелерді аласыз. Бұл жеке парақша — қонақтарға жібермеңіз.',
        'copy' => 'Көшіру', 'personal_order' => 'Тапсырыстың жеке сілтемесі', 'paid' => 'ТӨЛЕМ РАСТАЛДЫ',
        'invite' => 'Жақындарыңызды шақырыңыз', 'send_link' => 'Бұл сілтемені қонақтарға жіберіңіз:',
        'invite_link' => 'Шақыру сілтемесі', 'open_invite' => 'Шақыруды ашу', 'answers' => 'Қонақтардың жауаптары — тек сіз үшін',
        'answers_hint' => 'Бұл сілтемені құпия сақтаңыз. Сілтемесі бар адам қонақтар тізімін көре алады.',
        'answers_link' => 'Жауаптардың жеке сілтемесі', 'view_answers' => 'Жауаптарды көру',
        'rejected' => 'Тапсырыс қабылданбады', 'help' => 'WhatsApp арқылы хабарласып, №:id тапсырыс нөмірін көрсетіңіз.',
        'write_whatsapp' => 'WhatsApp-қа жазу', 'payment' => 'УАҚЫТША KASPI АУДАРЫМЫ', 'design' => 'Дизайн',
        'promo' => 'Промокод', 'kaspi' => 'Kaspi Pay жақында қосылады. Әзірге соманы Kaspi қолданбасында мына нөмірге аударыңыз:',
        'recipient' => 'Алушы', 'copy_number' => 'Нөмірді көшіру', 'check_number' => 'Төлем алдында алушы «Айдын Б.» екенін тексеріңіз.',
        'free' => 'Промокод толық соманы жапты. Аударым жасау қажет емес.',
        'paid_whatsapp' => 'Төледім — WhatsApp-қа жазу', 'order_whatsapp' => 'Тапсырыс туралы WhatsApp-қа жазу',
        'whatsapp_hint' => '№:id тапсырыс нөмірі жазылған дайын хабарлама ашылады. Сізге тек «Жіберу» батырмасын басу қалады.',
        'review' => '№:id тапсырыс тексеруге жіберілді. Расталғаннан кейін дайын сілтемелер осында шығады.', 'refresh' => 'Мәртебені жаңарту',
    ] : [
        'title' => 'Заказ №', 'catalog' => 'Каталог', 'ready' => 'Всё готово к празднику!', 'almost' => 'Ваше приглашение почти готово',
        'order' => 'Заказ', 'save' => 'Сохраните ссылку на заказ',
        'save_hint' => 'Здесь можно проверить статус и забрать готовые ссылки. Эта страница личная — не отправляйте её гостям.',
        'copy' => 'Копировать', 'personal_order' => 'Личная ссылка на заказ', 'paid' => 'ОПЛАТА ПОДТВЕРЖДЕНА',
        'invite' => 'Пригласите тех, кто дорог', 'send_link' => 'Отправьте эту ссылку гостям:',
        'invite_link' => 'Ссылка на приглашение', 'open_invite' => 'Открыть приглашение', 'answers' => 'Ответы гостей — только для вас',
        'answers_hint' => 'Храните эту ссылку в секрете. Любой, у кого она есть, сможет увидеть список гостей.',
        'answers_link' => 'Личная ссылка на ответы', 'view_answers' => 'Посмотреть ответы',
        'rejected' => 'Заказ отклонён', 'help' => 'Свяжитесь с нами в WhatsApp и укажите номер заказа №:id.',
        'write_whatsapp' => 'Написать в WhatsApp', 'payment' => 'ВРЕМЕННАЯ ОПЛАТА ЧЕРЕЗ KASPI', 'design' => 'Дизайн',
        'promo' => 'Промокод', 'kaspi' => 'Kaspi Pay скоро появится. Пока переведите сумму в приложении Kaspi на номер:',
        'recipient' => 'Получатель', 'copy_number' => 'Скопировать номер', 'check_number' => 'Перед оплатой убедитесь, что получатель — «Айдын Б.».',
        'free' => 'Промокод покрыл полную стоимость. Перевод не требуется.',
        'paid_whatsapp' => 'Я оплатил — написать в WhatsApp', 'order_whatsapp' => 'Написать в WhatsApp о заказе',
        'whatsapp_hint' => 'Откроется готовое сообщение с номером заказа №:id. Вам останется только нажать «Отправить».',
        'review' => 'Заявка №:id отправлена на проверку. После подтверждения здесь появятся готовые ссылки.', 'refresh' => 'Обновить статус',
    ];
    $replaceId = fn (string $text) => str_replace(':id', $order->id, $text);
@endphp

@extends('layouts.store', ['title' => $t['title'].$order->id.' — ZharZhar'])
@section('content')
<div class="shell narrow"><div class="page-heading"><a class="breadcrumb" href="{{ route('store.catalog') }}">← {{ $t['catalog'] }}</a><h1>{{ $order->status === 'paid' ? $t['ready'] : $t['almost'] }}</h1><span class="badge badge-{{ $order->status }}">{{ $order->statusLabel() }}</span><p class="hint">{{ $t['order'] }} №{{ $order->id }} · {{ $order->details['template_name'] }} · {{ $order->details['names'] }}</p></div>
<div class="stack"><section class="panel"><h3>{{ $t['save'] }}</h3><p class="hint">{{ $t['save_hint'] }}</p><div class="copy-row"><input aria-label="{{ $t['personal_order'] }}" readonly value="{{ url()->current() }}"><button type="button" class="button outline" data-copy="{{ url()->current() }}">{{ $t['copy'] }}</button></div></section>
@if($order->status === 'paid')
<section class="panel"><p class="eyebrow">{{ $t['paid'] }}</p><h3>{{ $t['invite'] }}</h3><p>{{ $t['send_link'] }}</p><div class="copy-row"><input aria-label="{{ $t['invite_link'] }}" readonly value="{{ $order->publicUrl('i/'.$order->invitation->slug) }}"><button type="button" class="button outline" data-copy="{{ $order->publicUrl('i/'.$order->invitation->slug) }}">{{ $t['copy'] }}</button></div><p><a class="button primary" href="{{ $order->publicUrl('i/'.$order->invitation->slug) }}">{{ $t['open_invite'] }} ↗</a></p><h3>{{ $t['answers'] }}</h3><p class="hint">{{ $t['answers_hint'] }}</p><div class="copy-row"><input aria-label="{{ $t['answers_link'] }}" readonly value="{{ $order->publicUrl('responses/'.$order->responses_token) }}"><button type="button" class="button outline" data-copy="{{ $order->publicUrl('responses/'.$order->responses_token) }}">{{ $t['copy'] }}</button></div><p><a href="{{ $order->publicUrl('responses/'.$order->responses_token) }}">{{ $t['view_answers'] }} →</a></p></section>
@elseif($order->status === 'rejected')
<section class="panel"><h3>{{ $t['rejected'] }}</h3><p>{{ $order->admin_note }}</p><p>{{ $replaceId($t['help']) }}</p><a class="button outline" href="https://wa.me/{{ preg_replace('/\D+/', '', config('store.whatsapp_phone')) }}?text={{ rawurlencode(($kk ? 'Сәлеметсіз бе! ZharZhar №' : 'Здравствуйте! Нужна помощь с заказом ZharZhar №').$order->id.($kk ? ' тапсырысы бойынша көмек қажет.' : '.')) }}">{{ $t['write_whatsapp'] }}</a></section>
@else
<section class="panel payment-panel"><p class="eyebrow">{{ $t['payment'] }}</p><div class="summary-row"><span>{{ $t['design'] }}</span><strong>{{ number_format($order->subtotal, 0, ',', ' ') }} ₸</strong></div>@if($order->discount)<div class="summary-row"><span>{{ $t['promo'] }} {{ $order->promo_code }}</span><strong>−{{ number_format($order->discount, 0, ',', ' ') }} ₸</strong></div>@endif<p class="payment-amount">{{ number_format($order->total, 0, ',', ' ') }} ₸</p>
@if($order->total > 0)<p>{{ $t['kaspi'] }}</p><p class="payment-phone">{{ config('store.kaspi_phone') }}</p><p class="payment-recipient"><span>{{ $t['recipient'] }}</span><strong>{{ config('store.kaspi_recipient') }}</strong></p><button type="button" class="button outline" data-copy="{{ preg_replace('/[^+0-9]/', '', config('store.kaspi_phone')) }}">{{ $t['copy_number'] }}</button><p class="hint">{{ $t['check_number'] }}</p>@else<p>{{ $t['free'] }}</p>@endif
@if($order->status === 'pending')<form method="POST" action="{{ route('store.payment.submit', $order->token) }}" data-submit-once>@csrf<button class="button whatsapp-button full" type="submit"><span aria-hidden="true">◉</span>{{ $order->total > 0 ? $t['paid_whatsapp'] : $t['order_whatsapp'] }}</button></form><p class="hint whatsapp-hint">{{ $replaceId($t['whatsapp_hint']) }}</p>@else<p class="notice">{{ $replaceId($t['review']) }}</p><a class="button outline" href="{{ route('store.payment', $order->token) }}">{{ $t['refresh'] }}</a>@endif
</section>@endif</div></div>
@endsection
