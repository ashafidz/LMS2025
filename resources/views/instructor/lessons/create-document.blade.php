@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Buat Pelajaran Baru</h5>
                            <p class="m-b-0">Tipe: Pelajaran Dokumen (PDF)</p>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex mt-3">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.courses.index') }}">Kursus Saya</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.courses.modules.index', $module->course) }}">Modul Saya</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.modules.lessons.index', $module) }}">{{ Str::limit($module->title, 20) }}</a></li>
                            <li class="breadcrumb-item"><a href="#!">Buat Dokumen</a></li>
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
                                        <h5>Detail Pelajaran Dokumen</h5>
                                        <span>Isi detail untuk pelajaran baru Anda.</span>
                                    </div>
                                    <div class="card-block">
                                        {{-- Penting: tambahkan enctype untuk unggahan file --}}
                                        <form action="{{ route('instructor.modules.lessons.store', $module) }}" method="POST" enctype="multipart/form-data" data-lesson-submit="true">
                                            @csrf
                                            <input type="hidden" name="lesson_type" value="document">

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Judul Pelajaran</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="Masukkan judul pelajaran...">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">File Dokumen (PDF)</label>
                                                <div class="col-sm-10">
                                                    <div class="border rounded p-4 text-center" style="border: 2px dashed #cccccc !important; background-color: #f8f9fa;">
                                                        <i class="fa fa-file-pdf-o text-danger mb-2" style="font-size: 3.5rem;"></i>
                                                        <h6 class="font-weight-bold">Unggah File PDF</h6>
                                                        <p class="text-muted small mb-3">Pilih dokumen materi pelajaran yang akan diunggah</p>
                                                        <input type="file" name="document_file" class="form-control-file mx-auto d-block" style="max-width: 250px;" required accept=".pdf">
                                                        <div class="mt-3">
                                                            <span class="badge badge-info p-2 mr-1"><i class="fa fa-file-pdf-o"></i> Format PDF</span>
                                                            <span class="badge badge-warning p-2"><i class="fa fa-hdd-o"></i> Maks 20MB</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row mt-4">
                                                <div class="col-sm-12 text-right">
                                                    <a href="{{ route('instructor.modules.lessons.index', $module) }}" class="btn btn-secondary">Batal</a>
                                                    <button type="submit" class="btn btn-primary js-lesson-submit-btn">
                                                        <span class="js-lesson-submit-text">Simpan Pelajaran</span>
                                                        <span class="spinner-border spinner-border-sm ms-2 d-none js-lesson-submit-spinner" role="status" aria-hidden="true"></span>
                                                    </button>
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