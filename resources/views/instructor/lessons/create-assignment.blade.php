{{-- @extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content rs-page-form">
        <!-- Page-header start -->
        <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], [\Illuminate\Support\Str::limit($module->course->title, 30), route('instructor.courses.modules.index', $module->course)], [(Str::limit($module->title, 20)), route('instructor.modules.lessons.index', $module)], ['Buat Tugas', null]]">
            <x-slot:context>Kursus: {{ $module->course->title }}  >  Modul: {{ $module->title }}</x-slot:context>
            <x-slot:title>Buat Pelajaran Baru</x-slot:title>
            <x-slot:subtitle>Tipe: Tugas (Assignment)</x-slot:subtitle>
        </x-dash.page-head>
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
                                        <form action="{{ route('instructor.modules.lessons.store', $module) }}" method="POST" data-lesson-submit="true" class="rs-form">
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

                                                                                        <div class="rs-form-actions">
                                                <a href="{{ route('instructor.modules.lessons.index', $module) }}" class="btn btn-secondary">Batal</a>
                                                <button type="submit" class="btn btn-primary js-lesson-submit-btn">
                                                <span class="js-lesson-submit-text">Simpan Pelajaran</span>
                                                <span class="spinner-border spinner-border-sm ms-2 d-none js-lesson-submit-spinner" role="status" aria-hidden="true"></span>
                                                </button>
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
    <div class="pcoded-content rs-page-form">
        <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], ['Kursus Saya', route('instructor.courses.index')], [\Illuminate\Support\Str::limit($module->course->title, 30), route('instructor.courses.modules.index', $module->course)], [(Str::limit($module->title, 20)), route('instructor.modules.lessons.index', $module)], ['Buat Tugas', null]]">
            <x-slot:title>Buat Pelajaran Baru</x-slot:title>
            <x-slot:subtitle>Tipe: Tugas (Assignment)</x-slot:subtitle>
        </x-dash.page-head>
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
                                        <form action="{{ route('instructor.modules.lessons.store', $module) }}" method="POST" class="rs-form">
                                            @csrf
                                            @include('instructor.lessons.partials._form-errors')
                                            <input type="hidden" name="lesson_type" value="assignment">

                                            <div class="rs-form-section"><h3>Instruksi</h3></div>
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

                                            <div class="rs-form-section"><h3>Penilaian & Batas Waktu</h3></div>
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

                                            <div class="rs-form-actions">
                                                <a href="{{ route('instructor.modules.lessons.index', $module) }}" class="btn btn-secondary">Batal</a>
                                                <button type="submit" class="btn btn-primary">Simpan Pelajaran</button>
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