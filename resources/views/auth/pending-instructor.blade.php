@extends('layouts.home-layout')

@section('content')
{{-- This wrapper uses flexbox to center the card vertically and horizontally --}}
<div class="container d-flex justify-content-center align-items-center h-100">

    {{-- The card container for your message --}}
    <div class="card shadow-sm border-0" style="max-width: 500px; width: 100%;">
        <div class="card-body p-5 text-center">

            {{-- Optional: Add an icon for visual flair --}}
            <div class="mb-4" style="font-size: 3rem;">⏳</div>

            <h3 class="card-title mb-3">Application Pending Review</h3>
            
            <p class="text-muted">
                Thank you for verifying your email. Your instructor application is currently under review by our team. You will be notified by email once a decision has been made.
            </p>
            
            <hr class="my-4">

            <div class="d-grid gap-2">
                <a href="{{ route('student.dashboard') }}" class="btn bg-default-blue text-white custom-btn-hover">Go to Student Dashboard</a>
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