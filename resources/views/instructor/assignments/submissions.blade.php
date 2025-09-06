@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Pengumpulan Tugas</h5>
                        <p class="m-b-0">Tugas: <strong>{{ $assignment->lesson->title }}</strong></p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
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
                                                            <th class="text-center" >NIM/NIDN/NIP</th>
                                                            <th>Nama Siswa</th>
                                                            <th>Email</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($notSubmittedStudents as $student)
                                                            <tr>
                                                                <td class="text-center">{{ $student->studentProfile->unique_id_number ? $student->studentProfile->unique_id_number : '-' }}</td>
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
<div class="modal-dialog modal-pdf-viewer" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Pengumpulan: {{ $submission->user->name }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-10">
                            <h6 class="font-weight-bold">Pratinjau File</h6>
                            @if(Str::endsWith($submission->file_path, '.pdf'))
                                {{-- <div class="embed-responsive embed-responsive-4by3" style="border: 1px solid #ddd;">
                                    <embed src="{{ Storage::url($submission->file_path) }}" type="application/pdf" width="100%" height="800px" />
                                </div> --}}
<div class="pdf-viewer-container">
    <iframe src="{{ Storage::url($submission->file_path) }}" 
            width="100%" 
            height="100%" 
            frameborder="0"
            style="display: block;">
        This browser does not support PDFs. Please download the PDF to view it.
    </iframe>
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
                        <div class="col-md-2">
                            <h6 class="font-weight-bold">Form Penilaian</h6>
                            {{-- Tambahkan peringatan keterlambatan di sini --}}
                            @if ($submission->is_late)
                                <div class="alert alert-warning bg-warning text-dark" role="alert">
                                    <i class="fa fa-exclamation-triangle"></i> 
                                    <strong>Perhatian:</strong> Siswa ini mengumpulkan tugas melewati batas waktu.
                                </div>
                            @endif
                            <form action="{{ route('instructor.submission.grade', $submission->id) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="grade-{{ $submission->id }}">Nilai (0-100)</label>
                                    <input type="number" name="grade" id="grade-{{ $submission->id }}" class="form-control" style="width: 30%;" value="{{ old('grade', $submission->grade) }}" min="0" max="100" required>
                                </div>
                                <div class="form-group">
                                    <label for="feedback-{{ $submission->id }}">Umpan Balik (Feedback)</label>
                                    <textarea name="feedback" id="feedback-{{ $submission->id }}" class="form-control" rows="8" style="width: 100%" placeholder="Berikan umpan balik untuk siswa...">{{ old('feedback', $submission->feedback) }}</textarea>
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



<style>
.pdf-viewer-container {
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 100%;
    
    /* Desktop: Large and wide */
    height: 85vh;
    min-height: 600px;
    max-height: 1200px;
}

/* Tablet (medium screens) */
@media (max-width: 768px) {
    .pdf-viewer-container {
        height: 70vh;
        min-height: 500px;
    }
}

/* Mobile (small screens) */
@media (max-width: 576px) {
    .pdf-viewer-container {
        height: 60vh;
        min-height: 400px;
    }
}

.pdf-viewer-container {
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 100%;
    
    /* Desktop: Large and wide */
    height: 85vh;
    min-height: 600px;
    max-height: 1200px;
}

/* Custom modal size for PDF viewing */
.modal-pdf-viewer {
    max-width: 95%;
    margin: 1rem auto;
}

/* Tablet (medium screens) */
@media (max-width: 768px) {
    .pdf-viewer-container {
        height: 70vh;
        min-height: 500px;
    }
    
    .modal-pdf-viewer {
        max-width: 98%;
        margin: 0.5rem auto;
    }
}

/* Mobile (small screens) */
@media (max-width: 576px) {
    .pdf-viewer-container {
        height: 60vh;
        min-height: 400px;
    }
    
    .modal-pdf-viewer {
        max-width: 100%;
        margin: 0;
    }
}

</style>


@endsection

