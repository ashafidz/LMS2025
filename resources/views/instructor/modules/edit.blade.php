@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">Edit Modul</h5>
                            <p class="m-b-0">Perbarui Detail Modul Anda.</p>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex mt-3">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.courses.index') }}">Kursus Saya</a></li>
                             <li class="breadcrumb-item"><a href="{{ route('instructor.courses.modules.index', $module->course->id) }}">Modul Saya</a></li>
                            <li class="breadcrumb-item"><a href="#!">Edit</a></li>
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
                                        <h5>Edit Detail Modul</h5>
                                        <span>Perbarui Judul Modul Anda.</span>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.modules.update', $module->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')

                                            {{-- Title --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Judul Modul</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $module->title) }}" required>
                                                    @error('title')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- BAGIAN BARU: Input untuk Poin yang Dibutuhkan --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Poin yang Dibutuhkan</label>
                                                <div class="col-sm-10">
                                                    <input type="number" name="points_required" class="form-control" value="{{ old('points_required', $module->points_required) }}" required min="0">
                                                    <small class="form-text text-muted">Masukkan jumlah poin yang harus dimiliki siswa untuk membuka modul ini. Masukkan <strong>0</strong> agar modul tidak terkunci.</small>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2"></label>
                                                <div class="col-sm-10">
                                                    <a href="{{ route('instructor.courses.modules.index', $module->course->id) }}" class="btn btn-secondary">Batal</a>
                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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
