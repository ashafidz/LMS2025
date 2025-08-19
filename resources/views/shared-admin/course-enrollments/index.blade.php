@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Kelola Pengguna Kursus</h5>
                        <p class="m-b-0">Pilih kursus untuk mengelola pengguna yang terdaftar.</p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route(Auth::user()->getRoleNames()->first() . '.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Kelola Pengguna</a></li>
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
                                    <h5>Daftar Kursus</h5>
                                </div>
                                <div class="card-block table-border-style">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Judul Kursus</th>
                                                    <th>Instruktur</th>
                                                    <th>Status Kursus</th>
                                                    <th>Jumlah Siswa</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($courses as $course)
                                                    <tr>
                                                        <td>{{ $course->title }}</td>
                                                        <td>{{ $course->instructor->name }}</td>
                                                        <td>
                                                            @php
                                                                $statusClasses = [
                                                                    'draft' => 'label-default',
                                                                    'pending_review' => 'label-warning',
                                                                    'published' => 'label-success',
                                                                    'rejected' => 'label-danger',
                                                                    'private' => 'label-inverse',
                                                                ];
                                                            @endphp
                                                            <label class="label {{ $statusClasses[$course->status] ?? 'label-default' }}">
                                                                {{ ucfirst(str_replace('_', ' ', $course->status)) }}
                                                            </label>
                                                        </td>
                                                        <td>{{ $course->students->count() }} Siswa</td>
                                                        <td class="text-center">
                                                            <a href="{{ route(Auth::user()->getRoleNames()->first() . '.course-enrollments.show', $course->id) }}" class="btn btn-primary btn-sm">
                                                                <i class="fa fa-users"></i> Kelola Pengguna
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center">Belum ada kursus yang dibuat.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        {{ $courses->links() }}
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