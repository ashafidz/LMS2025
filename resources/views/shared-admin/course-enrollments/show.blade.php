@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Kelola Pengguna</h5>
                        <p class="m-b-0">Kursus: <strong>{{ $course->title }}</strong></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route(Auth::user()->getRoleNames()->first() . '.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route(Auth::user()->getRoleNames()->first() . '.course-enrollments.index') }}">Kelola Pengguna</a></li>
                        <li class="breadcrumb-item"><a href="#!">{{ Str::limit($course->title, 20) }}</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Daftar Pengguna Terdaftar</h5>
                                    <span>Halaman kelola pengguna manual untuk kursus {{ $course->title }}</span>
                                    <div class="card-header-right">
                                        <a href="{{ route(Auth::user()->getRoleNames()->first() . '.course-enrollments.create', $course->id) }}" class="btn btn-primary">
                                            <i class="fa fa-plus"></i> Tambah Pengguna Baru
                                        </a>
                                    </div>
                                </div>
                                <div class="card-block">
                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif
                                    <form id="bulk-delete-form" action="{{ route(Auth::user()->getRoleNames()->first() . '.course-enrollments.destroy', $course->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <div class="mb-3">
                                            {{-- <button type="button" class="btn btn-danger" id="bulk-delete-btn" disabled><i class="fa fa-trash"></i> Hapus yang Dipilih</button> --}}
                                            <button type="button" class="btn btn-danger" id="bulk-delete-btn" data-toggle="modal" data-target="#bulkDeleteModal" disabled>
                                                <i class="fa fa-trash"></i> Hapus yang Dipilih
                                            </button>
                                            <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteAllModal"><i class="fa fa-trash-o"></i> Hapus Semua Pengguna</button>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 5%;">
                                                            {{-- <div class="checkbox-fade fade-in-primary" >
                                                                <label >
                                                                    <input type="checkbox" id="select-all">
                                                                    <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                                                                </label>
                                                            </div> --}}
                                                            <input type="checkbox" id="select-all">
                                                        </th>
                                                        <th>Nama Siswa</th>
                                                        <th>Email</th>
                                                        <th>Tanggal Terdaftar</th>
                                                        <th class="text-center">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($enrolledUsers as $user)
                                                        <tr>
                                                            <td>
                                                                {{-- <div class="checkbox-fade fade-in-primary" >
                                                                    <label>
                                                                        <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="user-checkbox">
                                                                        <span class="cr"><i class="cr-icon icofont icofont-ui-check txt-primary"></i></span>
                                                                    </label>
                                                                </div> --}}
                                                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="user-checkbox">
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('profile.show', $user->id) }}">{{ $user->name }}</a>
                                                            </td>
                                                            <td>{{ $user->email }}</td>
                                                            <td>{{ $user->pivot->enrolled_at ? \Carbon\Carbon::parse($user->pivot->enrolled_at)->format('d M Y, H:i') : 'N/A' }}</td>
                                                            <td class="text-center">
                                                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteSingleModal-{{ $user->id }}">
                                                                    <i class="fa fa-trash"></i> Hapus
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center">Belum ada pengguna yang terdaftar di kursus ini.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </form>
                                    <div class="d-flex justify-content-center">
                                        {{ $enrolledUsers->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach ($enrolledUsers as $user)
        <div class="modal fade" id="deleteSingleModal-{{ $user->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus <strong>{{ $user->name }}</strong> dari kursus ini?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <form action="{{ route(Auth::user()->getRoleNames()->first() . '.course-enrollments.destroy', $course->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="user_ids[]" value="{{ $user->id }}">
                            <button type="submit" class="btn btn-danger">Ya, Hapus</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    <div class="modal fade" id="deleteAllModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Konfirmasi Hapus Semua</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p>Apakah Anda yakin ingin menghapus <strong>semua pengguna</strong> dari kursus ini? Aksi ini tidak dapat dibatalkan.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <form action="{{ route(Auth::user()->getRoleNames()->first() . '.course-enrollments.destroy', $course->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            @foreach($enrolledUsers as $user)
                                <input type="hidden" name="user_ids[]" value="{{ $user->id }}">
                            @endforeach
                            <button type="submit" class="btn btn-danger">Ya, Hapus Semua</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
</div>


{{-- New Modal for Bulk Delete Confirmation --}}
<div class="modal fade" id="bulkDeleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus Pengguna Terpilih</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus pengguna yang <strong>dipilih</strong> dari kursus ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                {{-- This button will trigger the form submission via JavaScript --}}
                <button type="button" class="btn btn-danger" id="confirm-bulk-delete-btn">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- <script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('select-all');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');

    function toggleBulkDeleteBtn() {
        const anyChecked = Array.from(userCheckboxes).some(cb => cb.checked);
        bulkDeleteBtn.disabled = !anyChecked;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            userCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            toggleBulkDeleteBtn();
        });
    }

    userCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (!this.checked) {
                selectAll.checked = false;
            }
            toggleBulkDeleteBtn();
        });
    });
    
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            if(confirm('Apakah Anda yakin ingin menghapus semua pengguna yang dipilih dari kursus ini?')) {
                document.getElementById('bulk-delete-form').submit();
            }
        });
    }
});
</script> --}}



<script>
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('select-all');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const bulkDeleteBtn = document.getElementById('bulk-delete-btn');
    const confirmBulkDeleteBtn = document.getElementById('confirm-bulk-delete-btn'); // Get the new modal button

    function toggleBulkDeleteBtn() {
        const anyChecked = Array.from(userCheckboxes).some(cb => cb.checked);
        bulkDeleteBtn.disabled = !anyChecked;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            userCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            toggleBulkDeleteBtn();
        });
    }

    userCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (!this.checked) {
                // Uncheck "select all" if any individual box is unchecked
                if (selectAll) {
                    selectAll.checked = false;
                }
            } else {
                // Check if all are checked to update "select all"
                const allChecked = Array.from(userCheckboxes).every(cb => cb.checked);
                if (selectAll) {
                    selectAll.checked = allChecked;
                }
            }
            toggleBulkDeleteBtn();
        });
    });
    
    // This event listener now handles the final submission from the modal
    if (confirmBulkDeleteBtn) {
        confirmBulkDeleteBtn.addEventListener('click', function() {
            document.getElementById('bulk-delete-form').submit();
        });
    }

    // Initialize the button state on page load
    toggleBulkDeleteBtn();
});
</script>
@endpush