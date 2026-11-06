@extends('layouts.zharzhar')
@section('content')
<main style="max-width:480px;margin:8vh auto;padding:0 16px">
    <div class="card" style="padding:28px">
        <h1 style="margin-top:0">Регистрация персонала</h1>
        <p class="muted">Создайте профиль официанта, чтобы откликаться на смены ресторанов.</p>
        @if($errors->any())<p class="error" role="alert">{{ $errors->first() }}</p>@endif
        <form method="POST" action="{{ route('staff.register.store') }}">@csrf
            <label>Имя и фамилия<input name="name" value="{{ old('name') }}" autocomplete="name" required maxlength="100" style="width:100%;padding:12px;margin:8px 0 16px;box-sizing:border-box"></label>
            <label>Email<input name="email" type="email" value="{{ old('email') }}" autocomplete="email" required style="width:100%;padding:12px;margin:8px 0 16px;box-sizing:border-box"></label>
            <label>Телефон<input name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" required style="width:100%;padding:12px;margin:8px 0 16px;box-sizing:border-box"></label>
            <label>Пароль<input name="password" type="password" autocomplete="new-password" required minlength="8" style="width:100%;padding:12px;margin:8px 0 16px;box-sizing:border-box"></label>
            <label>Повторите пароль<input name="password_confirmation" type="password" autocomplete="new-password" required minlength="8" style="width:100%;padding:12px;margin:8px 0 16px;box-sizing:border-box"></label>
            <button class="button" type="submit">Создать профиль</button>
        </form>
        <p><a href="{{ route('login') }}">Уже есть аккаунт? Войти</a></p>
    </div>
</main>
@endsection
