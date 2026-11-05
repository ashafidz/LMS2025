@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Course: {{ $lesson->module->course->title }} | Modul: {{ $lesson->module->title }}</h5>
                            <p class="m-b-10 fw-bolder" style="font-size: 2rem;">Edit Pelajaran</p>
                            <p class="m-b-0">Tipe: Pelajaran Dokumen (PDF)</p>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex mt-3">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.courses.modules.index', $lesson->module->course) }}">Modul</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.modules.lessons.index', $lesson->module) }}">{{ Str::limit($lesson->module->title, 20) }}</a></li>
                            <li class="breadcrumb-item"><a href="#!">Edit Dokumen</a></li>
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
                                        <h5>Edit Detail Pelajaran Dokumen</h5>
                                    </div>
                                    <div class="card-block">
                                        @if ($errors->any())
                                            <div class="alert alert-danger">
                                                <ul class="mb-0">
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif

                                        <form action="{{ route('instructor.lessons.update', $lesson) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')

                                            {{-- Bagian Pratinjau PDF --}}
                                            <div class="form-group row">
                                                <label class="col-12 col-form-label">Pratinjau File Saat Ini</label>
                                                <div class="col-12">
                                                    @if($lesson->lessonable->file_path)
                                                        @php
                                                            $docUrl = route('files.lesson.document', [
                                                                'lesson' => $lesson,
                                                                'v' => substr(md5((string) $lesson->lessonable->file_path), 0, 8),
                                                            ]);
                                                        @endphp
                                                        <div class="rs-doc-viewer">
                                                            {{-- Menggunakan tag <embed> untuk menampilkan PDF secara langsung --}}
                                                            <embed src="{{ $docUrl }}" type="application/pdf" />
                                                        </div>
                                                        <p class="mt-2">
                                                            <i class="fa fa-file-pdf-o"></i>
                                                            <code>{{ basename($lesson->lessonable->file_path) }}</code>
                                                            <a href="{{ $docUrl }}" target="_blank" rel="noopener" class="ml-2">Buka di tab baru</a>
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
                                                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $lesson->title) }}" required>
                                                    @error('title')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Unggah File Baru (Opsional)</label>
                                                <div class="col-sm-10">
                                                    <div class="border rounded p-4 text-center" style="border: 2px dashed #cccccc !important; background-color: #f8f9fa;">
                                                        <i class="fa fa-upload text-primary mb-2" style="font-size: 3rem;"></i>
                                                        <h6 class="font-weight-bold">Ganti File PDF</h6>
                                                        <p class="text-muted small mb-3">Pilih file baru jika Anda ingin mengganti dokumen yang saat ini sudah diunggah</p>
                                                        <input type="file" name="document_file" class="form-control-file mx-auto d-block @error('document_file') is-invalid @enderror" style="max-width: 250px;" accept=".pdf">
                                                        @error('document_file')
                                                            <div class="text-danger small mt-2">{{ $message }}</div>
                                                        @enderror
                                                        <div class="mt-3">
                                                            <span class="badge badge-info p-2 mr-1"><i class="fa fa-file-pdf-o"></i> Format PDF</span>
                                                            <span class="badge badge-warning p-2"><i class="fa fa-hdd-o"></i> Maks 20MB</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row mt-4">
                                                <div class="col-sm-12 text-right">
                                                    <a href="{{ route('instructor.modules.lessons.index', $lesson->module) }}" class="btn btn-secondary">Batal</a>
                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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