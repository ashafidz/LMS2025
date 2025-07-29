@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Buat Badge Baru</h5>
                        <p class="m-b-0">Isi detail untuk badge pencapaian baru.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.badges.index') }}">Badge</a></li>
                        <li class="breadcrumb-item"><a href="#!">Buat</a></li>
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
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Detail Badge</h5>
                                </div>
                                <div class="card-block">
                                    <form action="{{ route('superadmin.badges.store') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Judul Badge</label>
                                            <div class="col-sm-10">
                                                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="Contoh: Master Kuis">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Deskripsi</label>
                                            <div class="col-sm-10">
                                                <textarea name="description" class="form-control" rows="3" required placeholder="Contoh: Diberikan kepada siswa yang berhasil lulus 10 kuis.">{{ old('description') }}</textarea>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Ikon Badge</label>
                                            <div class="col-sm-10">
                                                <input type="file" name="icon" class="form-control" required accept="image/png, image/svg+xml, image/jpeg">
                                                <small class="form-text text-muted">Unggah file gambar (PNG, JPG, SVG). Direkomendasikan rasio 1:1.</small>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Status</label>
                                            <div class="col-sm-10">
                                                <div class="form-radio">
                                                    <div class="radio radio-inline">
                                                        <label><input type="radio" name="is_active" value="1" checked><i class="helper"></i>Aktif</label>
                                                    </div>
                                                    <div class="radio radio-inline">
                                                        <label><input type="radio" name="is_active" value="0"><i class="helper"></i>Tidak Aktif</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-sm-12 text-right">
                                                <a href="{{ route('superadmin.badges.index') }}" class="btn btn-secondary">Batal</a>
                                                <button type="submit" class="btn btn-primary">Simpan Badge</button>
                                            </div>
                                        </div>
                                    </form>
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