@extends('layouts.home-layout')

@section('title', '419')

@section('content')
<section class="d-flex flex-column justify-content-center align-items-center text-center"
         style="background: #f8f9fa; min-height: 100vh; padding: 20px;">

    <div class="mb-4">
        <img src="{{ asset('images/419.svg') }}" 
             alt="419 Page Expired" 
             style="max-width: 400px; width: 100%;">
    </div>

    <h1 class="fw-bold mb-2" style="font-size: 2.5rem; color: #012970;">
        Sesi Kedaluwarsa
    </h1>

    <p class="mb-4" style="color: #5e5e5e; font-size: 1.2rem;">
        Sesi Anda sudah kedaluwarsa, biasanya karena terlalu lama tidak aktif atau token CSRF tidak valid.  
        Silakan refresh halaman atau login kembali.
    </p>

    <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-4" style="border-radius: 50px;">
        Login Ulang
    </a>
</section>
@endsection