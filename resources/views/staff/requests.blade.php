<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Работа официантом — ZharZhar</title>
    <style>
        :root{font-family:Inter,system-ui,-apple-system,sans-serif;color:#17212f;background:#f4f7fb}*{box-sizing:border-box}body{margin:0}main{max-width:850px;margin:auto;padding:28px 16px 70px}header{margin-bottom:26px}h1{font-size:clamp(28px,5vw,40px);margin:10px 0}p{line-height:1.5}.muted{color:#667085}.notice{padding:14px 18px;border-radius:12px;background:#e9f8ee;color:#17663a}.error{color:#b42318}.job{background:#fff;border:1px solid #e3e8ef;border-radius:18px;padding:22px;margin:16px 0;box-shadow:0 12px 32px rgba(24,39,75,.05)}.job h2{margin:0 0 4px;font-size:22px}.job-meta{display:flex;flex-wrap:wrap;gap:8px;margin:16px 0}.job-meta span{padding:7px 10px;border-radius:8px;background:#eef3ff;color:#234691;font-weight:650;font-size:13px}.job form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:18px}.job input,.job textarea{width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:9px;font:inherit}.job textarea{grid-column:1/-1;resize:vertical}.job button{grid-column:1/-1;border:0;border-radius:9px;background:#2563eb;color:#fff;padding:13px;font:inherit;font-weight:700;cursor:pointer}.job button:hover{background:#174bc4}@media(max-width:560px){.job form{grid-template-columns:1fr}}
    </style>
</head>
<body><main>
    <header><a href="{{ route('store.catalog') }}">← ZharZhar</a><h1>Открытые смены для официантов</h1><p class="muted">Выберите подходящую дату и откликнитесь. Ресторан свяжется с вами по телефону.</p></header>
    @if(session('success'))<p class="notice" role="status">{{ session('success') }}</p>@endif
    @if($errors->any())<p class="error" role="alert">{{ $errors->first() }}</p>@endif
    @forelse($requests as $job)
        <article class="job" id="job-{{ $job->id }}"><h2>{{ $job->restaurant->name }}</h2><p class="muted">{{ $job->restaurant->city }}</p>
            <div class="job-meta"><span>{{ $job->event_date->format('d.m.Y') }}</span><span>{{ substr($job->starts_at, 0, 5) }}–{{ substr($job->ends_at, 0, 5) }}</span><span>{{ number_format($job->pay_per_worker, 0, ',', ' ') }} ₸ за смену</span><span>{{ max(0, $job->workers_needed - $job->accepted_count) }} мест</span></div>
            @if($job->description)<p>{{ $job->description }}</p>@endif
            <form method="POST" action="{{ route('staff.requests.apply', $job) }}">@csrf<input name="name" value="{{ old('name') }}" maxlength="100" placeholder="Ваше имя" aria-label="Ваше имя" required><input name="phone" value="{{ old('phone') }}" type="tel" maxlength="30" placeholder="Номер телефона" aria-label="Номер телефона" required><textarea name="message" rows="2" maxlength="500" placeholder="Опыт работы или вопрос (необязательно)" aria-label="Сообщение">{{ old('message') }}</textarea><button type="submit">Откликнуться</button></form>
        </article>
    @empty<p class="job muted">Сейчас открытых смен нет. Загляните позже.</p>@endforelse
    {{ $requests->links() }}
</main></body></html>
