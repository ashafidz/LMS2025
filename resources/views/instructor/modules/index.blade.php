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
                        <p class="m-b-0">Manage modules for: <strong>{{ $course->title }}</strong></p>
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
                                    <div class="card-header-right">
                                        <a href="{{ route('instructor.courses.modules.create', $course->id) }}" class="btn btn-primary">
                                            <i class="bi bi-plus-lg text-white"></i> Buat Modul Baru</a>
                                    </div>
                                </div>
                                <div class="card-block">
                                    @if (session('success'))
                                        <div class="alert alert-success">
                                            {{ session('success') }}
                                        </div>
                                    @endif
                                    <div id="module-list">
                                        @forelse ($modules as $module)
                                            <div class="card" data-module-id="{{ $module->id }}">
                                                <div class="card-body d-flex justify-content-between align-items-center p-3">
                                                    <div>
                                                        <i class="fa fa-bars text-muted mr-3" style="cursor: move;"></i>
                                                        <strong>{{ $module->title }}</strong>
                                                        <span class="badge badge-info ml-2">{{ $module->lessons->count() }} Lessons</span>
                                                    </div>
                                                    <div>
                                                        <a href="{{ route('instructor.modules.lessons.index', $module->id) }}" class="btn btn-success btn-sm"><i class="bi bi-eye me-1"></i>View Lessons</a>
                                                        {{-- TOMBOL BARU UNTUK LEADERBOARD MODUL --}}
                                                        <button type="button" class="btn btn-warning btn-sm text-dark leaderboard-btn" data-url="{{ route('instructor.module.leaderboard', $module->id) }}">
                                                            <i class="fa fa-bar-chart text-dark me-1"></i> Peringkat
                                                        </button>
                                                        <a href="{{ route('instructor.modules.edit', $module->id) }}" class="btn btn-info btn-sm"><i class="fa fa-pencil"></i>Edit</a>
                                                        <form action="{{ route('instructor.modules.destroy', $module->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this module and all its lessons?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i>Hapus</button>
                                                        </form>
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
{{-- We can use jquery-ui sortable which is already included in the layout --}}
<script>
    $(document).ready(function () {
        $("#module-list").sortable({
            handle: '.fa-bars',
            update: function (event, ui) {
                let moduleIds = $(this).children().map(function () {
                    return $(this).data("module-id");
                }).get();

                $.ajax({
                    url: "{{ route('instructor.courses.modules.reorder', $course->id) }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        module_ids: moduleIds
                    },
                    success: function (response) {
                        // You can add a success notification here if you want
                        console.log(response.message);
                    },
                    error: function (xhr) {
                        console.error('Error reordering modules.');
                    }
                });
            }
        });
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
