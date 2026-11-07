@extends('layouts.app-layout') {{-- Sesuaikan dengan layout utama Anda --}}

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        @php
        [$previewHome, $previewList] = $is_preview ? \App\Services\LessonAccess::previewCrumbs(session('active_role')) : [null, null];
    @endphp
    <x-dash.page-head :crumbs="[$is_preview ? $previewHome : ['__home', route('student.dashboard')], $is_preview ? $previewList : ['Kursus Saya', route('student.my_courses')], [$quiz->lesson->module->course->title, route('student.courses.show', $quiz->lesson->module->course->slug) . ($is_preview ? '?preview=true' : '')], ['Kuis', null]]">
            <x-slot:title>Mulai Kuis</x-slot:title>
            <x-slot:subtitle>Kursus: {{ $quiz->lesson->module->course->title }}</x-slot:subtitle>
        </x-dash.page-head>
        <!-- Page-header end -->

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <div class="page-body">
                        {{-- Notifikasi untuk mode pratinjau --}}
                        @if ($is_preview)
                            <div class="alert alert-warning rs-preview-note" role="status">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                                <div><strong>Mode Pratinjau</strong><br>Anda melihat kuis ini seperti siswa. Jawaban, nilai, dan poin tidak disimpan.</div>
                            </div>
                        @endif

                        <div class="row d-flex justify-content-center">
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <h2 class="card-title" style="font-size: 25px;">{{ $quiz->title }}</h2>
                                        <p class="card-text text-muted" style="font-size: 16px;">
                                            {{ $quiz->description }}
                                        </p>
                                        <hr>
                                        <div class="row text-center">
                                            <div class="col-6 mb-4">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="fa fa-question-circle-o fa-3x mb-2"></i>
                                                    <h5>Jumlah Soal</h5>
                                                    <p class="card-text text-muted" style="font-size: 16px;">{{ $quiz->questions_count }} Soal</p>
                                                </div>
                                            </div>
                                            <div class="col-6 mb-4">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="fa fa-clock-o fa-3x mb-2"></i>
                                                    <h5>Durasi Waktu</h5>
                                                    <p class="card-text text-muted" style="font-size: 16px;">{{ $quiz->time_limit ? $quiz->time_limit . ' Menit' : 'Tidak ada' }}</p>
                                                </div>
                                            </div>
                                            <div class="col-6 mb-4">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="fa fa-check-square-o fa-3x mb-2"></i>
                                                    <h5>Nilai Kelulusan</h5>
                                                    <p class="card-text text-muted" style="font-size: 16px;">{{ $quiz->pass_mark }}</p>
                                                </div>
                                            </div>
                                            <div class="col-6 mb-4">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="fa fa-repeat fa-3x mb-2"></i>
                                                    <h5>Kesempatan</h5>
                                                    <p class="card-text text-muted" style="font-size: 16px;">
                                                        {{ $quiz->max_attempts ? $attemptCount . ' / ' . $quiz->max_attempts . ' Kali' : 'Tanpa Batas' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>

                                        {{-- @php
                                            $canAttempt = is_null($quiz->max_attempts) || $attemptCount < $quiz->max_attempts;
                                        @endphp
                                        @if ($canAttempt)
                               
                                            <form action="{{ route('student.quiz.begin', $quiz) }}" method="POST">
                                                @csrf
                         
                                                @if ($is_preview)
                                                    <input type="hidden" name="is_preview" value="true">
                                                @endif
                                                <button type="submit" class="btn btn-primary btn-lg">Mulai Kuis</button>
                                            </form>
                                        @endif --}}
                                        @if (!$is_preview)
                                            @php
                                                // Cek apakah siswa masih punya kesempatan untuk mencoba kuis
                                                $canAttempt =
                                                    is_null($quiz->max_attempts) || $attemptCount < $quiz->max_attempts;
                                            @endphp

                                            <div class="mt-4">
                                                @if ($isAvailable)
                                                    {{-- Tampilkan tombol "Mulai Kuis" atau "Coba Lagi" jika masih ada kesempatan --}}
                                                    @if ($canAttempt)
                                                        <form action="{{ route('student.quiz.begin', $quiz) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-primary btn-lg">
                                                                {{-- Ganti teks tombol jika ini bukan percobaan pertama --}}
                                                                {{ $attemptCount > 0 ? 'Coba Lagi' : 'Mulai Kuis' }}
                                                            </button>
                                                        </form>
                                                    @else
                                                        {{-- Tampilkan pesan jika kesempatan sudah habis --}}
                                                        <button class="btn btn-danger btn-lg" disabled>Kesempatan Habis</button>
                                                    @endif
                                                @else
                                                    <button class="btn btn-danger btn-lg" disabled>Tidak Tersedia</button>
                                                @endif
                                            </div>
                                        @else
                                            {{-- Tombol untuk mode Pratinjau (untuk admin/instruktur) --}}
                                            <form action="{{ route('student.quiz.begin', $quiz) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="is_preview" value="true">
                                                <button type="submit" class="btn btn-primary btn-lg">Mulai Kuis
                                                    (Pratinjau)</button>
                                            </form>
                                        @endif
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const isPreview = @json($is_preview);
            console.log(isPreview);
        })
    </script>
@endpush