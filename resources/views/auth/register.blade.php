@extends('layouts.plain-layout')

@section('title', 'Daftar')

@section('content')
<div class="rs-auth rs-auth--wide">
    <h1 class="rs-h2">Daftar</h1>
    <p class="rs-auth__lead">Buat akun untuk mulai belajar atau mengajar.</p>

    @if ($errors->any())
        <div class="rs-alert rs-alert--danger" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}" class="rs-form">
        @csrf

        <fieldset class="rs-choice">
            <legend>Daftar sebagai</legend>
            <label><input type="radio" name="role" value="student" @checked(old('role', 'student') !== 'instructor')> Siswa</label>
            <label><input type="radio" name="role" value="instructor" @checked(old('role') === 'instructor')> Instruktur</label>
        </fieldset>

        <div class="rs-field">
            <label for="name">Nama lengkap</label>
            <input id="name" class="rs-input" type="text" name="name" value="{{ old('name') }}" required autocomplete="name">
        </div>

        <div class="rs-field">
            <label for="email">Email</label>
            <input id="email" class="rs-input" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
        </div>

        <div id="instructor-fields" class="rs-form" hidden>
            <div class="rs-field">
                <label for="headline">Posisi atau jabatan</label>
                <input id="headline" class="rs-input" type="text" name="headline" value="{{ old('headline') }}">
                <p class="rs-hint">Contoh: Guru Matematika</p>
            </div>
            <div class="rs-field">
                <label for="website_url">Alamat website</label>
                <input id="website_url" class="rs-input" type="url" name="website_url" value="{{ old('website_url') }}">
                <p class="rs-hint">Wajib untuk instruktur. Contoh: https://sekolahku.sch.id</p>
            </div>
        </div>

        <div class="rs-form__row">
            <div class="rs-field">
                <label for="password">Kata sandi</label>
                <div class="rs-password">
                    <input id="password" class="rs-input" type="password" name="password" required autocomplete="new-password">
                    <button type="button" data-rs-toggle-password="password" aria-label="Tampilkan kata sandi" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
                </div>
                <p class="rs-hint">Minimal 8 karakter.</p>
            </div>
            <div class="rs-field">
                <label for="password_confirmation">Ulangi kata sandi</label>
                <div class="rs-password">
                    <input id="password_confirmation" class="rs-input" type="password" name="password_confirmation" required autocomplete="new-password">
                    <button type="button" data-rs-toggle-password="password_confirmation" aria-label="Tampilkan kata sandi" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
                </div>
            </div>
        </div>

        <button type="submit" class="rs-btn rs-btn--block">Daftar Sekarang</button>
    </form>

    <p class="rs-auth__alt">Sudah punya akun? <a class="rs-link" href="{{ route('login') }}">Masuk</a></p>
</div>
@endsection
