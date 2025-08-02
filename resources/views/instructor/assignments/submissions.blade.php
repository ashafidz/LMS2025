@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
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
    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Daftar Pengumpulan Siswa</h5>
                                    <span>Berikut adalah daftar semua siswa yang telah mengumpulkan tugas ini.</span>
                                </div>
                                <div class="card-block table-border-style">
                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>NIM/NIDN/NIP</th>
                                                    <th>Nama Siswa</th>
                                                    <th>Waktu Pengumpulan</th>
                                                    <th>Status</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($submissions as $submission)
                                                    <tr>
                                                        <th scope="row">{{ $loop->iteration + $submissions->firstItem() - 1 }}</th>
                                                        <th>{{ $submission->user->studentProfile->unique_id_number }}</th>
                                                        <td>{{ $submission->user->name }}</td>
                                                        <td>{{ $submission->submitted_at->format('d F Y, H:i') }}</td>
                                                        <td>
                                                            {{-- LOGIKA BARU UNTUK LABEL STATUS --}}
                                                            @php
                                                                $statusClasses = [
                                                                    'submitted' => 'label-info',
                                                                    'revision_required' => 'label-danger',
                                                                    'passed' => 'label-success',
                                                                ];
                                                                $statusText = [
                                                                    'submitted' => 'Menunggu Penilaian',
                                                                    'revision_required' => 'Perlu Revisi',
                                                                    'passed' => 'Lulus',
                                                                ];
                                                            @endphp
                                                            <label class="label {{ $statusClasses[$submission->status] ?? 'label-default' }}">
                                                                {{ $statusText[$submission->status] ?? 'Tidak Diketahui' }}
                                                            </label>
                                                            @if(!is_null($submission->grade))
                                                                <span class="badge badge-inverse">{{ $submission->grade }} / 100</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#submissionModal-{{ $submission->id }}">
                                                                Lihat & Nilai
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center">Belum ada siswa yang mengumpulkan tugas.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        {{ $submissions->links() }}
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
    @foreach ($submissions as $submission)
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
                                    {{-- DIPERBARUI: Tinggi diubah menjadi 650px --}}
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
@endsection