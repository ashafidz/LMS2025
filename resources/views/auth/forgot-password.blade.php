@extends('layouts.home-layout')

@section('content')
{{-- This wrapper uses flexbox to center the card vertically and horizontally --}}
<div class="container d-flex justify-content-center align-items-center h-100 min-vh-100">
    
    {{-- The card container for your form --}}
    <div class="card shadow-sm border-0" style="max-width: 450px; width: 100%;">
        <div class="card-body p-5">

            <h3 class="card-title text-center mb-4">Forgot Password</h3>

            <div class="mb-4 text-sm text-muted">
                {{ __('No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
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

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-3">
                    <label for="email" class="form-label">{{ __('Email') }}</label>
                    <input id="email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus />
                </div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-dark">
                        {{ __('Email Password Reset Link') }}
                    </button>
                </div>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="text-decoration-none">
                    &larr; Back to login
                </a>
            </div>

        </div>
    </div>

</div>
@endsection