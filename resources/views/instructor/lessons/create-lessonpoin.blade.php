@extends('layouts.app-layout')
@section('content')
<div class="pcoded-content rs-page-form">
    <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], ['Kursus Saya', route('instructor.courses.index')], [\Illuminate\Support\Str::limit($module->course->title, 30), route('instructor.courses.modules.index', $module->course)], [(Str::limit($module->title, 20)), route('instructor.modules.lessons.index', $module)], ['Buat Pelajaran Poin', null]]">
        <x-slot:context>Kursus: {{ $module->course->title }}  >  Modul: {{ $module->title }}</x-slot:context>
        <x-slot:title>Buat Pelajaran Poin Manual</x-slot:title>
        <x-slot:subtitle>Buat sesi di mana Anda bisa memberikan poin secara manual.</x-slot:subtitle>
    </x-dash.page-head>
    <div class="pcoded-inner-content">
        <div class="main-body"><div class="page-wrapper"><div class="page-body">
            <div class="row"><div class="col-sm-12">
                <div class="card">
                    <div class="card-header"><h5>Detail Sesi Poin</h5></div>
                    <div class="card-block">
                        <form action="{{ route('instructor.modules.lessons.store', $module) }}" method="POST" data-lesson-submit="true" class="rs-form">
                            @csrf
                            @include('instructor.lessons.partials._form-errors')
                            <input type="hidden" name="lesson_type" value="lessonpoin">
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Judul Pelajaran</label>
                                <div class="col-sm-10">
                                    <input type="text" name="title" class="form-control" required placeholder="Contoh: Keaktifan Sesi Zoom 1">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Judul Sesi</label>
                                <div class="col-sm-10">
                                    <input type="text" name="lessonpoin_title" class="form-control" required placeholder="Contoh: Sesi Tanya Jawab Interaktif">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Deskripsi (Opsional)</label>
                                <div class="col-sm-10">
                                    <textarea name="lessonpoin_description" class="form-control" rows="4"></textarea>
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
            </div></div>
        </div></div></div>
    </div>
</div>
@endsection