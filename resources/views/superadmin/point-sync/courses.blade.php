@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Kursus milik: {{ $instructor->name }}</h5>
                        <p class="m-b-0">Pilih kursus untuk melakukan sinkronisasi poin.</p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ auth()->user()->hasRole('superadmin') ? route('superadmin.dashboard') : route('admin.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.point-sync.index') }}">Sinkronisasi Poin</a></li>
                        <li class="breadcrumb-item"><a href="#!">{{ $instructor->name }}</a></li>
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
                            <h5>Daftar Kursus</h5>
                        </div>
                        <div class="card-block">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Judul Kursus</th>
                                            <th>Kategori</th>
                                            <th class="text-center">Jumlah Siswa</th>
                                            <th>Status</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($courses as $index => $course)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td><strong>{{ $course->title }}</strong></td>
                                                <td>{{ $course->category->name ?? '-' }}</td>
                                                <td class="text-center">{{ $course->students_count }}</td>
                                                <td>
                                                    @if($course->status == 'published')
                                                        <span class="badge bg-success">Published</span>
                                                    @elseif($course->status == 'draft')
                                                        <span class="badge bg-secondary">Draft</span>
                                                    @else
                                                        <span class="badge bg-warning">{{ ucfirst($course->status) }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('superadmin.point-sync.mass-sync.preview', $course) }}" class="btn btn-danger btn-sm" title="Sinkron Massal">
                                                            <i class="fa fa-magic me-1"></i> Sinkron Massal
                                                        </a>
                                                        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="collapse" data-bs-target="#students-{{ $course->id }}" title="Lihat Siswa">
                                                            <i class="fa fa-users me-1"></i> Lihat Siswa
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            {{-- Collapsible student list --}}
                                            <tr class="collapse" id="students-{{ $course->id }}">
                                                <td colspan="6" class="bg-light p-3">
                                                    @php $students = $course->students()->get(); @endphp
                                                    @if($students->count() > 0)
                                                        <table class="table table-sm table-bordered mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th>No</th>
                                                                    <th>Nama Siswa</th>
                                                                    <th>Email</th>
                                                                    <th class="text-center">Aksi</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($students as $si => $student)
                                                                    <tr>
                                                                        <td>{{ $si + 1 }}</td>
                                                                        <td>{{ $student->name }}</td>
                                                                        <td>{{ $student->email }}</td>
                                                                        <td class="text-center">
                                                                            <a href="{{ route('superadmin.point-sync.student-progress', [$course, $student]) }}" class="btn btn-outline-primary btn-sm">
                                                                                <i class="fa fa-list-alt me-1"></i> Progres & Sinkron
                                                                            </a>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @else
                                                        <p class="text-muted mb-0 text-center">Belum ada siswa yang terdaftar di kursus ini.</p>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">Instruktur ini belum memiliki kursus.</td>
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
