@extends('layouts.plain-layout')

@section('title', 'Buat kata sandi baru')

@section('content')
<div class="rs-auth">
    <h1 class="rs-h2">Buat kata sandi baru</h1>
    <p class="rs-auth__lead">Tulis kata sandi baru untuk akunmu.</p>

    @if (session('status'))
        <div class="rs-alert rs-alert--ok" role="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="rs-alert rs-alert--danger" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="rs-form">
        @csrf

        {{-- Password Reset Token --}}
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        {{-- Email Address --}}
        <div class="rs-field">
            <label for="email">Email</label>
            <input id="email" class="rs-input" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus readonly autocomplete="email">
        </div>

        <div class="rs-field">
            <label for="password">Kata sandi baru</label>
            <div class="rs-password">
                <input id="password" class="rs-input" type="password" name="password" required autocomplete="new-password">
                <button type="button" data-rs-toggle-password="password" aria-label="Tampilkan kata sandi" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
            </div>
        </div>

        <div class="rs-field">
            <label for="password_confirmation">Ulangi kata sandi baru</label>
            <div class="rs-password">
                <input id="password_confirmation" class="rs-input" type="password" name="password_confirmation" required autocomplete="new-password">
                <button type="button" data-rs-toggle-password="password_confirmation" aria-label="Tampilkan kata sandi" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
            </div>
        </div>

        <button type="submit" class="rs-btn rs-btn--block">Simpan kata sandi baru</button>
    </form>
</div>
@endsection
