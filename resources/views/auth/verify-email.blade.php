@extends('layouts.guest-layout')

@section('content')
{{-- This wrapper uses flexbox to center the card vertically and horizontally --}}
<div class="container d-flex justify-content-center align-items-center h-100">

    {{-- The card container for your message --}}
    <div class="card shadow-sm border-0" style="max-width: 500px; width: 100%;">
        <div class="card-body p-5 text-center">

            {{-- Optional: Add an icon for visual flair --}}
            <div class="mb-4" style="font-size: 3rem;">✉️</div>

            <h3 class="card-title mb-3">Check Your Inbox</h3>

            <p class="text-muted">
                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
            </p>

            @if (session('status') == 'verification-link-sent')
                <div class="alert alert-success mt-4" role="alert">
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                </div>
            @endif

            <hr class="my-4">

            {{-- This flex container will space out the two buttons --}}
            <div class="d-flex justify-content-between align-items-center">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn bg-default-blue text-white custom-btn-hover">
                        {{ __('Resend Verification Email') }}
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-link text-decoration-none text-muted">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>

        </div>
    </div>

</div>
@endsection