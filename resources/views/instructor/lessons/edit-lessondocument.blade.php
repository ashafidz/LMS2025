@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Edit Pelajaran</h5>
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
                                        <form action="{{ route('instructor.lessons.update', $lesson->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')

                                            {{-- Bagian Pratinjau PDF --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Pratinjau File Saat Ini</label>
                                                <div class="col-sm-10">
                                                    @if($lesson->lessonable->file_path)
                                                        <div class="embed-responsive embed-responsive-4by3" style="max-width: 800px; border: 1px solid #ddd;">
                                                            {{-- Menggunakan tag <embed> untuk menampilkan PDF secara langsung --}}
                                                            <embed src="{{ Storage::url($lesson->lessonable->file_path) }}" type="application/pdf" width="100%" height="500px" />
                                                        </div>
                                                        <p class="mt-2">
                                                            <i class="fa fa-file-pdf-o"></i>
                                                            <code>{{ basename($lesson->lessonable->file_path) }}</code>
                                                            <a href="{{ Storage::url($lesson->lessonable->file_path) }}" target="_blank" class="ml-2">(Unduh)</a>
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

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Unggah File Baru (Opsional)</label>
                                                <div class="col-sm-10">
                                                    <input type="file" name="document_file" class="form-control" accept=".pdf">
                                                    <small class="form-text text-muted">Pilih file baru jika Anda ingin mengganti file saat ini. Format: PDF. Maks: 20MB.</small>
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