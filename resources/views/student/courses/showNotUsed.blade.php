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
                    @if($is_preview)
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
                                                        <button class="btn btn-link" data-toggle="collapse" data-target="#collapse-{{ $module->id }}" aria-expanded="true" aria-controls="collapse-{{ $module->id }}">
                                                            <strong>{{ $module->title }}</strong>
                                                        </button>
                                                    </h5>
                                                </div>

                                                <div id="collapse-{{ $module->id }}" class="collapse show" aria-labelledby="heading-{{ $module->id }}" data-parent="#syllabus-accordion">
                                                    <div class="card-body p-0">
                                                        <ul class="list-group list-group-flush">
                                                            @foreach ($module->lessons as $lesson)
                                                                {{-- Beri ID unik pada setiap item daftar --}}
                                                                <li class="list-group-item d-flex justify-content-between align-items-center" id="sidebar-lesson-{{ $lesson->id }}">
                                                                    <a href="#" class="load-lesson text-dark" data-lesson-id="{{ $lesson->id }}">
                                                                        @php
                                                                            $icon = 'fa-file-text-o';
                                                                            $type = strtolower(class_basename($lesson->lessonable_type));
                                                                            if ($type === 'lessonvideo') $icon = 'fa-play-circle';
                                                                            if ($type === 'quiz') $icon = 'fa-question-circle';
                                                                            if ($type === 'lessondocument') $icon = 'fa-file-pdf-o';
                                                                            if ($type === 'lessonlinkcollection') $icon = 'fa-link';
                                                                            if ($type === 'lessonassignment') $icon = 'fa-pencil-square-o';
                                                                        @endphp
                                                                        <i class="fa {{ $icon }} mr-2"></i> {{ $lesson->title }}
                                                                    </a>
                                                                    {{-- Tampilkan ikon centang jika pelajaran sudah selesai --}}
                                                                    @if(in_array($lesson->id, $completedLessonIds))
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
                                            <a href="1kursusstd.html" class="btn btn-outline-primary w-100 mb-2">
                                            <i class="bi bi-chat-left-text me-2"></i> Feedback
                                            </a>
                                            <a href="" class="btn btn-outline-primary w-100 mb-2">
                                            <i class="bi bi-award-fill me-2"></i> Sertifikat
                                            </a>
                                            <a href="" class="btn btn-outline-primary w-100">
                                            <i class="bi bi-chat-fill me-2"></i> Forum
                                            </a>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lessonLinks = document.querySelectorAll('.load-lesson');
    const lessonTitleEl = document.getElementById('lesson-title');
    const lessonContentEl = document.getElementById('lesson-content');
    const isPreview = @json($is_preview);
    // Ambil daftar ID pelajaran yang sudah selesai dari PHP
    let completedLessons = @json($completedLessonIds);

    lessonLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const lessonId = this.dataset.lessonId;
            
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
                        
                        // Tambahkan tombol "Tandai Selesai" jika pelajaran belum selesai dan bukan mode pratinjau
                        let completeButtonHtml = '';
                        if (!isPreview && !completedLessons.includes(parseInt(lessonId))) {
                            completeButtonHtml = `
                                <hr>
                                <div class="text-center mt-4">
                                    <button class="btn btn-success mark-as-complete-btn" data-lesson-id="${lessonId}">
                                        <i class="fa fa-check"></i> Tandai Selesai
                                    </button>
                                </div>
                            `;
                        }
                        
                        lessonContentEl.innerHTML = data.html + completeButtonHtml;
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
        });
    });

    // Event listener untuk tombol "Tandai Selesai" (menggunakan event delegation)
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
                    // Sembunyikan tombol
                    button.style.display = 'none';
                    // Tambahkan ikon centang di sidebar
                    const sidebarItem = document.getElementById(`sidebar-lesson-${lessonId}`);
                    if (sidebarItem && !sidebarItem.querySelector('.fa-check-circle')) {
                        sidebarItem.insertAdjacentHTML('beforeend', ' <i class="fa fa-check-circle text-success"></i>');
                    }
                    // Tambahkan ID ke array JS agar tombol tidak muncul lagi
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
});
</script>
@endpush