@extends('layouts.app-layout') {{-- Sesuaikan dengan layout utama Anda --}}

@push('styles')
<style>
    /* Custom styles for our new, self-contained accordion */
    .custom-accordion .module-item {
        /* Creates the separator lines */
        border-bottom: 1px solid #f0f0f0;
    }
    .custom-accordion .module-item:first-of-type {
        border-top: 1px solid #f0f0f0;
    }

    .custom-accordion .module-title {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        background-color: #fff;
        color: #333;
    }

    .custom-accordion .module-title:hover {
        background-color: #f5f5f5;
    }

    /* Style for the active/open module title */
    .custom-accordion .module-title.active {
        background-color: #4680ff;
        color: #fff;
    }
    .custom-accordion .module-title.active .badge-warning {
        background-color: #fff;
        color: #f8b425;
    }


    /* The content area that slides up and down */
    .custom-accordion .module-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
        background-color: #fff;
        padding: 0 20px; /* Add padding for a cleaner look when open */
    }
</style>
@endpush

@section('content')
    <div class="pcoded-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <!-- Kolom Title - Order 1 di semua device -->
                    <div class="col-md-8 order-1">
                        <div class="page-header-title">
                            <h5 class="m-b-10">{{ $course->title }}</h5>
                            <p class="m-b-0">Oleh: {{ $course->instructor->name }}</p>
                        </div>
                    </div>
                    
                    <!-- Kolom Points - Order 3 di small device, Order 2 di medium+ -->
                    <div class="col-md-4 order-3 order-md-2">
                        <h5 class="md-text-right mt-2 mt-md-0 text-md-right text-left">Pointmu : {{ $currentPointEarned ? $currentPointEarned : 0 }} <span><i class="bi bi-star-fill text-warning"></i></span></h5>
                    </div>
                    
                    <!-- Kolom Breadcrumb - Order 2 di small device, Order 3 di medium+ -->
                    <div class="col-md-12 d-flex mt-md-3 mt-sm-1 order-2 order-md-3">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item"><a href="#"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="#">Kursus Saya</a></li>
                            <li class="breadcrumb-item"><a href="#!">{{ $course->title }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <div class="page-body">
                        @if ($is_preview)
                            <div class="alert alert-warning text-center text-dark" style="background-color: #f2e529;">
                                <strong>Mode Pratinjau</strong><br>
                                Anda melihat halaman ini sebagai Admin/Superadmin/Instruktur. Progres tidak akan disimpan.
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <div class="row">
                            {{-- Kolom Konten Pelajaran (Utama) --}}
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 id="lesson-title">Selamat Datang di Kursus!</h5>
                                    </div>
                                    <div class="card-block" id="lesson-content" style="min-height: 500px;">
                                        <div class="text-center text-muted">
                                            <p><i class="fa fa-arrow-left fa-2x"></i></p>
                                            <h5>Pilih pelajaran dari daftar isi di sebelah kanan untuk memulai.</h5>
                                            <hr>
                                            <p class="mt-4"><strong>Deskripsi Kursus:</strong></p>
                                            <div>{!! $course->description !!}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Kolom Daftar Isi (Sidebar) --}}
                            <div class="col-lg-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-header-text">Daftar Isi Kursus</h5>
                                    </div>
                                    <div class="card-block accordion-block">
                                        <div class="custom-accordion" id="custom-accordion">

                                            @forelse ($course->modules as $module)
                                                @php
                                                    // Logic to check if the module is locked for the student
                                                    $isLocked =
                                                        !$is_preview &&
                                                        $module->points_required > 0 &&
                                                        $currentCoursePoints < $module->points_required;
                                                @endphp

                                                <div class="module-item">
                                                    <!-- Accordion Module Title -->
                                                    <div class="module-title waves-effect waves-light">
                                                        <span>
                                                            @if ($isLocked)
                                                                <i class="fa fa-lock mr-2"></i>
                                                            @endif
                                                            <strong>{{ $module->title }}</strong>
                                                        </span>
                                                        @if ($isLocked)
                                                            <span class="badge badge-warning ml-auto">{{ $module->points_required }} Poin Dibutuhkan</span>
                                                        @endif
                                                    </div>
                                                    <!-- End Accordion Module Title -->

                                                    <!-- Accordion Content (List of Lessons) -->
                                                    <div class="module-content">
                                                        <ul class="list-group list-group-flush">
                                                             <li class="list-group-item d-flex justify-content-center align-items-center">
                                                                <a href="#" class="load-leaderboard-btn text-dark text-center font-weight-bold" data-module-id="{{ $module->id }}">
                                                                    <i class="fa fa-bar-chart mr-2"></i>
                                                                    Leaderboard 
                                                                </a>
                                                            </li>
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
                                                                @endphp

                                                                <li class="list-group-item d-flex justify-content-between align-items-center {{ $isLocked ? 'bg-light' : '' }}" id="sidebar-lesson-{{ $lesson->id }}">
                                                                    {{-- This link is now clickable even if locked, server will handle response --}}
                                                                    <a href="#" class="load-lesson {{ $isLocked ? 'text-muted' : 'text-dark' }}" data-lesson-id="{{ $lesson->id }}">
                                                                        <i class="{{ $icon }} mr-2"></i>
                                                                        {{ $lesson->title }}
                                                                    </a>
                                                                    @if (in_array($lesson->id, $completedLessonIds))
                                                                        <i class="fa fa-check-circle text-success"></i>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                    <!-- End Accordion Content -->
                                                </div>

                                            @empty
                                                <p class="text-muted p-3">Kursus ini belum memiliki modul.</p>
                                            @endforelse

                                        </div>

                                        {{-- Buttons below the accordion --}}
                                        <div class="mt-4">
                                            <button class="btn btn-outline-primary w-100 mb-2" id="load-leaderboard">
                                                <i class="fa fa-bar-chart mr-2"></i> Leaderboard
                                            </button>
                                            <button class="btn btn-outline-primary w-100 mb-2" id="load-review-form"
                                                @if(!$allLessonsCompleted && !$is_preview)
                                                    disabled
                                                    title="Selesaikan semua pelajaran untuk memberikan feedback"
                                                @endif>
                                                <i class="fa fa-star mr-2"></i> Feedback
                                            </button>
                                            @if ($isEligibleForCertificate)
                                                <button class="btn btn-outline-primary w-100 mb-2" id="load-certificate-preview">
                                                    <i class="bi bi-award-fill me-2"></i> Sertifikat
                                                </button>
                                                <button class="btn btn-outline-primary w-100 mb-2" id="convert-points-btn" data-toggle="modal" data-target="#conversionModal">
                                                    <i class="fa fa-exchange mr-2"></i> Konversi Poin
                                                </button>
                                            @else
                                                <button class="btn btn-outline-primary w-100 mb-2" disabled>
                                                    <i class="bi bi-award-fill me-2"></i> Sertifikat
                                                </button>
                                                <button class="btn btn-outline-primary w-100 mb-2" disabled>
                                                    <i class="fa fa-exchange mr-2"></i> Konversi Poin
                                                </button>
                                            @endif
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
                                    <span class="text-warning">{{ $pointsToConvert }} <i class="ti-medall-alt"></i></span>
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
                            <form action="{{ route('student.course.convert_points', $course->id) }}" method="POST">
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
    <script>
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
            const isPreview = @json($is_preview);
            let completedLessons = @json($completedLessonIds);

            const reviewButton = document.getElementById('load-review-form');
            const totalLessons = {{ $course->lessons->count() }};

            // --- FUNGSI UNTUK MEMUAT KONTEN PELAJARAN ---
            function loadLessonContent(lessonId) {
                lessonTitleEl.innerText = 'Memuat...';
                lessonContentEl.innerHTML = '<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-3x"></i></div>';

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
                                const lessonTypeIcon = sidebarItem.querySelector('a.load-lesson > i');
                                const isQuizOrAssignment = lessonTypeIcon.classList.contains('bi-pencil-square') || lessonTypeIcon.classList.contains('bi-clipboard2');
                                const isAlreadyCompleted = sidebarItem.querySelector('.fa-check-circle') !== null;

                                // **FIX**: Added !data.is_locked to ensure the button never shows for locked lessons.
                                if (!data.is_locked && !isQuizOrAssignment && !isPreview && !isAlreadyCompleted) {
                                    completeButtonHtml = `<hr><div class="text-center mt-4"><button class="btn btn-primary mark-as-complete-btn" data-lesson-id="${lessonId}"><i class="fa fa-check"></i> Tandai Selesai</button></div>`;
                                }
                            }
                            
                            const discussionHtml = data.discussion_html || '';
                            lessonContentEl.innerHTML = data.html + completeButtonHtml + discussionHtml;

                        } else {
                            lessonTitleEl.innerText = 'Gagal Memuat';
                            lessonContentEl.innerHTML = `<p class="text-danger">${data.message || 'Terjadi kesalahan.'}</p>`;
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching lesson content:', error);
                        lessonTitleEl.innerText = 'Gagal Memuat';
                        lessonContentEl.innerHTML = '<p class="text-danger">Terjadi kesalahan jaringan.</p>';
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
                    lessonTitleEl.innerText = 'Ulasan & Rating Kursus';
                    lessonContentEl.innerHTML = '<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-3x"></i></div>';
                    const url = "{{ route('student.course.review.create', $course->id) }}";
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
                                button.style.display = 'none';
                                const sidebarItem = document.getElementById(`sidebar-lesson-${lessonId}`);
                                if (sidebarItem && !sidebarItem.querySelector('.fa-check-circle')) {
                                    sidebarItem.insertAdjacentHTML('beforeend', ' <i class="fa fa-check-circle text-success"></i>');
                                }
                                completedLessons.push(parseInt(lessonId));

                                if (reviewButton && !isPreview && completedLessons.length >= totalLessons && totalLessons > 0) {
                                    reviewButton.disabled = false;
                                    reviewButton.removeAttribute('title');
                                    reviewButton.classList.remove('btn-outline-primary');
                                    reviewButton.classList.add('btn-success');
                                    reviewButton.innerHTML = '<i class="fa fa-star mr-2"></i> Beri Feedback Sekarang!';
                                }

                            } else {
                                alert(data.message || 'Gagal menandai pelajaran.');
                                button.disabled = false;
                                button.innerHTML = '<i class="fa fa-check"></i> Tandai Selesai';
                            }
                        })
                        .catch(error => {
                            console.error('Error marking lesson complete:', error);
                            alert('Terjadi kesalahan jaringan.');
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
                                if (reviewButton) reviewButton.style.display = 'none';
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
                    lessonTitleEl.innerText = 'Sertifikat Kelulusan';
                    lessonContentEl.innerHTML = '<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-3x"></i></div>';
                    const url = "{{ route('student.certificate.preview', $course->id) }}";
                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                lessonContentEl.innerHTML = data.html;
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
                    lessonTitleEl.innerText = 'Papan Peringkat Kursus';
                    lessonContentEl.innerHTML = '<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-3x"></i></div>';
                    const url = "{{ route('student.course.leaderboard', $course->id) }}";
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
                    lessonTitleEl.innerText = 'Papan Peringkat Modul';
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

        });
    </script>
@endpush
