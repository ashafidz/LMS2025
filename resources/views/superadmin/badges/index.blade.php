@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Manajemen Badge</h5>
                        <p class="m-b-0">Buat, lihat, dan kelola semua badge pencapaian.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Badge</a></li>
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
                                    <h5>Daftar Badge</h5>
                                    <div class="card-header-right">
                                        <a href="{{ route('superadmin.badges.create') }}" class="btn btn-primary">Buat Badge Baru</a>
                                    </div>
                                </div>
                                <div class="card-block table-border-style">
                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Ikon</th>
                                                    <th>Judul</th>
                                                    <th>Deskripsi</th>
                                                    <th>Status</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($badges as $badge)
                                                    <tr>
                                                        <th scope="row">{{ $loop->iteration + $badges->firstItem() - 1 }}</th>
                                                        <td>
                                                            <img src="{{ Storage::url($badge->icon_path) }}" alt="{{ $badge->title }}" style="width: 40px; height: 40px; object-fit: cover;">
                                                        </td>
                                                        <td>{{ $badge->title }}</td>
                                                        <td>{{ Str::limit($badge->description, 50) }}</td>
                                                        <td>
                                                            @if($badge->is_active)
                                                                <label class="label label-success">Aktif</label>
                                                            @else
                                                                <label class="label label-default">Tidak Aktif</label>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <a href="{{ route('superadmin.badges.edit', $badge->id) }}" class="btn btn-info btn-sm">
                                                                <i class="fa fa-pencil"></i> Edit
                                                            </a>
                                                            <form action="{{ route('superadmin.badges.destroy', $badge->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus badge ini?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm">
                                                                    <i class="fa fa-trash"></i> Hapus
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center">Belum ada badge yang dibuat.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        {{ $badges->links() }}
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