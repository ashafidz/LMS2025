@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Kelola Pelajaran: {{ $module->title }}</h5>
                        <p class="m-b-0">Atur semua pelajaran untuk modul ini.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('instructor.courses.modules.index', $module->course) }}">Modul</a></li>
                        <li class="breadcrumb-item"><a href="#!">{{ Str::limit($module->title, 20) }}</a></li>
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
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Daftar Pelajaran</h5>
                                    <span>Seret dan lepas pelajaran untuk mengubah urutan.</span>
                                    <div class="card-header-right">
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#selectLessonTypeModal">
                                            Buat Pelajaran Baru
                                        </button>
                                    </div>
                                </div>
                                <div class="card-block">
                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif

                                    @if ($lessons->isEmpty())
                                        <div class="text-center">
                                            <p>Belum ada pelajaran di modul ini. Silakan buat yang pertama!</p>
                                        </div>
                                    @else
                                        <div id="lesson-list" class="list-group">
                                            @foreach ($lessons as $lesson)
                                                {{-- Atribut data-id penting untuk JavaScript --}}
                                                <div class="list-group-item list-group-item-action flex-column align-items-start" data-id="{{ $lesson->id }}">
                                                    <div class="d-flex w-100 justify-content-between">
                                                        <h5 class="mb-1">
                                                            <i class="fa fa-bars handle" style="cursor: move; margin-right: 10px;"></i> {{ $lesson->title }}
                                                        </h5>
                                                        <small>Tipe: {{ ucfirst(class_basename($lesson->lessonable_type)) }}</small>
                                                    </div>
                                                    <div class="mt-2">
                                                        {{-- Tombol Edit Umum --}}
                                                        <a href="{{ route('instructor.lessons.edit', $lesson->id) }}" class="btn btn-info btn-sm">Edit</a>

                                                        {{-- Tombol Khusus untuk Kuis --}}
                                                        @if (strtolower(class_basename($lesson->lessonable_type)) === 'quiz')
                                                            <a href="{{ route('instructor.quizzes.manage_questions', $lesson->lessonable_id) }}" class="btn btn-success btn-sm">Kelola Soal</a>
                                                        @endif

                                                        {{-- Tombol Hapus --}}
                                                        <form action="{{ route('instructor.lessons.destroy', $lesson->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelajaran ini?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk Memilih Tipe Pelajaran -->
    <div class="modal fade" id="selectLessonTypeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Pilih Tipe Pelajaran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Silakan pilih tipe konten yang ingin Anda buat untuk pelajaran ini.</p>
                    <div class="list-group">
                        <a href="{{ route('instructor.modules.lessons.create', ['module' => $module, 'type' => 'article']) }}" class="list-group-item list-group-item-action">
                            <i class="fa fa-file-text-o"></i> <strong>Pelajaran Artikel</strong>
                            <br><small>Pelajaran berbasis teks dengan gambar.</small>
                        </a>
                        <a href="{{ route('instructor.modules.lessons.create', ['module' => $module, 'type' => 'video']) }}" class="list-group-item list-group-item-action">
                            <i class="fa fa-video-camera"></i> <strong>Pelajaran Video</strong>
                            <br><small>Unggah file video sebagai materi pelajaran.</small>
                        </a>
                        <a href="{{ route('instructor.modules.lessons.create', ['module' => $module, 'type' => 'quiz']) }}" class="list-group-item list-group-item-action">
                            <i class="fa fa-question-circle-o"></i> <strong>Kuis</strong>
                            <br><small>Buat kuis untuk menguji pemahaman siswa.</small>
                        </a>
                        <a href="{{ route('instructor.modules.lessons.create', ['module' => $module, 'type' => 'assignment']) }}" class="list-group-item list-group-item-action">
                            <i class="fa fa-paperclip"></i> <strong>Tugas (Assignment)</strong>
                            <br><small>Berikan tugas yang memerlukan pengumpulan file.</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
{{-- Library untuk drag-and-drop --}}
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('lesson-list');
    if (el) {
        const sortable = new Sortable(el, {
            handle: '.handle', // Elemen yang bisa di-drag
            animation: 150,
            onEnd: function (evt) {
                // Dijalankan setelah selesai drag-and-drop
                const lessonIds = Array.from(el.children).map(child => child.dataset.id);

                // Kirim urutan baru ke server via AJAX
                fetch('{{ route("instructor.modules.lessons.reorder", $module) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ lesson_ids: lessonIds })
                })
                .then(response => response.json())
                .then(data => {
                    if(data.status === 'success') {
                        // Anda bisa menambahkan notifikasi sukses di sini jika mau
                        console.log(data.message);
                    } else {
                        alert('Gagal memperbarui urutan.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan.');
                });
            }
        });
    }
});
</script>
@endpush