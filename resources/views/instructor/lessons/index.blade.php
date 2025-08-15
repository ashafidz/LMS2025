@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Kelola Pelajaran: {{ $module->title }}</h5>
                        <p class="m-b-0">Atur semua pelajaran untuk modul ini.</p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"> <i class="fa fa-home"></i> </a></li>
                        <li class="breadcrumb-item"><a href="{{ route('instructor.courses.index') }}">Kursus Saya</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('instructor.courses.modules.index', $module->course) }}">Modul Saya</a></li>
                        <li class="breadcrumb-item"><a href="#!">{{ Str::limit($module->title, 20) }}</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Daftar Pelajaran</h5>
                                    <span>Seret dan lepas pelajaran untuk mengubah urutan.</span>
                                    <div class="card-header-right">
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#selectLessonTypeModal">
                                            <i class="bi bi-plus-lg text-white"></i> Buat Pelajaran Baru
                                        </button>
                                    </div>
                                </div>
                                <div class="card-block">
                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif

                                    <div id="lesson-list">
                                        @forelse ($lessons as $lesson)
                                            <div class="card" data-id="{{ $lesson->id }}">
                                                <div class="card-body d-flex justify-content-between align-items-center p-3">
                                                    <div>
                                                        <i class="fa fa-bars handle text-muted mr-3" style="cursor: move;"></i>
                                                        <span class="badge badge-info ml-2">{{ ucfirst(str_replace('lesson', '', strtolower(class_basename($lesson->lessonable_type)))) }}</span>
                                                        <strong>{{ $lesson->title }}</strong>
                                                    </div>

                                                    <div>
                                                        @php
                                                            $lessonType = strtolower(class_basename($lesson->lessonable_type));
                                                        @endphp

                                                        @if ($lessonType === 'quiz')
                                                            <a href="{{ route('student.quiz.start', ['quiz' => $lesson->lessonable_id, 'preview' => 'true']) }}" target="_blank" class="btn btn-warning btn-sm text-dark" title="Pratinjau Kuis di Tab Baru">
                                                                <i class="bi bi-eye me-1"></i>Pratinjau
                                                            </a>
                                                        @else
                                                            <button type="button" class="btn btn-warning btn-sm text-dark" data-toggle="modal" data-target="#previewModal-{{ $lesson->id }}"><i class="bi bi-eye me-1"></i>
                                                                Pratinjau
                                                            </button>
                                                        @endif

                                                        <a href="{{ route('instructor.lessons.edit', $lesson->id) }}" class="btn btn-primary btn-sm"><i class="fa fa-pencil"></i>Edit</a>

                                                        @if ($lessonType === 'quiz')
                                                            <a href="{{ route('instructor.quiz.results', $lesson->lessonable_id) }}" class="btn btn-info btn-sm"><i class="fa fa-calculator me-1"></i>Lihat Nilai</a>
                                                            <a href="{{ route('instructor.quizzes.manage_questions', $lesson->lessonable_id) }}" class="btn btn-success btn-sm"><i class="fa fa-pencil-square me-1"></i>Kelola Soal</a>
                                                        @endif
                                                        
                                                        {{-- TOMBOL BARU UNTUK TUGAS (ASSIGNMENT) --}}
                                                        @if ($lessonType === 'lessonassignment')
                                                            <a href="{{ route('instructor.assignment.submissions', $lesson->lessonable_id) }}" class="btn btn-success btn-sm"><i class="fas fa-file-alt"></i>Lihat Pengumpulan</a>
                                                        @endif

                                                        @if ($lessonType === 'lessonpoint')
                                                            <a href="{{ route('instructor.lesson_points.manage', $lesson->id) }}" class="btn btn-success btn-sm"><i class="bi bi-gear-fill"></i>Kelola LessonPoin</a>
                                                        @endif

                                                        <form action="{{ route('instructor.lessons.destroy', $lesson->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelajaran ini?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i>Hapus</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center">
                                                <p>Belum ada pelajaran di modul ini. Silakan buat yang pertama!</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="selectLessonTypeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih Tipe Pelajaran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>Silakan pilih tipe konten yang ingin Anda buat untuk pelajaran ini.</p>
                    <div class="list-group">
                        <a href="{{ route('instructor.modules.lessons.create', ['module' => $module, 'type' => 'article']) }}" class="list-group-item list-group-item-action"><i class="bi bi-file-text"></i> <strong>Pelajaran Artikel</strong><br><small>Pelajaran berbasis teks dengan gambar.</small></a>
                        <a href="{{ route('instructor.modules.lessons.create', ['module' => $module, 'type' => 'video']) }}" class="list-group-item list-group-item-action"><i class="bi bi-collection-play"></i> <strong>Pelajaran Video</strong><br><small>Unggah file video atau tautkan dari YouTube.</small></a>
                        <a href="{{ route('instructor.modules.lessons.create', ['module' => $module, 'type' => 'document']) }}" class="list-group-item list-group-item-action"><i class="bi bi-file-earmark-pdf"></i> <strong>Pelajaran Dokumen (PDF)</strong><br><small>Unggah file PDF sebagai materi pelajaran.</small></a>
                        <a href="{{ route('instructor.modules.lessons.create', ['module' => $module, 'type' => 'link']) }}" class="list-group-item list-group-item-action"><i class="bi bi-folder2-open"></i> <strong>Pelajaran Kumpulan Link</strong><br><small>Bagikan beberapa tautan/referensi eksternal.</small></a>
                        <a href="{{ route('instructor.modules.lessons.create', ['module' => $module, 'type' => 'quiz']) }}" class="list-group-item list-group-item-action"><i class="bi bi-pencil-square"></i> <strong>Kuis</strong><br><small>Buat kuis untuk menguji pemahaman siswa.</small></a>
                        <a href="{{ route('instructor.modules.lessons.create', ['module' => $module, 'type' => 'assignment']) }}" class="list-group-item list-group-item-action"><i class="bi bi-clipboard2"></i> <strong>Tugas (Assignment)</strong><br><small>Berikan tugas yang memerlukan pengumpulan file.</small></a>
                        <a href="{{ route('instructor.modules.lessons.create', ['module' => $module, 'type' => 'lessonpoin']) }}" class="list-group-item list-group-item-action"><i class="bi bi-chat-left-quote"></i> <strong>LessonPoint</strong><br><small>Buat sesi LessonPoin baru.</small></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach ($lessons as $lesson)
        @if (strtolower(class_basename($lesson->lessonable_type)) !== 'quiz')
            <div class="modal fade" id="previewModal-{{ $lesson->id }}" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Pratinjau: {{ $lesson->title }}</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            @php $lessonType = strtolower(class_basename($lesson->lessonable_type)); @endphp
                            @if (view()->exists('instructor.lessons.previews._' . $lessonType))
                                @include('instructor.lessons.previews._' . $lessonType, ['lesson' => $lesson])
                            @else
                                <p class="text-muted text-center">Pratinjau untuk tipe pelajaran '{{ $lessonType }}' belum tersedia.</p>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const el = document.getElementById('lesson-list');
            if (el) {
                const sortable = new Sortable(el, {
                    handle: '.handle',
                    animation: 150,
                    onEnd: function (evt) {
                        const lessonIds = Array.from(el.children).map(child => child.dataset.id);
                        fetch('{{ route("instructor.modules.lessons.reorder", $module) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({ lesson_ids: lessonIds })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if(data.status === 'success') { console.log(data.message); } 
                            else { alert('Gagal memperbarui urutan.'); }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Terjadi kesalahan.');
                        });
                    }
                });
            }
        });
    </script>
@endpush