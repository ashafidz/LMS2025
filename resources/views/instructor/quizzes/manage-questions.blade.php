@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Kelola Soal Kuis</h5>
                            <p class="m-b-0">Kuis: <strong>{{ $quiz->title }}</strong></p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                            {{-- Navigasi kembali ke daftar pelajaran --}}
                            <li class="breadcrumb-item"><a href="{{ route('instructor.modules.lessons.index', $quiz->lesson->module) }}">Daftar Pelajaran</a></li>
                            <li class="breadcrumb-item"><a href="#!">Kelola Soal</a></li>
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
                            <div class="col-sm-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Daftar Soal dalam Kuis Ini</h5>
                                        <div class="card-header-right">
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addQuestionModal">
                                                <i class="fa fa-plus"></i> Tambah Soal
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-block table-border-style">
                                        @if (session('success'))
                                            <div class="alert alert-success">{{ session('success') }}</div>
                                        @endif
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Teks Soal</th>
                                                        <th>Tipe</th>
                                                        <th class="text-center">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($attachedQuestions as $question)
                                                        <tr>
                                                            <th scope="row">{{ $loop->iteration }}</th>
                                                            <td>{{ Str::limit(strip_tags($question->question_text), 100) }}</td>
                                                            <td>{{ ucfirst(str_replace('_', ' ', $question->question_type)) }}</td>
                                                            <td class="text-center">
                                                                {{-- Form untuk menghapus (detach) soal dari kuis --}}
                                                                <form action="{{ route('instructor.quizzes.detach_question', ['quiz' => $quiz, 'question' => $question]) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus soal ini dari kuis?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                                        <i class="fa fa-trash"></i> Hapus
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="text-center">Belum ada soal di dalam kuis ini.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk Memilih Sumber Soal -->
    <div class="modal fade" id="addQuestionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Soal Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Pilih dari mana Anda ingin menambahkan soal:</p>
                    <div class="list-group">
                        {{-- Opsi 1: Mengambil soal dari Bank Soal yang sudah ada --}}
                        <a href="{{ route('instructor.quizzes.browse_bank', $quiz) }}" class="list-group-item list-group-item-action">
                            <i class="fa fa-university"></i> <strong>Ambil dari Bank Soal</strong>
                            <br><small>Pilih soal yang sudah pernah Anda buat sebelumnya.</small>
                        </a>
                        {{-- Opsi 2: Membuat soal baru dari awal --}}
                        <a href="{{ route('instructor.question-bank.topics.index') }}" class="list-group-item list-group-item-action" target="_blank">
                            <i class="fa fa-plus-circle"></i> <strong>Buat Soal Baru di Bank Soal</strong>
                            <br><small>Ini akan membuka halaman Bank Soal di tab baru.</small>
                        </a>
                    </div>
                </div>
                 <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                </div>
            </div>
        </div>
    </div>
@endsection