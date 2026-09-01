@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Course Modules</h5>
                        <p class="m-b-0 fw-bolder" style="font-size: 2rem;" ><strong>{{ $course->title }}</strong></p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"> <i class="fa fa-home"></i> </a></li>
                        <li class="breadcrumb-item"><a href="{{ route('instructor.courses.index') }}">Kursus Saya</a></li>
                        <li class="breadcrumb-item"><a href="#!">Modul Saya</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- Page-header end -->
    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                <div class="page-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>List Modul</h5>
                                    <span>Seret dan lepaskan modul untuk mengubah urutannya.</span>
                                    <div class="card-header-right d-none d-md-block">
                                        <a href="{{ route('instructor.courses.modules.create', $course) }}" class="btn btn-primary">
                                            <i class="bi bi-plus-lg text-white"></i> Buat Modul Baru</a>
                                    </div>
                                </div>
                                <div class="card-block">
                                    <div class="d-block d-md-none mb-3">
                                        <a href="{{ route('instructor.courses.modules.create', $course) }}" class="btn btn-primary w-100">
                                            <i class="bi bi-plus-lg text-white me-1"></i> Buat Modul Baru</a>
                                    </div>
                                    @if (session('success'))
                                        <div class="alert alert-success">
                                            {{ session('success') }}
                                        </div>
                                    @endif
                                    <div id="module-list">
                                        @forelse ($modules as $module)
                                            <div class="card" data-module-id="{{ $module->id }}">
                                                <div class="card-body d-flex justify-content-between align-items-center p-3">
                                                    <div class="d-flex align-items-center overflow-hidden me-2 flex-grow-1" style="min-width: 0;">
                                                        <i class="fa fa-bars text-muted mr-3 flex-shrink-0" style="cursor: move;"></i>
                                                        <div class="text-truncate">
                                                            <strong class="d-block mb-1 text-truncate">{{ $module->title }}</strong>
                                                            <span class="badge badge-info">{{ $module->lessons->count() }} Pelajaran</span>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center flex-shrink-0" style="gap: 5px;">
                                                        <a href="{{ route('instructor.modules.lessons.index', $module) }}" class="btn btn-primary btn-sm" title="Kelola Pelajaran">
                                                            <i class="bi bi-journal-text"></i> <span class="d-none d-md-inline ms-1">Kelola Pelajaran</span>
                                                        </a>
                                                        
                                                        <div class="dropdown">
                                                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Aksi Lainnya" data-bs-boundary="window">
                                                                <i class="fa fa-cog"></i>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                                                <li>
                                                                    <button class="dropdown-item leaderboard-btn" type="button" data-url="{{ route('instructor.module.leaderboard', $module) }}">
                                                                        <i class="fa fa-bar-chart text-warning me-2"></i> Papan Peringkat
                                                                    </button>
                                                                </li>
                                                                <li>
                                                                    <a class="dropdown-item" href="{{ route('instructor.modules.edit', $module) }}">
                                                                        <i class="fa fa-pencil text-info me-2"></i> Edit Modul
                                                                    </a>
                                                                </li>
                                                                <li><hr class="dropdown-divider"></li>
                                                                <li>
                                                                    <form action="{{ route('instructor.modules.destroy', $module) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus modul ini beserta seluruh pelajarannya?');">
                                                                        @csrf @method('DELETE')
                                                                        <button type="submit" class="dropdown-item text-danger">
                                                                            <i class="fa fa-trash text-danger me-2"></i> Hapus Modul
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center">
                                                <p>No modules found for this course. Create one to get started!</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Page-body end -->
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
<style>
    .sortable-ghost {
        opacity: 0.4;
        background-color: #f8f9fa;
        border: 2px dashed #007bff !important;
    }
    .sortable-chosen {
        background-color: #e9ecef;
        box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
        transform: scale(1.02);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .fa-bars {
        cursor: grab !important;
    }
    .fa-bars:active {
        cursor: grabbing !important;
    }
    #module-list .card {
        transition: transform 0.2s ease;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const el = document.getElementById('module-list');
        if (el) {
            new Sortable(el, {
                handle: '.fa-bars',
                animation: 350,
                easing: "cubic-bezier(1, 0, 0, 1)",
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function (evt) {
                    const moduleIds = Array.from(el.children).map(child => child.dataset.moduleId);
                    
                    fetch('{{ route("instructor.courses.modules.reorder", $course) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ module_ids: moduleIds })
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data.message || 'Reorder successful');
                    })
                    .catch(error => {
                        console.error('Error reordering modules:', error);
                    });
                }
            });
        }
    });


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
