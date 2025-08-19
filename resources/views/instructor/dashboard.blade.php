@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">
                            Dashboard
                        </h5>
                        <p class="m-b-0">
                            Selamat datang di Dashboard Instructor {{ Auth::user()->name }}
                        </p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home') }}">
                                <i
                                    class="fa fa-home"
                                ></i>
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="#!">Dashboard</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- Page-header end -->
        <div class="container-fluid mt-5 px-4 pb-5">

                        <div class="row g-3">

    <!-- Total Siswa -->
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body py-3 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="text-primary mb-1">23</h4>
                        <p class="text-muted mb-0 small">Total Siswa</p>
                    </div>
                    <i class="fa fa-users fa-lg text-primary"></i>
                </div>
            </div>
            <div class="card-footer bg-primary text-white d-flex justify-content-between align-items-center py-2 px-3">
                <small class="small">+5 siswa baru bulan ini</small>
                <i class="fa fa-line-chart small"></i>
            </div>
        </div>
    </div>

    <!-- Total Kursus -->
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body py-3 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="text-danger mb-1">5</h4>
                        <p class="text-muted mb-0 small">Total Kursus</p>
                    </div>
                    <i class="fa fa-book fa-lg text-danger"></i>
                </div>
            </div>
            <div class="card-footer bg-danger text-white d-flex justify-content-between align-items-center py-2 px-3">
                <small class="small">+2 kursus baru</small>
                <i class="fa fa-line-chart small"></i>
            </div>
        </div>
    </div>

    <!-- Siswa Belum Menyelesaikan -->
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body py-3 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="text-success mb-1">12</h4>
                        <p class="text-muted mb-0 small">Siswa Belum Menyelesaikan Kursus</p>
                    </div>
                    <i class="fa fa-user-check fa-lg text-success"></i>
                </div>
            </div>
            <div class="card-footer bg-success text-white d-flex justify-content-between align-items-center py-2 px-3">
                <small class="small">+3 siswa belum selesai</small>
                <i class="fa fa-line-chart small"></i>
            </div>
        </div>
    </div>

    <!-- Siswa Sudah Menyelesaikan -->
    <div class="col-lg-3 col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body py-3 px-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-1" style="color: orange;">59</h4>
                        <p class="text-muted mb-0 small">Siswa Sudah Menyelesaikan Kursus</p>
                    </div>
                    <i class="fa fa-graduation-cap fa-2x" style="color: orange;"></i>
                </div>
            </div>
            <div class="card-footer text-white d-flex justify-content-between align-items-center py-2 px-3" style="background-color: orange;">
                <small class="small">+10 siswa selesai</small>
                <i class="fa fa-line-chart small"></i>
            </div>
        </div>
    </div>

</div>



                <!-- Baris Kedua: Kursus Saya ditampilkan 2 kolom -->
<div class="row g-4 mt-4">

    <!-- Kursus Saya Kiri -->
    <div class="col-xl-6 col-md-12">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Student saya tiap kursus</h5>
                <input type="text" class="form-control form-control-sm w-50" placeholder="Search..." id="searchCourseLeft">
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Team Instruktur</th>
                                <th>Total Kursus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Belajar ChatGpt</td><td>40</td></tr>
                            <tr><td>Full Stack</td><td>28</td></tr>
                        </tbody>
                    </table>
                    <!-- <div class="text-center mt-3">
                        <a href="#!" class="b-b-primary text-primary">Show More</a>
                    </div> -->
                </div>
            </div>
        </div>
    </div>

    <!-- Kursus Saya Kanan -->
    <div class="col-xl-6 col-md-12">
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Penyelesaian tugas kursus</h5>
            <input type="text" class="form-control form-control-sm w-50" placeholder="Search..." id="searchCourseRight">
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Kursus</th>
                            <th>Yang sudah menyelesaikan</th>
                            <th>Yang belum menyelesaikan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>HTML Dasar</td><td>5</td><td>6</td></tr>
                        <tr><td>CSS Lanjutan</td><td>7</td><td>3</td></tr>
                        <tr><td>JavaScript</td><td>2</td><td>7</td></tr>
                    </tbody>
                </table>
                <!-- <div class="text-center mt-3">
                    <a href="#!" class="b-b-primary text-primary">Show More</a>
                </div> -->
            </div>
        </div>
    </div>
</div>



@endsection

@push('scripts')
    <script type="text/javascript" src="{{ asset('pages/dashboard/custom-dashboard.js') }}"></script>
@endpush
