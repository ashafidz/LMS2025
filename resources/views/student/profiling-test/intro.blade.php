@extends('layouts.app-layout')

@section('content')
<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body p-5 text-center">
                    <img src="{{ Storage::url($course->thumbnail_url) }}" alt="{{ $course->title }}" class="img-fluid rounded mb-4" style="max-height: 200px;">
                    <h2 class="mb-3">Selamat Datang di Profiling Test</h2>
                    <h4 class="text-primary mb-4">{{ $course->title }}</h4>
                    
                    <p class="lead mb-4">
                        Kursus ini menggunakan <strong>Adaptive Learning</strong>. Sebelum memulai materi, Anda perlu menjawab beberapa pertanyaan untuk membantu sistem menyesuaikan konten dan gaya belajar terbaik untuk Anda.
                    </p>

                    <div class="alert alert-info text-left">
                        <ul class="mb-0">
                            <li>Tes ini terdiri dari 4 bagian (Goal Setting, Prior Knowledge, Motivation, AI Preference).</li>
                            <li>Tidak ada jawaban benar/salah pada bagian preferensi, jawablah dengan jujur.</li>
                            <li>Progres Anda akan disimpan, jadi Anda bisa melanjutkannya nanti jika terputus.</li>
                        </ul>
                    </div>

                    <div class="mt-5">
                        @if($canResume)
                            <a href="{{ route('student.profiling-test.start', $course->slug) }}" class="btn btn-warning btn-lg px-5">Lanjutkan Test (Bagian {{ $attempt->current_component }})</a>
                        @else
                            <a href="{{ route('student.profiling-test.start', $course->slug) }}" class="btn btn-primary btn-lg px-5">Mulai Profiling Test</a>
                        @endif
                        <br>
                        <a href="{{ route('student.dashboard') }}" class="btn btn-link mt-3 text-muted">Kembali ke Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
