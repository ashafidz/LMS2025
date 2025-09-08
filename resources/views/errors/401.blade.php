@extends('layouts.home-layout')

@section('title', '401')

@section('content')
<section class="d-flex flex-column justify-content-center align-items-center text-center" 
         style="background: #f8f9fa; min-height: 100vh; padding: 20px;">

    <!-- Illustration -->
    <div class="mb-4">
        <img src="https://static.domainesia.com/assets/slots/webcdn/server/img/error-code/svg/error-401.svg" 
             alt="401 Unauthorized" 
             style="max-width: 400px; width: 100%;">
    </div>

    <!-- Title -->
    <h1 class="fw-bold mb-2" style="font-size: 2.5rem; color: #012970;">
        Tidak Diizinkan
    </h1>

    <!-- Message -->
    <p class="mb-4" style="color: #5e5e5e; line-height: 1.5; font-size: 1.2rem;">
        Anda belum login atau token tidak valid.  
        Silakan login untuk melanjutkan akses.
    </p>

    <!-- Button -->
    <a href="{{ route('login') }}" class="btn btn-primary btn-lg px-4" style="border-radius: 50px;">
        Login Sekarang
    </a>

</section>
@endsection