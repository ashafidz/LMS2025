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
                            Selamat datang di Dashboard Student {{ Auth::user()->name }}
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
    <div class="pcoded-inner-content">
        <!-- Main-body start -->
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                <div class="page-body">
                    <div class="row">
                            <div class="col-sm-12">
                                <div class="card">
                                    <div class="card-block text-center p-5">
                                        <i class="fa fa-book fa-3x text-muted mb-3"></i>
                                        <h4>Selamat Datang Student {{ Auth::user()->name }}</h4>
                                        <p>Mari kita belajar bersama!</p>
                                        <a href="{{ route('courses') }}" class="btn btn-primary mt-2">Lihat Katalog Kursus</a>
                                        <a href="{{ route('student.my_courses') }}" class="btn btn-primary mt-2">
                                            Lihat Kursus Saya
                                        </a>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
                <!-- Page-body end -->
            </div>
            <div id="styleSelector"></div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    <script type="text/javascript" src="{{ asset('pages/dashboard/custom-dashboard.js') }}"></script>
@endpush