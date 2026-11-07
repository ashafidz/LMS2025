@extends('layouts.app-layout')
@section('content')
<div class="pcoded-content rs-page-form">
    <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], [\Illuminate\Support\Str::limit($module->course->title, 30), route('instructor.courses.modules.index', $module->course)], ['Pelajaran', route('instructor.modules.lessons.index', $module)], ['Tambah Word Cloud', null]]">
        <x-slot:context>Kursus: {{ $module->course->title }}  >  Modul: {{ $module->title }}</x-slot:context>
        <x-slot:title>Tambah Pelajaran Word Cloud</x-slot:title>
    </x-dash.page-head>
    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="card">
                        <div class="card-header">
                            <h5>Detail Word Cloud</h5>
                        </div>
                        <div class="card-block">
                            <form action="{{ route('instructor.modules.lessons.store', $module) }}" method="POST" data-lesson-submit="true" class="rs-form">
                                @csrf
                                @include('instructor.lessons.partials._form-errors')
                                <input type="hidden" name="lesson_type" value="wordcloud">
                                <div class="rs-form-section"><h3>Pertanyaan</h3></div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Judul Pelajaran</label>
                                    <div class="col-sm-10">
                                        <input type="text" name="title" class="form-control" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Prompt / Pertanyaan Word Cloud</label>
                                    <div class="col-sm-10">
                                        <input type="text" name="wordcloud_question" class="form-control" required placeholder="Contoh: Sebutkan 1-2 kata tentang...">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Deskripsi (Opsional)</label>
                                    <div class="col-sm-10">
                                        <textarea name="wordcloud_description" class="form-control" rows="4"></textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Maksimal Kata</label>
                                    <div class="col-sm-10">
                                        <select name="max_words" class="form-control" required>
                                            <option value="1">1 Kata</option>
                                            <option value="2">2 Kata</option>
                                            <option value="3">3 Kata</option>
                                        </select>
                                        <small class="text-muted">Berapa banyak kata maksimal yang dapat dikirimkan oleh siswa (1-3).</small>
                                    </div>
                                </div>

                                <div class="rs-form-section"><h3>Pengaturan</h3></div>
                                <div class="form-group row mt-3">
                                    <label class="col-sm-2 col-form-label">Status Aktif</label>
                                    <div class="col-sm-10">
                                        <div class="checkbox-fade fade-in-primary">
                                            <label>
                                                <input type="checkbox" id="isActive" name="is_active" value="1" checked>
                                                <span class="cr"><i class="cr-icon fa fa-check txt-primary"></i></span>
                                                <span class="text-inverse">Word Cloud dapat diisi oleh siswa</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="rs-grid-2">
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Waktu Mulai</label>
                                    <div class="col-sm-10">
                                        <input type="datetime-local" name="start_time" class="form-control">
                                        <small class="text-muted">Kosongkan jika ingin langsung dimulai tanpa jadwal.</small>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Waktu Selesai</label>
                                    <div class="col-sm-10">
                                        <input type="datetime-local" name="end_time" class="form-control">
                                        <small class="text-muted">Kosongkan jika Word Cloud tidak memiliki batas waktu (selamanya terbuka selama status aktif).</small>
                                    </div>
                                </div>
                                </div>

                                                                <div class="rs-form-actions">
                                    <button type="submit" class="btn btn-primary" id="btn-submit-lesson">Simpan Pelajaran Word Cloud</button>
                                    <a href="{{ route('instructor.modules.lessons.index', $module) }}" class="btn btn-secondary">Batal</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
