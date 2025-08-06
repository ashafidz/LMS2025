@extends('layouts.guest-layout')

@section('content')
{{-- This wrapper uses flexbox to center the card vertically and horizontally --}}
<div class="container d-flex justify-content-center align-items-center h-100 min-vh-100">

    {{-- The card container for your message --}}
    <div class="card shadow-sm border-0" style="max-width: 500px; width: 100%;">
        <div class="card-body p-5 text-center">

            {{-- Optional: Add an icon for visual flair --}}
            <div class="mb-4" style="font-size: 3rem;">⏳</div>

            <h3 class="card-title mb-3">Instructor Deactivated</h3>
            
            <p class="text-muted">
                {{-- message for deactivated instructor (can be banned or inactive too long) --}}
                Wahai Instructor terhormat, kami ingin menginformasikan bahwa akun anda telah di non-aktifkan oleh tim kami.
                <br>
                <br>
                Terima kasih telah menggunakan layanan kami.
            </p>
            
            <hr class="my-4">

            <div class="d-grid gap-2">
                <form method="POST" action="{{ route('logout') }}" class="d-grid">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>

        </div>
    </div>

</div>
@endsection