@extends('layouts.app-layout') {{-- Sesuaikan dengan layout utama Anda --}}

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
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
    <!-- Page-header end -->

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    {{-- Menampilkan notifikasi jika ini adalah mode pratinjau --}}
                    @if($is_preview)
                    <div class="alert alert-warning text-center">
                        <strong>Mode Pratinjau</strong><br>
                        Anda melihat halaman ini sebagai Admin/Superadmin. Hasil kuis atau progres tidak akan disimpan.
                    </div>
                    @endif

                    <div class="row">
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
                                                                <li class="list-group-item">
                                                                    {{-- Tombol untuk memuat konten pelajaran --}}
                                                                    <a href="#" class="load-lesson" data-lesson-id="{{ $lesson->id }}">
                                                                        @php
                                                                            $icon = 'fa-file-text-o'; // default
                                                                            $type = strtolower(class_basename($lesson->lessonable_type));
                                                                            if ($type === 'lessonvideo') $icon = 'fa-play-circle';
                                                                            if ($type === 'quiz') $icon = 'fa-question-circle';
                                                                            if ($type === 'lessondocument') $icon = 'fa-file-pdf-o';
                                                                            if ($type === 'lessonlinkcollection') $icon = 'fa-link';
                                                                            if ($type === 'lessonassignment') $icon = 'fa-pencil-square-o';
                                                                        @endphp
                                                                        <i class="fa {{ $icon }} mr-2"></i> {{ $lesson->title }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-muted">Kursus ini belum memiliki modul.</p>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Kolom Konten Pelajaran (Utama) --}}
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 id="lesson-title">Selamat Datang di Kursus!</h5>
                                </div>
                                <div class="card-block" id="lesson-content" style="min-height: 500px;">
                                    {{-- Konten pelajaran akan dimuat di sini menggunakan JavaScript --}}
                                    <div class="text-center text-muted">
                                        <p><i class="fa fa-arrow-left fa-2x"></i></p>
                                        <h5>Pilih pelajaran dari daftar isi di sebelah kiri untuk memulai.</h5>
                                        <hr>
                                        <p class="mt-4"><strong>Deskripsi Kursus:</strong></p>
                                        <p>{{ $course->description }}</p>
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
{{-- Script untuk memuat konten pelajaran secara dinamis --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lessonLinks = document.querySelectorAll('.load-lesson');
    const lessonTitleEl = document.getElementById('lesson-title');
    const lessonContentEl = document.getElementById('lesson-content');
    // Cek apakah halaman ini sedang dalam mode pratinjau
    const isPreview = @json($is_preview);

    lessonLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            const lessonId = this.dataset.lessonId;
            
            lessonTitleEl.innerText = 'Memuat...';
            lessonContentEl.innerHTML = '<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-3x"></i></div>';

            // DIPERBARUI: Tambahkan parameter preview jika ada
            let url = `/lessons/${lessonId}/content`;
            if (isPreview) {
                url += '?preview=true';
            }

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        lessonTitleEl.innerText = data.title;
                        lessonContentEl.innerHTML = data.html;
                    } else {
                        lessonTitleEl.innerText = 'Gagal Memuat';
                        lessonContentEl.innerHTML = '<p class="text-danger">Terjadi kesalahan saat memuat konten pelajaran.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching lesson content:', error);
                    lessonTitleEl.innerText = 'Gagal Memuat';
                    lessonContentEl.innerHTML = '<p class="text-danger">Terjadi kesalahan jaringan.</p>';
                });
        });
    });
});
</script>
@endpush