@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Dashboard</h5>
                        <p class="m-b-0">Selamat datang di Dashboard Instructor, {{ Auth::user()->name }}</p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Dashboard</a></li>
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
                        <!-- Total Siswa -->
                        <div class="col-lg-3 col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-body py-3 px-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h4 class="text-primary mb-1">{{ $totalStudents }}</h4>
                                            <p class="text-muted mb-0 small">Total Siswa</p>
                                        </div>
                                        <i class="fa fa-users fa-2x text-primary"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Kursus -->
                        <div class="col-lg-3 col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-body py-3 px-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h4 class="text-danger mb-1">{{ $totalCourses }}</h4>
                                            <p class="text-muted mb-0 small">Total Kursus</p>
                                        </div>
                                        <i class="fa fa-book fa-2x text-danger"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Siswa Belum Menyelesaikan -->
                        <div class="col-lg-3 col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-body py-3 px-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h4 class="text-warning mb-1">{{ $totalInProgressStudents }}</h4>
                                            <p class="text-muted mb-0 small">Siswa Belum Selesai</p>
                                        </div>
                                        <i class="fa fa-hourglass-half fa-2x text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Siswa Sudah Menyelesaikan -->
                        <div class="col-lg-3 col-md-6">
                            <div class="card shadow-sm h-100">
                                <div class="card-body py-3 px-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h4 class="text-success mb-1">{{ $totalCompletedStudents }}</h4>
                                            <p class="text-muted mb-0 small">Siswa Sudah Selesai</p>
                                        </div>
                                        <i class="fa fa-graduation-cap fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Baris Kedua: Tabel Rincian -->
                    <div class="row mt-4">
                        <!-- Tabel Siswa per Kursus -->
                        <div class="col-xl-6 col-md-12">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0">Siswa per Kursus</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Nama Kursus</th>
                                                    <th class="text-center">Jumlah Siswa</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($courses as $course)
                                                <tr>
                                                    <td>{{ $course->title }}</td>
                                                    <td class="text-center">{{ $course->students_count }}</td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="2" class="text-center">Anda belum memiliki kursus.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabel Progres Penyelesaian per Kursus -->
                        <div class="col-xl-6 col-md-12">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h5 class="mb-0">Progres Penyelesaian Kursus</h5>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Nama Kursus</th>
                                                    <th class="text-center">Sudah Selesai</th>
                                                    <th class="text-center">Belum Selesai</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($courses as $course)
                                                <tr>
                                                    <td>{{ $course->title }}</td>
                                                    <td class="text-center">{{ $course->completed_students_count }}</td>
                                                    <td class="text-center">{{ $course->inprogress_students_count }}</td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="3" class="text-center">Anda belum memiliki kursus.</td></tr>
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
    </div>
</div>
@endsection