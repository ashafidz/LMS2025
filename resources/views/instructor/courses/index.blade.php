@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Kursus Saya</h5>
                        <p class="m-b-0">Kelola semua kursus yang telah Anda buat.</p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Kursus Saya</a></li>
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
                                    <h5>Daftar Kursus</h5>
                                    <span>Daftar semua kursus yang telah Anda buat</span>
                                    <div class="card-header-right">
                                        <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">
                                            <i class="bi bi-plus-lg text-white"></i> Buat Kursus Baru
                                        </a>
                                    </div>
                                </div>
                                <div class="card-block table-border-style">
                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif
                                    @if (session('error'))
                                        <div class="alert alert-danger">{{ session('error') }}</div>
                                    @endif
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Judul</th>
                                                    <th>Kategori</th>
                                                    <th>Status</th>
                                                    <th class="text-center">Kelola</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($courses as $course)
                                                    <tr>
                                                        <th scope="row">{{ $loop->iteration + $courses->firstItem() - 1 }}</th>
                                                        <td>{{ $course->title }}</td>
                                                        <td>{{ $course->category->name }}</td>
                                                        <td>
                                                            @php
                                                                $statusClasses = [
                                                                    'draft' => 'label-default',
                                                                    'pending_review' => 'label-warning',
                                                                    'published' => 'label-success',
                                                                    'rejected' => 'label-danger',
                                                                    'private' => 'label-inverse',
                                                                ];
                                                            @endphp
                                                            <label class="label {{ $statusClasses[$course->status] ?? 'label-default' }}">
                                                                {{ ucfirst(str_replace('_', ' ', $course->status)) }}
                                                            </label>
                                                        </td>
                                                        <td class="text-center">
                                                            {{-- Tombol Pratinjau (akan kita fungsikan nanti) --}}
                                                            <a href="{{ route('student.courses.show', ['course' => $course->slug, 'preview' => 'true']) }}"
                                                               class="btn btn-inverse btn-sm"
                                                               target="_blank">
                                                                <i class="bi bi-eye me-1"></i> Pratinjau
                                                            </a>
                                                            {{-- Tombol-tombol Aksi yang Sudah Dipisah --}}
                                                            <a href="{{ route('instructor.courses.modules.index', $course->id) }}" class="btn btn-success btn-sm" title="Kelola Modul">
                                                                <i class="fa fa-list-ul"></i> Modul
                                                            </a>
                                                            <button type="button" class="btn btn-warning btn-sm leaderboard-btn text-dark" data-url="{{ route('instructor.course.leaderboard', $course->id) }}" title="Lihat Papan Peringkat Kursus">
                                                                <i class="fa fa-bar-chart text-dark"></i> Peringkat
                                                            </button>

                                                            @if(in_array($course->status, ['draft', 'rejected']))
                                                                <form action="{{ route('instructor.courses.submit_review', $course->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Ajukan kursus ini untuk direview?');">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit" class="btn btn-primary btn-sm" title="Ajukan untuk Review">
                                                                        <i class="fa fa-paper-plane"></i> Ajukan
                                                                    </button>
                                                                </form>
                                                            @endif

                                                            @if(in_array($course->status, ['draft', 'published']))
                                                                <form action="{{ route('instructor.courses.make_private', $course->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Jadikan kursus ini privat?');">
                                                                    @csrf
                                                                    @method('PATCH')
                                                                    <button type="submit" class="btn btn-inverse btn-sm" title="Jadikan Privat">
                                                                        <i class="fa fa-lock"></i> Privat
                                                                    </button>
                                                                </form>
                                                            @endif

                                                        </td>
                                                        <td class="text-center">
                                                                                                                        <a href="{{ route('instructor.courses.edit', $course->id) }}" class="btn btn-info btn-sm" title="Edit Kursus">
                                                                <i class="fa fa-pencil"></i> Edit
                                                            </a>
                                                            
                                                                                                                        {{-- TOMBOL BARU UNTUK CLONE --}}
                                                            <form action="{{ route('instructor.courses.clone', $course->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin meng-clone kursus ini?');">
                                                                @csrf
                                                                <button type="submit" class="btn btn-warning btn-sm" title="Clone Kursus">
                                                                    <i class="fa fa-clone"></i> Clone
                                                                </button>
                                                            </form>
                                                            
                                                            <form action="{{ route('instructor.courses.destroy', $course->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kursus ini?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus Kursus">
                                                                    <i class="fa fa-trash"></i> Hapus
                                                                </button>
                                                            </form>

                                                        </td>
                                                        
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center">Anda belum membuat kursus.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        {{ $courses->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>





<!-- Modal Universal untuk Leaderboard -->
<div class="modal fade" id="leaderboardModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Papan Peringkat</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" id="leaderboardModalContent">
                {{-- Konten leaderboard akan dimuat di sini --}}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const leaderboardButtons = document.querySelectorAll('.leaderboard-btn');
    const modalContent = document.getElementById('leaderboardModalContent');
    const leaderboardModal = new bootstrap.Modal(document.getElementById('leaderboardModal'));

    leaderboardButtons.forEach(button => {
        button.addEventListener('click', function() {
            const url = this.dataset.url;
            modalContent.innerHTML = '<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-3x"></i></div>';
            leaderboardModal.show();
            
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    modalContent.innerHTML = data.html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    modalContent.innerHTML = '<p class="text-danger">Gagal memuat data papan peringkat.</p>';
                });
        });
    });
});
</script>
@endpush