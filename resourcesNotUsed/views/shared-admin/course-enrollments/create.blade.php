@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Tambah Pengguna ke Kursus</h5>
                        <p class="m-b-0">Kursus: <strong>{{ $course->title }}</strong></p>
                    </div>
                </div>
                <div class="col-md-4">
                     <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route(Auth::user()->getRoleNames()->first() . '.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route(Auth::user()->getRoleNames()->first() . '.course-enrollments.index') }}">Kelola Pengguna</a></li>
                        <li class="breadcrumb-item"><a href="{{ route(Auth::user()->getRoleNames()->first() . '.course-enrollments.show', $course->id) }}">{{ Str::limit($course->title, 20) }}</a></li>
                        <li class="breadcrumb-item"><a href="#!">Tambah</a></li>
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
                                    <h5>Cari & Tambah Pengguna</h5>
                                    <span>Pilih pengguna yang ingin ditambahkan ke kursus {{ $course->title }}</span>
                                    <div class="card-header-right">
                                        <form action="{{ route(Auth::user()->getRoleNames()->first() . '.course-enrollments.create', $course->id) }}" method="GET">
                                            <div class="input-group">
                                                <input type="text" name="search" class="form-control" placeholder="Cari nama atau email..." value="{{ request('search') }}">
                                                <button class="btn btn-primary" type="submit">
                                                    Cari
                                                    {{-- <i class="fa fa-search"></i> --}}
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <div class="card-block">
                                    <form action="{{ route(Auth::user()->getRoleNames()->first() . '.course-enrollments.store', $course->id) }}" method="POST">
                                        @csrf
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 5%;"><input type="checkbox" id="select-all"></th>
                                                        <th>Nama Siswa</th>
                                                        <th>Email</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($availableUsers as $user)
                                                        <tr>
                                                            <td><input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="user-checkbox"></td>
                                                            <td>{{ $user->name }}</td>
                                                            <td>{{ $user->email }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="text-center">Tidak ada pengguna yang cocok atau semua siswa sudah terdaftar.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-3">
                                            <a href="{{ route(Auth::user()->getRoleNames()->first() . '.course-enrollments.show', $course->id) }}" class="btn btn-secondary">Batal</a>
                                            <div class="d-flex align-items-center">
                                                <div class="mr-3">
                                                    {{ $availableUsers->links() }}
                                                </div>
                                                <button type="submit" class="btn btn-primary">Tambahkan Pengguna Terpilih</button>
                                            </div>
                                        </div>
                                    </form>
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
document.addEventListener('DOMContentLoaded', function() {
    const selectAll = document.getElementById('select-all');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');

    if(selectAll) {
        selectAll.addEventListener('change', function() {
            userCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
        });
    }
});
</script>
@endpush