{{-- @extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content rs-page-form">
        <!-- Page-header start -->
        <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], ['Modul', route('instructor.courses.modules.index', $lesson->module->course)], [(Str::limit($lesson->module->title, 20)), route('instructor.modules.lessons.index', $lesson->module)], ['Edit Tugas', null]]">
            <x-slot:context>Kursus: {{ $lesson->module->course->title }}  >  Modul: {{ $lesson->module->title }}</x-slot:context>
            <x-slot:title>Edit Pelajaran</x-slot:title>
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
                                        <h5>Edit Detail Pelajaran Tugas</h5>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.lessons.update', $lesson) }}" method="POST" class="rs-form">
                                            @csrf
                                            @method('PUT')

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Judul Pelajaran</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="title" class="form-control" value="{{ old('title', $lesson->title) }}" required>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Instruksi Tugas</label>
                                                <div class="col-sm-10">
                                                    <textarea rows="10" name="instructions" class="form-control" required>{{ old('instructions', $lesson->lessonable->instructions) }}</textarea>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Batas Waktu (Opsional)</label>
                                                <div class="col-sm-10">
                                          
                                                    <input type="datetime-local" name="due_date" class="form-control" value="{{ old('due_date', $lesson->lessonable->due_date ? $lesson->lessonable->due_date->format('Y-m-d\TH:i') : '') }}">
                                                </div>
                                            </div>

                                                                                        <div class="rs-form-actions">
                                                <a href="{{ route('instructor.modules.lessons.index', $lesson->module) }}" class="btn btn-secondary">Batal</a>
                                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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
        <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], ['Modul', route('instructor.courses.modules.index', $lesson->module->course)], [(Str::limit($lesson->module->title, 20)), route('instructor.modules.lessons.index', $lesson->module)], ['Edit Tugas', null]]">
            <x-slot:title>Edit Pelajaran</x-slot:title>
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
                                        <h5>Edit Detail Pelajaran Tugas</h5>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.lessons.update', $lesson) }}" method="POST" class="rs-form">
                                            @csrf
                                            @include('instructor.lessons.partials._form-errors')
                                            @method('PUT')

                                            <div class="rs-form-section"><h3>Instruksi</h3></div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Judul Pelajaran</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="title" class="form-control" value="{{ old('title', $lesson->title) }}" required>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Instruksi Tugas</label>
                                                <div class="col-sm-10">
                                                    <textarea rows="10" name="instructions" class="form-control" required>{{ old('instructions', $lesson->lessonable->instructions) }}</textarea>
                                                </div>
                                            </div>

                                            <div class="rs-form-section"><h3>Penilaian & Batas Waktu</h3></div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Batas Waktu (Opsional)</label>
                                                <div class="col-sm-10">
                                                    <input type="datetime-local" name="due_date" class="form-control" value="{{ old('due_date', $lesson->lessonable->due_date ? $lesson->lessonable->due_date->format('Y-m-d\TH:i') : '') }}">
                                                </div>
                                            </div>
                                            
                                            {{-- BAGIAN BARU: Input untuk Nilai Kelulusan --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Nilai Kelulusan</label>
                                                <div class="col-sm-10">
                                                    <input type="number" name="pass_mark" class="form-control" value="{{ old('pass_mark', $lesson->lessonable->pass_mark) }}" required min="0" max="100">
                                                    <small class="form-text text-muted">Nilai minimum agar siswa dianggap lulus (0-100).</small>
                                                </div>
                                            </div>

                                            <div class="rs-form-actions">
                                                <a href="{{ route('instructor.modules.lessons.index', $lesson->module) }}" class="btn btn-secondary">Batal</a>
                                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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