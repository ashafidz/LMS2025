@extends('layouts.app-layout')
@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Edit Pelajaran Word Cloud</h5>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('instructor.courses.modules.index', $lesson->module->course) }}">Modul Saya</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('instructor.modules.lessons.index', $lesson->module) }}">Pelajaran</a></li>
                        <li class="breadcrumb-item">Edit Word Cloud</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="card">
                        <div class="card-header">
                            <h5>Detail Word Cloud</h5>
                        </div>
                        <div class="card-block">
                            <form action="{{ route('instructor.lessons.update', $lesson) }}" method="POST" data-lesson-submit="true">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="lesson_type" value="wordcloud">
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Judul Pelajaran</label>
                                    <div class="col-sm-10">
                                        <input type="text" name="title" class="form-control" value="{{ $lesson->title }}" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Prompt / Pertanyaan Word Cloud</label>
                                    <div class="col-sm-10">
                                        <input type="text" name="wordcloud_question" class="form-control" value="{{ $lesson->lessonable->question }}" required>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Deskripsi (Opsional)</label>
                                    <div class="col-sm-10">
                                        <textarea name="wordcloud_description" class="form-control" rows="4">{{ $lesson->lessonable->description }}</textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Maksimal Kata</label>
                                    <div class="col-sm-10">
                                        <select name="max_words" class="form-control" required>
                                            <option value="1" {{ optional($lesson->lessonable)->max_words == 1 ? 'selected' : '' }}>1 Kata</option>
                                            <option value="2" {{ optional($lesson->lessonable)->max_words == 2 ? 'selected' : '' }}>2 Kata</option>
                                            <option value="3" {{ optional($lesson->lessonable)->max_words == 3 ? 'selected' : '' }}>3 Kata</option>
                                        </select>
                                        <small class="text-muted">Berapa banyak kata maksimal yang dapat dikirimkan oleh siswa (1-3).</small>
                                    </div>
                                </div>

                                <hr>
                                <h5 class="mt-4">Pengaturan Waktu & Status (Opsional)</h5>
                                <div class="form-group row mt-3">
                                    <label class="col-sm-2 col-form-label">Status Aktif</label>
                                    <div class="col-sm-10">
                                        <div class="checkbox-fade fade-in-primary">
                                            <label>
                                                <input type="checkbox" id="isActive" name="is_active" value="1" {{ optional($lesson->lessonable)->is_active ? 'checked' : '' }}>
                                                <span class="cr"><i class="cr-icon fa fa-check txt-primary"></i></span>
                                                <span class="text-inverse">Word Cloud dapat diisi oleh siswa</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Waktu Mulai</label>
                                    <div class="col-sm-10">
                                        <input type="datetime-local" name="start_time" class="form-control" value="{{ $lesson->lessonable->start_time ? Carbon\Carbon::parse($lesson->lessonable->start_time)->format('Y-m-d\TH:i') : '' }}">
                                        <small class="text-muted">Kosongkan jika ingin langsung dimulai tanpa jadwal.</small>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Waktu Selesai</label>
                                    <div class="col-sm-10">
                                        <input type="datetime-local" name="end_time" class="form-control" value="{{ $lesson->lessonable->end_time ? Carbon\Carbon::parse($lesson->lessonable->end_time)->format('Y-m-d\TH:i') : '' }}">
                                        <small class="text-muted">Kosongkan jika Word Cloud tidak memiliki batas waktu.</small>
                                    </div>
                                </div>

                                <div class="form-group row mt-4">
                                    <div class="col-sm-10 offset-sm-2">
                                        <button type="submit" class="btn btn-primary" id="btn-submit-lesson">Simpan Perubahan</button>
                                        <a href="{{ route('instructor.modules.lessons.index', $lesson->module) }}" class="btn btn-secondary">Batal</a>
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
@endsection
