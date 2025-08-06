@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Buat Pelajaran Baru</h5>
                            <p class="m-b-0">Tipe: Pelajaran Video</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.courses.modules.index', $module->course) }}">Modul</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.modules.lessons.index', $module) }}">{{ Str::limit($module->title, 20) }}</a></li>
                            <li class="breadcrumb-item"><a href="#!">Buat Video</a></li>
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
                                        <h5>Detail Pelajaran Video</h5>
                                        <span>Isi detail untuk pelajaran baru Anda.</span>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.modules.lessons.store', $module) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="lesson_type" value="video">

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Judul Pelajaran</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required placeholder="Masukkan judul pelajaran...">
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Sumber Video</label>
                                                <div class="col-sm-10">
                                                    <div class="form-radio">
                                                        <div class="radio radio-inline">
                                                            <label>
                                                                <input type="radio" name="source_type" value="upload" checked="checked" onchange="toggleVideoSource(this.value)">
                                                                <i class="helper"></i>Unggah File
                                                            </label>
                                                        </div>
                                                        <div class="radio radio-inline">
                                                            <label>
                                                                <input type="radio" name="source_type" value="youtube" onchange="toggleVideoSource(this.value)">
                                                                <i class="helper"></i>Tautan YouTube
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Input untuk Unggah File (ditampilkan secara default) --}}
                                            <div id="upload-source" class="form-group row">
                                                <label class="col-sm-2 col-form-label">File Video</label>
                                                <div class="col-sm-10">
                                                    <input type="file" name="video_file" class="form-control" accept="video/mp4,video/x-matroska,video/quicktime">
                                                    <small class="form-text text-muted">Format: MP4, MKV, MOV. Maks: 100MB.</small>
                                                </div>
                                            </div>

                                            {{-- Input untuk Tautan YouTube (disembunyikan secara default) --}}
                                            <div id="youtube-source" class="form-group row" style="display: none;">
                                                <label class="col-sm-2 col-form-label">URL YouTube</label>
                                                <div class="col-sm-10">
                                                    <input type="url" name="video_path" class="form-control" placeholder="Contoh: https://www.youtube.com/watch?v=xxxxxx">
                                                </div>
                                            </div>


                                            <div class="form-group row mt-4">
                                                <div class="col-sm-12 text-right">
                                                    <a href="{{ route('instructor.modules.lessons.index', $module) }}" class="btn btn-secondary">Batal</a>
                                                    <button type="submit" class="btn btn-primary">Simpan Pelajaran</button>
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

@push('scripts')
<script>
    function toggleVideoSource(source) {
        const uploadDiv = document.getElementById('upload-source');
        const youtubeDiv = document.getElementById('youtube-source');
        const fileInput = uploadDiv.querySelector('input');
        const urlInput = youtubeDiv.querySelector('input');

        if (source === 'upload') {
            uploadDiv.style.display = '';
            youtubeDiv.style.display = 'none';
            fileInput.required = true;
            urlInput.required = false;
        } else {
            uploadDiv.style.display = 'none';
            youtubeDiv.style.display = '';
            fileInput.required = false;
            urlInput.required = true;
        }
    }

    // Panggil fungsi saat halaman dimuat untuk memastikan keadaan awal benar
    document.addEventListener('DOMContentLoaded', function() {
        const initialSource = document.querySelector('input[name="source_type"]:checked').value;
        toggleVideoSource(initialSource);
    });
</script>
@endpush