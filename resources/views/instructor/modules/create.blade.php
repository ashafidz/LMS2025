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
                                Buat Modul Baru
                            </h5>
                            <p class="m-b-0">
                                Tambahkan Modul baru didalam Kursus: {{ $course->title }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex mt-3">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item">
                                <a href="{{ route('instructor.dashboard') }}">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('instructor.courses.index') }}">Kursus Saya</a>
                            </li>
                             <li class="breadcrumb-item">
                                <a href="{{ route('instructor.courses.modules.index', $course->id) }}">Modul Saya</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#!">Buat</a>
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
                                    <div class="card-header">
                                        <h5>Detail Modul Baru</h5>
                                        <span>Isi formulir di bawah ini untuk membuat modul baru.</span>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.courses.modules.store', $course->id) }}" method="POST">
                                            @csrf

                                            {{-- Title --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Judul Modul</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Masukkan Judul Modul" required>
                                                    @error('title')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- BAGIAN BARU: Input untuk Poin yang Dibutuhkan --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Poin yang Dibutuhkan</label>
                                                <div class="col-sm-10">
                                                    <input type="number" name="points_required" class="form-control" value="{{ old('points_required', 0) }}" required min="0">
                                                    <small class="form-text text-muted">Masukkan jumlah poin yang harus dimiliki siswa untuk membuka modul ini. Masukkan <strong>0</strong> agar modul tidak terkunci.</small>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2"></label>
                                                <div class="col-sm-10">
                                                    <a href="{{ route('instructor.courses.modules.index', $course->id) }}" class="btn btn-secondary">Batal</a>
                                                    <button type="submit" class="btn btn-primary">Buat Modul</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Page-body end -->
                </div>
            </div>
        </div>
    </div>
@endsection
