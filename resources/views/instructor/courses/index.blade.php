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
                                    <div class="card-header-right d-none d-md-block">
                                        <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">
                                            <i class="bi bi-plus-lg text-white"></i> Buat Kursus Baru</a>
                                    </div>
                                </div>
                                <div class="card-block">
                                    <div class="d-block d-md-none mb-3">
                                        <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary w-100">
                                            <i class="bi bi-plus-lg text-white me-1"></i> Buat Kursus Baru</a>
                                    </div>
                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif
                                    @if (session('error'))
                                        <div class="alert alert-danger">{{ session('error') }}</div>
                                    @endif
                                    <div class="table-responsive" style="overflow: visible;">
                                        @php
                                            $statusClasses = [
                                                'draft' => 'label-default',
                                                'pending_review' => 'label-warning',
                                                'published' => 'label-success',
                                                'rejected' => 'label-danger',
                                                'private' => 'label-inverse',
                                            ];
                                        @endphp
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th class="d-none d-md-table-cell" width="5%">#</th>
                                                    <th width="35%">Info Kursus</th>
                                                    <th class="d-none d-md-table-cell" width="20%">Kategori</th>
                                                    <th class="d-none d-md-table-cell" width="15%">Status</th>
                                                    <th class="text-center" width="25%">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($courses as $course)
                                                    <tr>
                                                        <th scope="row" class="d-none d-md-table-cell align-middle">{{ $loop->iteration }}</th>
                                                        <td class="align-middle text-wrap" style="max-width: 150px; word-wrap: break-word;">
                                                            <strong>{{ $course->title }}</strong>
                                                            {{-- Info Status dan Kategori Khusus Mobile --}}
                                                            <div class="d-block d-md-none mt-1">
                                                                <small class="text-muted d-block">{{ $course->category->name }}</small>
                                                                <label class="label {{ $statusClasses[$course->status] ?? 'label-default' }} mt-1">
                                                                    {{ ucfirst(str_replace('_', ' ', $course->status)) }}
                                                                </label>
                                                            </div>
                                                        </td>
                                                        <td class="d-none d-md-table-cell align-middle">{{ $course->category->name }}</td>
                                                        <td class="d-none d-md-table-cell align-middle">
                                                            <label class="label {{ $statusClasses[$course->status] ?? 'label-default' }}">
                                                                {{ ucfirst(str_replace('_', ' ', $course->status)) }}
                                                            </label>
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            <div class="d-flex justify-content-center align-items-center" style="gap: 5px;">
                                                                {{-- Aksi Utama --}}
                                                                <a href="{{ route('instructor.courses.modules.index', $course) }}" class="btn btn-primary btn-sm" title="Kelola Modul">
                                                                    <i class="fa fa-list-ul"></i> Modul
                                                                </a>
                                                                
                                                                {{-- Dropdown Aksi Lainnya --}}
                                                                <div class="dropdown">
                                                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Aksi Lainnya">
                                                                        <i class="fa fa-cog"></i>
                                                                    </button>
                                                                    <ul class="dropdown-menu dropdown-menu-end shadow">
                                                                        <li><h6 class="dropdown-header">Kelola</h6></li>
                                                                        <li>
                                                                            <a class="dropdown-item" href="{{ route('student.courses.show', ['course' => $course->slug, 'preview' => 'true']) }}" target="_blank">
                                                                                <i class="bi bi-eye text-primary me-2"></i> Pratinjau
                                                                            </a>
                                                                        </li>
                                                                        <li>
                                                                            <a class="dropdown-item" href="{{ route('instructor.courses.edit', $course) }}">
                                                                                <i class="fa fa-pencil text-info me-2"></i> Edit Kursus
                                                                            </a>
                                                                        </li>
                                                                        <li>
                                                                            <a class="dropdown-item" href="{{ route('instructor.recap.index', $course) }}">
                                                                                <i class="fa fa-file-text-o text-success me-2"></i> Rekap Nilai
                                                                            </a>
                                                                        </li>
                                                                        <li>
                                                                            <a class="dropdown-item" href="{{ route('instructor.course.monitoring.overview', $course) }}">
                                                                                <i class="fa fa-shield text-info me-2"></i> Lap. Pelanggaran
                                                                            </a>
                                                                        </li>
                                                                        <li>
                                                                            <button class="dropdown-item leaderboard-btn" type="button" data-url="{{ route('instructor.course.leaderboard', $course) }}">
                                                                                <i class="fa fa-bar-chart text-warning me-2"></i> Data Student
                                                                            </button>
                                                                        </li>
                                                                        
                                                                        @if(in_array($course->status, ['draft', 'rejected']))
                                                                        <li><hr class="dropdown-divider"></li>
                                                                        <li><h6 class="dropdown-header">Status</h6></li>
                                                                        <li>
                                                                            <form action="{{ route('instructor.courses.submit_review', $course) }}" method="POST" onsubmit="return confirm('Ajukan kursus ini untuk direview?');">
                                                                                @csrf @method('PATCH')
                                                                                <button type="submit" class="dropdown-item">
                                                                                    <i class="fa fa-paper-plane text-primary me-2"></i> Ajukan Review
                                                                                </button>
                                                                            </form>
                                                                        </li>
                                                                        @endif

                                                                        @if(in_array($course->status, ['draft', 'published']))
                                                                        @if(!in_array($course->status, ['draft', 'rejected']))
                                                                        <li><hr class="dropdown-divider"></li>
                                                                        <li><h6 class="dropdown-header">Status</h6></li>
                                                                        @endif
                                                                        <li>
                                                                            <form action="{{ route('instructor.courses.make_private', $course) }}" method="POST" onsubmit="return confirm('Jadikan kursus ini privat?');">
                                                                                @csrf @method('PATCH')
                                                                                <button type="submit" class="dropdown-item">
                                                                                    <i class="fa fa-lock text-secondary me-2"></i> Jadikan Privat
                                                                                </button>
                                                                            </form>
                                                                        </li>
                                                                        @endif
                                                                        
                                                                        <li><hr class="dropdown-divider"></li>
                                                                        <li><h6 class="dropdown-header">Lainnya</h6></li>
                                                                        <li>
                                                                            <form action="{{ route('instructor.courses.clone', $course) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin meng-clone kursus ini?');">
                                                                                @csrf
                                                                                <button type="submit" class="dropdown-item">
                                                                                    <i class="fa fa-clone text-warning me-2"></i> Clone Kursus
                                                                                </button>
                                                                            </form>
                                                                        </li>
                                                                        
                                                                        <li>
                                                                            <form action="{{ route('instructor.courses.destroy', $course) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kursus ini?');">
                                                                                @csrf @method('DELETE')
                                                                                <button type="submit" class="dropdown-item text-danger">
                                                                                    <i class="fa fa-trash text-danger me-2"></i> Hapus Kursus
                                                                                </button>
                                                                            </form>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center py-4 text-muted">Anda belum membuat kursus.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
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
                <h5 class="modal-title">Daftar Siswa</h5>
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