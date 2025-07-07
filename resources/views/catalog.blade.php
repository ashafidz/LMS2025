@extends('layouts.guest-layout')

@section('content')
{{-- Add flexbox classes here to center the content --}}
<div class="container-fluid d-flex justify-content-center align-items-center h-100">
    <div class="hero-section text-center">
        <h1 class="display-4 fw-bold">Welcome</h1>
        <p class="lead mx-auto text-muted">
            Ini halaman Katalog Courses website ini
        </p>
        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mt-4">
            
            @guest
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg px-4 gap-3">Register</a>
            @else
                {{-- dashboard link for all role --}}
                @if (Auth::user()->hasRole('superadmin'))
                    <a href="{{ route('superadmin.dashboard') }}" class="btn btn-primary btn-lg px-4 gap-3">Dashboard</a>
                @elseif (Auth::user()->hasRole('admin'))
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-lg px-4 gap-3">Dashboard</a>
                @elseif (Auth::user()->hasRole('instructor') && Auth::user()->instructorProfile?->application_status === 'approved')
                    <a href="{{ route('instructor.dashboard') }}" class="btn btn-primary btn-lg px-4 gap-3">Dashboard</a>
                @elseif (Auth::user()->hasRole('student'))
                    <a href="{{ route('student.dashboard') }}" class="btn btn-primary btn-lg px-4 gap-3">Dashboard</a>
                @else
                    <a href="{{ route('student.dashboard') }}" class="btn btn-primary btn-lg px-4 gap-3">Dashboard</a>
                @endif
            @endguest
        </div>
    </div>
</div>
@endsection