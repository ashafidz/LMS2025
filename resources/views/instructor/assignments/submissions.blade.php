@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Pengumpulan Tugas</h5>
                        <p class="m-b-0">Tugas: <strong>{{ $assignment->lesson->title }}</strong></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('instructor.modules.lessons.index', $assignment->lesson->module) }}">Daftar Pelajaran</a></li>
                        <li class="breadcrumb-item"><a href="#!">Pengumpulan</a></li>
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
                                    <h5>Daftar Pengumpulan Siswa</h5>
                                    <span>Berikut adalah daftar semua siswa baik yang telah mengumpulkan tugas ini atau belum.</span>
                                </div>
                                <div class="card-block">
                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif
                                    
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs md-tabs" role="tablist">
                                        <li class="nav-item">
                                            <a class="nav-link active" data-toggle="tab" href="#submitted" role="tab">Menunggu Dinilai <span class="badge badge-info">{{ $submittedSubmissions->count() }}</span></a>
                                            <div class="slide"></div>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#revision" role="tab">Perlu Revisi <span class="badge badge-danger">{{ $revisionSubmissions->count() }}</span></a>
                                            <div class="slide"></div>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#passed" role="tab">Lulus <span class="badge badge-success">{{ $passedSubmissions->count() }}</span></a>
                                            <div class="slide"></div>
                                        </li>
                                        <li class="nav-item">
                                            <a class="nav-link" data-toggle="tab" href="#not-submitted" role="tab">Belum Mengumpulkan <span class="badge badge-default">{{ $notSubmittedStudents->count() }}</span></a>
                                            <div class="slide"></div>
                                        </li>
                                    </ul>
                                    <!-- Tab panes -->
                                    <div class="tab-content card-block">
                                        {{-- TAB: Menunggu Dinilai --}}
                                        <div class="tab-pane active" id="submitted" role="tabpanel">
                                            @include('instructor.assignments.partials._submission_table', ['submissions' => $submittedSubmissions])
                                        </div>
                                        {{-- TAB: Perlu Revisi --}}
                                        <div class="tab-pane" id="revision" role="tabpanel">
                                            @include('instructor.assignments.partials._submission_table', ['submissions' => $revisionSubmissions])
                                        </div>
                                        {{-- TAB: Lulus --}}
                                        <div class="tab-pane" id="passed" role="tabpanel">
                                            @include('instructor.assignments.partials._submission_table', ['submissions' => $passedSubmissions])
                                        </div>
                                        {{-- TAB: Belum Mengumpulkan --}}
                                        <div class="tab-pane" id="not-submitted" role="tabpanel">
                                            <div class="table-responsive">
                                                <table class="table table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Nama Siswa</th>
                                                            <th>Email</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($notSubmittedStudents as $student)
                                                            <tr>
                                                                <td>{{ $student->name }}</td>
                                                                <td>{{ $student->email }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="2" class="text-center">Semua siswa sudah mengumpulkan tugas.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk setiap pengumpulan -->
    @foreach ($submittedSubmissions->concat($revisionSubmissions)->concat($passedSubmissions) as $submission)
    <div class="modal fade" id="submissionModal-{{ $submission->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Pengumpulan: {{ $submission->user->name }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-7">
                            <h6 class="font-weight-bold">Pratinjau File</h6>
                            @if(Str::endsWith($submission->file_path, '.pdf'))
                                <div class="embed-responsive embed-responsive-4by3" style="border: 1px solid #ddd;">
                                    <embed src="{{ Storage::url($submission->file_path) }}" type="application/pdf" width="100%" height="650px" />
                                </div>
                            @else
                                <div class="text-center p-5 bg-light">
                                    <i class="fa fa-file-zip-o fa-3x"></i>
                                    <p class="mt-2">Pratinjau tidak tersedia untuk file ZIP.</p>
                                </div>
                            @endif
                            <a href="{{ Storage::url($submission->file_path) }}" class="btn btn-secondary btn-block mt-3" download>
                                <i class="fa fa-download"></i> Unduh File Tugas
                            </a>
                        </div>
                        <div class="col-md-5">
                            <h6 class="font-weight-bold">Form Penilaian</h6>
                            <form action="{{ route('instructor.submission.grade', $submission->id) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="grade-{{ $submission->id }}">Nilai (0-100)</label>
                                    <input type="number" name="grade" id="grade-{{ $submission->id }}" class="form-control" value="{{ old('grade', $submission->grade) }}" min="0" max="100" required>
                                </div>
                                <div class="form-group">
                                    <label for="feedback-{{ $submission->id }}">Umpan Balik (Feedback)</label>
                                    <textarea name="feedback" id="feedback-{{ $submission->id }}" class="form-control" rows="8" placeholder="Berikan umpan balik untuk siswa...">{{ old('feedback', $submission->feedback) }}</textarea>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Simpan Penilaian</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection