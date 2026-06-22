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
                        <div class="card-header">
                            <h5>Pilih Kluster Archetype</h5>
                            <span>Kelola modul dan lesson spesifik untuk setiap archetype.</span>
                        </div>
                        <div class="card-block">
                            <ul class="nav nav-tabs md-tabs" role="tablist">
                                @foreach($archetypes as $name => $description)
                                    <li class="nav-item">
                                        <a class="nav-link {{ $activeArchetype === $name ? 'active' : '' }}" 
                                           href="{{ route('instructor.adaptive.index', [$course, 'archetype' => $name]) }}" 
                                           role="tab">
                                            <i class="fa fa-users mr-1"></i> {{ $name }}
                                        </a>
                                        <div class="slide"></div>
                                    </li>
                                @endforeach
                            </ul>
                            
                            <div class="tab-content card-block">

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
                                    <div class="dropdown-primary dropdown d-inline-block ml-1">
                                        <button class="btn btn-warning btn-sm dropdown-toggle" type="button" id="dropdown-ai" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fa fa-magic"></i> Generate AI
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-ai">
                                            <a class="dropdown-item" href="#!" data-toggle="modal" data-target="#modal-ai-full">
                                                <i class="fa fa-book mr-2 text-primary"></i> Full Curriculum (Modul & Lesson)
                                            </a>
                                            <a class="dropdown-item" href="#!" data-toggle="modal" data-target="#modal-ai-modules">
                                                <i class="fa fa-folder mr-2 text-warning"></i> Hanya Modul Saja
                                            </a>
                                        </div>
                                    </div>
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
                                            <button type="button" class="btn btn-outline-warning btn-sm ml-1"
                                                    data-toggle="modal"
                                                    data-target="#modal-ai-lessons-{{ $module->id }}">
                                                <i class="fa fa-magic"></i> Generate Lesson AI
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

                                {{-- Modal Generate Lesson AI --}}
                                <div class="modal fade" id="modal-ai-lessons-{{ $module->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title text-warning"><i class="fa fa-magic mr-1"></i> Generate Lesson AI</h5>
                                                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                            </div>
                                            <form action="{{ url('#') }}" method="POST" class="ai-sync-form">
                                                @csrf
                                                <input type="hidden" name="module_id" value="{{ $module->id }}">
                                                <div class="modal-body">
                                                    <div class="alert alert-warning border border-warning">
                                                        <small><i class="fa fa-info-circle mr-1"></i> AI akan membuat lesson untuk modul <strong>{{ $module->title }}</strong> berdasarkan archetype <strong>{{ $activeArchetype }}</strong>.</small>
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Jumlah Lesson <span class="text-danger">*</span></label>
                                                        <select name="count" class="form-control" required>
                                                            <option value="1">1 Lesson</option>
                                                            <option value="2" selected>2 Lesson</option>
                                                            <option value="3">3 Lesson</option>
                                                            <option value="4">4 Lesson</option>
                                                            <option value="5">5 Lesson</option>
                                                        </select>
                                                    </div>
                                                    <p class="text-muted small mt-3"><i class="fa fa-clock-o mr-1"></i> Estimasi waktu: 10-30 detik. Mohon tunggu dan jangan tutup halaman.</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-warning ai-submit-btn">
                                                        <i class="fa fa-magic"></i> Generate Sekarang
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

                            </div>{{-- end tab-content --}}
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

{{-- ============================================================
     MODAL: Generate Modul Saja (AI)
============================================================= --}}
<div class="modal fade" id="modal-ai-modules" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-warning"><i class="fa fa-folder mr-1"></i> Generate Modul AI</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <form action="{{ url('#') }}" method="POST" class="ai-sync-form">
                @csrf
                <input type="hidden" name="archetype_name" value="{{ $activeArchetype }}">
                <div class="modal-body">
                    <div class="alert alert-warning border border-warning">
                        <small><i class="fa fa-info-circle mr-1"></i> AI akan membuat daftar modul saja (tanpa lesson di dalamnya) untuk kluster <strong>{{ $activeArchetype }}</strong>.</small>
                    </div>
                    <div class="form-group">
                        <label>Jumlah Modul <span class="text-danger">*</span></label>
                        <select name="count" class="form-control" required>
                            <option value="1">1 Modul</option>
                            <option value="2">2 Modul</option>
                            <option value="3" selected>3 Modul</option>
                            <option value="4">4 Modul</option>
                            <option value="5">5 Modul</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Topik Spesifik <small class="text-muted">(opsional)</small></label>
                        <input type="text" name="extra_topics" class="form-control" placeholder="Contoh: Fokus ke pengenalan algoritma...">
                    </div>
                    <p class="text-muted small mt-3"><i class="fa fa-clock-o mr-1"></i> Estimasi waktu: 5-10 detik.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning ai-submit-btn">
                        <i class="fa fa-magic"></i> Generate Modul
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ============================================================
     MODAL: Generate Full Curriculum (AI)
============================================================= --}}
<div class="modal fade" id="modal-ai-full" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-primary">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white"><i class="fa fa-book mr-1"></i> Generate Full Curriculum</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <small>Proses ini akan membuat beberapa modul sekaligus beserta lesson di dalamnya secara otomatis menggunakan AI.</small>
                </div>
                
                <form id="form-ai-full">
                    @csrf
                    <input type="hidden" name="archetype_name" value="{{ $activeArchetype }}">
                    
                    <div class="row">
                        <div class="col-6 form-group">
                            <label>Jumlah Modul</label>
                            <select name="module_count" class="form-control" required>
                                <option value="1">1 Modul</option>
                                <option value="2" selected>2 Modul</option>
                                <option value="3">3 Modul</option>
                            </select>
                        </div>
                        <div class="col-6 form-group">
                            <label>Lesson per Modul</label>
                            <select name="lesson_count" class="form-control" required>
                                <option value="1">1 Lesson</option>
                                <option value="2" selected>2 Lesson</option>
                                <option value="3">3 Lesson</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Topik Spesifik <small class="text-muted">(opsional)</small></label>
                        <input type="text" name="extra_topics" class="form-control" placeholder="Fokus materi...">
                    </div>
                </form>

                {{-- Status UI (Hidden by default) --}}
                <div id="ai-full-status" class="d-none mt-4 text-center">
                    <h6 class="text-primary mb-2">Sedang Memproses...</h6>
                    <div class="progress mb-2" style="height: 10px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" style="width: 100%"></div>
                    </div>
                    <p class="text-muted small" id="ai-full-status-text">Menyiapkan prompt AI...</p>
                    <p class="text-warning small"><i class="fa fa-exclamation-triangle"></i> Proses ini bisa memakan waktu 1-3 menit. Jangan tutup browser.</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" id="btn-ai-full-cancel">Batal</button>
                <button type="button" class="btn btn-primary" id="btn-ai-full-submit">
                    <i class="fa fa-magic"></i> Mulai Generate
                </button>
            </div>
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

            // Prevent multiple clicks on sync AI forms
            document.querySelectorAll('.ai-sync-form').forEach(form => {
                form.addEventListener('submit', function() {
                    const btn = this.querySelector('.ai-submit-btn');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Memproses...';
                    }
                });
            });

            // Async Full Curriculum Logic
            const btnFullSubmit = document.getElementById('btn-ai-full-submit');
            const btnFullCancel = document.getElementById('btn-ai-full-cancel');
            const formFull = document.getElementById('form-ai-full');
            const statusFull = document.getElementById('ai-full-status');
            const statusTextFull = document.getElementById('ai-full-status-text');

            if (btnFullSubmit) {
                btnFullSubmit.addEventListener('click', function() {
                    const formData = new FormData(formFull);
                    
                    // Update UI state
                    formFull.classList.add('d-none');
                    statusFull.classList.remove('d-none');
                    btnFullSubmit.disabled = true;
                    btnFullCancel.disabled = true;

                    fetch('{{ url('#') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'queued' && data.job_id) {
                            pollJobStatus(data.job_id);
                        } else {
                            handleJobError('Gagal memulai proses generasi AI.');
                        }
                    })
                    .catch(error => {
                        handleJobError('Terjadi kesalahan jaringan.');
                    });
                });
            }

            function pollJobStatus(jobId) {
                const interval = setInterval(() => {
                    fetch(`/courses/{{ $course->id }}/adaptive/ai/status/${jobId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'processing') {
                                statusTextFull.innerText = data.message || 'Memproses...';
                            } else if (data.status === 'completed') {
                                clearInterval(interval);
                                statusTextFull.innerText = 'Selesai! Memuat ulang halaman...';
                                statusTextFull.classList.remove('text-muted');
                                statusTextFull.classList.add('text-success', 'font-weight-bold');
                                setTimeout(() => window.location.reload(), 1500);
                            } else if (data.status === 'failed') {
                                clearInterval(interval);
                                handleJobError(data.message || 'Proses gagal.');
                            }
                        })
                        .catch(err => {
                            console.error(err);
                        });
                }, 3000); // poll every 3 seconds
            }

            function handleJobError(message) {
                statusTextFull.innerText = message;
                statusTextFull.classList.remove('text-muted');
                statusTextFull.classList.add('text-danger');
                btnFullCancel.disabled = false;
                btnFullCancel.innerText = 'Tutup';
            }
        });
    </script>
@endpush
