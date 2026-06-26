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
                                <div class="col-12">
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
                                            <button type="button" class="btn btn-primary btn-sm ml-2"
                                                    data-toggle="modal" data-target="#modal-ai-generator">
                                                <i class="fa fa-magic"></i> Buka AI Generator
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
                                                            @if($lesson->lesson_type === 'assignment')
                                                                <span class="badge badge-danger ml-1" title="Penugasan">
                                                                    <i class="fa fa-tasks"></i> Tugas
                                                                </span>
                                                            @elseif($lesson->lesson_type === 'quiz')
                                                                <span class="badge badge-success ml-1" title="Quiz">
                                                                    <i class="fa fa-check-square-o"></i> Quiz
                                                                </span>
                                                            @elseif($lesson->lesson_type === 'video')
                                                                <span class="badge badge-info ml-1" title="Video">
                                                                    <i class="fa fa-play-circle"></i> Video
                                                                </span>
                                                            @endif
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
                                                            @if($lesson->lesson_type === 'quiz')
                                                                <a href="{{ route('instructor.adaptive.lessons.quiz', [$course, $lesson]) }}" class="btn btn-xs btn-outline-success mr-1">
                                                                    <i class="fa fa-list-ol"></i> Kelola Soal
                                                                </a>
                                                            @endif
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
                                                                <div class="modal-header {{ $lesson->lesson_type === 'assignment' ? 'bg-danger text-white' : ($lesson->lesson_type === 'quiz' ? 'bg-success text-white' : '') }}">
                                                                    <h5 class="modal-title">
                                                                        @if($lesson->lesson_type === 'assignment')
                                                                            <i class="fa fa-tasks"></i> Penugasan: {{ $lesson->title }}
                                                                        @elseif($lesson->lesson_type === 'quiz')
                                                                            <i class="fa fa-check-square-o"></i> Quiz: {{ $lesson->title }}
                                                                        @elseif($lesson->lesson_type === 'video')
                                                                            <i class="fa fa-play-circle"></i> Video: {{ $lesson->title }}
                                                                        @else
                                                                            <i class="fa fa-eye"></i> Preview: {{ $lesson->title }}
                                                                        @endif
                                                                    </h5>
                                                                    <button type="button" class="close {{ in_array($lesson->lesson_type, ['assignment', 'quiz']) ? 'text-white' : '' }}" data-dismiss="modal"><span>&times;</span></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    @if($lesson->lesson_type === 'assignment')
                                                                        <div class="mb-3">
                                                                            <h6 class="text-muted text-uppercase small font-weight-bold">Deskripsi Tugas</h6>
                                                                            <div class="p-3 bg-light border rounded">{!! $lesson->content !!}</div>
                                                                        </div>
                                                                        @if($lesson->assignment_instructions)
                                                                        <div class="mb-2">
                                                                            <h6 class="text-muted text-uppercase small font-weight-bold">Instruksi Pengerjaan</h6>
                                                                            <div class="p-3 bg-light border rounded" style="max-height: 50vh; overflow-y: auto;">{!! $lesson->assignment_instructions !!}</div>
                                                                        </div>
                                                                        @endif
                                                                        <div class="d-flex align-items-center">
                                                                            <span class="badge badge-primary"><i class="fa fa-star"></i> Nilai Maks: {{ $lesson->assignment_max_score ?? 100 }}</span>
                                                                        </div>
                                                                    @elseif($lesson->lesson_type === 'quiz')
                                                                        <div class="mb-3">
                                                                            <h6 class="text-muted text-uppercase small font-weight-bold">Instruksi Quiz</h6>
                                                                            <div class="p-3 bg-light border rounded">{!! $lesson->content !!}</div>
                                                                        </div>
                                                                        <div class="mb-2">
                                                                            <h6 class="text-muted text-uppercase small font-weight-bold">Pertanyaan Quiz (Dibuat AI)</h6>
                                                                            <div class="p-3 bg-light border rounded" style="max-height: 50vh; overflow-y: auto;">
                                                                                @if(empty($lesson->quiz_data))
                                                                                    <p class="text-muted"><i>Belum ada pertanyaan quiz.</i></p>
                                                                                @else
                                                                                    @foreach($lesson->quiz_data as $qIndex => $qData)
                                                                                        <div class="mb-3 pb-3 border-bottom">
                                                                                            <p class="font-weight-bold mb-1">{{ $qIndex + 1 }}. {{ $qData['question_text'] ?? '' }}</p>
                                                                                            <ol type="A" class="mb-2">
                                                                                                @foreach($qData['options'] ?? [] as $optIndex => $optText)
                                                                                                    <li class="{{ isset($qData['correct_answer_index']) && $qData['correct_answer_index'] == $optIndex ? 'text-success font-weight-bold' : '' }}">
                                                                                                        {{ $optText }}
                                                                                                        @if(isset($qData['correct_answer_index']) && $qData['correct_answer_index'] == $optIndex)
                                                                                                            <i class="fa fa-check-circle ml-1"></i> (Jawaban Benar)
                                                                                                        @endif
                                                                                                    </li>
                                                                                                @endforeach
                                                                                            </ol>
                                                                                            @if(!empty($qData['explanation']))
                                                                                                <div class="small bg-white p-2 border border-info rounded text-info">
                                                                                                    <i class="fa fa-info-circle mr-1"></i> <strong>Penjelasan:</strong> {{ $qData['explanation'] }}
                                                                                                </div>
                                                                                            @endif
                                                                                        </div>
                                                                                    @endforeach
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    @elseif($lesson->lesson_type === 'video')
                                                                        <div class="mb-3">
                                                                            <h6 class="text-muted text-uppercase small font-weight-bold">URL Video</h6>
                                                                            <div class="p-3 bg-light border rounded">
                                                                                @if($lesson->video_url)
                                                                                    @php
                                                                                        $embedUrl = null;
                                                                                        if (str_contains($lesson->video_url, 'youtube.com/watch')) {
                                                                                            parse_str((string) parse_url($lesson->video_url, PHP_URL_QUERY), $vars);
                                                                                            if (isset($vars['v'])) $embedUrl = 'https://www.youtube.com/embed/' . $vars['v'];
                                                                                        } elseif (str_contains($lesson->video_url, 'youtu.be/')) {
                                                                                            $embedUrl = 'https://www.youtube.com/embed' . parse_url($lesson->video_url, PHP_URL_PATH);
                                                                                        }
                                                                                    @endphp
                                                                                    
                                                                                    @if($embedUrl)
                                                                                        <div class="embed-responsive embed-responsive-16by9 mb-3">
                                                                                            <iframe class="embed-responsive-item" src="{{ $embedUrl }}" allowfullscreen></iframe>
                                                                                        </div>
                                                                                    @else
                                                                                        <a href="{{ $lesson->video_url }}" target="_blank" class="btn btn-sm btn-danger"><i class="fa fa-youtube-play"></i> Tonton Video</a>
                                                                                        <div class="mt-2 text-muted small">{{ $lesson->video_url }}</div>
                                                                                    @endif
                                                                                @else
                                                                                    <span class="text-muted"><i>Tidak ada URL video yang dilampirkan.</i></span>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <h6 class="text-muted text-uppercase small font-weight-bold">Konten / Instruksi</h6>
                                                                            <div class="p-3 bg-light border rounded" style="max-height: 50vh; overflow-y: auto;">
                                                                                {!! $lesson->content ?? '<p class="text-muted"><i>Tidak ada konten.</i></p>' !!}
                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        <div style="max-height: 60vh; overflow-y: auto;" class="p-3 bg-light border rounded">
                                                                            {!! $lesson->content ?? '<p class="text-muted"><i>Tidak ada konten artikel.</i></p>' !!}
                                                                        </div>
                                                                    @endif
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
                                                                            <label>Tipe Lesson <span class="text-danger">*</span></label>
                                                                            <select name="lesson_type" class="form-control" onchange="toggleLessonTypeFields(this)">
                                                                                <option value="article" {{ $lesson->lesson_type === 'article' ? 'selected' : '' }}>Artikel</option>
                                                                                <option value="assignment" {{ $lesson->lesson_type === 'assignment' ? 'selected' : '' }}>Penugasan</option>
                                                                                <option value="quiz" {{ $lesson->lesson_type === 'quiz' ? 'selected' : '' }}>Quiz</option>
                                                                                <option value="video" {{ $lesson->lesson_type === 'video' ? 'selected' : '' }}>Video</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="video-fields" style="display: {{ $lesson->lesson_type === 'video' ? 'block' : 'none' }};">
                                                                            <div class="form-group">
                                                                                <label>URL Video <span class="text-muted">(opsional)</span></label>
                                                                                <input type="url" name="video_url" class="form-control" value="{{ old('video_url', $lesson->video_url) }}" placeholder="https://youtube.com/...">
                                                                            </div>
                                                                        </div>
                                                                        <div class="assignment-fields" style="display: {{ $lesson->lesson_type === 'assignment' ? 'block' : 'none' }};">
                                                                            <div class="form-group">
                                                                                <label>Skor Maksimal <span class="text-danger">*</span></label>
                                                                                <input type="number" name="assignment_max_score" class="form-control" value="{{ old('assignment_max_score', $lesson->assignment_max_score ?? 100) }}" min="1" max="1000">
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label>Instruksi Penugasan</label>
                                                                                <textarea name="assignment_instructions" class="form-control tinymce-editor" rows="5">{!! old('assignment_instructions', $lesson->assignment_instructions) !!}</textarea>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>Konten / Instruksi</label>
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
                                                                <label>Tipe Lesson <span class="text-danger">*</span></label>
                                                                <select name="lesson_type" class="form-control" onchange="toggleLessonTypeFields(this)">
                                                                    <option value="article">Artikel</option>
                                                                    <option value="assignment">Penugasan</option>
                                                                    <option value="quiz">Quiz</option>
                                                                    <option value="video">Video</option>
                                                                </select>
                                                            </div>
                                                            <div class="video-fields" style="display: none;">
                                                                <div class="form-group">
                                                                    <label>URL Video <span class="text-muted">(opsional)</span></label>
                                                                    <input type="url" name="video_url" class="form-control" placeholder="https://youtube.com/...">
                                                                </div>
                                                            </div>
                                                            <div class="assignment-fields" style="display: none;">
                                                                <div class="form-group">
                                                                    <label>Skor Maksimal <span class="text-danger">*</span></label>
                                                                    <input type="number" name="assignment_max_score" class="form-control" value="100" min="1" max="1000">
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Instruksi Penugasan</label>
                                                                    <textarea name="assignment_instructions" class="form-control tinymce-editor" rows="5"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>Konten / Instruksi</label>
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
                                {{-- MODAL AI GENERATOR --}}
                                <div class="modal fade" id="modal-ai-generator" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header bg-primary text-white d-flex justify-content-between align-items-center p-3">
                                                <h5 class="modal-title m-0"><i class="fa fa-magic"></i> AI Generator Co-Pilot</h5>
                                                <div>
                                                    <button class="btn btn-sm btn-light text-primary mr-2" data-toggle="modal" data-target="#modal-ai-references">
                                                        <i class="fa fa-paperclip"></i> RAG Referensi
                                                    </button>
                                                    <button type="button" class="close text-white d-inline-block" data-dismiss="modal" aria-label="Close" style="margin: -1rem -1rem -1rem auto; float: none;">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="modal-body bg-light" id="ai-panel-body">
                                                
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
                                                        <i class="fa fa-info-circle"></i> Proses ini memakan waktu beberapa menit. Anda boleh menutup modal ini, proses akan tetap berjalan di latar belakang.
                                                    </div>
                                                    <div class="text-center mt-2">
                                                        <button type="button" class="btn btn-danger btn-sm" id="btn-cancel-job" data-job-id="{{ isset($activeJob) ? $activeJob->id : '' }}">
                                                            <i class="fa fa-times-circle"></i> Batalkan Proses
                                                        </button>
                                                    </div>
                                                </div>

                                                {{-- FORM GENERATION --}}
                                                <div id="ai-form-container" class="{{ isset($activeJob) ? 'd-none' : '' }}">
                                                    <form id="ai-generate-form">
                                                        <div class="form-group">
                                                            <label class="small font-weight-bold">Tipe Generasi</label>
                                                            <select class="form-control form-control-sm" id="gen-type" name="type">
                                                                <option value="full">Full Curriculum (Modul &amp; Lesson)</option>
                                                                <option value="modules">Hanya Modul (Tanpa Lesson)</option>
                                                                <option value="lessons">Tambah Lesson ke Modul yang Ada</option>
                                                                <option value="assignments">Tambah Penugasan ke Modul yang Ada</option>
                                                                <option value="quizzes">Tambah Quiz ke Modul yang Ada</option>
                                                            </select>
                                                        </div>

                                                        {{-- Module selector (only for 'lessons' type) --}}
                                                        <div class="form-group d-none" id="module-select-group">
                                                            <label class="small font-weight-bold">Pilih Modul Target <span class="text-danger">*</span></label>
                                                            <select class="form-control form-control-sm" id="gen-module-id" name="module_id">
                                                                @forelse($modules as $mod)
                                                                    <option value="{{ $mod->id }}">{{ $mod->title }}</option>
                                                                @empty
                                                                    <option value="" disabled>Belum ada modul. Buat modul dulu.</option>
                                                                @endforelse
                                                            </select>
                                                        </div>

                                                        <div class="row" id="module-count-group">
                                                            <div class="col-6">
                                                                <div class="form-group">
                                                                    <label class="small font-weight-bold">Jumlah Modul</label>
                                                                    <input type="number" class="form-control form-control-sm" id="gen-modules" name="module_count" min="1" max="10" value="3">
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="form-group" id="lesson-group">
                                                                    <label class="small font-weight-bold">Lesson per Modul</label>
                                                                    <input type="number" class="form-control form-control-sm" id="gen-lessons" name="lesson_count" min="1" max="10" value="2">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- Lesson count for single-module mode --}}
                                                        <div class="form-group d-none" id="lesson-only-count-group">
                                                            <label class="small font-weight-bold">Jumlah Lesson yang dibuat</label>
                                                            <input type="number" class="form-control form-control-sm" id="gen-lessons-only" min="1" max="10" value="3">
                                                        </div>

                                                        <div class="form-group">
                                                            <label class="small font-weight-bold">Fokus / Topik Tambahan <span class="text-muted font-weight-normal">(Opsional)</span></label>
                                                            <textarea class="form-control form-control-sm" id="gen-topics" name="extra_topics" rows="2" placeholder="Contoh: Fokus pada studi kasus industri..."></textarea>
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

                                                {{-- RIWAYAT JOB --}}
                                                <div class="card mt-4">
                                                    <div class="card-header bg-white p-2 border-bottom">
                                                        <h6 class="m-0 text-dark"><i class="fa fa-history mr-1"></i> Riwayat Job AI (Terbaru)</h6>
                                                    </div>
                                                    <div class="card-body p-0">
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-striped m-0" style="font-size: 0.85rem;">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Waktu</th>
                                                                        <th>Tipe</th>
                                                                        <th>Status</th>
                                                                        <th class="text-right">Aksi</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @forelse($jobHistory ?? [] as $history)
                                                                        <tr id="job-row-{{ $history->id }}">
                                                                            <td>{{ $history->created_at->format('d M H:i') }}</td>
                                                                            <td>{{ ucfirst($history->type) }}</td>
                                                                            <td>
                                                                                @if(in_array($history->status, ['queued', 'processing']))
                                                                                    <span class="badge badge-warning text-white"><i class="fa fa-spinner fa-spin"></i> {{ ucfirst($history->status) }}</span>
                                                                                @elseif($history->status === 'completed')
                                                                                    <span class="badge badge-success"><i class="fa fa-check"></i> Selesai</span>
                                                                                @else
                                                                                    <span class="badge badge-danger" title="{{ $history->error }}"><i class="fa fa-times"></i> Gagal</span>
                                                                                @endif
                                                                            </td>
                                                                            <td class="text-right">
                                                                                @if($history->status === 'failed')
                                                                                    <button type="button" class="btn btn-xs btn-outline-danger btn-delete-job" data-job-id="{{ $history->id }}" title="Hapus Riwayat">
                                                                                        <i class="fa fa-trash"></i>
                                                                                    </button>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @empty
                                                                        <tr>
                                                                            <td colspan="4" class="text-center text-muted">Belum ada riwayat job.</td>
                                                                        </tr>
                                                                    @endforelse
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- END RIWAYAT JOB --}}

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                {{-- END MODAL AI GENERATOR --}}
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

            const moduleSelectGroup = document.getElementById('module-select-group');
            const moduleCountGroup = document.getElementById('module-count-group');
            const lessonOnlyCountGroup = document.getElementById('lesson-only-count-group');

            // Toggle fields based on generation type
            genType.addEventListener('change', function() {
                const val = this.value;
                if (val === 'modules') {
                    moduleSelectGroup.classList.add('d-none');
                    moduleCountGroup.classList.remove('d-none');
                    lessonOnlyCountGroup.classList.add('d-none');
                    lessonGroup.style.display = 'none';
                    document.getElementById('gen-modules').value = 3;
                } else if (val === 'lessons' || val === 'assignments' || val === 'quizzes') {
                    moduleSelectGroup.classList.remove('d-none');
                    moduleCountGroup.classList.add('d-none');
                    lessonOnlyCountGroup.classList.remove('d-none');
                } else { // full
                    moduleSelectGroup.classList.add('d-none');
                    moduleCountGroup.classList.remove('d-none');
                    lessonOnlyCountGroup.classList.add('d-none');
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
                let endpoint;
                let payload = { archetype_name: archetype, extra_topics: document.getElementById('gen-topics').value };

                if (type === 'full') {
                    endpoint = `/instructor/courses/${courseId}/adaptive/ai/generate-full`;
                    payload.module_count = document.getElementById('gen-modules').value;
                    payload.lesson_count = document.getElementById('gen-lessons').value;
                } else if (type === 'lessons' || type === 'assignments' || type === 'quizzes') {
                    const moduleId = document.getElementById('gen-module-id').value;
                    if (!moduleId) {
                        let what = 'lesson';
                        if (type === 'assignments') what = 'penugasan';
                        if (type === 'quizzes') what = 'quiz';
                        alert(`Pilih modul yang ingin ditambahkan ${what}-nya.`);
                        btnGenerate.disabled = false;
                        btnGenerate.innerHTML = '<i class="fa fa-magic"></i> Mulai Generate';
                        return;
                    }
                    endpoint = `/instructor/courses/${courseId}/adaptive/ai/generate-${type}`;
                    payload.module_id = moduleId;
                    payload.lesson_count = document.getElementById('gen-lessons-only').value;
                } else { // modules only
                    endpoint = `/instructor/courses/${courseId}/adaptive/ai/generate-modules`;
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

            const btnCancelJob = document.getElementById('btn-cancel-job');
            if (btnCancelJob) {
                btnCancelJob.addEventListener('click', function() {
                    const jid = this.getAttribute('data-job-id') || activeJobId;
                    if (!jid) return;

                    if (!confirm('Apakah Anda yakin ingin menghentikan proses AI ini?')) return;

                    this.disabled = true;
                    this.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Membatalkan...';

                    fetch(`/instructor/courses/${courseId}/adaptive/ai/cancel/${jid}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        // Polling will naturally pick up the 'failed' status and update the UI
                        alert('Permintaan pembatalan terkirim. Menunggu proses latar belakang dihentikan...');
                    })
                    .catch(err => {
                        this.disabled = false;
                        this.innerHTML = '<i class="fa fa-times-circle"></i> Batalkan Proses';
                        alert('Terjadi kesalahan saat membatalkan.');
                    });
                });
            }

            // Handle hapus riwayat job
            document.querySelectorAll('.btn-delete-job').forEach(btn => {
                btn.addEventListener('click', function() {
                    const jid = this.getAttribute('data-job-id');
                    if(!confirm('Yakin ingin menghapus riwayat ini?')) return;

                    const row = document.getElementById('job-row-' + jid);
                    this.disabled = true;

                    fetch(`/instructor/courses/${courseId}/adaptive/ai/jobs/${jid}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if(data.message) {
                            if(row) row.remove();
                        } else {
                            alert(data.error || 'Gagal menghapus riwayat');
                            this.disabled = false;
                        }
                    })
                    .catch(err => {
                        alert('Terjadi kesalahan jaringan.');
                        this.disabled = false;
                    });
                });
            });

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
                $('#modal-ai-generator').modal('show');
                startPolling();
            }
        });
        
        function toggleLessonTypeFields(selectElem) {
            const modalBody = selectElem.closest('.modal-body');
            const assignmentFields = modalBody.querySelector('.assignment-fields');
            const videoFields = modalBody.querySelector('.video-fields');
            
            if (assignmentFields) {
                assignmentFields.style.display = selectElem.value === 'assignment' ? 'block' : 'none';
            }
            if (videoFields) {
                videoFields.style.display = selectElem.value === 'video' ? 'block' : 'none';
            }
        }
    </script>
@endpush
