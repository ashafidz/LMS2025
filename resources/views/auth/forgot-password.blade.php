@extends('layouts.plain-layout')

@section('title', 'Lupa kata sandi')

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

    <form method="POST" action="{{ route('password.email') }}" class="rs-form">
        @csrf

        <div class="rs-field">
            <label for="email">Email</label>
            <input id="email" class="rs-input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email">
        </div>

        <button type="submit" class="rs-btn rs-btn--block">Kirim tautan atur ulang</button>
    </form>

    <p class="rs-auth__alt"><a class="rs-link" href="{{ route('login') }}">Kembali ke halaman masuk</a></p>
</div>
@endsection
