@extends('layouts.zharzhar', ['title' => 'Сотрудники — ZharZhar'])
@section('content')
<link rel="stylesheet" href="{{ asset('broker-extra.css') }}">
<header><div class="brand">Сотрудники · Broker</div><a class="button secondary" href="{{ route('admin.dashboard') }}">В админку</a></header>
@if(session('success'))<p class="success">{{ session('success') }}</p>@endif
@if($errors->any())<p class="error">{{ $errors->first() }}</p>@endif
<p>Кабинет сотрудника: <a href="https://{{ config('store.broker_domain') }}/login">{{ config('store.broker_domain') }}</a></p>
<div class="grid">
<section class="card"><h2>Добавить сотрудника</h2><form class="form-stack" method="post" action="{{ route('admin.brokers.store') }}">@csrf<label>Имя<input name="name" required maxlength="150" value="{{ old('name') }}"></label><label>Email<input type="email" name="email" required value="{{ old('email') }}"></label><fieldset><legend>Доступные города</legend><div class="checkbox-grid">@foreach($cities as $city)<label><input type="checkbox" name="cities[]" value="{{ $city }}" @checked(in_array($city, old('cities', ['Атырау'])))> {{ $city }}</label>@endforeach</div></fieldset><label>Пароль<input name="password" type="password" minlength="8" required autocomplete="new-password"></label><label>Повторите пароль<input name="password_confirmation" type="password" minlength="8" required autocomplete="new-password"></label><button class="button">Создать доступ</button></form></section>
<section class="card"><h2>Сотрудники</h2>@forelse($staff as $member)<article class="staff-card"><p><strong>{{ $member->name }}</strong><br>{{ $member->email }}</p><form class="form-stack" method="post" action="{{ route('admin.brokers.update', $member) }}">@csrf @method('PUT')<fieldset><legend>Города сотрудника</legend><div class="checkbox-grid">@php($selected = $member->broker_cities ?: array_filter([$member->broker_city]))@foreach($cities as $city)<label><input type="checkbox" name="cities[]" value="{{ $city }}" @checked(in_array($city, $selected))> {{ $city }}</label>@endforeach</div></fieldset><button class="button secondary">Сохранить города</button></form></article>@empty<p>Сотрудники ещё не добавлены.</p>@endforelse{{ $staff->links() }}</section>
</div>
@endsection
