@extends('layouts.app-layout')
@section('content')
<div class="pcoded-content rs-page-form">
    <x-dash.page-head :crumbs="[['__home', route('superadmin.dashboard')], ['Kelola Admin', route('superadmin.admins.index')], ['Tambah', null]]">
        <x-slot:title>Tambah Admin Baru</x-slot:title>
    </x-dash.page-head>
    <div class="pcoded-inner-content"><div class="main-body"><div class="page-wrapper"><div class="page-body"><div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header"><h5>Detail Akun Admin</h5></div>
                <div class="card-block">
                    <form action="{{ route('superadmin.admins.store') }}" method="POST" class="rs-form">
                        @csrf
                        <div class="form-group row"><label class="col-sm-2 col-form-label">Nama Lengkap</label><div class="col-sm-10"><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div></div>
                        <div class="form-group row"><label class="col-sm-2 col-form-label">Email</label><div class="col-sm-10"><input type="email" name="email" class="form-control" value="{{ old('email') }}" required></div></div>
                        <div class="form-group row"><label class="col-sm-2 col-form-label">Password</label><div class="col-sm-10"><input type="password" name="password" class="form-control" required></div></div>
                        <div class="form-group row"><label class="col-sm-2 col-form-label">Konfirmasi Password</label><div class="col-sm-10"><input type="password" name="password_confirmation" class="form-control" required></div></div>
                                                <div class="rs-form-actions">
                            <a href="{{ route('superadmin.admins.index') }}" class="btn btn-secondary">Batal</a><button type="submit" class="btn btn-primary">Simpan Admin</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div></div></div></div>
</div>
@endsection