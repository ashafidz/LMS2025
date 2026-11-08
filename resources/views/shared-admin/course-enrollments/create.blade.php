@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <x-dash.page-head :crumbs="[['__home', route(Auth::user()->getRoleNames()->first() . '.dashboard')], ['Kelola Pengguna', route(Auth::user()->getRoleNames()->first() . '.course-enrollments.index')], [(Str::limit($course->title, 20)), route(Auth::user()->getRoleNames()->first() . '.course-enrollments.show', $course->id)], ['Tambah', null]]">
        <x-slot:title>Tambah Pengguna ke Kursus</x-slot:title>
        <x-slot:subtitle>Kursus: <strong>{{ $course->title }}</strong></x-slot:subtitle>
    </x-dash.page-head>
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
                                </div>
                                <div class="card-block">
                                    <x-dash.list-toolbar placeholder="Cari nama atau email..." label="Cari pengguna" />
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
                                        <div class="d-flex flex-wrap justify-content-between align-items-center mt-3" style="gap: 12px;">
                                            <a href="{{ route(Auth::user()->getRoleNames()->first() . '.course-enrollments.show', $course->id) }}" class="btn btn-secondary">Batal</a>
                                            <div class="d-flex flex-wrap align-items-center" style="gap: 12px;">
                                                <div>
                                                    <x-dash.pagination :items="$availableUsers" />
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