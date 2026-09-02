@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Sinkronisasi Poin</h5>
                        <p class="m-b-0">Pilih instruktur untuk mengelola sinkronisasi poin kursusnya.</p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ auth()->user()->hasRole('superadmin') ? route('superadmin.dashboard') : route('admin.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Sinkronisasi Poin</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="card">
                        <div class="card-header">
                            <h5>Daftar Instruktur</h5>
                        </div>
                        <div class="card-block">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($instructors as $index => $instructor)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td><strong>{{ $instructor->name }}</strong></td>
                                                <td>{{ $instructor->email }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('superadmin.point-sync.courses', $instructor) }}" class="btn btn-primary btn-sm">
                                                        <i class="fa fa-book me-1"></i> Lihat Kursus
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">Tidak ada instruktur terdaftar.</td>
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
@endsection
