@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Ambil dari Bank Soal</h5>
                            <p class="m-b-0">Pilih soal untuk ditambahkan ke kuis: <strong>{{ $quiz->title }}</strong></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.modules.lessons.index', $quiz->lesson->module) }}">Daftar Pelajaran</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.quizzes.manage_questions', $quiz) }}">Kelola Soal</a></li>
                            <li class="breadcrumb-item"><a href="#!">Pilih dari Bank Soal</a></li>
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
                            <!-- Kolom Daftar Topik -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Topik Soal Anda</h5>
                                    </div>
                                    <div class="card-block">
                                        <div class="list-group">
                                            @forelse ($topics as $topic)
                                                {{-- Tautan untuk memfilter soal berdasarkan topik --}}
                                                <a href="{{ route('instructor.quizzes.browse_bank', ['quiz' => $quiz, 'topic_id' => $topic->id]) }}"
                                                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ request('topic_id') == $topic->id ? 'active' : '' }}">
                                                    {{ $topic->name }}
                                                    {{-- Jumlah soal yang tersedia untuk ditambahkan --}}
                                                    <span class="badge badge-primary badge-pill">{{ $topic->questions_count }}</span>
                                                </a>
                                            @empty
                                                <p class="text-muted">Anda belum memiliki topik soal di Bank Soal.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom Daftar Soal -->
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Pilih Soal</h5>
                                        <span>Centang soal yang ingin Anda tambahkan ke kuis.</span>
                                    </div>
                                    <div class="card-block">
                                        @if ($questionsInTopic)
                                            <form action="{{ route('instructor.quizzes.attach_questions', $quiz) }}" method="POST">
                                                @csrf
                                                <div class="table-responsive">
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th style="width: 5%;">Pilih</th>
                                                                <th>Tipe</th>
                                                                <th>Teks Soal</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse ($questionsInTopic as $question)
                                                                <tr>
                                                                    <td>
                                                                        <div class="checkbox-fade fade-in-primary">
                                                                            <label>
                                                                                <input type="checkbox" name="question_ids[]" value="{{ $question->id }}">
                                                                                <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                                                                            </label>
                                                                        </div>
                                                                    </td>
                                                                    <td>{{ ucfirst(str_replace('_', ' ', $question->question_type)) }}</td>
                                                                    <td>{{ Str::limit(strip_tags($question->question_text), 100) }}</td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="3" class="text-center">Tidak ada soal yang tersedia untuk ditambahkan di topik ini.</td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <hr>
                                                <div class="text-right">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fa fa-plus-circle"></i> Tambah Soal Terpilih ke Kuis
                                                    </button>
                                                </div>
                                            </form>
                                        @else
                                            <div class="text-center">
                                                <p class="text-muted"><i class="fa fa-arrow-left"></i> Silakan pilih topik di sebelah kiri untuk menampilkan daftar soal.</p>
                                            </div>
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