@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content rs-page-form">
    <x-dash.page-head :crumbs="[['__home', route(Auth::user()->getRoleNames()->first() . '.dashboard')], ['Kategori Kursus', route(Auth::user()->getRoleNames()->first() . '.course-categories.index')], ['Buat', null]]">
        <x-slot:title>Buat Kategori Baru</x-slot:title>
    </x-dash.page-head>

    <div class="pcoded-inner-content">
        <div class="main-body"><div class="page-wrapper"><div class="page-body"><div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header"><h5>Detail Kategori</h5></div>
                    <div class="card-block">
                        <form action="{{ route(Auth::user()->getRoleNames()->first() . '.course-categories.store') }}" method="POST" class="rs-form">
                            @csrf
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Nama Kategori</label>
                                <div class="col-sm-10">
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>
                            </div>
                                                        <div class="rs-form-actions">
                                <a href="{{ route(Auth::user()->getRoleNames()->first() . '.course-categories.index') }}" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary">Simpan Kategori</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div></div></div>
    </div>
</div>
@endsection