@extends('layouts.home-layout')

@section('content')
<section id="hero" class="d-flex align-items-center" style="background: #f8f9fa; min-height: 100vh;">
  <div class="container d-flex justify-content-center">

    <!-- Card Container -->
    <div class="card shadow-lg border-0" style="max-width: 750px; width: 100%; border-radius: 20px;">
      <div class="card-body text-center p-5">

        <!-- Icon / Illustration -->
        <div class="mb-4">
          <span style="font-size: 4rem; display: inline-block;">⏳</span>
        </div>

        <!-- Title -->
        <h1 class="fw-bold mb-3" style="font-size: 2rem; color: #012970;">
          Student Deactivated
        </h1>

        <!-- Subtitle -->
        <p class="mb-4" style="color: #5e5e5e; line-height: 1.4;">
          Hai, kami ingin memberi tahu bahwa akun belajar Anda saat ini tidak aktif.
          Hal ini dilakukan demi menjaga kenyamanan dan keamanan semua pengguna.
          Terima kasih sudah menjadi bagian dari komunitas belajar kami, semoga bisa segera kembali aktif!
        </p>

        <!-- Divider -->
        <hr class="my-4" style="max-width: 200px; margin: 20px auto; border-color: #ddd;">

        <!-- Logout Button -->
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn btn-primary btn-lg px-4" style="border-radius: 50px;">
            {{ __('Log Out') }}
          </button>
        </form>

      </div>
    </div>
    <!-- End Card -->

  </div>
</section>
@endsection
