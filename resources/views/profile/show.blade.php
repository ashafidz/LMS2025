@extends('layouts.home-layout')

@section('title', 'Profil ' . $user->name)

@section('content')

{{-- Hero Section --}}
<section class="hero-section text-start py-5" style="background-color: #edf8fd;">
  <div class="container">
    <div class="row align-items-center g-4">
      
      {{-- Teks Hero --}}
      <div class="col-md-8">
        <p class="text-uppercase fw-bold text-secondary mb-1 fs-6" style="margin-top: 70px;">
            {{-- Tampilkan role secara dinamis --}}
            @if($user->hasRole('instructor'))
                INSTRUKTUR & STUDENT
            @else
                STUDENT
            @endif
        </p>
        <h2 class="fw-bold mb-2 fs-1">{{ $user->name }}</h2>
        <p class="text-muted fs-4 mb-3">{{ $profile->headline ?? '' }}</p>
        @if($user->hasRole('instructor'))
        <span class="badge bg-light text-primary border border-primary mb-4 fs-6">
          Mitra Instruktur {{ config('app.name', 'EduGames') }}
        </span>
        @endif
      </div>

      <div class="col-md-4" style="margin-top:110px;">
        <div class="card shadow border-0 rounded-4 text-center p-4">
          <img src="{{ asset($user->profile_picture_url ?? 'assets/profile-images/avatar-1.png') }}" 
               class="rounded-circle mb-3 mx-auto" 
               width="140" height="140" 
               alt="{{ $user->name }}"
               style="object-fit: cover;">
          <div class="d-flex justify-content-center gap-2">
            @if($profile && $profile->website_url)
            <a href="{{ $profile->website_url }}" class="btn btn-outline-primary btn-sm" target="_blank">
              <i class="bi bi-link-45deg"></i> Website
            </a>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Bagian Profil --}}
<section class="py-5 bg-white">
  <div class="container">
    <div class="row g-4">
      
      {{-- Tentang Saya --}}
      <div class="col-md-8">
        <div class="d-flex flex-wrap gap-5 mb-4">
          @if($user->hasRole('instructor'))
          <div>
            <h4 class="fw-bold mb-0">{{ number_format($totalStudents, 0, ',', '.') }}</h4>
            <small class="text-muted">Total siswa</small>
          </div>
          @endif
          <div>
            <h4 class="fw-bold mb-0 d-flex align-items-center">
              {{ number_format($totalPoints, 0, ',', '.') }} 
              <i class="fa fa-star text-warning ms-2"></i>
            </h4>
            <small class="text-muted">Total Poin</small>
          </div>
        </div>

        <h5 class="fw-bold" style="margin-top:30px; font-size:26px; font-weight:700;">
          Tentang Saya
        </h5>

        <div class="text-muted">
            {!! nl2br(e($profile->bio ?? 'Pengguna ini belum menulis bio.')) !!}
        </div>
      </div>
    </div>
  </div>
</section>

{{-- Bagian Kursus (hanya tampil jika pengguna adalah instruktur) --}}
@if($user->hasRole('instructor') && $publishedCourses->isNotEmpty())
<section id="courses" class="py-5 bg-light">
  <div class="container">
    <h4 class="fw-bold text-primary mb-4">Kursus oleh {{ $user->name }} ({{ $publishedCourses->count() }})</h4>
    <div class="row g-4">

      @foreach($publishedCourses as $course)
      <div class="col-md-4">
        <a href="{{ route('courses.show', $course->slug) }}" class="text-decoration-none">
            <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
              <img src="{{ $course->thumbnail_url ? Storage::url($course->thumbnail_url) : 'https://placehold.co/350x200' }}" 
                   class="card-img-top" 
                   style="height: 200px; object-fit: cover;"
                   alt="{{ $course->title }}">
              <div class="card-body">
                <h6 class="fw-bold mb-1">{{ $course->title }}</h6>
                <p class="text-muted mb-1">{{ $course->instructor->name }}</p>
                @if($course->payment_type === 'money')
                    <p class="fw-bold text-dark mb-0">
                        {{ $course->price > 0 ? 'Rp' . number_format($course->price, 0, ',', '.') : 'Gratis' }}
                    </p>
                @else
                    <p class="fw-bold text-info mb-0 d-flex align-items-center">
                        <i class="fa fa-diamond me-2"></i> {{ number_format($course->diamond_price, 0, ',', '.') }}
                    </p>
                @endif
              </div>
            </div>
        </a>
      </div>
      @endforeach
      
    </div>
  </div>
</section>
@endif

@endsection