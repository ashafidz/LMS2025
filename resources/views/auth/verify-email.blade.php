@extends('layouts.home-layout')

@section('content')
<div class="container d-flex justify-content-center align-items-start min-vh-100" style="background: #f0f4ff; padding-top: 195px;">

    <div class="card shadow-sm border-0 rounded-4" style="max-width: 650px; width: 100%;">
        <div class="card-body px-5 py-4 text-center">

            {{-- Logo Gmail --}}
            <img src="https://ssl.gstatic.com/ui/v1/icons/mail/rfr/logo_gmail_lockup_default_1x_r2.png" alt="Gmail" style="width: 60px;" class="mb-3">

            {{-- Judul --}}
            <h4 class="fw-bold mb-3">Verifikasi Email Anda</h4>

            {{-- Pesan --}}
            <p class="text-muted mb-3">
                Kami telah mengirimkan tautan verifikasi ke email Anda.<br>
                Silakan periksa kotak masuk dan klik tautan tersebut untuk mengaktifkan akun Anda.
            </p>

            {{-- Notifikasi jika link baru dikirim --}}
            @if (session('status') == 'verification-link-sent')
                <div class="alert alert-success mb-3" role="alert">
                    Tautan verifikasi baru telah dikirim ke alamat email Anda.
                </div>
            @endif

            {{-- Tombol kirim ulang --}}
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn btn-primary w-100 fw-semibold py-2" style="font-size: 1rem;">
                    Kirim Ulang Email Verifikasi
                </button>
            </form>

            {{-- Link logout --}}
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="btn btn-link text-decoration-none text-muted">
                    Log Out
                </button>
            </form>

            {{-- Informasi tambahan --}}
            <p class="text-muted small mt-3 mb-0">
                Belum menerima email? Periksa folder spam atau klik tombol di atas untuk mengirim ulang.
            </p>

        </div>
    </div>
</div>
@endsection
