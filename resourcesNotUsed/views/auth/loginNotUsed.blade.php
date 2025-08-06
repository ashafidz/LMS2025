@extends('layouts.guest-layout')

<style>
    /* Custom style for the side image */
    .side-image {
    width: 100%;
    height: auto; /* Changed height to auto to maintain aspect ratio */
    max-height: 100vh; /* Ensures the image doesn't exceed the viewport height */
    object-fit: cover; /* Ensures the image covers the area without distortion */
    }

    /* Default unselected button look */
    .btn-outline-primary {
        color: black;
        border-color: black;
        background-color: white;
    }

    /* Hover on unselected */
    .btn-outline-primary:hover {
        background-color: #333;
        color: white;
        border-color: black;
    }

    /* When selected (checked) */
    .btn-check:checked+.btn-outline-primary {
        background-color: black !important;
        color: white !important;
        border-color: black !important;
    }
</style>

@section('content')
<div class="container-fluid p-0 h-100">
    <div class="row g-0 h-100">

        {{-- Column for side image --}}
        {{-- You can change this image to whatever you like --}}
        <div class="col-md-6 d-none d-md-block">
            <img src="{{ asset('images/side-images/studio-unsplash.jpg') }}" class="side-image" alt="Photo by Studio Media on Unsplash">
        </div>
        {{-- End column for side image --}}

        {{-- Column for login card --}}
        <div class="col-12 col-md-6 d-flex justify-content-center align-items-center bg-light">
            
            {{-- Login Card --}}
            <div class="card shadow-sm border-0" style="max-width: 450px; width: 100%;">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4">Log in</h3>

                    <div class="mb-4 text-center">
                        <div class="btn-group" role="group" aria-label="Login as">
                            <input type="radio" class="btn-check" name="login_as" id="login_as_student" value="student" autocomplete="off" checked>
                            <label class="btn btn-outline-primary" for="login_as_student">🎓 Student</label>

                            <input type="radio" class="btn-check" name="login_as" id="login_as_instructor" value="instructor" autocomplete="off">
                            <label class="btn btn-outline-primary" for="login_as_instructor">🧑‍🏫 Instructor</label>
                        </div>
                    </div>

                    @if (session('status'))
                        <div class="alert alert-success mb-4" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger mb-4">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <input type="hidden" name="login_preference" id="login_preference" value="student">

                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('Email') }}</label>
                            <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus />
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">{{ __('Password') }}</label>
                            <input id="password" class="form-control" type="password" name="password" required autocomplete="current-password" />
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
                                <label for="remember_me" class="form-check-label">{{ __('Remember me') }}</label>
                            </div>
                            @if (Route::has('password.request'))
                                <a class="text-decoration-none" style="font-size: 0.9rem;" href="{{ route('password.request') }}">
                                    {{ __('Forgot your password?') }}
                                </a>
                            @endif
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-dark">
                                {{ __('Log in') }}
                            </button>
                        </div>
                    </form>

                    <hr class="my-4">

                    <div class="text-center">
                        @if (Route::has('register'))
                            <p class="mb-0">
                                <a class="text-decoration-none" href="{{ route('register') }}">
                                    {{ __("Don't have an account? Register") }}
                                </a>
                            </p>
                        @endif
                    </div>
                </div>
            </div>
            {{-- End Login Card --}}
        </div>
        {{-- End column for login card --}}
    </div>
</div>
@endsection

@push('scripts')
<script>
    // This script updates the hidden input when the user clicks a role button
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('input[name="login_as"]').forEach((elem) => {
            elem.addEventListener("change", function(event) {
                document.getElementById('login_preference').value = event.target.value;
            });
        });
    });
</script>
@endpush