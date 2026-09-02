@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Sinkronisasi Poin Massal</h5>
                        <p class="m-b-0">Kursus: <strong>{{ $course->title }}</strong> — Instruktur: {{ $course->instructor->name ?? '-' }}</p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ auth()->user()->hasRole('superadmin') ? route('superadmin.dashboard') : route('admin.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.point-sync.index') }}">Sinkronisasi Poin</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.point-sync.courses', $course->instructor) }}">{{ $course->instructor->name ?? 'Instruktur' }}</a></li>
                        <li class="breadcrumb-item"><a href="#!">Sinkron Massal</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            {{ session('info') }}
                            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5>Pratinjau Anomali Poin</h5>
                            <span class="text-white d-block mt-2">Sistem telah memindai seluruh progres siswa di kursus ini dan menemukan <strong>{{ count($anomalies) }}</strong> catatan poin yang tidak sinkron.</span>
                        </div>
                        <div class="card-block">
                            @if(count($anomalies) > 0)
                                <div class="table-responsive mb-4">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>No</th>
                                                <th>Nama Siswa</th>
                                                <th>Materi</th>
                                                <th class="text-center">Poin Saat Ini</th>
                                                <th class="text-center">Poin Ekspektasi</th>
                                                <th>Deskripsi Masalah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($anomalies as $index => $anomaly)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>
                                                        <strong>{{ $anomaly['student']->name }}</strong>
                                                        <br><small class="text-muted">{{ $anomaly['student']->email }}</small>
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-info">{{ $anomaly['display_type'] }}</span><br>
                                                        {{ $anomaly['lesson']->title }}
                                                    </td>
                                                    <td class="text-center text-danger font-weight-bold">
                                                        {{ $anomaly['actual_points'] }}
                                                    </td>
                                                    <td class="text-center text-success font-weight-bold">
                                                        {{ $anomaly['expected_points'] }}
                                                    </td>
                                                    <td>
                                                        @if($anomaly['is_missing'])
                                                            <span class="text-danger"><i class="fa fa-exclamation-triangle"></i> Data riwayat poin tidak ditemukan di database.</span>
                                                        @else
                                                            <span class="text-warning"><i class="fa fa-exclamation-circle"></i> Poin saat ini tidak sesuai dengan pengaturan bobot.</span>
                                                        @endif
                                                        <br><small class="text-muted">Akan ditambahkan deskripsi: "{{ $anomaly['description'] }}"</small>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="alert alert-warning border-warning">
                                    <h5 class="alert-heading"><i class="fa fa-exclamation-triangle"></i> Perhatian</h5>
                                    <p>Tindakan eksekusi sinkronisasi massal ini akan <strong>menimpa secara permanen</strong> riwayat poin siswa-siswa di atas menjadi sesuai dengan <strong>Poin Ekspektasi</strong>, dan juga akan mengkalkulasikan ulang total perolehan poin mereka di kursus ini. Tindakan ini tidak dapat dibatalkan.</p>
                                </div>

                                <form action="{{ route('superadmin.point-sync.mass-sync.execute', $course) }}" method="POST" onsubmit="return confirm('Anda yakin ingin menyinkronkan {{ count($anomalies) }} data poin ini secara permanen?');">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-lg btn-block shadow-sm w-100">
                                        <i class="fa fa-magic me-2"></i> Eksekusi Sinkronisasi Massal Sekarang
                                    </button>
                                </form>
                            @else
                                <div class="text-center p-5">
                                    <i class="fa fa-check-circle text-success" style="font-size: 64px;"></i>
                                    <h4 class="mt-3">Sistem Poin Stabil</h4>
                                    <p class="text-muted">Pindai mendalam selesai. Tidak ditemukan masalah atau anomali pada perolehan poin seluruh siswa di kursus ini.</p>
                                    <a href="{{ route('superadmin.point-sync.courses', $course->instructor) }}" class="btn btn-secondary mt-3"><i class="fa fa-arrow-left"></i> Kembali ke Daftar Kursus</a>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
