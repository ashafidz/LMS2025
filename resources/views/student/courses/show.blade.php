@extends('layouts.app-layout') {{-- Sesuaikan dengan layout utama Anda --}}

@push('styles')
<style>
    /* Learning page layout (ADR-014). Ids and JS hooks stay; only layout and look change. */
    #course-learning-row { align-items: flex-start; }
    @media (min-width: 992px) {
        #course-content-col { flex: 0 0 68%; max-width: 68%; }
        #course-toc-col { flex: 0 0 32%; max-width: 32%; position: sticky; top: 84px; align-self: flex-start;
            max-height: calc(100vh - 100px); overflow: auto; }
        #course-learning-row.is-doc-wide #course-toc-col { display: none; }
        #course-learning-row.is-doc-wide #course-content-col { flex: 0 0 100%; max-width: 100%; }
    }
    /* The wide toggle only has an effect at >= 992px */
    @media (max-width: 991.98px) {
        #toggle-doc-wide { display: none !important; }
        #course-toc-col { margin-bottom: 16px; }
    }

    /* Table of contents */
    .rs-toc-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; cursor: pointer; }
    /* The header is the whole card while the list is folded (phones): it needs its own bottom padding */
    #course-toc-col .rs-toc-head { padding-bottom: var(--rs-pad-card); }
    .rs-toc-progress { display: block; margin-top: 2px; font-size: var(--rs-fs-small); color: var(--rs-slate); }
    .rs-dash .card .card-header .rs-toc-progress > span { display: inline; font-size: inherit; }
    .rs-toc-actions { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 16px; }
    .rs-toc-actions .btn { flex: 1 1 calc(50% - 6px); min-height: var(--rs-btn-h-sm); padding: 6px 8px; font-size: var(--rs-fs-xs); gap: .3rem; white-space: normal; line-height: 1.2; text-align: center; }
    /* The lesson box keeps some height on desktop (no jump while a lesson loads); on phones it fits its content */
    #lesson-content { min-height: 500px; }
    @media (max-width: 991.98px) { #lesson-content { min-height: 240px; } }
    .rs-toc-actions .btn-outline-primary:not(.btn-success) { background: var(--rs-paper); border-color: var(--rs-paper); color: var(--rs-primary); }
    .rs-toc-actions .btn-outline-primary:not(.btn-success):hover:not(:disabled) { background: var(--rs-line); border-color: var(--rs-line); }
    .rs-toc-actions .btn:disabled { opacity: .55; }

    .custom-accordion .module-item { margin-bottom: 8px; }
    .custom-accordion .module-title { display: flex; align-items: center; gap: 8px; padding: 12px 14px; cursor: pointer;
        background: var(--rs-paper); color: var(--rs-ink); border-radius: var(--rs-radius-md); border-left: 3px solid transparent;
        transition: background-color .2s ease; }
    .custom-accordion .module-title:hover { background: var(--rs-line); }
    .custom-accordion .module-title.active { background: var(--rs-paper); color: var(--rs-ink); border-left-color: var(--rs-accent); border-radius: 0 var(--rs-radius-md) var(--rs-radius-md) 0; }
    .custom-accordion .module-title { flex-wrap: wrap; }
    .custom-accordion .module-title .rs-mod-name { flex: 1 1 0; min-width: 0; font-size: var(--rs-fs-small); line-height: 1.35; overflow-wrap: anywhere;
        display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    .custom-accordion .module-title.active .rs-mod-name { display: block; -webkit-line-clamp: unset; overflow: visible; }
    .custom-accordion .module-title .badge-warning { order: 5; flex: 0 0 auto; }
    .custom-accordion .module-title .rs-mod-count { font-size: var(--rs-fs-xs); font-weight: 700; color: var(--rs-slate); white-space: nowrap; }
    .custom-accordion .module-title .rs-mod-chev { transition: transform .2s ease; color: var(--rs-slate); }
    .custom-accordion .module-title.active .rs-mod-chev { transform: rotate(180deg); }
    .custom-accordion .module-title.active .badge-warning { background-color: #fff; color: #8a5200; }
    .custom-accordion .module-content { max-height: 0; overflow: hidden; transition: max-height .3s ease-out; background: #fff; padding: 0 4px; }
    .custom-accordion .module-content .list-group-item { border: 0; padding: 0; background: transparent; }

    .rs-lesson-row { display: flex; align-items: center; gap: 8px; min-height: 44px; border-radius: var(--rs-radius-sm); padding: 4px 8px !important; }
    .rs-lesson-link { display: flex; align-items: center; gap: 10px; flex: 1 1 auto; min-width: 0; padding: 4px 0; }
    .rs-lesson-link > i { flex: 0 0 28px; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;
        margin: 0 !important; border-radius: 8px; background: var(--rs-paper); color: var(--rs-primary); font-size: 14px; }
    .rs-lesson-name { font-size: var(--rs-fs-small); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.35; overflow-wrap: anywhere; min-width: 0; }
    .rs-lesson-row.is-current .rs-lesson-name { display: block; -webkit-line-clamp: unset; overflow: visible; }
    .rs-lesson-row.is-current { background: var(--rs-info-bg, #e8f1ff); }
    .rs-lesson-row.is-current .rs-lesson-link, .rs-lesson-row.is-current .rs-lesson-name { color: var(--rs-primary) !important; font-weight: 700; }
    .rs-lesson-row .fa-check-circle, .rs-lesson-row .fa-lock { flex: 0 0 auto; font-size: 16px; }
    .rs-lesson-row .fa-lock { color: var(--rs-slate); }
    .rs-mod-board-row { border: 0 !important; padding: 6px 0 2px !important; }
    .rs-mod-board { display: flex; align-items: center; justify-content: center; gap: 6px; min-height: 36px; border-radius: var(--rs-radius-sm); background: var(--rs-paper); color: var(--rs-primary) !important; font-size: 13px; font-weight: 700; text-decoration: none !important; }
    .rs-mod-board:hover { background: var(--rs-line); }

    /* Lesson card */
    #main-content-card > .card-header { align-items: flex-start; }
    .rs-lesson-head { min-width: 0; flex: 1 1 auto; }
    .rs-type-chip { display: inline-block; margin-bottom: 6px; padding: 2px 10px; border-radius: var(--rs-radius-pill); background: var(--rs-info-bg, #e8f1ff); color: var(--rs-primary); font-size: var(--rs-fs-xs); font-weight: 700; }
    .rs-dash .card .card-header .rs-type-chip { display: inline-block; width: auto; } /* the template sets card-header spans to display: block */
    #lesson-title { margin: 0; font-family: var(--rs-font-head); font-weight: 800; font-size: var(--rs-fs-sec); line-height: 1.3; color: var(--rs-ink); overflow-wrap: anywhere; }
    /* The partials print the lesson title again; the header above is the only one. */
    #lesson-content .article-content :is(p, li, td, th, blockquote) { font-size: inherit; line-height: inherit; } /* the template sets every p to 13px */
    #lesson-content .rs-lesson-heading { display: none; }
    #lesson-content .article-content { max-width: none; font-size: var(--rs-fs-body); line-height: 1.7; } /* fills the card: a 72ch limit left an empty right half */
    #lesson-content .article-content h1, #lesson-content .article-content h2, #lesson-content .article-content h3, #lesson-content .article-content h4 { margin: 1.4em 0 .5em; }
    #lesson-content .article-content img { max-width: 100%; height: auto; }
    /* Lesson footer */
    .rs-lesson-foot { display: grid; grid-template-columns: 1fr auto 1fr; align-items: center; gap: 12px; margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--rs-line); }
    .rs-lesson-foot .rs-foot-prev { justify-self: start; text-align: left; }
    .rs-lesson-foot .rs-foot-next { justify-self: end; text-align: right; }
    .rs-lesson-foot .btn { max-width: 100%; min-width: 0; overflow: hidden; }
    .rs-lesson-foot .btn > span { display: block; min-width: 0; max-width: 100%; }
    .rs-foot-label { display: block; }
    /* A fixed cap: a percentage inside min() is ignored when the button sizes itself, so a long title made the button as wide as the whole title. */
    .rs-foot-title { display: block; max-width: 18ch; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 12px; font-weight: 400; color: var(--rs-slate); }
    @media (max-width: 575.98px) {
        .rs-lesson-foot { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
        .rs-lesson-foot .rs-foot-done { grid-column: 1 / -1; grid-row: 1; justify-self: stretch; text-align: center; }
        .rs-lesson-foot .rs-foot-done .btn { width: 100%; }
        .rs-lesson-foot .rs-foot-prev, .rs-lesson-foot .rs-foot-next { grid-row: 2; justify-self: stretch; }
        .rs-lesson-foot .rs-foot-prev .btn, .rs-lesson-foot .rs-foot-next .btn { width: 100%; }
    }
    #lesson-discussion:empty { display: none; }
    #lesson-discussion { margin-top: 20px; background: #fff; border-radius: var(--rs-radius-lg); box-shadow: var(--rs-shadow-card); }
    #lesson-discussion .discussion-container { padding: var(--rs-pad-card); }
    /* The comment form sat in a card inside the discussion card (double frame and padding); keep only the outer card */
    #lesson-discussion .discussion-container > .card { margin-bottom: 20px !important; border: 0; box-shadow: none; background: transparent; }
    #lesson-discussion .discussion-container > .card > .card-body { padding: 0; }
    #lesson-discussion .discussion-container h4 { margin-bottom: 16px !important; }
    #lesson-discussion hr { display: none; }
    #lesson-discussion .discussion-container h4 { font-family: var(--rs-font-head); font-size: 20px; }
</style>
@endpush

@section('content')
    <div class="pcoded-content">
        @php
            [$previewHome, $previewList] = $is_preview ? \App\Services\LessonAccess::previewCrumbs(session('active_role')) : [null, null];
        @endphp
        <x-dash.page-head :crumbs="[$is_preview ? $previewHome : ['__home', route('student.dashboard')], $is_preview ? $previewList : ['Kursus Saya', route('student.my_courses')], [$course->title, null]]">
            <x-slot:title>{{ $course->title }}</x-slot:title>
            <x-slot:subtitle>Oleh: {{ $course->instructor->name }}</x-slot:subtitle>
            @unless ($is_preview)
                <x-slot:actions>
                    <h5 class="mb-0">Pointmu : {{ $currentPointEarned ? $currentPointEarned : 0 }} <span><i class="bi bi-star-fill text-warning"></i></span></h5>
                </x-slot:actions>
            @endunless
        </x-dash.page-head>
        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <div class="page-body">
                        @if ($is_preview)
                            <div class="alert alert-warning rs-preview-note" role="status">
                                <i class="bi bi-eye" aria-hidden="true"></i>
                                <div><strong>Mode Pratinjau</strong><br>Anda melihat kursus ini seperti siswa. Jawaban, poin, dan progres tidak disimpan.</div>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <div class="row" id="course-learning-row">
                            {{-- Kolom Daftar Isi (Sidebar) --}}
                            @php
                                $tocTotal = $course->lessons->count();
                                $tocDone = $course->lessons->whereIn('id', $completedLessonIds)->count();
                                $typeLabels = \App\Models\Lesson::TYPE_LABELS;
                            @endphp
                            <div class="col-lg-4 order-1 order-lg-2" id="course-toc-col">
                                <div class="card">
                                    <div class="card-header rs-toc-head" data-toggle="collapse" data-target="#toc-collapse" role="button" aria-controls="toc-collapse">
                                        <div>
                                            <h5 class="card-header-text mb-0">Daftar Isi Kursus</h5>
                                            @if ($tocTotal > 0)
                                                <span class="rs-toc-progress" id="toc-progress" data-total="{{ $tocTotal }}"><span id="toc-progress-done">{{ $tocDone }}</span> dari {{ $tocTotal }} materi selesai</span>
                                            @endif
                                        </div>
                                        <i class="fa fa-chevron-down d-lg-none" aria-hidden="true"></i>
                                    </div>
                                    <div class="collapse show" id="toc-collapse">
                                        <div class="card-block accordion-block">

                                        {{-- Course action buttons --}}
                                        <div class="rs-toc-actions">
                                            <button class="btn btn-outline-primary btn-sm" id="load-leaderboard">
                                                <i class="fa fa-bar-chart" aria-hidden="true"></i> Peringkat Kursus
                                            </button>
                                            <button class="btn btn-outline-primary btn-sm" id="load-review-form"
                                                @if($is_preview)
                                                    disabled
                                                    title="Tidak tersedia di mode pratinjau"
                                                @elseif(!$allLessonsCompleted)
                                                    disabled
                                                    title="Selesaikan semua pelajaran untuk memberikan feedback"
                                                @endif>
                                                <i class="fa fa-star" aria-hidden="true"></i> Feedback
                                            </button>
                                            {{-- Always rendered with an id: sending the feedback unlocks both in place (no reload needed). --}}
                                            <button class="btn btn-outline-primary btn-sm" id="load-certificate-preview" data-needs-feedback
                                                @if ($is_preview) disabled title="Tidak tersedia di mode pratinjau" @elseif (! $isEligibleForCertificate) disabled title="Selesaikan semua pelajaran dan kirim feedback untuk membuka sertifikat" @endif>
                                                <i class="bi bi-award-fill" aria-hidden="true"></i> Sertifikat
                                            </button>
                                            <button class="btn btn-outline-primary btn-sm" id="convert-points-btn" data-needs-feedback data-toggle="modal" data-target="#conversionModal"
                                                @if ($is_preview) disabled title="Tidak tersedia di mode pratinjau" @elseif (! $isEligibleForCertificate) disabled title="Selesaikan semua pelajaran dan kirim feedback untuk menukar poin" @endif>
                                                <i class="fa fa-exchange" aria-hidden="true"></i> Konversi Poin
                                            </button>
                                        </div>

                                        <div class="custom-accordion" id="custom-accordion">

                                            @forelse ($course->modules as $module)
                                                @php
                                                    // Logic to check if the module is locked for the student
                                                    $isLocked =
                                                        !$is_preview &&
                                                        $module->points_required > 0 &&
                                                        $currentCoursePoints < $module->points_required;
                                                    $modTotal = $module->lessons->count();
                                                    $modDone = $module->lessons->whereIn('id', $completedLessonIds)->count();
                                                @endphp

                                                <div class="module-item">
                                                    <!-- Accordion Module Title -->
                                                    <div class="module-title waves-effect waves-light" title="{{ $module->title }}">
                                                        <span class="rs-mod-name">
                                                            @if ($isLocked)
                                                                <i class="fa fa-lock mr-2"></i>
                                                            @endif
                                                            <strong>{{ $module->title }}</strong>
                                                        </span>
                                                        @if ($isLocked)
                                                            <span class="badge badge-warning ml-auto">{{ $module->points_required }} Poin Dibutuhkan</span>
                                                        @endif
                                                        @if ($modTotal > 0)
                                                            <span class="rs-mod-count"><span class="rs-mod-done">{{ $modDone }}</span>/{{ $modTotal }}</span>
                                                        @endif
                                                        <i class="fa fa-chevron-down rs-mod-chev" aria-hidden="true"></i>
                                                    </div>
                                                    <!-- End Accordion Module Title -->

                                                    <!-- Accordion Content (List of Lessons) -->
                                                    <div class="module-content">
                                                        <ul class="list-group list-group-flush">
                                                            @foreach ($module->lessons as $lesson)
                                                                @php
                                                                    // Logic to determine the icon based on lesson type
                                                                    $icon = 'bi bi-file-text'; // Default icon
                                                                    $type = strtolower(class_basename($lesson->lessonable_type));

                                                                    if ($type === 'lessonvideo') $icon = 'bi bi-collection-play';
                                                                    if ($type === 'quiz') $icon = 'bi bi-pencil-square';
                                                                    if ($type === 'lessondocument') $icon = 'bi bi-file-earmark-pdf';
                                                                    if ($type === 'lessonlinkcollection') $icon = 'bi bi-folder2-open';
                                                                    if ($type === 'lessonassignment') $icon = 'bi bi-clipboard2';
                                                                    if ($type === 'lessonpoint') $icon = 'bi bi-chat-left-quote';
                                                                    if ($type === 'lessonpolling') $icon = 'bi bi-bar-chart-fill';
                                                                    if ($type === 'lessonwordcloud') $icon = 'bi bi-cloud-fill';
                                                                @endphp

                                                                <li class="list-group-item rs-lesson-row {{ $isLocked ? 'bg-light' : '' }}" id="sidebar-lesson-{{ $lesson->id }}">
                                                                    {{-- This link is now clickable even if locked, server will handle response --}}
                                                                    <a href="#" class="load-lesson rs-lesson-link {{ $isLocked ? 'text-muted' : 'text-dark' }}" title="{{ $lesson->title }}" data-lesson-id="{{ $lesson->id }}" data-lesson-type="{{ $typeLabels[$type] ?? 'Artikel' }}">
                                                                        <i class="{{ $icon }}" aria-hidden="true"></i>
                                                                        <span class="rs-lesson-name">{{ $lesson->title }}</span>
                                                                    </a>
                                                                    @if ($isLocked)
                                                                        <i class="fa fa-lock" title="Terkunci" aria-label="Terkunci"></i>
                                                                    @endif
                                                                    @if (in_array($lesson->id, $completedLessonIds))
                                                                        <i class="fa fa-check-circle text-success"></i>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                            <li class="list-group-item rs-mod-board-row">
                                                                <a href="#" class="load-leaderboard-btn rs-mod-board" data-module-id="{{ $module->id }}">
                                                                    <i class="fa fa-bar-chart mr-1" aria-hidden="true"></i> Peringkat Modul
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <!-- End Accordion Content -->
                                                </div>

                                            @empty
                                                <p class="text-muted p-3">Kursus ini belum memiliki modul.</p>
                                            @endforelse

                                        </div>

                                        </div>
                                    </div>
                                    <script>if (window.innerWidth < 992) { document.getElementById('toc-collapse').classList.remove('show'); }</script>
                                </div>
                            </div>
                            
                            {{-- Kolom Konten Pelajaran (Utama) --}}
                            <div class="col-lg-8 order-2 order-lg-1" id="course-content-col">
                                <div class="card" id="main-content-card">
                                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                        <div class="rs-lesson-head">
                                            <span class="rs-type-chip d-none" id="lesson-type-chip"></span>
                                            <h2 id="lesson-title">Selamat Datang di Kursus!</h2>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-primary d-none" id="toggle-doc-wide" aria-pressed="false">
                                            <i class="bi bi-arrows-angle-expand" aria-hidden="true"></i> <span>Lebarkan dokumen</span>
                                        </button>
                                    </div>
                                    <div class="card-block" id="lesson-content">
                                        <div class="text-center text-muted mt-5">
                                            <p><i class="bi bi-journal-bookmark text-primary" style="font-size: 3rem;"></i></p>
                                            <h5 class="mt-3">Pilih pelajaran dari daftar isi untuk memulai.</h5>
                                            <hr class="mt-4 mb-4">
                                            <p><strong>Deskripsi Kursus:</strong></p>
                                            <div class="text-left mt-3">{!! \App\Support\HtmlSanitizer::clean($course->description) !!}</div>
                                        </div>
                                    </div>
                                </div>
                                <div id="lesson-discussion"></div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Point Conversion Modal --}}
    @if ($isEligibleForCertificate)
        <div class="modal fade" id="conversionModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Konversi Poin ke Diamond</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        @php
                            $pointsData = Auth::user()->coursePoints()->where('course_id', $course->id)->first();
                            $pointsToConvert = $pointsData->pivot->points_earned ?? 0;
                            $conversionRate = \App\Models\SiteSetting::first()->point_to_diamond_rate ?? 1;
                            $diamondsEarned = floor($pointsToConvert * $conversionRate);
                        @endphp

                        @if ($pointsData && $pointsData->pivot->is_converted_to_diamond)
                            <div class="alert alert-success text-center">
                                <i class="fa fa-check-circle fa-2x mb-2"></i><br>
                                Anda sudah pernah mengonversi poin dari kursus ini.
                            </div>
                        @else
                            <p>Anda akan mengonversi semua poin yang Anda dapatkan dari kursus ini menjadi Diamond.</p>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Total Poin di Kursus Ini
                                    <span class="text-warning">{{ $pointsToConvert }} <i class="fa fa-medal"></i></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Rasio Konversi
                                    <span>1 Poin = {{ (float) $conversionRate }} Diamond</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <strong>Diamond yang Akan Didapat</strong>
                                    <strong class="text-primary">{{ $diamondsEarned }} <i class="fa fa-diamond"></i></strong>
                                </li>
                            </ul>
                            <p class="text-muted mt-3"><small>Proses ini hanya bisa dilakukan satu kali per kursus dan tidak dapat dibatalkan.</small></p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        @if ($pointsData && !$pointsData->pivot->is_converted_to_diamond)
                            <form action="{{ route('student.course.convert_points', $course) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary">Konfirmasi & Konversi Poin</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script src="{{ asset('js/certificate-canvas-fallback.js') }}"></script>
    <script>
        // Tugas: cek file sebelum dikirim. Formulir dimuat lewat AJAX, jadi pakai listener di document.
        document.addEventListener('submit', function (e) {
            if (!e.target.matches || !e.target.matches('.assignment-submit-form')) { return; }
            var f = e.target.querySelector('input[type="file"]');
            if (f && f.files.length > 0 && f.files[0].size > 20 * 1024 * 1024) {
                e.preventDefault();
                rsAlert({ text: 'Ukuran file melebihi 20MB!', icon: 'warning' });
            } else if (!f || f.files.length === 0) {
                e.preventDefault();
                rsAlert({ text: 'Harap pilih file terlebih dahulu.', icon: 'warning' });
            }
        });

        document.addEventListener('DOMContentLoaded', function() {

            // --- START: Custom Accordion Script ---
            const accordion = document.getElementById('custom-accordion');
            if (accordion) {
                const moduleTitles = accordion.querySelectorAll('.module-title');

                moduleTitles.forEach(title => {
                    title.addEventListener('click', function() {
                        const content = this.nextElementSibling;
                        const isAlreadyActive = this.classList.contains('active');

                        moduleTitles.forEach(otherTitle => {
                            if (otherTitle !== this) {
                                otherTitle.classList.remove('active');
                                otherTitle.nextElementSibling.style.maxHeight = null;
                                otherTitle.nextElementSibling.style.padding = "0 20px";
                            }
                        });

                        if (isAlreadyActive) {
                            this.classList.remove('active');
                            content.style.maxHeight = null;
                            content.style.padding = "0 20px";
                        } else {
                            this.classList.add('active');
                            content.style.maxHeight = content.scrollHeight + "px";
                            content.style.padding = "10px 20px";
                        }
                    });
                });
            }
            // --- END: Custom Accordion Script ---


            const lessonLinks = document.querySelectorAll('.load-lesson');
            const lessonTitleEl = document.getElementById('lesson-title');
            const lessonContentEl = document.getElementById('lesson-content');
            const lessonChipEl = document.getElementById('lesson-type-chip');
            const discussionEl = document.getElementById('lesson-discussion');
            const tocProgressDone = document.getElementById('toc-progress-done');

            // Header for the special views (review, certificate, leaderboards): title only, no lesson chip, no discussion.
            function setSpecialHeader(title) {
                lessonTitleEl.innerText = title;
                lessonChipEl.classList.add('d-none');
                discussionEl.innerHTML = '';
                document.querySelectorAll('.rs-lesson-row.is-current').forEach(el => el.classList.remove('is-current'));
            }

            // Keep the "x dari y materi selesai" counts in step after "Tandai Selesai".
            function refreshProgress() {
                // A number that really changed pulses once (class re-added after a forced reflow); nothing runs on page load.
                const setCount = (el, value) => {
                    const changed = el.textContent.trim() !== String(value);
                    el.textContent = value;
                    if (changed) {
                        el.classList.remove('rs-bump');
                        void el.offsetWidth;
                        el.classList.add('rs-bump');
                    }
                };
                if (tocProgressDone) {
                    setCount(tocProgressDone, document.querySelectorAll('#custom-accordion li[id^="sidebar-lesson-"] .fa-check-circle').length);
                }
                document.querySelectorAll('#custom-accordion .module-item').forEach(item => {
                    const done = item.querySelector('.rs-mod-done');
                    if (done) {
                        setCount(done, item.querySelectorAll('li[id^="sidebar-lesson-"] .fa-check-circle').length);
                    }
                });
            }
            const isPreview = @json($is_preview);
            let completedLessons = @json($completedLessonIds);

            const reviewButton = document.getElementById('load-review-form');
            const totalLessons = {{ $course->lessons->count() }};

            // Simpan info pelajaran untuk navigasi next/prev
            const lessonsData = Array.from(lessonLinks).map(link => ({
                id: link.dataset.lessonId,
                title: link.innerText.trim()
            }));

            // --- LEBARKAN DOKUMEN (sembunyikan daftar isi di layar lebar) ---
            const learningRow = document.getElementById('course-learning-row');
            const docWideButton = document.getElementById('toggle-doc-wide');

            function setDocWide(on) {
                learningRow.classList.toggle('is-doc-wide', on);
                docWideButton.setAttribute('aria-pressed', on ? 'true' : 'false');
                docWideButton.querySelector('i').className = on ? 'bi bi-arrows-angle-contract' : 'bi bi-arrows-angle-expand';
                docWideButton.querySelector('span').textContent = on ? 'Tampilkan daftar isi' : 'Lebarkan dokumen';
            }

            function hideDocWideButton() {
                setDocWide(false);
                docWideButton.classList.add('d-none');
            }

            docWideButton.addEventListener('click', function() {
                setDocWide(!learningRow.classList.contains('is-doc-wide'));
            });

            // --- FUNGSI UNTUK MEMUAT KONTEN PELAJARAN ---
            function loadLessonContent(lessonId) {
                setSpecialHeader('Memuat...');
                lessonContentEl.innerHTML = '<div class="rs-skel" role="status" aria-label="Memuat pelajaran"><span class="sr-only">Memuat pelajaran...</span><div class="rs-skel__line rs-skel__line--title"></div><div class="rs-skel__block"></div><div class="rs-skel__line"></div><div class="rs-skel__line"></div><div class="rs-skel__line rs-skel__line--short"></div></div>';

                if (window.innerWidth < 992) {
                    const mainCard = document.getElementById('main-content-card');
                    if (mainCard) {
                        mainCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                    const tocCollapse = document.getElementById('toc-collapse');
                    if (tocCollapse && tocCollapse.classList.contains('show')) {
                        $(tocCollapse).collapse('hide');
                    }
                }

                let url = `/student/lessons/${lessonId}/content`;
                if (isPreview) {
                    url += '?preview=true';
                }

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            lessonTitleEl.innerText = data.title;
                            let completeButtonHtml = '';

                            const sidebarItem = document.getElementById(`sidebar-lesson-${lessonId}`);
                            if (sidebarItem) {
                                sidebarItem.classList.add('is-current');
                                const typeLabel = sidebarItem.querySelector('a.load-lesson').dataset.lessonType;
                                if (typeLabel && !data.is_locked) {
                                    lessonChipEl.textContent = typeLabel;
                                    lessonChipEl.classList.remove('d-none');
                                }
                                const lessonTypeIcon = sidebarItem.querySelector('a.load-lesson > i');
                                const isQuizOrAssignmentOrPoll = lessonTypeIcon.classList.contains('bi-pencil-square') || 
                                                                 lessonTypeIcon.classList.contains('bi-clipboard2') || 
                                                                 lessonTypeIcon.classList.contains('bi-bar-chart-fill') || 
                                                                 lessonTypeIcon.classList.contains('bi-cloud-fill');
                                const isAlreadyCompleted = sidebarItem.querySelector('.fa-check-circle') !== null;

                                // **FIX**: Added !data.is_locked to ensure the button never shows for locked lessons.
                                if (!data.is_locked && !isQuizOrAssignmentOrPoll && !isPreview && !isAlreadyCompleted) {
                                    completeButtonHtml = `<div class="rs-foot-done"><button class="btn btn-primary mark-as-complete-btn" data-lesson-id="${lessonId}"><i class="fa fa-check"></i> Tandai Selesai</button></div>`;
                                }
                            }
                            
                            const discussionHtml = data.discussion_html || '';

                            // Footer: previous (soft), "Tandai Selesai" (primary), next (soft)
                            const currentIndex = lessonsData.findIndex(l => l.id == lessonId);
                            const esc = t => String(t).replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));
                            let navHtml = '<div class="rs-lesson-foot">';

                            if (currentIndex > 0) {
                                const prevLesson = lessonsData[currentIndex - 1];
                                navHtml += `<div class="rs-foot-prev"><button class="btn btn-secondary load-lesson-nav" data-lesson-id="${prevLesson.id}"><span><span class="rs-foot-label"><i class="fa fa-chevron-left mr-1"></i> Sebelumnya</span><span class="rs-foot-title">${esc(prevLesson.title)}</span></span></button></div>`;
                            } else {
                                navHtml += `<div class="rs-foot-prev"></div>`;
                            }

                            navHtml += completeButtonHtml || '<div class="rs-foot-done"></div>';

                            if (currentIndex !== -1 && currentIndex < lessonsData.length - 1) {
                                const nextLesson = lessonsData[currentIndex + 1];
                                navHtml += `<div class="rs-foot-next"><button class="btn btn-secondary load-lesson-nav" data-lesson-id="${nextLesson.id}"><span><span class="rs-foot-label">Selanjutnya <i class="fa fa-chevron-right ml-1"></i></span><span class="rs-foot-title">${esc(nextLesson.title)}</span></span></button></div>`;
                            } else {
                                navHtml += `<div class="rs-foot-next"></div>`;
                            }
                            navHtml += '</div>';

                            lessonContentEl.innerHTML = data.html + navHtml;
                            discussionEl.innerHTML = discussionHtml;

                            if (lessonContentEl.querySelector('[data-doc-viewer]')) {
                                docWideButton.classList.remove('d-none');
                            } else {
                                hideDocWideButton();
                            }

                            // Pasang event listener untuk navigasi baru
                            lessonContentEl.querySelectorAll('.load-lesson-nav').forEach(btn => {
                                btn.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    loadLessonContent(this.dataset.lessonId);
                                });
                            });

                        } else {
                            setSpecialHeader('Gagal Memuat');
                            lessonContentEl.innerHTML = `<p class="text-danger">${data.message || 'Terjadi kesalahan.'}</p>`;
                            hideDocWideButton();
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching lesson content:', error);
                        setSpecialHeader('Gagal Memuat');
                        lessonContentEl.innerHTML = '<p class="text-danger">Terjadi kesalahan jaringan.</p>';
                        hideDocWideButton();
                    });
            }

            lessonLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    loadLessonContent(this.dataset.lessonId);
                });
            });

            // --- LOGIKA UNTUK TOMBOL FEEDBACK ---
            if (reviewButton) {
                reviewButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    setSpecialHeader('Ulasan & Rating Kursus');
                    lessonContentEl.innerHTML = '<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-3x"></i></div>';
                    const url = "{{ route('student.course.review.create', $course) }}";
                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                lessonContentEl.innerHTML = data.html;
                            } else {
                                lessonContentEl.innerHTML = `<p class="text-danger">${data.message || 'Gagal memuat form ulasan.'}</p>`;
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching review form:', error);
                            lessonContentEl.innerHTML = '<p class="text-danger">Terjadi kesalahan jaringan.</p>';
                        });
                });
            }

            // --- Event listener untuk tombol "Tandai Selesai" ---
            lessonContentEl.addEventListener('click', function(e) {
                if (e.target && e.target.classList.contains('mark-as-complete-btn')) {
                    const button = e.target;
                    const lessonId = button.dataset.lessonId;
                    button.disabled = true;
                    button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memproses...';
                    fetch(`/lessons/${lessonId}/complete`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Anchor is read before the button is hidden
                                if (typeof rsPointsGained === 'function') { rsPointsGained(data.points, button); }
                                button.style.display = 'none';
                                const sidebarItem = document.getElementById(`sidebar-lesson-${lessonId}`);
                                if (sidebarItem && !sidebarItem.querySelector('.fa-check-circle')) {
                                    sidebarItem.insertAdjacentHTML('beforeend', ' <i class="fa fa-check-circle text-success rs-check-pop"></i>');
                                }
                                const wasDone = completedLessons.length >= totalLessons;
                                completedLessons.push(parseInt(lessonId));
                                refreshProgress();

                                if (reviewButton && !isPreview && completedLessons.length >= totalLessons && totalLessons > 0) {
                                    reviewButton.disabled = false;
                                    reviewButton.removeAttribute('title');
                                    reviewButton.classList.remove('btn-outline-primary');
                                    reviewButton.classList.add('btn-success');
                                    reviewButton.innerHTML = '<i class="fa fa-star mr-2"></i> Beri Feedback Sekarang!';

                                    if (!wasDone) {
                                        if (typeof rsCelebrate === 'function') { rsCelebrate(); }
                                        if (typeof rsToast === 'function') { rsToast('Kursus selesai! Beri feedback untuk membuka sertifikat.', 'success'); }
                                    }
                                }

                            } else {
                                rsAlert({ text: data.message || 'Gagal menandai pelajaran.', icon: 'error' });
                                button.disabled = false;
                                button.innerHTML = '<i class="fa fa-check"></i> Tandai Selesai';
                            }
                        })
                        .catch(error => {
                            console.error('Error marking lesson complete:', error);
                            rsAlert({ text: 'Terjadi kesalahan jaringan.', icon: 'error' });
                            button.disabled = false;
                            button.innerHTML = '<i class="fa fa-check"></i> Tandai Selesai';
                        });
                }
            });

            // --- Event listener untuk form submit review ---
            lessonContentEl.addEventListener('submit', function(e) {
                if (e.target && e.target.id === 'course-review-form') {
                    e.preventDefault();
                    const form = e.target;
                    const submitButton = form.querySelector('button[type="submit"]');
                    const errorAlert = document.getElementById('review-error-alert');
                    submitButton.disabled = true;
                    submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Mengirim...';
                    errorAlert.style.display = 'none';
                    const formData = new FormData(form);
                    fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                            },
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                lessonContentEl.innerHTML = `
                                <div class="text-center p-5">
                                    <i class="fa fa-check-circle text-success" style="font-size: 4rem;"></i>
                                    <h4 class="mt-3">Terima Kasih!</h4>
                                    <p class="text-muted">Ulasan Anda telah berhasil kami terima.</p>
                                </div>
                            `;
                                // Keep the Feedback button (it now opens the "already sent" note) and unlock the
                                // certificate and point conversion, as the page would after a reload.
                                if (reviewButton) {
                                    reviewButton.classList.remove('btn-success');
                                    reviewButton.classList.add('btn-outline-primary');
                                    reviewButton.innerHTML = '<i class="fa fa-star" aria-hidden="true"></i> Feedback';
                                }
                                if (!isPreview && totalLessons > 0 && completedLessons.length >= totalLessons) {
                                    document.querySelectorAll('[data-needs-feedback]').forEach(function (btn) {
                                        btn.disabled = false;
                                        btn.removeAttribute('title');
                                    });
                                }
                            } else {
                                errorAlert.innerText = data.message || 'Terjadi kesalahan. Pastikan semua kolom terisi.';
                                errorAlert.style.display = 'block';
                            }
                        })
                        .catch(error => {
                            console.error('Error submitting review:', error);
                            errorAlert.innerText = 'Terjadi kesalahan jaringan. Silakan coba lagi.';
                            errorAlert.style.display = 'block';
                        })
                        .finally(() => {
                            submitButton.disabled = false;
                            submitButton.innerText = 'Kirim Ulasan';
                        });
                }
            });

            // --- LOGIKA UNTUK TOMBOL SERTIFIKAT ---
            const certificateButton = document.getElementById('load-certificate-preview');
            if (certificateButton) {
                certificateButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    setSpecialHeader('Sertifikat Kelulusan');
                    lessonContentEl.innerHTML = '<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-3x"></i></div>';
                    const url = "{{ route('student.certificate.preview', $course) }}";
                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                lessonContentEl.innerHTML = data.html;
                                // Browser lama tanpa satuan cqw: ukuran huruf dihitung dari lebar kanvas
                                if (window.fitCertificateCanvas) { window.fitCertificateCanvas(lessonContentEl); }
                            } else {
                                lessonContentEl.innerHTML = `<p class="text-danger">${data.message || 'Gagal memuat pratinjau sertifikat.'}</p>`;
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching certificate preview:', error);
                            lessonContentEl.innerHTML = '<p class="text-danger">Terjadi kesalahan jaringan.</p>';
                        });
                });
            }

            // --- LOGIKA UNTUK TOMBOL LEADERBOARD KURSUS ---
            const leaderboardButton = document.getElementById('load-leaderboard');
            if (leaderboardButton) {
                leaderboardButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    setSpecialHeader('Papan Peringkat Kursus');
                    lessonContentEl.innerHTML = '<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-3x"></i></div>';
                    const url = "{{ route('student.course.leaderboard', $course) }}";
                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                lessonContentEl.innerHTML = data.html;
                            } else {
                                lessonContentEl.innerHTML = `<p class="text-danger">${data.message || 'Gagal memuat papan peringkat.'}</p>`;
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching leaderboard:', error);
                            lessonContentEl.innerHTML = '<p class="text-danger">Terjadi kesalahan jaringan.</p>';
                        });
                });
            }

            // --- LOGIKA UNTUK TOMBOL LEADERBOARD MODUL ---
            const moduleLeaderboardButtons = document.querySelectorAll('.load-leaderboard-btn');
            moduleLeaderboardButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation(); // Mencegah accordion dari menutup saat link di dalamnya diklik
                    const moduleId = this.dataset.moduleId;
                    setSpecialHeader('Papan Peringkat Modul');
                    lessonContentEl.innerHTML = '<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-3x"></i></div>';
                    const url = `{{ url('/modules') }}/${moduleId}/leaderboard`;
                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                lessonContentEl.innerHTML = data.html;
                            } else {
                                lessonContentEl.innerHTML = `<p class="text-danger">${data.message || 'Gagal memuat papan peringkat.'}</p>`;
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching module leaderboard:', error);
                            lessonContentEl.innerHTML = '<p class="text-danger">Terjadi kesalahan jaringan.</p>';
                        });
                });
            });

            // Tutup TOC by default di mobile
            if (window.innerWidth < 992) {
                const tocCollapse = document.getElementById('toc-collapse');
                if (tocCollapse) {
                    tocCollapse.classList.remove('show');
                }
            }

        });
    </script>
@endpush
