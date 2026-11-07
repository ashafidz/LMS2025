@extends('layouts.plain-layout')

@section('title', 'Masuk')
@section('art-image', 'rs-03-masuk')
@section('art-title', 'Masuk untuk melanjutkan belajar.')
@section('art-text', 'Kursus, kuis, dan sertifikatmu menunggu di satu tempat.')

@section('content')
<div class="rs-auth">
    <h1 class="rs-h2">Masuk</h1>
    <p class="rs-auth__lead">Masuk untuk melanjutkan belajar.</p>

    @if ($errors->any())
        <div class="rs-alert rs-alert--danger" role="alert">{{ $errors->first() }}</div>
    @endif

    @if (session('status'))
        <div class="rs-alert rs-alert--ok" role="status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="rs-form"@if (\App\Services\Recaptcha::enabled()) data-recaptcha-site-key="{{ config('services.recaptcha.site_key') }}" data-recaptcha-action="login"@endif>
        @csrf

        <fieldset class="rs-choice">
            <legend>Masuk sebagai</legend>
            <div class="rs-choice__seg">
                <label><input type="radio" name="login_preference" value="student" @checked(old('login_preference', 'student') !== 'instructor')> Siswa</label>
                <label><input type="radio" name="login_preference" value="instructor" @checked(old('login_preference') === 'instructor')> Instruktur</label>
            </div>
        </fieldset>

        <input type="hidden" name="timezone" id="user_timezone">

        <div class="rs-field">
            <label for="email">Email</label>
            <input id="email" class="rs-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
            @error('email')
                <p id="email-error" class="rs-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="rs-field">
            <label for="password">Kata sandi</label>
            <div class="rs-password">
                <input id="password" class="rs-input" type="password" name="password" required autocomplete="current-password"
                    @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                <button type="button" data-rs-toggle-password="password" aria-label="Tampilkan kata sandi" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
            </div>
            @error('password')
                <p id="password-error" class="rs-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="rs-auth__row">
            <label class="rs-check"><input type="checkbox" name="remember"><span>Ingat saya</span></label>
            @if (Route::has('password.request'))
                <a class="rs-link" href="{{ route('password.request') }}">Lupa kata sandi?</a>
            @endif
        </div>

        <button type="submit" class="rs-btn rs-btn--block">Masuk</button>

        @if (\App\Services\Recaptcha::enabled())
            <input type="hidden" name="recaptcha-response" value="">
            <p class="rs-form-legal">Form ini dilindungi reCAPTCHA. Berlaku <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Kebijakan Privasi</a> dan <a href="https://policies.google.com/terms" target="_blank" rel="noopener">Persyaratan Layanan</a> Google.</p>
            <p class="rs-form-legal" data-recaptcha-status role="status" hidden></p>
        @endif
    </form>

    <p class="rs-auth__alt">Belum punya akun? <a class="rs-link" href="{{ route('register') }}">Daftar</a></p>
</div>
@endsection

@if (\App\Services\Recaptcha::enabled())
    @push('scripts')
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
        <script src="{{ asset('js/rs-recaptcha.js') }}"></script>
    @endpush
@endif
