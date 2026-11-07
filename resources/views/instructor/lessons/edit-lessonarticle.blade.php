@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content rs-page-form">
        <!-- Page-header start -->
        <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], [\Illuminate\Support\Str::limit($lesson->module->course->title, 30), route('instructor.courses.modules.index', $lesson->module->course)], [(Str::limit($lesson->module->title, 20)), route('instructor.modules.lessons.index', $lesson->module)], ['Edit Artikel', null]]">
            <x-slot:context>Kursus: {{ $lesson->module->course->title }}  >  Modul: {{ $lesson->module->title }}</x-slot:context>
            <x-slot:title>Edit Pelajaran</x-slot:title>
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
                                        <h5>Edit Detail Pelajaran Artikel</h5>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.lessons.update', $lesson) }}" method="POST" class="rs-form rs-form--wide">
                                            @csrf
                                            @include('instructor.lessons.partials._form-errors')
                                            @method('PUT')

                                            <div class="rs-form-section"><h3>Isi Pelajaran</h3></div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Judul Pelajaran</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="title" class="form-control" value="{{ old('title', $lesson->title) }}" required>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Konten Artikel</label>
                                                <div class="col-sm-10">
                                                    {{-- Beri ID pada textarea ini --}}
                                                    <textarea id="article-content" rows="15" name="content" class="form-control">{{ old('content', $lesson->lessonable->content) }}</textarea>
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

@push('scripts')
    @include('instructor.lessons.partials._article-editor-scripts')
    <script>
        // Kembalikan tombol simpan bila isi artikel ditolak (lihat partial editor)
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('article-content').closest('form');
            form.addEventListener('article:blocked', function () {
                const button = form.querySelector('button[type="submit"]');
                if (button) {
                    setTimeout(() => {
                        button.disabled = false;
                        // Jika ada loading state, kembalikan teksnya
                        if (button.textContent === 'Menyimpan...') {
                            button.textContent = 'Simpan Perubahan';
                        }
                    }, 10);
                }
            });
        });
    </script>
@endpush