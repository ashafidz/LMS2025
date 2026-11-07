@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content rs-page-form">
        <!-- Page-header start -->
        <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], ['Modul', route('instructor.courses.modules.index', $lesson->module->course)], [(Str::limit($lesson->module->title, 20)), route('instructor.modules.lessons.index', $lesson->module)], ['Edit Dokumen', null]]">
            <x-slot:context>Kursus: {{ $lesson->module->course->title }}  >  Modul: {{ $lesson->module->title }}</x-slot:context>
            <x-slot:title>Edit Pelajaran</x-slot:title>
            <x-slot:subtitle>Tipe: Pelajaran Dokumen (PDF)</x-slot:subtitle>
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
                                        <h5>Edit Detail Pelajaran Dokumen</h5>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.lessons.update', $lesson) }}" method="POST" enctype="multipart/form-data" class="rs-form">
                                            @csrf
                                            @include('instructor.lessons.partials._form-errors')
                                            @method('PUT')

                                            {{-- Bagian Pratinjau PDF --}}
                                            <div class="form-group row">
                                                <label class="col-12 col-form-label">Pratinjau File Saat Ini</label>
                                                <div class="col-12">
                                                    @if($lesson->lessonable->file_path)
                                                        <div class="rs-doc-viewer">
                                                            {{-- Menggunakan tag <embed> untuk menampilkan PDF secara langsung --}}
                                                            <embed src="{{ route('files.lesson.document', $lesson) }}" type="application/pdf" />
                                                        </div>
                                                        <p class="mt-2">
                                                            <i class="fa fa-file-pdf-o"></i>
                                                            <code>{{ basename($lesson->lessonable->file_path) }}</code>
                                                            <a href="{{ route('files.lesson.document', $lesson) }}" target="_blank" rel="noopener" class="ml-2">Buka di tab baru</a>
                                                        </p>
                                                    @else
                                                        <p class="text-muted">Tidak ada file yang diunggah.</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <hr>
                                            {{-- Akhir Bagian Pratinjau --}}

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Judul Pelajaran</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="title" class="form-control" value="{{ old('title', $lesson->title) }}" required>
                                                </div>
                                            </div>

                                            <div class="rs-form-section"><h3>File</h3></div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Unggah File Baru (Opsional)</label>
                                                <div class="col-sm-10">
                                                    <div class="border rounded p-4 text-center" style="border: 2px dashed #cccccc !important; background-color: #f8f9fa;">
                                                        <i class="fa fa-upload text-primary mb-2" style="font-size: 3rem;"></i>
                                                        <h6 class="font-weight-bold">Ganti File PDF</h6>
                                                        <p class="text-muted small mb-3">Pilih file baru jika Anda ingin mengganti dokumen yang saat ini sudah diunggah</p>
                                                        <input type="file" name="document_file" class="form-control-file mx-auto d-block" style="max-width: 250px;" accept=".pdf">
                                                        <div class="mt-3">
                                                            <span class="badge badge-info p-2 mr-1"><i class="fa fa-file-pdf-o"></i> Format PDF</span>
                                                            <span class="badge badge-warning p-2"><i class="fa fa-hdd-o"></i> Maks 20MB</span>
                                                        </div>
                                                    </div>
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