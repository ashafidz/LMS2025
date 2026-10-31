@extends('layouts.plain-layout')

@section('title', 'Ganti kata sandi')

@section('content')
<div class="rs-auth">
    <h1 class="rs-h2">Ganti kata sandi</h1>
    <p class="rs-auth__lead">Isi kata sandi lama, lalu tulis kata sandi baru.</p>

    @if (session('status') == 'password-updated')
        <div class="rs-alert rs-alert--ok" role="status">Kata sandi berhasil diganti.</div>
    @endif

    <form method="POST" action="{{ route('user-password.update') }}" class="rs-form">
        @csrf
        @method('PUT')

        <div class="rs-field">
            <label for="current_password">Kata sandi lama</label>
            <div class="rs-password">
                <input id="current_password" class="rs-input" type="password" name="current_password" required autocomplete="current-password"
                    @error('current_password', 'updatePassword') aria-invalid="true" aria-describedby="current_password-error" @enderror>
                <button type="button" data-rs-toggle-password="current_password" aria-label="Tampilkan kata sandi" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
            </div>
            @error('current_password', 'updatePassword')
                <p id="current_password-error" class="rs-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="rs-field">
            <label for="password">Kata sandi baru</label>
            <div class="rs-password">
                <input id="password" class="rs-input" type="password" name="password" required autocomplete="new-password"
                    @error('password', 'updatePassword') aria-invalid="true" aria-describedby="password-error" @enderror>
                <button type="button" data-rs-toggle-password="password" aria-label="Tampilkan kata sandi" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
            </div>
            @error('password', 'updatePassword')
                <p id="password-error" class="rs-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="rs-field">
            <label for="password_confirmation">Ulangi kata sandi baru</label>
            <div class="rs-password">
                <input id="password_confirmation" class="rs-input" type="password" name="password_confirmation" required autocomplete="new-password"
                    @error('password_confirmation', 'updatePassword') aria-invalid="true" aria-describedby="password_confirmation-error" @enderror>
                <button type="button" data-rs-toggle-password="password_confirmation" aria-label="Tampilkan kata sandi" aria-pressed="false"><i class="bi bi-eye" aria-hidden="true"></i></button>
            </div>
            @error('password_confirmation', 'updatePassword')
                <p id="password_confirmation-error" class="rs-field__error">{{ $message }}</p>
            @enderror
        </div>

        <div class="rs-actions">
            <button type="submit" class="rs-btn">Simpan perubahan</button>
            <a href="{{ route('user.profile.index') }}" class="rs-link">Batal</a>
        </div>
    </form>
</div>
@endsection
