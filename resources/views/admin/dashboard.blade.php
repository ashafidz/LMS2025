@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">
                            Dashboard
                        </h5>
                        <p class="m-b-0">
                            Selamat datang di Dashboard Admin {{ Auth::user()->name }}
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
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

                        <!-- Baris Kartu Statistik -->
                        <div class="row gy-4 gx-4">

    <!-- Total Siswa -->
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body py-4 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="text-primary mb-1">250</h3>
                        <p class="text-muted mb-0">Total Instruktur</p>
                    </div>
                    <i class="fa fa-users fa-2x text-primary"></i>
                </div>
            </div>
            <div class="card-footer bg-primary text-white d-flex justify-content-between align-items-center py-2 px-4">
                <small>+5% bulan ini</small>
                <i class="fa fa-line-chart"></i>
            </div>
        </div>
    </div>

    <!-- Total Kursus -->
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body py-4 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="text-danger mb-1">130</h3>
                        <p class="text-muted mb-0">Total Kursus</p>
                    </div>
                    <i class="fa fa-book fa-2x text-danger"></i>
                </div>
            </div>
            <div class="card-footer bg-danger text-white d-flex justify-content-between align-items-center py-2 px-4">
                <small>+10 kursus baru</small>
                <i class="fa fa-line-chart"></i>
            </div>
        </div>
    </div>

    <!-- Siswa Umum Menyelesaikan Kursus -->
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body py-4 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="text-success mb-1">500</h3>
                        <p class="text-muted mb-0">Total Student Keseluruhan Course</p>
                    </div>
                    <i class="fa fa-user-check fa-2x text-success"></i>
                </div>
            </div>
            <div class="card-footer bg-success text-white d-flex justify-content-between align-items-center py-2 px-4">
                <small>+12% dibanding bulan lalu</small>
                <i class="fa fa-line-chart"></i>
            </div>
        </div>
    </div>

    <!-- Siswa Kursus Menyelesaikan Kursus -->
    <div class="col-xl-3 col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body py-4 px-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="text-warning mb-1">350</h3>
                        <p class="text-muted mb-0">------</p>
                    </div>
                    <i class="fa fa-graduation-cap fa-2x text-warning"></i>
                </div>
            </div>
            <div class="card-footer bg-warning text-white d-flex justify-content-between align-items-center py-2 px-4">
                <small>+6% dibanding bulan lalu</small>
                <i class="fa fa-line-chart"></i>
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
                <h5 class="mb-0">Total Instruktur dan Kursus yang dimiliki</h5>
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
                            <tr><td>Doni</td><td>5</td></tr>
                            <tr><td>Rizi</td><td>8</td></tr>
                        </tbody>
                    </table>
                    <div class="text-center mt-3">
                        <a href="#!" class="b-b-primary text-primary">Show More</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kursus Saya Kanan -->
    <div class="col-xl-6 col-md-12">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">----------</h5>
                <input type="text" class="form-control form-control-sm w-50" placeholder="Search..." id="searchCourseRight">
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>-------</th>
                                <th>----------</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>---</td><td>---</td></tr>
                            <tr><td>---</td><td>---</td></tr>
                            <tr><td>---</td><td>---</td></tr>
                        </tbody>
                    </table>
                    <div class="text-center mt-3">
                        <a href="#!" class="b-b-primary text-primary">Show More</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
    <script type="text/javascript" src="{{ asset('pages/dashboard/custom-dashboard.js') }}"></script>
@endpush
