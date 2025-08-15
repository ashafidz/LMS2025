@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Kelola Kategori Kursus</h5>
                        <p class="m-b-0">Buat, lihat, dan kelola semua kategori untuk kursus.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route(Auth::user()->getRoleNames()->first() . '.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Kategori Kursus</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="pcoded-inner-content">
        <div class="main-body"><div class="page-wrapper"><div class="page-body">
            <div class="row">
                <div class="col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h5>Daftar Kategori</h5>
                            <div class="card-header-right">
                                <a href="{{ route(Auth::user()->getRoleNames()->first() . '.course-categories.create') }}" class="btn btn-primary">Buat Kategori Baru</a>
                            </div>
                        </div>
                        <div class="card-block table-border-style">
                            @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
                            @if (session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nama Kategori</th>
                                            <th>Jumlah Kursus</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($categories as $category)
                                            <tr>
                                                <td><strong>{{ $category->name }}</strong></td>
                                                <td>{{ $category->courses_count }} Kursus</td>
                                                <td class="text-center">
                                                    <a href="{{ route(Auth::user()->getRoleNames()->first() . '.course-categories.edit', $category->id) }}" class="btn btn-info btn-sm"><i class="fa fa-pencil"></i> Edit</a>
                                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal-{{ $category->id }}" {{ $categories->count() <= 1 ? 'disabled' : '' }}>
                                                        <i class="fa fa-trash"></i> Hapus
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center">Belum ada kategori yang dibuat.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center">{{ $categories->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div></div></div>
    </div>
</div>

<!-- Modal Hapus -->
@foreach ($categories as $category)
<div class="modal fade" id="deleteModal-{{ $category->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus Kategori</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form action="{{ route(Auth::user()->getRoleNames()->first() . '.course-categories.destroy', $category->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Anda akan menghapus kategori <strong>"{{ $category->name }}"</strong>.</p>
                    <p>Semua ({{ $category->courses_count }}) kursus dalam kategori ini harus dipindahkan ke kategori lain.</p>
                    <div class="form-group">
                        <label for="new_category_id">Pindahkan kursus ke kategori:</label>
                        <select name="new_category_id" class="form-control" required>
                            <option value="">-- Pilih Kategori Tujuan --</option>
                            @foreach ($categories->where('id', '!=', $category->id) as $otherCategory)
                                <option value="{{ $otherCategory->id }}">{{ $otherCategory->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Ya, Pindahkan & Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection