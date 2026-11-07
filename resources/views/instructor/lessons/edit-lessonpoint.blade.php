@extends('layouts.app-layout')
@section('content')
<div class="pcoded-content rs-page-form">
    <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], ['Modul', route('instructor.courses.modules.index', $lesson->module->course)], [(Str::limit($lesson->module->title, 20)), route('instructor.modules.lessons.index', $lesson->module)], ['Edit Sesi Poin', null]]">
        <x-slot:context>Kursus: {{ $lesson->module->course->title }}  >  Modul: {{ $lesson->module->title }}</x-slot:context>
        <x-slot:title>Edit Pelajaran</x-slot:title>
        <x-slot:subtitle>Tipe: Sesi Poin (Lesson Poin)</x-slot:subtitle>
    </x-dash.page-head>
    <div class="pcoded-inner-content">
        <div class="main-body"><div class="page-wrapper"><div class="page-body">
            <div class="row"><div class="col-sm-12">
                <div class="card">
                    <div class="card-header"><h5>Edit Detail Sesi Poin</h5></div>
                    <div class="card-block">
                        <form action="{{ route('instructor.lessons.update', $lesson) }}" method="POST" class="rs-form">
                            @csrf
                            @include('instructor.lessons.partials._form-errors')
                            @method('PUT')
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Judul Pelajaran</label>
                                <div class="col-sm-10">
                                    <input type="text" name="title" class="form-control" value="{{ old('title', $lesson->title) }}" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Judul Sesi</label>
                                <div class="col-sm-10">
                                    <input type="text" name="lessonpoin_title" class="form-control" value="{{ old('lessonpoin_title', $lesson->lessonable->title) }}" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Deskripsi (Opsional)</label>
                                <div class="col-sm-10">
                                    <textarea name="lessonpoin_description" class="form-control" rows="4">{{ old('lessonpoin_description', $lesson->lessonable->description) }}</textarea>
                                </div>
                            </div>
                                                        <div class="rs-form-actions">
                                <a href="{{ route('instructor.modules.lessons.index', $lesson->module) }}" class="btn btn-secondary">Batal</a>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div></div>
        </div></div></div>
    </div>
</div>
@endsection