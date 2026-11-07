@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <x-dash.page-head :crumbs="[['__home', auth()->user()->hasRole('superadmin') ? route('superadmin.dashboard') : route('admin.dashboard')], ['Sinkronisasi Poin', null]]">
        <x-slot:title>Sinkronisasi Poin</x-slot:title>
        <x-slot:subtitle>Pilih instruktur untuk mengelola sinkronisasi poin kursusnya.</x-slot:subtitle>
    </x-dash.page-head>

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
