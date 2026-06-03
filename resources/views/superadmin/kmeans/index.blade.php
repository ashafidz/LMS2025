@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">K-Means Clustering</h5>
                        <p class="m-b-0">Analisis Profil Murid pada Kursus Adaptive</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="card">
                        <div class="card-header">
                            <h5>Daftar Kursus Adaptive</h5>
                            <span>Pilih kursus untuk menjalankan algoritma K-Means</span>
                        </div>
                        <div class="card-block table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Judul Kursus</th>
                                        <th>Instruktur</th>
                                        <th>Total Siswa (Attempt)</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($courses as $course)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $course->title }}</td>
                                        <td>{{ $course->instructor->name ?? '-' }}</td>
                                        <td>{{ $course->profiling_attempts_count }}</td>
                                        <td>
                                            <form action="{{ route('superadmin.kmeans.run', $course->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Jalankan proses clustering sekarang? Waktu proses tergantung jumlah data.');">
                                                @csrf
                                                <button class="btn btn-primary btn-sm"><i class="fa fa-play"></i> Jalankan K-Means</button>
                                            </form>
                                            <a href="{{ route('superadmin.kmeans.show', $course->id) }}" class="btn btn-info btn-sm"><i class="fa fa-chart-bar"></i> Lihat Hasil</a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Belum ada kursus bertipe Adaptive.</td>
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
@endsection
