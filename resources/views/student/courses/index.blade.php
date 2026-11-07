@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <x-dash.page-head :crumbs="[['__home', route('student.dashboard')], ['Kursus Saya', null]]">
        <x-slot:title>Kursus Saya</x-slot:title>
        <x-slot:subtitle>Lanjutkan progres belajar Anda di kursus yang telah Anda miliki.</x-slot:subtitle>
    </x-dash.page-head>
    <!-- Page-header end -->

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="row">
                        @forelse ($enrolledCourses as $course)
                            <div class="col-md-6 col-lg-4">
                                <div class="card">
                                    <img class="card-img-top" src="{{ $course->thumbnail_url ? Storage::url($course->thumbnail_url) : 'https://placehold.co/600x400/e0edff/007bff?text=Kursus' }}" alt="{{ $course->title }}" style="height: 180px; object-fit: cover;">
                                    <div class="card-block">
                                        <h5 class="card-title">{{ $course->title }}</h5>
                                        <a class="card-text text-muted mb-2" href="{{ route('profile.show', $course->instructor->id) }}">
                                            <p class="card-text text-muted mb-2">{{ $course->instructor->name }}</p>
                                        </a>

                                        
                                        {{-- Progress Bar --}}
                                        @php
                                            $progress = 0;
                                            if ($course->lessons_count > 0) {
                                                $progress = ($course->completed_lessons_count / $course->lessons_count) * 100;
                                            }
                                        @endphp
                                        <div class="rs-progress-label"><span>Progres</span><strong>{{ round($progress) }}%</strong></div>
                                        <div class="progress mb-3">
                                            <div class="progress-bar" role="progressbar" style="width: {{ $progress }}%;" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100">{{ round($progress) }}%</div>
                                        </div>

                                        <a href="{{ route('student.courses.show', $course->slug) }}" class="btn btn-primary btn-block">Lanjutkan Belajar</a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-sm-12">
                                <div class="card">
                                    <div class="card-block text-center p-5">
                                        <x-rs.illustration name="rs-07-cari-kursus" size="md" class="mb-3" />
                                        <h4>Anda Belum Terdaftar di Kursus Apapun</h4>
                                        <p>Jelajahi katalog kami untuk menemukan kursus yang cocok untuk Anda.</p>
                                        <a href="{{ route('courses') }}" class="btn btn-primary mt-2">Lihat Katalog Kursus</a>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    {{-- Navigasi Halaman --}}
                    <div class="row">
                        <div class="col-12 d-flex justify-content-center">
                            {{ $enrolledCourses->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection