@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Kursus Saya</h5>
                        <p class="m-b-0">Lanjutkan progres belajar Anda di kursus yang telah Anda miliki.</p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Kursus Saya</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
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
                                        <i class="fa fa-book fa-3x text-muted mb-3"></i>
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