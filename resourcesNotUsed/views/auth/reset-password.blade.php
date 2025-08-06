@extends('layouts.guest-layout')

@section('content')
{{-- This wrapper uses flexbox to center the card vertically and horizontally --}}
<div class="container d-flex justify-content-center align-items-center h-100">

    {{-- The card container for your form --}}
    <div class="card shadow-sm border-0" style="max-width: 450px; width: 100%;">
        <div class="card-body p-5">

            <h3 class="card-title text-center mb-4">Reset Password</h3>

            <div class="mb-4 text-muted">
                {{ __('Enter your email address and new password to reset your account.') }}
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

            <form method="POST" action="{{ route('password.update') }}">
                @csrf

                {{-- Password Reset Token --}}
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                {{-- Email Address --}}
                <div class="mb-3">
                    <label for="email" class="form-label">{{ __('Email') }}</label>
                    <input id="email" class="form-control" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus readonly />
                </div>

                {{-- Password --}}
                <div class="mb-3">
                    <label for="password" class="form-label">{{ __('Password') }}</label>
                    <input id="password" class="form-control" type="password" name="password" required autocomplete="new-password" />
                </div>

                {{-- Confirm Password --}}
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                    <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required autocomplete="new-password" />
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn bg-default-blue text-white custom-btn-hover">
                        {{ __('Reset Password') }}
                    </button>
                </div>
            </form>

        </div>
    </div>

</div>
@endsection