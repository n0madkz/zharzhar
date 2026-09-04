@extends('layouts.zharzhar')
@section('content')
<main style="max-width:460px;margin:12vh auto"><div class="card"><div class="brand">Жар-Жар</div><p class="muted">Вход в рабочий кабинет</p><form method="POST" action="{{ route('login.store') }}">@csrf<label>Email<br><input name="email" type="email" value="{{ old('email') }}" required style="width:100%;padding:12px;margin:8px 0 16px;box-sizing:border-box"></label><label>Пароль<br><input name="password" type="password" required style="width:100%;padding:12px;margin:8px 0 16px;box-sizing:border-box"></label>@error('email')<div class="error">{{ $message }}</div>@enderror<button class="button" type="submit">Войти</button></form></div></main>
@endsection
