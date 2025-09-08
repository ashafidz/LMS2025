@extends('layouts.home-layout')

@section('title', '503')

@section('content')
<section class="d-flex flex-column justify-content-center align-items-center text-center"
         style="background: #f8f9fa; min-height: 100vh; padding: 20px;">

    <div class="mb-4">
        <img src="https://static.domainesia.com/assets/slots/webcdn/server/img/error-code/svg/error-503.svg"
             alt="503 Service Unavailable"
             style="max-width: 400px; width: 100%;">
    </div>

    <h1 class="fw-bold mb-2" style="font-size: 2.5rem; color: #012970;">
        Layanan Tidak Tersedia
    </h1>

    <p class="mb-4" style="color: #5e5e5e; font-size: 1.2rem;">
        Server sedang sibuk atau dalam perawatan.  
        Silakan coba kembali beberapa saat lagi.
    </p>

    <a href="{{ url('/') }}" class="btn btn-primary btn-lg px-4" style="border-radius: 50px;">
        Coba Lagi
    </a>
</section>
@endsection