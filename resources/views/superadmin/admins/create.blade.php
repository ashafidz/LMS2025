@extends('layouts.app-layout')
@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8"><div class="page-header-title"><h5 class="m-b-10">Tambah Admin Baru</h5></div></div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.admins.index') }}">Kelola Admin</a></li>
                        <li class="breadcrumb-item"><a href="#!">Tambah</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="pcoded-inner-content"><div class="main-body"><div class="page-wrapper"><div class="page-body"><div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header"><h5>Detail Akun Admin</h5></div>
                <div class="card-block">
                    <form action="{{ route('superadmin.admins.store') }}" method="POST">
                        @csrf
                        <div class="form-group row"><label class="col-sm-2 col-form-label">Nama Lengkap</label><div class="col-sm-10"><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div></div>
                        <div class="form-group row"><label class="col-sm-2 col-form-label">Email</label><div class="col-sm-10"><input type="email" name="email" class="form-control" value="{{ old('email') }}" required></div></div>
                        <div class="form-group row"><label class="col-sm-2 col-form-label">Password</label><div class="col-sm-10"><input type="password" name="password" class="form-control" required></div></div>
                        <div class="form-group row"><label class="col-sm-2 col-form-label">Konfirmasi Password</label><div class="col-sm-10"><input type="password" name="password_confirmation" class="form-control" required></div></div>
                        <div class="form-group row"><div class="col-sm-12 text-right"><a href="{{ route('superadmin.admins.index') }}" class="btn btn-secondary">Batal</a><button type="submit" class="btn btn-primary">Simpan Admin</button></div></div>
                    </form>
                </div>
            </div>
        </div>
    </div></div></div></div>
</div>
@endsection