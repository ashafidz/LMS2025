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
                                 SPLIT LAYOUT: LEFT (MODULES), RIGHT (AI CHAT)
                            ================================================= --}}
                            <div class="row">
                                <div class="col-lg-7">
                                    {{-- ACTION BAR --}}
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

                                    {{-- MODULE LIST --}}
                                    <div style="max-height: 600px; overflow-y: auto; padding-right: 5px;">
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
                                                        <br><small class="text-muted">— {{ Str::limit($module->description, 80) }}</small>
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
                                                            <button class="btn btn-xs btn-outline-info mr-1"
                                                                    data-toggle="modal"
                                                                    data-target="#modal-preview-lesson-{{ $lesson->id }}">
                                                                <i class="fa fa-eye"></i> Preview
                                                            </button>
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

                                                    {{-- Modal Preview Lesson --}}
                                                    <div class="modal fade" id="modal-preview-lesson-{{ $lesson->id }}" tabindex="-1">
                                                        <div class="modal-dialog modal-lg">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title"><i class="fa fa-eye"></i> Preview: {{ $lesson->title }}</h5>
                                                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="p-3 bg-light border rounded" style="max-height: 60vh; overflow-y: auto;">
                                                                        {!! $lesson->content !!}
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                                                </div>
                                                            </div>
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
                                                        <i class="fa fa-plus"></i> Tambah Lesson Manual
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
                                            <p class="text-muted small">Tambahkan modul secara manual atau minta AI Co-Pilot merancangnya.</p>
                                        </div>
                                    @endforelse
                                    </div>
                                </div>
                                {{-- AI GENERATION FORM & PROGRESS --}}
                                <div class="col-lg-5">
                                    <div class="card border-primary" style="height: 100%; border: 1px solid #007bff;">
                                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center p-2">
                                            <h6 class="m-0 text-white"><i class="fa fa-magic mr-1"></i> AI Generator</h6>
                                            <button class="btn btn-sm btn-light text-primary" data-toggle="modal" data-target="#modal-ai-references">
                                                <i class="fa fa-paperclip"></i> RAG Referensi
                                            </button>
                                        </div>
                                        <div class="card-body bg-light" id="ai-panel-body">
                                            
                                            {{-- PROGRESS TRACKER (Hidden initially if no active job) --}}
                                            <div id="job-tracker" class="{{ isset($activeJob) ? '' : 'd-none' }}">
                                                <div class="text-center mb-3">
                                                    <i class="fa fa-cogs fa-3x text-primary fa-spin mb-2"></i>
                                                    <h6 class="text-primary">AI Sedang Bekerja...</h6>
                                                    <p class="small text-muted" id="job-message">{{ isset($activeJob) ? $activeJob->message : '' }}</p>
                                                </div>
                                                <div class="progress mb-2" style="height: 20px;">
                                                    <div id="job-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: {{ isset($activeJob) ? $activeJob->progress : 0 }}%;" aria-valuenow="{{ isset($activeJob) ? $activeJob->progress : 0 }}" aria-valuemin="0" aria-valuemax="100">{{ isset($activeJob) ? $activeJob->progress : 0 }}%</div>
                                                </div>
                                                <div class="alert alert-warning small">
                                                    <i class="fa fa-info-circle"></i> Proses ini memakan waktu beberapa menit. Anda boleh meninggalkan halaman ini atau me-refresh, proses akan tetap berjalan di latar belakang.
                                                </div>
                                            </div>

                                            {{-- FORM GENERATION --}}
                                            <div id="ai-form-container" class="{{ isset($activeJob) ? 'd-none' : '' }}">
                                                <form id="ai-generate-form">
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold">Tipe Generasi</label>
                                                        <select class="form-control form-control-sm" id="gen-type" name="type">
                                                            <option value="full">Full Curriculum (Modul & Lesson)</option>
                                                            <option value="modules">Hanya Modul (Tanpa Lesson)</option>
                                                        </select>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <div class="form-group">
                                                                <label class="small font-weight-bold">Jumlah Modul</label>
                                                                <input type="number" class="form-control form-control-sm" id="gen-modules" name="module_count" min="1" max="10" value="3" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="form-group" id="lesson-group">
                                                                <label class="small font-weight-bold">Lesson per Modul</label>
                                                                <input type="number" class="form-control form-control-sm" id="gen-lessons" name="lesson_count" min="1" max="5" value="2">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label class="small font-weight-bold">Fokus / Topik Tambahan <span class="text-muted font-weight-normal">(Opsional)</span></label>
                                                        <textarea class="form-control form-control-sm" id="gen-topics" name="extra_topics" rows="3" placeholder="Contoh: Fokus pada studi kasus industri..."></textarea>
                                                    </div>
                                                    
                                                    <button type="submit" class="btn btn-primary btn-block" id="btn-generate">
                                                        <i class="fa fa-magic"></i> Mulai Generate
                                                    </button>
                                                </form>
                                                <hr>
                                                <div class="text-center">
                                                    <small class="text-muted"><i class="fa fa-info-circle"></i> AI akan merancang silabus berdasarkan pengaturan di atas dan dokumen RAG yang Anda unggah.</small>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- END SPLIT LAYOUT --}}

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
                    <div class="form-group">
                        <label>Judul Modul <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control"
                               placeholder="Contoh: Dasar-dasar Machine Learning" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi <small class="text-muted">(opsional)</small></label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
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
     MODAL: AI References (RAG)
============================================================= --}}
<div class="modal fade" id="modal-ai-references" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-paperclip"></i> Dokumen Referensi (RAG)</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info small">
                    Unggah dokumen PDF atau TXT. AI Co-Pilot akan menggunakan dokumen ini sebagai referensi (konteks) dalam merancang materi kursus.
                </div>
                <form id="form-upload-reference" enctype="multipart/form-data">
                    <input type="file" name="file" id="reference-file" class="form-control mb-2" accept=".pdf,.txt,.md" required>
                    <button type="submit" class="btn btn-success btn-sm btn-block" id="btn-upload-ref">
                        <i class="fa fa-upload"></i> Unggah Referensi
                    </button>
                </form>
                <hr>
                <ul id="reference-list" class="list-group list-group-flush">
                    @forelse($references as $ref)
                        <li class="list-group-item d-flex justify-content-between align-items-center px-2 py-1 small">
                            <span class="text-truncate" style="max-width: 80%;"><i class="fa fa-file-text-o mr-1"></i> {{ $ref->original_filename }}</span>
                            <button type="button" class="btn btn-xs btn-danger btn-delete-ref" data-id="{{ $ref->id }}"><i class="fa fa-trash"></i></button>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted small py-2">Belum ada referensi.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    {{-- TinyMCE v6 --}}
    <script src="https://cdn.tiny.cloud/1/fl2a5lp7k46s1mglp4rekz1mbeugac2hok87g2ca88v4mwja/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    {{-- Marked.js for parsing AI Markdown --}}
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    
    <script>
        // Init TinyMCE
        function initTinyMCE(selector) {
            if (tinymce.get(selector.replace('#', ''))) return;
            tinymce.init({
                selector: selector,
                plugins: 'code table lists image link',
                toolbar: 'undo redo | blocks | bold italic underline | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table | link',
                height: 400,
                promotion: false,
                branding: false,
            });
        }

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
                    }, 300);
                });
            });

            // ==========================================
            // AI GENERATOR & POLLING LOGIC
            // ==========================================
            const courseId = '{{ $course->id }}';
            const archetype = '{{ $activeArchetype }}';
            const csrfToken = '{{ csrf_token() }}';
            
            const formContainer = document.getElementById('ai-form-container');
            const jobTracker = document.getElementById('job-tracker');
            const aiForm = document.getElementById('ai-generate-form');
            const btnGenerate = document.getElementById('btn-generate');
            const genType = document.getElementById('gen-type');
            const lessonGroup = document.getElementById('lesson-group');
            const refForm = document.getElementById('form-upload-reference');
            const refList = document.getElementById('reference-list');
            
            let activeJobId = {{ isset($activeJob) ? $activeJob->id : 'null' }};
            let pollInterval = null;

            // Toggle lesson count input based on type
            genType.addEventListener('change', function() {
                if (this.value === 'modules') {
                    lessonGroup.style.display = 'none';
                    document.getElementById('gen-lessons').value = 0;
                } else {
                    lessonGroup.style.display = 'block';
                    document.getElementById('gen-lessons').value = 2;
                }
            });

            // Initialize delete reference buttons
            document.querySelectorAll('.btn-delete-ref').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    if(confirm('Hapus referensi ini?')) {
                        fetch(`/instructor/courses/${courseId}/adaptive/ai/references/${id}`, {
                            method: 'DELETE',
                            headers: { 'X-CSRF-TOKEN': csrfToken }
                        }).then(res => res.json()).then(data => {
                            location.reload();
                        });
                    }
                });
            });

            // Handle Form Submit
            aiForm.addEventListener('submit', function(e) {
                e.preventDefault();
                btnGenerate.disabled = true;
                btnGenerate.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memulai...';

                const type = genType.value;
                const endpoint = type === 'full' ? `/instructor/courses/${courseId}/adaptive/ai/generate-full` : `/instructor/courses/${courseId}/adaptive/ai/generate-modules`;
                
                const payload = {
                    archetype_name: archetype,
                    module_count: document.getElementById('gen-modules').value,
                    extra_topics: document.getElementById('gen-topics').value
                };
                if (type === 'full') {
                    payload.lesson_count = document.getElementById('gen-lessons').value;
                } else {
                    payload.count = document.getElementById('gen-modules').value;
                }

                fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.job_id) {
                        activeJobId = data.job_id;
                        formContainer.classList.add('d-none');
                        jobTracker.classList.remove('d-none');
                        startPolling();
                    } else {
                        alert('Gagal memulai proses AI.');
                        btnGenerate.disabled = false;
                        btnGenerate.innerHTML = '<i class="fa fa-magic"></i> Mulai Generate';
                    }
                })
                .catch(err => {
                    alert('Terjadi kesalahan.');
                    btnGenerate.disabled = false;
                    btnGenerate.innerHTML = '<i class="fa fa-magic"></i> Mulai Generate';
                });
            });

            function startPolling() {
                if (!activeJobId) return;
                
                pollInterval = setInterval(() => {
                    fetch(`/instructor/courses/${courseId}/adaptive/ai/status/${activeJobId}`)
                    .then(res => res.json())
                    .then(data => {
                        const pb = document.getElementById('job-progress-bar');
                        const msg = document.getElementById('job-message');
                        
                        pb.style.width = data.progress + '%';
                        pb.textContent = data.progress + '%';
                        msg.textContent = data.message;

                        if (data.status === 'completed') {
                            clearInterval(pollInterval);
                            pb.classList.remove('progress-bar-animated');
                            msg.innerHTML = '<span class="text-success"><i class="fa fa-check"></i> Selesai! Me-refresh halaman...</span>';
                            setTimeout(() => location.reload(), 2000);
                        } else if (data.status === 'failed') {
                            clearInterval(pollInterval);
                            pb.classList.remove('progress-bar-animated', 'bg-success');
                            pb.classList.add('bg-danger');
                            msg.innerHTML = `<span class="text-danger"><i class="fa fa-times"></i> Gagal: ${data.error}</span>`;
                            setTimeout(() => {
                                formContainer.classList.remove('d-none');
                                jobTracker.classList.add('d-none');
                                btnGenerate.disabled = false;
                                btnGenerate.innerHTML = '<i class="fa fa-magic"></i> Mulai Generate';
                            }, 5000);
                        }
                    });
                }, 3000); // poll every 3 seconds
            }

            refForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = document.getElementById('btn-upload-ref');
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Mengunggah...';

                const formData = new FormData(this);
                formData.append('archetype', archetype);

                fetch(`/instructor/courses/${courseId}/adaptive/ai/references`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if(data.error) {
                        alert(data.error);
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-upload"></i> Unggah Referensi';
                    } else {
                        alert('Referensi berhasil diunggah!');
                        location.reload(); // Simple reload to show new reference
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-upload"></i> Unggah Referensi';
                    alert("Terjadi kesalahan unggah.");
                });
            });

            // If there's an active job on page load, start polling immediately
            if (activeJobId) {
                startPolling();
            }
        });
    </script>
@endpush
