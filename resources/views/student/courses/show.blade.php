@extends('layouts.app-layout') {{-- Sesuaikan dengan layout utama Anda --}}

@section('content')
    <div class="pcoded-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="page-header-title">
                            <h5 class="m-b-10">{{ $course->title }}</h5>
                            <p class="m-b-0">Oleh: {{ $course->instructor->name }}</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item"><a href="#"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="#">Kursus</a></li>
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
                            <div class="alert alert-warning text-center">
                                <strong>Mode Pratinjau</strong><br>
                                Anda melihat halaman ini sebagai Admin/Superadmin/Instruktur. Progres tidak akan disimpan.
                            </div>
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
                                        <h5>Daftar Isi Kursus</h5>
                                    </div>
                                    <div class="card-block">
                                        <div id="syllabus-accordion">
                                            @forelse ($course->modules as $module)
                                                <div class="card mb-2">
                                                    <div class="card-header" id="heading-{{ $module->id }}">
                                                        <h5 class="mb-0">
                                                            <button class="btn btn-link" data-toggle="collapse"
                                                                data-target="#collapse-{{ $module->id }}"
                                                                aria-expanded="true"
                                                                aria-controls="collapse-{{ $module->id }}">
                                                                <strong>{{ $module->title }}</strong>
                                                            </button>
                                                        </h5>
                                                    </div>
                                                    <div id="collapse-{{ $module->id }}" class="collapse show"
                                                        aria-labelledby="heading-{{ $module->id }}"
                                                        data-parent="#syllabus-accordion">
                                                        <div class="card-body p-0">
                                                            <ul class="list-group list-group-flush">
                                                                @foreach ($module->lessons as $lesson)
                                                                    <li class="list-group-item d-flex justify-content-between align-items-center"
                                                                        id="sidebar-lesson-{{ $lesson->id }}">
                                                                        <a href="#" class="load-lesson text-dark"
                                                                            data-lesson-id="{{ $lesson->id }}">
                                                                            @php
                                                                                $icon = 'fa-file-text-o';
                                                                                $type = strtolower(
                                                                                    class_basename(
                                                                                        $lesson->lessonable_type,
                                                                                    ),
                                                                                );
                                                                                if ($type === 'lessonvideo') {
                                                                                    $icon = 'fa-play-circle';
                                                                                }
                                                                                if ($type === 'quiz') {
                                                                                    $icon = 'fa-question-circle';
                                                                                }
                                                                                if ($type === 'lessondocument') {
                                                                                    $icon = 'fa-file-pdf-o';
                                                                                }
                                                                                if ($type === 'lessonlinkcollection') {
                                                                                    $icon = 'fa-link';
                                                                                }
                                                                                if ($type === 'lessonassignment') {
                                                                                    $icon = 'fa-pencil-square-o';
                                                                                }
                                                                            @endphp
                                                                            <i class="fa {{ $icon }} mr-2"></i>
                                                                            {{ $lesson->title }}
                                                                        </a>
                                                                        @if (in_array($lesson->id, $completedLessonIds))
                                                                            <i class="fa fa-check-circle text-success"></i>
                                                                        @endif
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <p class="text-muted">Kursus ini belum memiliki modul.</p>
                                            @endforelse

                                            <div class="mt-4 mb-4">
                                                <button class="btn btn-outline-primary w-100 mb-2" id="load-leaderboard">
                                                    <i class="fa fa-bar-chart mr-2"></i> Leaderboard
                                                </button>
                                                <button class="btn btn-outline-primary w-100 mb-2" id="load-review-form">
                                                    <i class="fa fa-star mr-2"></i> Feedback
                                                </button>
                                                @if ($isEligibleForCertificate)
                                                    <button class="btn btn-outline-primary w-100 mb-2"
                                                        id="load-certificate-preview">
                                                        <i class="bi bi-award-fill me-2"></i> Sertifikat
                                                    </button>
                                                    <button class="btn btn-outline-primary w-100 mb-2"
                                                        id="convert-points-btn" data-toggle="modal"
                                                        data-target="#conversionModal">
                                                        <i class="fa fa-exchange mr-2"></i> Konversi Poin
                                                    </button>
                                                @else
                                                    <button class="btn btn-outline-primary w-100 mb-2" disabled>
                                                        <i class="bi bi-award-fill me-2"></i> Sertifikat
                                                    </button>
                                                    <button class="btn btn-outline-primary w-100 mb-2" disabled>
                                                        <i class="bi bi-award-fill me-2"></i> Konversi Poin
                                                    </button>
                                                @endif
                                                {{-- <a href="" class="btn btn-outline-primary w-100">
                                            <i class="bi bi-chat-fill me-2"></i> Forum
                                            </a> --}}
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
    </div>



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
                                    <strong class="text-primary">{{ $diamondsEarned }} <i
                                            class="fa fa-diamond"></i></strong>
                                </li>
                            </ul>
                            <p class="text-muted mt-3"><small>Proses ini hanya bisa dilakukan satu kali per kursus dan
                                    tidak dapat dibatalkan.</small></p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        @if ($pointsData && !$pointsData->pivot->is_converted_to_diamond)
                            <form action="#" method="POST"> {{-- Ganti action ke route konversi nanti --}}
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
            const lessonLinks = document.querySelectorAll('.load-lesson');
            const lessonTitleEl = document.getElementById('lesson-title');
            const lessonContentEl = document.getElementById('lesson-content');
            const isPreview = @json($is_preview);
            console.log(isPreview);
            let completedLessons = @json($completedLessonIds);

            // --- FUNGSI UNTUK MEMUAT KONTEN PELAJARAN ---
            function loadLessonContent(lessonId) {
                lessonTitleEl.innerText = 'Memuat...';
                lessonContentEl.innerHTML =
                    '<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-3x"></i></div>';

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
                            const lessonTypeIcon = sidebarItem.querySelector('i.fa');
                            const isQuizOrAssignment = lessonTypeIcon.classList.contains(
                                'fa-question-circle') || lessonTypeIcon.classList.contains(
                                'fa-pencil-square-o');
                            if (!isQuizOrAssignment && !isPreview && !completedLessons.includes(parseInt(
                                    lessonId))) {
                                completeButtonHtml =
                                    `<hr><div class="text-center mt-4"><button class="btn btn-success mark-as-complete-btn" data-lesson-id="${lessonId}"><i class="fa fa-check"></i> Tandai Selesai</button></div>`;
                            }
                            lessonContentEl.innerHTML = data.html + completeButtonHtml + data.discussion_html;
                        } else {
                            lessonTitleEl.innerText = 'Gagal Memuat';
                            lessonContentEl.innerHTML =
                                `<p class="text-danger">${data.message || 'Terjadi kesalahan.'}</p>`;
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

            // --- LOGIKA BARU UNTUK TOMBOL FEEDBACK ---
            const reviewButton = document.getElementById('load-review-form');
            if (reviewButton) {
                reviewButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    lessonTitleEl.innerText = 'Ulasan & Rating Kursus';
                    lessonContentEl.innerHTML =
                        '<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-3x"></i></div>';

                    const url = "{{ route('student.course.review.create', $course->id) }}";

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                lessonContentEl.innerHTML = data.html;
                            } else {
                                lessonContentEl.innerHTML =
                                    `<p class="text-danger">${data.message || 'Gagal memuat form ulasan.'}</p>`;
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching review form:', error);
                            lessonContentEl.innerHTML =
                                '<p class="text-danger">Terjadi kesalahan jaringan.</p>';
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
                                const sidebarItem = document.getElementById(
                                    `sidebar-lesson-${lessonId}`);
                                if (sidebarItem && !sidebarItem.querySelector('.fa-check-circle')) {
                                    sidebarItem.insertAdjacentHTML('beforeend',
                                        ' <i class="fa fa-check-circle text-success"></i>');
                                }
                                completedLessons.push(parseInt(lessonId));
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


            lessonContentEl.addEventListener('submit', function(e) {
                if (e.target && e.target.id === 'course-review-form') {
                    e.preventDefault(); // Mencegah form submit secara normal
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
                                // Sembunyikan tombol feedback setelah berhasil submit
                                if (reviewButton) reviewButton.style.display = 'none';
                            } else {
                                errorAlert.innerText = data.message ||
                                    'Terjadi kesalahan. Pastikan semua kolom terisi.';
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

            // --- LOGIKA BARU UNTUK TOMBOL SERTIFIKAT ---
            const certificateButton = document.getElementById('load-certificate-preview');
            if (certificateButton) {
                certificateButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    lessonTitleEl.innerText = 'Sertifikat Kelulusan';
                    lessonContentEl.innerHTML =
                        '<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-3x"></i></div>';
                    const url = "{{ route('student.certificate.preview', $course->id) }}";
                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                lessonContentEl.innerHTML = data.html;
                            } else {
                                lessonContentEl.innerHTML =
                                    `<p class="text-danger">${data.message || 'Gagal memuat pratinjau sertifikat.'}</p>`;
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching certificate preview:', error);
                            lessonContentEl.innerHTML =
                                '<p class="text-danger">Terjadi kesalahan jaringan.</p>';
                        });
                });
            }



            // --- LOGIKA BARU UNTUK TOMBOL LEADERBOARD ---
            const leaderboardButton = document.getElementById('load-leaderboard');
            if (leaderboardButton) {
                leaderboardButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    lessonTitleEl.innerText = 'Papan Peringkat Kursus';
                    lessonContentEl.innerHTML =
                        '<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-3x"></i></div>';

                    const url = "{{ route('student.course.leaderboard', $course->id) }}";

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                lessonContentEl.innerHTML = data.html;
                            } else {
                                lessonContentEl.innerHTML =
                                    `<p class="text-danger">${data.message || 'Gagal memuat papan peringkat.'}</p>`;
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching leaderboard:', error);
                            lessonContentEl.innerHTML =
                                '<p class="text-danger">Terjadi kesalahan jaringan.</p>';
                        });
                });


            }



        });
    </script>
@endpush
