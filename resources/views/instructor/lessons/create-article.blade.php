@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content rs-page-form">
        <!-- Page-header start -->
        <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], ['Kursus Saya', route('instructor.courses.index')], [\Illuminate\Support\Str::limit($module->course->title, 30), route('instructor.courses.modules.index', $module->course)], [(Str::limit($module->title, 20)), route('instructor.modules.lessons.index', $module)], ['Buat Artikel', null]]">
            <x-slot:context>Kursus: {{ $module->course->title }}  >  Modul: {{ $module->title }}</x-slot:context>
            <x-slot:title>Buat Pelajaran Baru</x-slot:title>
            <x-slot:subtitle>Tipe: Pelajaran Artikel</x-slot:subtitle>
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
                                        <h5>Detail Pelajaran Artikel</h5>
                                        <span>Isi detail untuk pelajaran baru Anda.</span>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.modules.lessons.store', $module) }}" method="POST" data-lesson-submit="true" class="rs-form rs-form--wide">
                                            @csrf
                                            @include('instructor.lessons.partials._form-errors')
                                            <input type="hidden" name="lesson_type" value="article">

                                            <div class="rs-form-section"><h3>Isi Pelajaran</h3></div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Judul Pelajaran</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="Masukkan judul pelajaran...">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Konten Artikel</label>
                                                <div class="col-sm-10">
                                                    {{-- ID textarea ini dipakai oleh editor (Jodit) dan skrip pemeriksa konten kosong --}}
                                                    <textarea id="article-content" rows="15" name="content" class="form-control">{{ old('content') }}</textarea>
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
@endsection

@push('scripts')
    @include('instructor.lessons.partials._article-editor-scripts')
    <script>
        // Kembalikan tombol simpan bila isi artikel ditolak (lihat partial editor)
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('article-content').closest('form');
            form.addEventListener('article:blocked', function () {
                const button = form.querySelector('.js-lesson-submit-btn');
                if (button) {
                    setTimeout(() => {
                        button.disabled = false;
                        const spinner = button.querySelector('.js-lesson-submit-spinner');
                        const text = button.querySelector('.js-lesson-submit-text');
                        if (text) text.textContent = 'Simpan Pelajaran';
                        if (spinner) spinner.classList.add('d-none');
                    }, 10);
                }
            });
        });
    </script>
@endpush