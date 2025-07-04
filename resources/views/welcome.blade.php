@extends('layouts.guest-layout')

@section('content')
{{-- Add flexbox classes here to center the content --}}
<div class="container-fluid d-flex justify-content-center align-items-center h-100">
    <div class="hero-section text-center">
        <h1 class="display-4 fw-bold">Welcome</h1>
        <p class="lead mx-auto text-muted">
            Ini halaman home website ini
        </p>
        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mt-4">
            @guest
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg px-4 gap-3">Register</a>
            @else
                <a href="{{ url('/dashboard') }}" class="btn btn-primary btn-lg px-4 gap-3">Go to Dashboard</a>
            @endguest
        </div>
    </div>
</div>
@endsection