@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], ['Kursus Saya', route('instructor.courses.index')], [\Illuminate\Support\Str::limit($quiz->lesson->module->course->title, 30), route('instructor.courses.modules.index', $quiz->lesson->module->course)], ['Daftar Pelajaran', route('instructor.modules.lessons.index', $quiz->lesson->module)], ['Kelola Soal', null]]">
            <x-slot:title>Kelola Soal Kuis</x-slot:title>
            <x-slot:subtitle>Judul Kuis: <strong>{{ $quiz->title }}</strong></x-slot:subtitle>
        </x-dash.page-head>
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
                                        <span>Daftar soal yang telah ditambahkan ke kuis.</span>
                                        <div class="card-header-right">
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addQuestionModal"><i class="bi bi-plus-lg text-white"></i> 
                                                Tambah Soal
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-block table-border-style">
                                        @error('question_ids')
                                            <div class="alert alert-danger">{{ $message }}</div>
                                        @enderror
                                        @if ($attachedQuestions->isNotEmpty())
                                            {{-- Form hapus massal; checkbox di tabel terhubung lewat atribut form="bulk-detach-form" --}}
                                            <form id="bulk-detach-form" action="{{ route('instructor.quizzes.detach_questions', $quiz) }}" method="POST" class="d-flex flex-wrap align-items-center mb-3" style="gap: .5rem;" data-confirm="Hapus semua soal yang dipilih dari kuis ini? Soal tetap tersimpan di Bank Soal." data-confirm-danger data-confirm-ok="Ya, lepaskan">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" id="bulk-detach-btn" disabled>
                                                    <i class="fa fa-trash"></i> Hapus Terpilih (<span id="bulk-detach-count">0</span>)
                                                </button>
                                                <small class="text-muted">Centang soal di tabel, lalu hapus sekaligus dari kuis.</small>
                                            </form>
                                        @endif
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 1%;">
                                                            @if ($attachedQuestions->isNotEmpty())
                                                                <input type="checkbox" id="select-all-questions" aria-label="Pilih semua soal">
                                                            @endif
                                                        </th>
                                                        <th>#</th>
                                                        <th>Teks Soal</th>
                                                        <th>Tipe</th>
                                                        <th class="text-center">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($attachedQuestions as $question)
                                                        <tr>
                                                            <td>
                                                                <input type="checkbox" class="question-check" name="question_ids[]" value="{{ $question->id }}" form="bulk-detach-form" aria-label="Pilih soal {{ $loop->iteration }}">
                                                            </td>
                                                            <th scope="row">{{ $loop->iteration }}</th>
                                                            <td>{{ $question->plainSummary(100) }}</td>
                                                            <td>{{ $question->type_label }}</td>
                                                            <td class="text-center">
                                                                {{-- Form untuk menghapus (detach) soal dari kuis --}}
                                                                <form action="{{ route('instructor.quizzes.detach_question', ['quiz' => $quiz, 'question' => $question]) }}" method="POST" data-confirm="Apakah Anda yakin ingin menghapus soal ini dari kuis?" data-confirm-danger data-confirm-ok="Ya, lepaskan">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                                        <i class="fa fa-trash"></i> Hapus
                                                                    </button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center">Belum ada soal di dalam kuis ini.</td>
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

@push('scripts')
    <script>
        (function () {
            const selectAll = document.getElementById('select-all-questions');
            const button = document.getElementById('bulk-detach-btn');
            const count = document.getElementById('bulk-detach-count');
            if (!selectAll || !button) { return; }
            const checks = Array.from(document.querySelectorAll('.question-check'));

            function refresh() {
                const checked = checks.filter(function (c) { return c.checked; }).length;
                count.textContent = checked;
                button.disabled = checked === 0;
                selectAll.checked = checked > 0 && checked === checks.length;
                selectAll.indeterminate = checked > 0 && checked < checks.length;
            }

            selectAll.addEventListener('change', function () {
                checks.forEach(function (c) { c.checked = selectAll.checked; });
                refresh();
            });
            checks.forEach(function (c) { c.addEventListener('change', refresh); });
            refresh();
        })();
    </script>
@endpush