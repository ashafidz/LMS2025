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
                        <div class="col-lg-3 col-md-6 col-6 mb-3">
                            <div class="card shadow-sm h-100 mb-0">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-truncate mr-2">
                                            <h4 class="text-primary mb-1">{{ $totalStudents }}</h4>
                                            <p class="text-muted mb-0 small text-truncate">Total Siswa</p>
                                        </div>
                                        <div class="d-none d-sm-block">
                                            <i class="fa fa-users fa-2x text-primary opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Kursus -->
                        <div class="col-lg-3 col-md-6 col-6 mb-3">
                            <div class="card shadow-sm h-100 mb-0">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-truncate mr-2">
                                            <h4 class="text-danger mb-1">{{ $totalCourses }}</h4>
                                            <p class="text-muted mb-0 small text-truncate">Total Kursus</p>
                                        </div>
                                        <div class="d-none d-sm-block">
                                            <i class="fa fa-book fa-2x text-danger opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Siswa Belum Menyelesaikan -->
                        <div class="col-lg-3 col-md-6 col-6 mb-3">
                            <div class="card shadow-sm h-100 mb-0">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-truncate mr-2">
                                            <h4 class="text-warning mb-1">{{ $totalInProgressStudents }}</h4>
                                            <p class="text-muted mb-0 small text-truncate">Belum Selesai</p>
                                        </div>
                                        <div class="d-none d-sm-block">
                                            <i class="fa fa-hourglass-half fa-2x text-warning opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Siswa Sudah Menyelesaikan -->
                        <div class="col-lg-3 col-md-6 col-6 mb-3">
                            <div class="card shadow-sm h-100 mb-0">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-truncate mr-2">
                                            <h4 class="text-success mb-1">{{ $totalCompletedStudents }}</h4>
                                            <p class="text-muted mb-0 small text-truncate">Sudah Selesai</p>
                                        </div>
                                        <div class="d-none d-sm-block">
                                            <i class="fa fa-graduation-cap fa-2x text-success opacity-50"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Baris Kedua: Tabel Rincian -->
                    <div class="row mt-2">
                        <!-- Tabel Siswa per Kursus -->
                        <div class="col-xl-6 col-md-12 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-white border-bottom">
                                    <h6 class="mb-0 font-weight-bold">Siswa per Kursus</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="border-top-0">Nama Kursus</th>
                                                    <th class="text-center border-top-0">Jumlah Siswa</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($courses as $course)
                                                <tr>
                                                    <td>
                                                        <span class="d-block text-truncate" style="max-width: 200px;">{{ $course->title }}</span>
                                                    </td>
                                                    <td class="text-center"><span class="badge badge-primary badge-pill">{{ $course->students_count }}</span></td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="2" class="text-center text-muted py-4">Belum ada data siswa.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabel Progres Penyelesaian per Kursus -->
                        <div class="col-xl-6 col-md-12 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-header bg-white border-bottom">
                                    <h6 class="mb-0 font-weight-bold">Progres Penyelesaian</h6>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-hover table-borderless mb-0">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th class="border-top-0">Nama Kursus</th>
                                                    <th class="text-center border-top-0">Selesai</th>
                                                    <th class="text-center border-top-0">Belum</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($courses as $course)
                                                <tr>
                                                    <td>
                                                        <span class="d-block text-truncate" style="max-width: 150px;">{{ $course->title }}</span>
                                                    </td>
                                                    <td class="text-center"><span class="text-success font-weight-bold">{{ $course->completed_students_count }}</span></td>
                                                    <td class="text-center"><span class="text-warning font-weight-bold">{{ $course->inprogress_students_count }}</span></td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="3" class="text-center text-muted py-4">Belum ada data progres.</td></tr>
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