@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">

    {{-- Page Header --}}
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Kelola Konten Adaptive</h5>
                        <p class="m-b-0">Kursus: <strong>{{ $course->title }}</strong></p>
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('instructor.kmeans.show', $course) }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-bar-chart"></i> Lihat Analisis K-Means
                    </a>
                    <a href="{{ route('instructor.courses.index') }}" class="btn btn-light btn-sm ml-1">
                        <i class="fa fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">

                    {{-- Flash Messages --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fa fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    @endif

                    {{-- ====================================================
                         ARCHETYPE TABS
                    ===================================================== --}}
                    <div class="card">
                        <div class="card-header p-0">
                            <ul class="nav nav-tabs card-header-tabs flex-nowrap" style="overflow-x:auto;" id="archetype-tabs">
                                @foreach($archetypes as $name => $description)
                                    <li class="nav-item">
                                        <a class="nav-link {{ $activeArchetype === $name ? 'active' : '' }} text-nowrap px-3 py-3"
                                           href="{{ route('instructor.adaptive.index', [$course, 'archetype' => $name]) }}">
                                            <i class="fa fa-users mr-1"></i>
                                            {{ $name }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="card-block">

                            {{-- Archetype Info Banner --}}
                            <div class="alert alert-info d-flex align-items-start mb-4" style="border-left: 4px solid #1abc9c;">
                                <i class="fa fa-info-circle fa-lg mr-3 mt-1 text-info"></i>
                                <div>
                                    <strong>{{ $activeArchetype }}</strong><br>
                                    <small class="text-muted">{{ $archetypes[$activeArchetype] }}</small>
                                </div>
                            </div>

                            {{-- ================================================
                                 ACTION BAR
                            ================================================= --}}
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="m-0 text-muted">
                                    <i class="fa fa-folder-open mr-1"></i>
                                    {{ $modules->count() }} Modul
                                </h6>
                                <div>
                                    <button type="button" class="btn btn-success btn-sm"
                                            data-toggle="modal" data-target="#modal-add-module">
                                        <i class="fa fa-plus"></i> Tambah Modul
                                    </button>
                                </div>
                            </div>

                            {{-- ================================================
                                 MODULE LIST
                            ================================================= --}}
                            @forelse($modules as $moduleIndex => $module)
                                <div class="card border mb-3" id="module-{{ $module->id }}">
                                    {{-- Module Header --}}
                                    <div class="card-header d-flex justify-content-between align-items-center bg-light py-2">
                                        <div>
                                            <span class="badge badge-secondary mr-2">Modul {{ $moduleIndex + 1 }}</span>
                                            <strong>{{ $module->title }}</strong>
                                            @if($module->ai_generated)
                                                <span class="badge badge-warning ml-1" title="Dibuat oleh AI">
                                                    <i class="fa fa-magic"></i> AI
                                                </span>
                                            @endif
                                            @if($module->description)
                                                <small class="text-muted ml-2">— {{ Str::limit($module->description, 80) }}</small>
                                            @endif
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <button class="btn btn-xs btn-outline-primary mr-1"
                                                    data-toggle="modal"
                                                    data-target="#modal-edit-module-{{ $module->id }}">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                            <form action="{{ route('instructor.adaptive.modules.destroy', [$course, $module]) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Hapus modul ini beserta semua lesson-nya?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-xs btn-outline-danger">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    {{-- Lesson List --}}
                                    <div class="card-block p-3">
                                        @forelse($module->lessons as $lessonIndex => $lesson)
                                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                                <div>
                                                    <i class="fa fa-file-text-o text-muted mr-2"></i>
                                                    <span>{{ $lessonIndex + 1 }}. {{ $lesson->title }}</span>
                                                    @if($lesson->ai_generated)
                                                        <span class="badge badge-warning ml-1" title="Dibuat oleh AI">
                                                            <i class="fa fa-magic"></i> AI
                                                        </span>
                                                    @endif
                                                </div>
                                                <div>
                                                    <button class="btn btn-xs btn-outline-primary mr-1"
                                                            data-toggle="modal"
                                                            data-target="#modal-edit-lesson-{{ $lesson->id }}">
                                                        <i class="fa fa-pencil"></i> Edit
                                                    </button>
                                                    <form action="{{ route('instructor.adaptive.lessons.destroy', [$course, $lesson]) }}"
                                                          method="POST" class="d-inline"
                                                          onsubmit="return confirm('Hapus lesson ini?')">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-xs btn-outline-danger">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>

                                            {{-- Modal Edit Lesson --}}
                                            <div class="modal fade" id="modal-edit-lesson-{{ $lesson->id }}" tabindex="-1">
                                                <div class="modal-dialog modal-xl">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Edit Lesson</h5>
                                                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                        </div>
                                                        <form action="{{ route('instructor.adaptive.lessons.update', [$course, $lesson]) }}" method="POST">
                                                            @csrf @method('PUT')
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label>Judul Lesson</label>
                                                                    <input type="text" name="title" class="form-control"
                                                                           value="{{ old('title', $lesson->title) }}" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Konten Artikel</label>
                                                                    <textarea id="edit-lesson-content-{{ $lesson->id }}"
                                                                              name="content" class="form-control tinymce-editor"
                                                                              rows="15">{!! old('content', $lesson->content) !!}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-primary">
                                                                    <i class="fa fa-save"></i> Simpan Perubahan
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                        @empty
                                            <p class="text-muted text-center py-3 mb-0">
                                                <i class="fa fa-inbox fa-2x d-block mb-2"></i>
                                                Belum ada lesson. Tambahkan lesson pertama di bawah.
                                            </p>
                                        @endforelse

                                        {{-- Add Lesson Button --}}
                                        <div class="mt-3">
                                            <button type="button" class="btn btn-outline-success btn-sm"
                                                    data-toggle="modal"
                                                    data-target="#modal-add-lesson-{{ $module->id }}">
                                                <i class="fa fa-plus"></i> Tambah Lesson ke Modul Ini
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Modal Edit Module --}}
                                <div class="modal fade" id="modal-edit-module-{{ $module->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Modul</h5>
                                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                            </div>
                                            <form action="{{ route('instructor.adaptive.modules.update', [$course, $module]) }}" method="POST">
                                                @csrf @method('PUT')
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label>Judul Modul <span class="text-danger">*</span></label>
                                                        <input type="text" name="title" class="form-control"
                                                               value="{{ old('title', $module->title) }}" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Deskripsi</label>
                                                        <textarea name="description" class="form-control" rows="3">{{ old('description', $module->description) }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fa fa-save"></i> Simpan
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Modal Add Lesson --}}
                                <div class="modal fade" id="modal-add-lesson-{{ $module->id }}" tabindex="-1">
                                    <div class="modal-dialog modal-xl">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    Tambah Lesson — <em>{{ $module->title }}</em>
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                            </div>
                                            <form action="{{ route('instructor.adaptive.lessons.store', [$course, $module]) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label>Judul Lesson <span class="text-danger">*</span></label>
                                                        <input type="text" name="title" class="form-control"
                                                               placeholder="Masukkan judul lesson..." required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Konten Artikel</label>
                                                        <textarea id="new-lesson-content-{{ $module->id }}"
                                                                  name="content" class="form-control tinymce-editor"
                                                                  rows="15" placeholder="Isi konten lesson..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-success">
                                                        <i class="fa fa-plus"></i> Tambah Lesson
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            @empty
                                <div class="text-center py-5">
                                    <i class="fa fa-folder-open-o fa-4x text-muted mb-3 d-block"></i>
                                    <h6 class="text-muted">Belum ada modul untuk kluster <strong>{{ $activeArchetype }}</strong></h6>
                                    <p class="text-muted small">Tambahkan modul secara manual atau gunakan AI untuk membuat kurikulum.</p>
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal-add-module">
                                        <i class="fa fa-plus"></i> Tambah Modul Pertama
                                    </button>
                                </div>
                            @endforelse

                        </div>{{-- end card-block --}}
                    </div>{{-- end card --}}

                </div>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     MODAL: Tambah Modul Baru
============================================================= --}}
<div class="modal fade" id="modal-add-module" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-folder-o mr-1"></i> Tambah Modul Baru</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ route('instructor.adaptive.modules.store', $course) }}" method="POST">
                @csrf
                <input type="hidden" name="archetype_name" value="{{ $activeArchetype }}">
                <div class="modal-body">
                    <div class="alert alert-light border">
                        <small><i class="fa fa-users mr-1"></i> Modul ini akan ditambahkan ke kluster:
                            <strong>{{ $activeArchetype }}</strong>
                        </small>
                    </div>
                    <div class="form-group">
                        <label>Judul Modul <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control"
                               placeholder="Contoh: Dasar-dasar Machine Learning" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi <small class="text-muted">(opsional)</small></label>
                        <textarea name="description" class="form-control" rows="3"
                                  placeholder="Deskripsi singkat tentang isi modul ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-plus"></i> Buat Modul
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    {{-- TinyMCE v6 — reuse API key yang sudah ada di project --}}
    <script src="https://cdn.tiny.cloud/1/fl2a5lp7k46s1mglp4rekz1mbeugac2hok87g2ca88v4mwja/tinymce/6/tinymce.min.js"
            referrerpolicy="origin"></script>
    <script>
        // Inisialisasi TinyMCE pada semua textarea dengan class .tinymce-editor
        // termasuk yang ada di dalam modal Bootstrap
        function initTinyMCE(selector) {
            if (tinymce.get(selector.replace('#', ''))) return; // Cegah duplikat init
            tinymce.init({
                selector: selector,
                plugins: 'code table lists image link',
                toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table | link',
                height: 400,
                promotion: false,
                branding: false,
            });
        }

        // Init TinyMCE saat modal dibuka (lazy init agar tidak crash sebelum modal terbuka)
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-toggle="modal"]').forEach(function (trigger) {
                trigger.addEventListener('click', function () {
                    const targetId = this.getAttribute('data-target');
                    setTimeout(function () {
                        const modal = document.querySelector(targetId);
                        if (!modal) return;
                        modal.querySelectorAll('textarea.tinymce-editor').forEach(function (ta) {
                            if (!ta.id) ta.id = 'tinymce-' + Math.random().toString(36).substr(2, 9);
                            initTinyMCE('#' + ta.id);
                        });
                    }, 300); // Tunggu animasi modal selesai
                });
            });
        });
    </script>
@endpush
