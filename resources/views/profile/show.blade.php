@extends('layouts.home-layout')

@section('title', 'Profil ' . $user->name)

@section('content')
<div class="rs-page">
{{-- Hero Section --}}
<section class="rs-pagehead">
  <div class="rs-wrap">
    <div class="rs-profile-head">
      <img src="{{ asset($user->profile_picture_url ?? 'assets/profile-images/avatar-1.png') }}" alt="{{ $user->name }}">
      <div>
        {{-- Tampilkan role secara dinamis --}}
        <p class="rs-eyebrow">{{ $user->hasRole('instructor') ? 'Instruktur' : 'Siswa' }}</p>
        <h1 class="rs-h1">{{ $user->name }}</h1>
        @if($profile?->headline)
          <p class="rs-lead">{{ $profile->headline }}</p>
        @endif
        @if($user->hasRole('instructor'))
          <p class="rs-muted">Mitra Instruktur {{ config('app.name', 'EduGames') }}</p>
        @endif
        @if($profile && $profile->website_url)
          <a href="{{ $profile->website_url }}" class="rs-btn rs-btn--outline" target="_blank" rel="noopener"><i class="bi bi-link-45deg" aria-hidden="true"></i>Website</a>
        @endif
      </div>
    </div>
  </div>
</section>

{{-- Bagian Profil --}}
<section class="rs-section">
  <div class="rs-wrap">
    <div class="rs-stats">
      @if($user->hasRole('instructor'))
        <div>
          <b>{{ number_format($totalStudents, 0, ',', '.') }}</b>
          <span>Total siswa</span>
        </div>
      @endif
      <div>
        <b>{{ number_format($totalPoints, 0, ',', '.') }}</b>
        <span>Total poin</span>
      </div>
    </div>

    <h2 class="rs-h2">Tentang Saya</h2>
    <div class="rs-prose rs-muted">
      {!! nl2br(e($profile->bio ?? 'Pengguna ini belum menulis bio.')) !!}
    </div>
  </div>
</section>

{{-- Bagian Kursus (hanya tampil jika pengguna adalah instruktur) --}}
@if($user->hasRole('instructor') && $publishedCourses->isNotEmpty())
<section id="courses" class="rs-section rs-section--paper">
  <div class="rs-wrap">
    <h2 class="rs-h2">Kursus oleh {{ $user->name }} ({{ $publishedCourses->count() }})</h2>
    <div class="rs-books">
      @foreach($publishedCourses as $course)
        <x-rs.course-book :course="$course" :show-instructor="true" />
      @endforeach
    </div>
  </div>
</section>
@endif
</div>
@endsection
