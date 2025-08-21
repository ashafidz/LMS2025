@extends('layouts.home-layout')

@section('title', '403')

@section('content')
<section class="d-flex flex-column justify-content-center align-items-center text-center" 
         style="background: #f8f9fa; min-height: 100vh; padding: 20px;">

    <!-- Illustration -->
    <div class="mb-4">
        <img src="https://static.domainesia.com/assets/slots/webcdn/server/img/error-code/svg/error-403.svg" 
             alt="403 Akses Ditolak" 
             style="max-width: 400px; width: 100%;">
    </div>

    <!-- Title -->
    <h1 class="fw-bold mb-2" style="font-size: 2.5rem; color: #012970;">
        Akses Ditolak
    </h1>

    <!-- Message -->
    <p class="mb-4" style="color: #5e5e5e; line-height: 1.5; font-size: 1.2rem;">
        Anda sudah login, tetapi tidak memiliki izin untuk mengakses halaman ini.  
        Jika menurut Anda ini adalah kesalahan, silakan hubungi administrator.
    </p>

    <!-- Button -->
    <a href="{{ url('/') }}" class="btn btn-primary btn-lg px-4" style="border-radius: 50px;">
        Kembali ke Beranda
    </a>

</section>
@endsection