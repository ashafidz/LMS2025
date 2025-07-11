@extends('layouts.app-layout')

<style>
    /* Your styles remain the same */
    .side-image { 
            width: 100%;
    height: auto; /* Changed height to auto to maintain aspect ratio */
    min-height: 100vh; /* Ensures the image doesn't exceed the viewport height */
    object-fit: cover; /* Ensures the image covers the area without distortion */
     }
    .btn-outline-primary { color: #448aff; border-color: #448aff; background-color: white; }
    .btn-outline-primary:hover { background-color: #333; color: white; border-color: #448aff; }
    .btn-check:checked+.btn-outline-primary { background-color: #448aff !important; color: white !important; border-color: #448aff !important; }

    .custom-btn-hover:hover {
  color: #000000 !important; /* Change text to black on hover */
  /* You can also change the background or border here */
  background-color: #FFFFFF;
  border-color: #448aff;
}


</style>

@section('content')
<div class="container-fluid p-0 h-100">
    <div class="row g-0 h-100">
        {{-- Side Image Column --}}
        <div class="col-md-6 d-none d-lg-block">
            <img src="{{ asset('images/side-images/studio-unsplash.jpg') }}" class="side-image" alt="Photo by Studio Media on Unsplash">
        </div>
        
        {{-- Login Card Column --}}
        <div class="col-12 col-lg-6 d-flex justify-content-center align-items-center bg-light">
            <div class="card shadow-sm border-0" style="max-width: 450px; width: 100%;">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4">Log in</h3>

                    {{-- Main login form with errors filtered --}}
                    @if (session('status') && !str_contains(session('status'), 'link has been sent'))
                        <div class="alert alert-success mb-4" role="alert">{{ session('status') }}</div>
                    @endif
                    @if ($errors->any() && !old('is_forgot_password'))
                        <div class="alert alert-danger mb-4">
                            <ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="main-login-form">
                        @csrf
                        {{-- Role Selection for main form --}}
                        <div class="mb-4 text-center">
                            <div class="btn-group" role="group">
                                <input type="radio" class="btn-check" name="login_as_main" id="login_as_student_main" value="student" autocomplete="off" checked>
                                <label class="btn btn-outline-primary" for="login_as_student_main">🎓 Student</label>
                                <input type="radio" class="btn-check" name="login_as_main" id="login_as_instructor_main" value="instructor" autocomplete="off">
                                <label class="btn btn-outline-primary" for="login_as_instructor_main">🧑‍🏫 Instructor</label>
                            </div>
                        </div>
                        
                        {{-- CHANGE 1: Use a class for the hidden input --}}
                        <input type="hidden" name="login_preference" class="login-preference-input" value="student">

                        {{-- Main form fields --}}
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
                                <a href="#" class="text-decoration-none" style="font-size: 0.9rem;" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">{{ __('Forgot your password?') }}</a>
                            @endif
                        </div>
                        <div class="d-grid"><button type="submit" class="btn bg-default-blue text-white custom-btn-hover">{{ __('Log in') }}</button></div>
                    </form>

                    <hr class="my-4">
                    <div class="text-center">@if (Route::has('register'))<p class="mb-0"><a class="text-decoration-none" href="{{ route('register') }}">{{ __("Don't have an account? Register") }}</a></p>@endif</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Forgot Password Modal --}}
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            {{-- <div class="modal-header">
                <h5 class="modal-title" id="forgotPasswordModalLabel">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div> --}}
            <div class="modal-body">
                <button type="button" class="btn-close position-absolute top-0 end-0 mt-2 me-2" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="mb-4 d-flex justify-content-center" style="font-size: 3rem;">🔒</div>
                <h3 class="text-center">Forgot Your Password</h3>
                <p class="text-muted text-center mb-0">{{ __('No problem. Enter your email address.') }}</p>
                <p class="text-muted text-center">{{ __('Connected to Your Account.') }}</p>

                @if (session('status') && str_contains(session('status'), 'link has been sent'))
                    <div class="alert alert-success mb-4" role="alert">{{ session('status') }}</div>
                @endif
                @if ($errors->any() && old('is_forgot_password'))
                    <div class="alert alert-danger mb-4">
                        <ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('password.email') }}" class="forgot-password-form">
                    @csrf
                    <input type="hidden" name="is_forgot_password" value="1">
                    
                    {{-- CHANGE 2: Add Role Selection to the modal --}}
                    {{-- <div class="mb-4 text-center">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="login_as_modal" id="login_as_student_modal" value="student" autocomplete="off" checked>
                            <label class="btn btn-outline-primary" for="login_as_student_modal">🎓 Student</label>
                            <input type="radio" class="btn-check" name="login_as_modal" id="login_as_instructor_modal" value="instructor" autocomplete="off">
                            <label class="btn btn-outline-primary" for="login_as_instructor_modal">🧑‍🏫 Instructor</label>
                        </div>
                    </div> --}}

                    {{-- Also use a class for the modal's hidden input --}}
                    {{-- <input type="hidden" name="login_preference" class="login-preference-input" value="student"> --}}

                    <div class="mb-3">
                        <label for="forgot_email" class="form-label">{{ __('Email') }}</label>
                        <input id="forgot_email" class="form-control" type="email" name="email" value="{{ old('email') }}" required />
                    </div>
                    <div class="d-grid mt-4"><button type="submit" class="border btn bg-default-blue text-white custom-btn-hover">{{ __('Email Password Reset Link') }}</button></div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- CHANGE 3: A smarter script to handle both forms --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Find all role-switching radio buttons on the page
        const roleRadios = document.querySelectorAll('.btn-check[name^="login_as_"]');

        roleRadios.forEach((radio) => {
            radio.addEventListener("change", function(event) {
                // Get the form that this radio button is inside
                const form = event.target.closest('form');
                if (form) {
                    // Find the hidden input *within that specific form*
                    const hiddenInput = form.querySelector('.login-preference-input');
                    if (hiddenInput) {
                        hiddenInput.value = event.target.value;
                    }
                }
            });
        });
    });
</script>

{{-- Script to auto-open modal (this part is still correct) --}}
@if ( (session('status') && str_contains(session('status'), 'link')) || ($errors->any() && old('is_forgot_password')) )
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var forgotPasswordModal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
        forgotPasswordModal.show();
    });
</script>
@endif
@endpush