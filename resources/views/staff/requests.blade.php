<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Смены для персонала — ZharZhar</title>
    <style>
        :root{font-family:Inter,system-ui,-apple-system,sans-serif;color:#17212f;background:#f4f7fb}*{box-sizing:border-box}body{margin:0}main{max-width:860px;margin:auto;padding:24px 16px 72px}h1{font-size:clamp(28px,5vw,40px);margin:12px 0}h2{font-size:21px;margin:0 0 6px}p{line-height:1.5}.muted{color:#667085}.header-row{display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}.account{display:flex;align-items:center;gap:12px;flex-wrap:wrap}.account form{margin:0}.account button{border:0;background:none;color:#2563eb;font:inherit;cursor:pointer}.button{display:inline-block;border:0;border-radius:10px;background:#2563eb;color:#fff;padding:12px 16px;font:inherit;font-weight:700;text-decoration:none;cursor:pointer}.button:hover{background:#174bc4}.notice{padding:14px 18px;border-radius:12px;background:#e9f8ee;color:#17663a}.error{padding:14px 18px;border-radius:12px;background:#fff0ee;color:#b42318}.job{background:#fff;border:1px solid #e3e8ef;border-radius:18px;padding:22px;margin:16px 0;box-shadow:0 12px 32px rgba(24,39,75,.05)}.job-meta{display:flex;flex-wrap:wrap;gap:8px;margin:16px 0}.job-meta span{padding:7px 10px;border-radius:8px;background:#eef3ff;color:#234691;font-weight:650;font-size:13px}.job form{display:grid;gap:10px;margin-top:18px}.job textarea{width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:9px;font:inherit;resize:vertical}.section-title{margin-top:32px}
    </style>
</head>
<body><main>
    <header>
        <div class="header-row"><a href="{{ route('store.catalog') }}">← ZharZhar</a><div class="account">
            @auth
                <span>{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Выйти</button></form>
            @else
                <a href="{{ route('login') }}">Войти</a><a class="button" href="{{ route('staff.register') }}">Стать персоналом</a>
            @endauth
        </div></div>
        <h1>Смены для персонала</h1>
        <p class="muted">Выберите подходящую дату и откликнитесь. Ресторан свяжется с вами по телефону.</p>
    </header>
    @if(session('success'))<p class="notice" role="status">{{ session('success') }}</p>@endif
    @if($errors->any())<p class="error" role="alert">{{ $errors->first() }}</p>@endif
    @if(!$staffEnabled)<p class="error" role="status">Смены временно недоступны. Администратору нужно выполнить миграции базы данных.</p>@endif

    @if($myApplications->isNotEmpty())
        <h2 class="section-title">Мои отклики</h2>
        @foreach($myApplications as $application)
            <article class="job"><h2>{{ $application->staffRequest?->restaurant?->name ?? 'Ресторан' }}</h2>
                <p class="muted">{{ $application->staffRequest?->event_date?->format('d.m.Y') }} · {{ match($application->status) {'accepted' => 'Принят', 'rejected' => 'Отклонён', default => 'На рассмотрении'} }}</p>
            </article>
        @endforeach
    @endif

    <h2 class="section-title">Открытые заявки</h2>
    @forelse($requests as $job)
        <article class="job" id="job-{{ $job->id }}"><h2>{{ $job->restaurant->name }}</h2><p class="muted">{{ $job->restaurant->city }}</p>
            <div class="job-meta"><span>{{ $job->event_date->format('d.m.Y') }}</span><span>{{ substr($job->starts_at, 0, 5) }}–{{ substr($job->ends_at, 0, 5) }}</span><span>{{ number_format($job->pay_per_worker, 0, ',', ' ') }} ₸ за смену</span><span>{{ max(0, $job->workers_needed - $job->accepted_count) }} мест</span></div>
            @if($job->description)<p>{{ $job->description }}</p>@endif
            @if(auth()->user()?->isRole('staff'))
                @if($myApplications->contains('staff_request_id', $job->id))
                    <p class="notice">Вы уже откликнулись на эту смену.</p>
                @else
                    <form method="POST" action="{{ route('staff.requests.apply', $job) }}">@csrf<textarea name="message" rows="2" maxlength="500" placeholder="Ваш опыт или вопрос (необязательно)" aria-label="Сообщение">{{ old('message') }}</textarea><button class="button" type="submit">Откликнуться</button></form>
                @endif
            @elseif(!auth()->check())
                <a class="button" href="{{ route('staff.register') }}">Создать профиль и откликнуться</a>
            @else
                <p class="muted">Откликаться на смены можно из аккаунта персонала.</p>
            @endif
        </article>
    @empty<p class="job muted">Сейчас открытых смен нет. Загляните позже.</p>@endforelse
    {{ $requests->links() }}
</main></body></html>
