@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Edit Badge</h5>
                        <p class="m-b-0">Perbarui detail untuk badge: {{ $badge->title }}</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.badges.index') }}">Badge</a></li>
                        <li class="breadcrumb-item"><a href="#!">Edit</a></li>
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
                                    <form action="{{ route('superadmin.badges.update', $badge->id) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Judul Badge</label>
                                            <div class="col-sm-10">
                                                <input type="text" name="title" class="form-control" value="{{ old('title', $badge->title) }}" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Deskripsi</label>
                                            <div class="col-sm-10">
                                                <textarea name="description" class="form-control" rows="3" required>{{ old('description', $badge->description) }}</textarea>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Ikon Saat Ini</label>
                                            <div class="col-sm-10">
                                                <img src="{{ Storage::url($badge->icon_path) }}" alt="{{ $badge->title }}" style="width: 60px; height: 60px; object-fit: cover; background: #f2f2f2; padding: 5px; border-radius: 5px;">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Unggah Ikon Baru (Opsional)</label>
                                            <div class="col-sm-10">
                                                <input type="file" name="icon" class="form-control" accept="image/png, image/svg+xml, image/jpeg">
                                                <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah ikon.</small>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Status</label>
                                            <div class="col-sm-10">
                                                <div class="form-radio">
                                                    <div class="radio radio-inline">
                                                        <label><input type="radio" name="is_active" value="1" {{ old('is_active', $badge->is_active) == 1 ? 'checked' : '' }}><i class="helper"></i>Aktif</label>
                                                    </div>
                                                    <div class="radio radio-inline">
                                                        <label><input type="radio" name="is_active" value="0" {{ old('is_active', $badge->is_active) == 0 ? 'checked' : '' }}><i class="helper"></i>Tidak Aktif</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-sm-12 text-right">
                                                <a href="{{ route('superadmin.badges.index') }}" class="btn btn-secondary">Batal</a>
                                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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