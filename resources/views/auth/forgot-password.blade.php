@extends('layouts.plain-layout')

@section('title', 'Lupa kata sandi')
@section('art-image', 'rs-05-lupa-sandi')
@section('art-title', 'Akun Ruang Studi')
@section('art-text', 'Kami kirim tautan ke email kamu untuk membuat kata sandi baru.')

@section('content')
<div class="rs-auth">
    <h1 class="rs-h2">Lupa kata sandi</h1>
    <p class="rs-auth__lead">Tidak masalah. Tulis alamat email kamu, kami akan mengirim tautan untuk membuat kata sandi baru.</p>

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

    <form method="POST" action="{{ route('password.email') }}" class="rs-form"@if (\App\Services\Recaptcha::enabled()) data-recaptcha-site-key="{{ config('services.recaptcha.site_key') }}" data-recaptcha-action="forgot_password"@endif>
        @csrf

        <div class="rs-field">
            <label for="email">Email</label>
            <input id="email" class="rs-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
        </div>

        <button type="submit" class="rs-btn rs-btn--block">Kirim tautan atur ulang</button>

        @if (\App\Services\Recaptcha::enabled())
            <input type="hidden" name="recaptcha-response" value="">
            <p class="rs-form-legal">Form ini dilindungi reCAPTCHA. Berlaku <a href="https://policies.google.com/privacy" target="_blank" rel="noopener">Kebijakan Privasi</a> dan <a href="https://policies.google.com/terms" target="_blank" rel="noopener">Persyaratan Layanan</a> Google.</p>
            <p class="rs-form-legal" data-recaptcha-status role="status" hidden></p>
        @endif
    </form>

    <p class="rs-auth__alt"><a class="rs-link" href="{{ route('login') }}">Kembali ke halaman masuk</a></p>
</div>
@endsection

@if (\App\Services\Recaptcha::enabled())
    @push('scripts')
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
        <script src="{{ asset('js/rs-recaptcha.js') }}"></script>
    @endpush
@endif
