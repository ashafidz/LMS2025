@extends('layouts.home-layout')

@section('title', '429')

@section('content')
<section class="d-flex flex-column justify-content-center align-items-center text-center"
         style="background: #f8f9fa; min-height: 100vh; padding: 20px;">

    <div class="mb-4">
        <img src="https://static.domainesia.com/assets/slots/webcdn/server/img/error-code/svg/error-429.svg"
             alt="429 Too Many Requests"
             style="max-width: 400px; width: 100%;">
    </div>

    <h1 class="fw-bold mb-2" style="font-size: 2.5rem; color: #012970;">
        Terlalu Banyak Permintaan
    </h1>

    <p class="mb-4" style="color: #5e5e5e; font-size: 1.2rem;">
        Anda telah mengirim terlalu banyak permintaan dalam waktu singkat.  
        Silakan tunggu beberapa saat sebelum mencoba lagi.
    </p>

    <a href="{{ url()->previous() }}" class="btn btn-primary btn-lg px-4" style="border-radius: 50px;">
        Coba Lagi
    </a>
</section>
@endsection