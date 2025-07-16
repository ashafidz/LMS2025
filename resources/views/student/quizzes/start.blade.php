@extends('layouts.app-layout') {{-- Sesuaikan dengan layout utama Anda --}}

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Mulai Kuis</h5>
                        <p class="m-b-0">Kursus: {{ $quiz->lesson->module->course->title }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="#"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.courses.show', $quiz->lesson->module->course->slug) }}">Kursus</a></li>
                        <li class="breadcrumb-item"><a href="#!">Kuis</a></li>
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
                    {{-- Notifikasi untuk mode pratinjau --}}
                    @if($is_preview)
                    <div class="alert alert-warning text-center">
                        <strong>Mode Pratinjau</strong><br>
                        Anda melihat halaman ini sebagai Instruktur/Admin. Hasil kuis tidak akan disimpan.
                    </div>
                    @endif

                    <div class="row d-flex justify-content-center">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h2 class="card-title">{{ $quiz->title }}</h2>
                                    <p class="card-text text-muted">
                                        {{ $quiz->description }}
                                    </p>
                                    <hr>
                                    <div class="row">
                                        <div class="col-6">
                                            <h5><i class="fa fa-question-circle-o mr-2"></i> Jumlah Soal</h5>
                                            <p>{{ $quiz->questions_count }} Soal</p>
                                        </div>
                                        <div class="col-6">
                                            <h5><i class="fa fa-clock-o mr-2"></i> Batas Waktu</h5>
                                            <p>{{ $quiz->time_limit ? $quiz->time_limit . ' Menit' : 'Tidak ada' }}</p>
                                        </div>
                                        <div class="col-6">
                                            <h5><i class="fa fa-check-square-o mr-2"></i> Nilai Kelulusan</h5>
                                            <p>{{ $quiz->pass_mark }}%</p>
                                        </div>
                                        <div class="col-6">
                                            <h5><i class="fa fa-repeat mr-2"></i> Kesempatan</h5>
                                            <p>1 Kali</p> {{-- Anda bisa membuat ini dinamis nanti --}}
                                        </div>
                                    </div>
                                    <hr>
                                    {{-- Form untuk memulai kuis --}}
                                    <form action="{{ route('student.quiz.begin', $quiz->id) }}" method="POST">
                                        @csrf
                                        {{-- Kirim status pratinjau ke controller selanjutnya --}}
                                        @if($is_preview)
                                            <input type="hidden" name="is_preview" value="true">
                                        @endif
                                        <button type="submit" class="btn btn-primary btn-lg">Mulai Kuis</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection