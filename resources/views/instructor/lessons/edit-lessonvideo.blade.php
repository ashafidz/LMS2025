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
                            <p class="m-b-0">Tipe: Pelajaran Video</p>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex mt-3">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.courses.modules.index', $lesson->module->course) }}">Modul Saya</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.modules.lessons.index', $lesson->module) }}">{{ Str::limit($lesson->module->title, 20) }}</a></li>
                            <li class="breadcrumb-item"><a href="#!">Edit Video</a></li>
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
                                        <h5>Edit Detail Pelajaran Video</h5>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.lessons.update', $lesson->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')

                                            {{-- Pratinjau Video Saat Ini --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Pratinjau Video Saat Ini</label>
                                                <div class="col-sm-10">
                                                    @if($lesson->lessonable->video_path)
                                                        @if($lesson->lessonable->source_type == 'upload')
                                                            {{-- Tampilkan pemutar untuk video yang diunggah --}}
                                                            <video width="100%" style="max-width: 560px;" controls>
                                                                <source src="{{ Storage::url($lesson->lessonable->video_path) }}" type="video/mp4">
                                                                Browser Anda tidak mendukung tag video.
                                                            </video>
                                                        @elseif($lesson->lessonable->source_type == 'youtube')
                                                            {{-- Embed video dari YouTube --}}
                                                            @php
                                                                // Ekstrak ID video dari URL YouTube
                                                                preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $lesson->lessonable->video_path, $match);
                                                                $youtube_id = $match[1] ?? null;
                                                            @endphp
                                                            @if($youtube_id)
                                                                <div class="embed-responsive embed-responsive-16by9" style="max-width: 560px;">
                                                                    <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/{{ $youtube_id }}" allowfullscreen></iframe>
                                                                </div>
                                                            @else
                                                                <p class="text-danger">URL YouTube tidak valid.</p>
                                                            @endif
                                                        @endif
                                                    @else
                                                        <p class="text-muted">Tidak ada video yang terhubung dengan pelajaran ini.</p>
                                                    @endif
                                                </div>
                                            </div>

                                            <hr>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Judul Pelajaran</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="title" class="form-control" value="{{ old('title', $lesson->title) }}" required>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Ubah Sumber Video</label>
                                                <div class="col-sm-10">
                                                    <div class="form-radio">
                                                        <div class="radio radio-inline">
                                                            <label>
                                                                <input type="radio" name="source_type" value="upload" {{ old('source_type', $lesson->lessonable->source_type) == 'upload' ? 'checked' : '' }} onchange="toggleVideoSource(this.value)">
                                                                <i class="helper"></i>Unggah File
                                                            </label>
                                                        </div>
                                                        <div class="radio radio-inline">
                                                            <label>
                                                                <input type="radio" name="source_type" value="youtube" {{ old('source_type', $lesson->lessonable->source_type) == 'youtube' ? 'checked' : '' }} onchange="toggleVideoSource(this.value)">
                                                                <i class="helper"></i>Tautan YouTube
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div id="upload-source" class="form-group row">
                                                <label class="col-sm-2 col-form-label">File Video Baru (Opsional)</label>
                                                <div class="col-sm-10">
                                                    <input type="file" name="video_file" class="form-control" accept="video/mp4,video/x-matroska,video/quicktime">
                                                    <small class="form-text text-muted">Pilih file baru jika ingin mengganti video. Format: MP4, MKV, MOV. Maks: 100MB.</small>
                                                </div>
                                            </div>

                                            <div id="youtube-source" class="form-group row">
                                                <label class="col-sm-2 col-form-label">URL YouTube</label>
                                                <div class="col-sm-10">
                                                    <input type="url" name="video_path" class="form-control" placeholder="Contoh: https://www.youtube.com/watch?v=xxxxxx" value="{{ old('video_path', $lesson->lessonable->source_type == 'youtube' ? $lesson->lessonable->video_path : '') }}">
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

@push('scripts')
<script>
    function toggleVideoSource(source) {
        const uploadDiv = document.getElementById('upload-source');
        const youtubeDiv = document.getElementById('youtube-source');

        if (source === 'upload') {
            uploadDiv.style.display = '';
            youtubeDiv.style.display = 'none';
        } else {
            uploadDiv.style.display = 'none';
            youtubeDiv.style.display = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const initialSource = document.querySelector('input[name="source_type"]:checked').value;
        toggleVideoSource(initialSource);
    });
</script>
@endpush