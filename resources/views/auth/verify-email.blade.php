@extends('layouts.plain-layout')

@section('title', 'Verifikasi email')

@section('content')
<x-rs.status tone="info" icon="bi-envelope-check" title="Verifikasi Email Anda">
    <p>
        Kami telah mengirimkan tautan verifikasi ke email Anda.<br>
        Silakan periksa kotak masuk dan klik tautan tersebut untuk mengaktifkan akun Anda.
    </p>

    {{-- Notifikasi jika link baru dikirim --}}
    @if (session('status') == 'verification-link-sent')
        <div class="rs-alert rs-alert--ok" role="status">
            Tautan verifikasi baru telah dikirim ke alamat email Anda.
        </div>
    @endif

    <div class="rs-status__actions">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="rs-btn rs-btn--block">Kirim Ulang Email Verifikasi</button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="rs-btn rs-btn--outline rs-btn--block">Keluar</button>
        </form>
    </div>

    <p class="rs-hint rs-mt-3">
        Belum menerima email? Periksa folder spam atau klik tombol di atas untuk mengirim ulang.
    </p>
</x-rs.status>
@endsection
