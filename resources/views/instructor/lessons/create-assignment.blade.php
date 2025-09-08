{{-- @extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Buat Pelajaran Baru</h5>
                            <p class="m-b-0">Tipe: Tugas (Assignment)</p>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex mt-3">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.courses.modules.index', $module->course) }}">Modul Saya</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.modules.lessons.index', $module) }}">{{ Str::limit($module->title, 20) }}</a></li>
                            <li class="breadcrumb-item"><a href="#!">Buat Tugas</a></li>
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
                                        <h5>Detail Pelajaran Tugas</h5>
                                        <span>Isi detail untuk tugas baru Anda.</span>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.modules.lessons.store', $module) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="lesson_type" value="assignment">

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Judul Pelajaran</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="Contoh: Tugas 1 - Membuat Landing Page">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Instruksi Tugas</label>
                                                <div class="col-sm-10">
                                                    <textarea rows="10" name="instructions" class="form-control" required placeholder="Jelaskan secara detail apa yang harus dilakukan siswa, kriteria penilaian, dan cara pengumpulan.">{{ old('instructions') }}</textarea>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Batas Waktu (Opsional)</label>
                                                <div class="col-sm-10">
                                                    <input type="datetime-local" name="due_date" class="form-control" value="{{ old('due_date') }}">
                                                    <small class="form-text text-muted">Kosongkan jika tidak ada batas waktu pengumpulan.</small>
                                                </div>
                                            </div>

                                            <div class="form-group row mt-4">
                                                <div class="col-sm-12 text-right">
                                                    <a href="{{ route('instructor.modules.lessons.index', $module) }}" class="btn btn-secondary">Batal</a>
                                                    <button type="submit" class="btn btn-primary">Simpan Pelajaran</button>
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
@endsection --}}


@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Buat Pelajaran Baru</h5>
                            <p class="m-b-0">Tipe: Tugas (Assignment)</p>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex mt-3">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.courses.index') }}">Kursus Saya</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.courses.modules.index', $module->course) }}">Modul Saya</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.modules.lessons.index', $module) }}">{{ Str::limit($module->title, 20) }}</a></li>
                            <li class="breadcrumb-item"><a href="#!">Buat Tugas</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <div class="page-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Detail Pelajaran Tugas</h5>
                                        <span>Isi detail untuk tugas baru Anda.</span>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.modules.lessons.store', $module) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="lesson_type" value="assignment">

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Judul Pelajaran</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="Contoh: Tugas 1 - Membuat Landing Page">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Instruksi Tugas</label>
                                                <div class="col-sm-10">
                                                    <textarea rows="10" name="instructions" class="form-control" required placeholder="Jelaskan secara detail apa yang harus dilakukan siswa...">{{ old('instructions') }}</textarea>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Batas Waktu (Opsional)</label>
                                                <div class="col-sm-10">
                                                    <input type="datetime-local" name="due_date" class="form-control" value="{{ old('due_date') }}">
                                                </div>
                                            </div>

                                            {{-- BAGIAN BARU: Input untuk Nilai Kelulusan --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Nilai Kelulusan</label>
                                                <div class="col-sm-10">
                                                    <input type="number" name="pass_mark" class="form-control" value="{{ old('pass_mark', 75) }}" required min="0" max="100">
                                                    <small class="form-text text-muted">Nilai minimum agar siswa dianggap lulus (0-100).</small>
                                                </div>
                                            </div>

                                            <div class="form-group row mt-4">
                                                <div class="col-sm-12 text-right">
                                                    <a href="{{ route('instructor.modules.lessons.index', $module) }}" class="btn btn-secondary">Batal</a>
                                                    <button type="submit" class="btn btn-primary">Simpan Pelajaran</button>
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