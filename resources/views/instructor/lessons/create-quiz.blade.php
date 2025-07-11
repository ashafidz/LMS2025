@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Buat Pelajaran Baru</h5>
                            <p class="m-b-0">Tipe: Kuis</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.courses.modules.index', $module->course) }}">Modul</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.modules.lessons.index', $module) }}">{{ Str::limit($module->title, 20) }}</a></li>
                            <li class="breadcrumb-item"><a href="#!">Buat Kuis</a></li>
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
                                        <h5>Detail Pelajaran Kuis</h5>
                                        <span>Isi detail untuk pelajaran baru Anda. Setelah kuis dibuat, Anda dapat menambahkan soal.</span>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.modules.lessons.store', $module) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="lesson_type" value="quiz">

                                            <h6 class="font-weight-bold">Informasi Pelajaran Umum</h6>
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Judul Pelajaran</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="Contoh: Kuis 1 - Dasar-dasar PHP">
                                                    <small class="form-text text-muted">Ini adalah judul yang akan muncul di daftar pelajaran.</small>
                                                </div>
                                            </div>

                                            <hr>

                                            <h6 class="font-weight-bold mt-4">Informasi Spesifik Kuis</h6>
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Judul Kuis</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="quiz_title" class="form-control" value="{{ old('quiz_title') }}" required placeholder="Contoh: Ujian Pemahaman Dasar PHP">
                                                    <small class="form-text text-muted">Ini adalah judul yang akan dilihat siswa saat mengerjakan kuis.</small>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Deskripsi Kuis (Opsional)</label>
                                                <div class="col-sm-10">
                                                    <textarea rows="3" name="quiz_description" class="form-control" placeholder="Jelaskan instruksi atau topik yang dicakup dalam kuis ini...">{{ old('quiz_description') }}</textarea>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Nilai Kelulusan (%)</label>
                                                <div class="col-sm-10">
                                                    <input type="number" name="pass_mark" class="form-control" value="{{ old('pass_mark', 75) }}" required min="0" max="100">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Batas Waktu (Menit)</label>
                                                <div class="col-sm-10">
                                                    <input type="number" name="time_limit" class="form-control" value="{{ old('time_limit') }}" min="1" placeholder="Kosongkan jika tidak ada batas waktu">
                                                </div>
                                            </div>

                                            <div class="form-group row mt-4">
                                                <div class="col-sm-12 text-right">
                                                    <a href="{{ route('instructor.modules.lessons.index', $module) }}" class="btn btn-secondary">Batal</a>
                                                    <button type="submit" class="btn btn-primary">Simpan & Lanjutkan</button>
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