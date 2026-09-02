@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Progres Siswa: {{ $student->name }}</h5>
                        <p class="m-b-0">Melihat detail progres untuk kursus <strong>{{ $course->title }}</strong></p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('instructor.courses.index') }}">Kursus Saya</a></li>
                        <li class="breadcrumb-item"><a href="#!">Progres Siswa</a></li>
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
                    
                    {{-- Header Card --}}
                    <div class="card mb-4 shadow-sm border-0" style="border-radius: 12px; background: linear-gradient(135deg, #1E88E5, #1565C0); color: white;">
                        <div class="card-block p-4 d-flex align-items-center justify-content-between flex-wrap">
                            <div class="d-flex align-items-center mb-2 mb-md-0">
                                <h3 class="font-weight-bold mb-1"><i class="fa fa-user-circle me-2"></i> {{ $student->name }}</h3>
                                <div class="ms-3 ms-md-4">
                                    <p class="mb-0 text-white-50" style="line-height: 1.2;"><i class="fa fa-envelope me-1"></i> {{ $student->email }}</p>
                                    <p class="mb-0 text-white-50" style="line-height: 1.2;"><i class="fa fa-id-card me-1"></i> NIM: {{ $student->studentProfile->unique_id_number ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="text-md-end text-center p-3 rounded" style="background: rgba(255,255,255,0.1);">
                                <h6 class="mb-1 text-white-50 text-uppercase" style="font-size: 11px; letter-spacing: 1px;">Total Perolehan Poin</h6>
                                <h3 class="mb-0 font-weight-bold text-warning"><i class="fa fa-star me-1"></i> {{ number_format($pointHistories->sum('points'), 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>

                    {{-- Checklist per Module --}}
                    <div class="row">
                        <div class="col-12">
                            @forelse($course->modules as $module)
                            <div class="card shadow-sm mb-4" style="border-radius: 12px;">
                                <div class="card-header bg-white border-bottom pt-4 pb-3" style="border-radius: 12px 12px 0 0;">
                                    <h5 class="font-weight-bold mb-0 text-dark"><i class="fa fa-folder-open text-warning me-2"></i> {{ $module->title }}</h5>
                                </div>
                                <div class="card-block p-0">
                                    <div class="list-group list-group-flush">
                                        @forelse($module->lessons as $lesson)
                                            @php
                                                $isCompleted = in_array($lesson->id, $completedLessonIds);
                                                $actualPoints = isset($pointHistories[$lesson->id]) ? $pointHistories[$lesson->id]->points : 0;
                                                
                                                // Hitung potensi poin berdasarkan tipe materi
                                                $potentialPoints = 0;
                                                $typeStr = class_basename($lesson->lessonable_type);
                                                $displayType = $typeStr;
                                                if ($typeStr == 'LessonArticle') {
                                                    $potentialPoints = $siteSettings->points_for_article;
                                                    $displayType = 'Artikel';
                                                } elseif ($typeStr == 'LessonVideo') {
                                                    $potentialPoints = $siteSettings->points_for_video;
                                                    $displayType = 'Video';
                                                } elseif ($typeStr == 'LessonDocument') {
                                                    $potentialPoints = $siteSettings->points_for_document;
                                                    $displayType = 'Dokumen / Slide';
                                                } elseif ($typeStr == 'Quiz') {
                                                    $potentialPoints = $siteSettings->points_for_quiz;
                                                    $displayType = 'Kuis';
                                                } elseif ($typeStr == 'LessonAssignment') {
                                                    $potentialPoints = $siteSettings->points_for_assignment;
                                                    $displayType = 'Tugas';
                                                } elseif ($typeStr == 'LessonPolling') {
                                                    $potentialPoints = $siteSettings->points_for_polling;
                                                    $displayType = 'Polling';
                                                } elseif ($typeStr == 'LessonWordcloud') {
                                                    $potentialPoints = $siteSettings->points_for_wordcloud;
                                                    $displayType = 'Word Cloud';
                                                }
                                            @endphp
                                            <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                                <div class="d-flex align-items-center">
                                                    @if($isCompleted)
                                                        <div class="bg-success rounded-circle shadow-sm me-3 flex-shrink-0" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fa fa-check text-white m-0" style="font-size: 16px;"></i>
                                                        </div>
                                                    @else
                                                        <div class="bg-light border text-muted rounded-circle me-3 flex-shrink-0" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fa fa-circle-o m-0" style="font-size: 16px;"></i>
                                                        </div>
                                                    @endif
                                                    
                                                    <div>
                                                        <h6 class="mb-1 {{ $isCompleted ? 'text-dark font-weight-bold' : 'text-muted' }}">{{ $lesson->title }}</h6>
                                                        <small class="text-muted"><i class="fa fa-file-text-o me-1"></i> {{ $displayType }}</small>
                                                    </div>
                                                </div>
                                                
                                                <div class="text-end">
                                                    @if($isCompleted)
                                                        <div class="d-flex flex-column align-items-end">
                                                            <span class="badge badge-light-success text-success font-weight-bold p-2 px-3 mb-1 shadow-sm" style="font-size: 13px;">
                                                                <i class="fa fa-star text-warning me-1"></i> +{{ number_format($actualPoints, 0, ',', '.') }} Poin Diterima
                                                            </span>
                                                            @if($actualPoints != $potentialPoints)
                                                                <small class="text-danger mt-1" style="font-size: 11px; max-width: 250px; text-align: right; line-height: 1.2;">
                                                                    <i class="fa fa-info-circle"></i> Berbeda dengan pengaturan saat ini ({{ $potentialPoints }} poin). Poin yang tercatat adalah berdasarkan waktu saat materi dikerjakan.
                                                                </small>
                                                            @else
                                                                <small class="text-muted" style="font-size: 11px;">(Potensi saat ini: {{ $potentialPoints }} Poin)</small>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="d-flex flex-column align-items-end">
                                                            <span class="badge badge-light-secondary text-secondary font-weight-bold p-2 px-3 mb-1" style="font-size: 13px;">
                                                                0 Poin
                                                            </span>
                                                            <small class="text-muted" style="font-size: 11px;">(Potensi: +{{ number_format($potentialPoints, 0, ',', '.') }})</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @empty
                                            <div class="list-group-item py-4 text-center text-muted">
                                                Tidak ada pelajaran dalam modul ini.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center p-5 bg-white shadow-sm" style="border-radius: 12px;">
                                <i class="fa fa-folder-o fa-3x text-muted mb-3 d-block"></i>
                                <h5>Belum ada modul</h5>
                                <p class="text-muted">Kursus ini belum memiliki modul atau materi pelajaran.</p>
                            </div>
                            @endforelse
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
