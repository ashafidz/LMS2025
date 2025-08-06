@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Buat Pertanyaan Ulasan Baru</h5>
                        <p class="m-b-0">Isi detail untuk pertanyaan skala Likert.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route(Auth::user()->getRoleNames()->first() . '.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route(Auth::user()->getRoleNames()->first() . '.likert-questions.index') }}">Pertanyaan Ulasan</a></li>
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
                                    <h5>Detail Pertanyaan</h5>
                                </div>
                                <div class="card-block">
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <form action="{{ route(Auth::user()->getRoleNames()->first() . '.likert-questions.store') }}" method="POST">
                                        @csrf
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Teks Pertanyaan</label>
                                            <div class="col-sm-10">
                                                <textarea name="question_text" class="form-control" rows="4" required placeholder="Contoh: Materi kursus mudah dipahami.">{{ old('question_text') }}</textarea>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Kategori Pertanyaan</label>
                                            <div class="col-sm-10">
                                                <select name="category" class="form-control" required>
                                                    <option value="">-- Pilih Kategori --</option>
                                                    <option value="course" {{ old('category') == 'course' ? 'selected' : '' }}>Untuk Menilai Kursus</option>
                                                    <option value="instructor" {{ old('category') == 'instructor' ? 'selected' : '' }}>Untuk Menilai Instruktur</option>
                                                    <option value="platform" {{ old('category') == 'platform' ? 'selected' : '' }}>Untuk Menilai Platform</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Status</label>
                                            <div class="col-sm-10">
                                                <div class="form-radio">
                                                    <div class="radio radio-inline">
                                                        <label><input type="radio" name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}><i class="helper"></i>Aktif</label>
                                                    </div>
                                                    <div class="radio radio-inline">
                                                        <label><input type="radio" name="is_active" value="0" {{ old('is_active') == '0' ? 'checked' : '' }}><i class="helper"></i>Tidak Aktif</label>
                                                    </div>
                                                </div>
                                                <small class="form-text text-muted">Hanya pertanyaan yang aktif yang akan ditampilkan kepada siswa.</small>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <div class="col-sm-12 text-right">
                                                <a href="{{ route(Auth::user()->getRoleNames()->first() . '.likert-questions.index') }}" class="btn btn-secondary">Batal</a>
                                                <button type="submit" class="btn btn-primary">Simpan Pertanyaan</button>
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