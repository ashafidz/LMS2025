@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Course: {{ $module->course->title }}</h5>
                        <p class="m-b-10 fw-bolder" style="font-size: 2rem;">Kelola Pelajaran Modul: {{ $module->title }}</p>
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
                                    <div class="card-header-right d-none d-md-block">
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#selectLessonTypeModal">
                                            <i class="bi bi-plus-lg text-white"></i> Buat Pelajaran Baru
                                        </button>
                                    </div>
                                </div>
                                <div class="card-block">
                                    <div class="d-block d-md-none mb-3">
                                        <button type="button" class="btn btn-primary w-100" data-toggle="modal" data-target="#selectLessonTypeModal">
                                            <i class="bi bi-plus-lg text-white me-1"></i> Buat Pelajaran Baru
                                        </button>
                                    </div>
                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif

                                    <div id="lesson-list">
                                        @forelse ($lessons as $lesson)
                                            <div class="card" data-id="{{ $lesson->id }}">
                                                <div class="card-body d-flex justify-content-between align-items-center p-3">
                                                    <div class="d-flex align-items-center overflow-hidden me-2 flex-grow-1" style="min-width: 0;">
                                                        <i class="fa fa-bars handle text-muted mr-3 flex-shrink-0" style="cursor: move;"></i>
                                                        <div class="text-truncate">
                                                            <strong class="d-block mb-1 text-truncate">{{ $lesson->title }}</strong>
                                                            <span class="badge badge-info">{{ ucfirst(str_replace('lesson', '', strtolower(class_basename($lesson->lessonable_type)))) }}</span>
                                                        </div>
                                                    </div>
                                                    
                                                    @php $lessonType = strtolower(class_basename($lesson->lessonable_type)); @endphp
                                                    
                                                    <div class="d-flex align-items-center flex-shrink-0" style="gap: 5px;">
                                                        @if ($lessonType === 'quiz')
                                                            <a href="{{ route('instructor.quizzes.manage_questions', $lesson->lessonable) }}" class="btn btn-primary btn-sm" title="Kelola Soal">
                                                                <i class="fa fa-pencil-square"></i> <span class="d-none d-md-inline ms-1">Kelola Soal</span>
                                                            </a>
                                                        @else
                                                            <a href="{{ route('instructor.lessons.edit', $lesson) }}" class="btn btn-primary btn-sm" title="Edit Pelajaran">
                                                                <i class="fa fa-pencil"></i> <span class="d-none d-md-inline ms-1">Edit</span>
                                                            </a>
                                                        @endif
                                                        
                                                        <div class="dropdown">
                                                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false" title="Aksi Lainnya" data-boundary="window">
                                                                <i class="fa fa-cog"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right shadow">
                                                                @if ($lessonType === 'quiz')
                                                                    <a class="dropdown-item" href="{{ route('instructor.lessons.edit', $lesson) }}"><i class="fa fa-pencil text-info me-2"></i> Edit Judul Kuis</a>
                                                                    <a class="dropdown-item" href="{{ route('student.quiz.start', ['quiz' => $lesson->lessonable, 'preview' => 'true']) }}" target="_blank"><i class="bi bi-eye text-primary me-2"></i> Pratinjau Kuis</a>
                                                                    <div class="dropdown-divider"></div>
                                                                    <h6 class="dropdown-header">Laporan & Pengaturan</h6>
                                                                    <a class="dropdown-item" href="{{ route('instructor.quiz.results', $lesson->lessonable) }}"><i class="fa fa-calculator text-success me-2"></i> Lihat Nilai Siswa</a>
                                                                    <a class="dropdown-item" href="{{ route('instructor.quiz.security.edit', $lesson->lessonable) }}"><i class="fa fa-shield text-info me-2"></i> Opsi Keamanan Kuis</a>
                                                                    <a class="dropdown-item" href="{{ route('instructor.quiz.monitoring.review', $lesson->lessonable) }}"><i class="fa fa-eye text-warning me-2"></i> Hasil Pelanggaran</a>
                                                                @else
                                                                    <button class="dropdown-item" type="button" data-toggle="modal" data-target="#previewModal-{{ $lesson->id }}">
                                                                        <i class="bi bi-eye text-primary me-2"></i> Pratinjau
                                                                    </button>
                                                                    @if ($lessonType === 'lessonassignment')
                                                                        <a class="dropdown-item" href="{{ route('instructor.assignment.submissions', $lesson->lessonable) }}"><i class="fas fa-file-alt text-success me-2"></i> Lihat Pengumpulan</a>
                                                                    @endif
                                                                    @if ($lessonType === 'lessonpoint')
                                                                        <a class="dropdown-item" href="{{ route('instructor.lesson_points.manage', $lesson) }}"><i class="bi bi-gear-fill text-info me-2"></i> Kelola LessonPoin</a>
                                                                    @endif
                                                                    @if ($lessonType === 'lessonpolling')
                                                                        <a class="dropdown-item" href="{{ route('instructor.lessons.polling.results', $lesson) }}"><i class="fa fa-bar-chart text-success me-2"></i> Lihat Hasil Polling</a>
                                                                    @endif
                                                                    @if ($lessonType === 'lessonwordcloud')
                                                                        <a class="dropdown-item" href="{{ route('instructor.lessons.wordcloud.results', $lesson) }}"><i class="fa fa-cloud text-info me-2"></i> Lihat Hasil Word Cloud</a>
                                                                    @endif
                                                                @endif
                                                                
                                                                <div class="dropdown-divider"></div>
                                                                <form action="{{ route('instructor.lessons.destroy', $lesson) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelajaran ini?');">
                                                                    @csrf @method('DELETE')
                                                                    <button type="submit" class="dropdown-item text-danger">
                                                                        <i class="fa fa-trash text-danger me-2"></i> Hapus Pelajaran
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
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
                        <a href="{{ route('instructor.modules.lessons.create', ['module' => $module, 'type' => 'polling']) }}" class="list-group-item list-group-item-action"><i class="bi bi-bar-chart-fill"></i> <strong>Polling</strong><br><small>Buat polling interaktif untuk siswa.</small></a>
                        <a href="{{ route('instructor.modules.lessons.create', ['module' => $module, 'type' => 'wordcloud']) }}" class="list-group-item list-group-item-action"><i class="bi bi-cloud-fill"></i> <strong>Word Cloud</strong><br><small>Kumpulkan kata-kata interaktif ke dalam Word Cloud.</small></a>
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
    <style>
        .sortable-ghost {
            opacity: 0.4;
            background-color: #f8f9fa;
            border: 2px dashed #007bff !important;
        }
        .sortable-chosen {
            background-color: #e9ecef;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
            transform: scale(1.02);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .handle {
            cursor: grab !important;
        }
        .handle:active {
            cursor: grabbing !important;
        }
        #lesson-list .card {
            transition: transform 0.2s ease;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const el = document.getElementById('lesson-list');
            if (el) {
                const sortable = new Sortable(el, {
                    handle: '.handle',
                    animation: 350,
                    easing: "cubic-bezier(1, 0, 0, 1)",
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
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