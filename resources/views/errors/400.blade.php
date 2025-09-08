@extends('layouts.home-layout')

@section('title', '404')

@section('content')
<section class="d-flex flex-column justify-content-center align-items-center text-center" 
         style="background: #f8f9fa; min-height: 100vh; padding: 20px;">

    <!-- Illustration -->
    <div class="mb-4">
        <img src="https://static.domainesia.com/assets/slots/webcdn/server/img/error-code/svg/error-400.svg" 
             alt="404 Not Found" 
             style="max-width: 400px; width: 100%;">
    </div>

    <!-- Title -->
    <h1 class="fw-bold mb-2" style="font-size: 2.5rem; color: #012970;">
        Halaman Tidak Ditemukan
    </h1>

    <!-- Message -->
    <p class="mb-4" style="color: #5e5e5e; line-height: 1.5; font-size: 1.2rem;">
        Maaf, halaman yang Anda cari tidak tersedia atau sudah dipindahkan.
    </p>

    <!-- Button -->
    <a href="{{ url('/') }}" class="btn btn-primary btn-lg px-4" style="border-radius: 50px;">
        Kembali ke Beranda
    </a>

</section>
@endsection