@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Course Quiz Violation Overview - {{ $course->title }}</h5>
                        <p class="m-b-0">Rekap pelanggaran untuk semua kuis dalam course ini</p>
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('instructor.courses.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Kembali ke Manajemen Course
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">

                    {{-- Statistics Cards --}}
                    <div class="row">
                        <div class="col-md-2">
                            <div class="card">
                                <div class="card-block text-center">
                                    <h3 class="text-primary">{{ $stats['total_quizzes'] }}</h3>
                                    <p class="m-b-0">Total Kuis</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card">
                                <div class="card-block text-center">
                                    <h3 class="text-info">{{ $stats['total_students'] }}</h3>
                                    <p class="m-b-0">Mahasiswa</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card">
                                <div class="card-block text-center">
                                    <h3 class="text-dark">{{ $stats['total_attempts'] }}</h3>
                                    <p class="m-b-0">Total Attempts</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card">
                                <div class="card-block text-center">
                                    <h3 class="text-warning">{{ $stats['total_tab_violations'] }}</h3>
                                    <p class="m-b-0">Tab Switches</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card">
                                <div class="card-block text-center">
                                    <h3 class="text-danger">{{ $stats['total_camera_violations'] }}</h3>
                                    <p class="m-b-0">Camera Violations</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="card bg-dark">
                                <div class="card-block text-center text-white">
                                    <h3>{{ $stats['total_violations'] }}</h3>
                                    <p class="m-b-0">Total Violations</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quizzes Table --}}
                    <div class="card">
                        <div class="card-header">
                            <h5>Monitoring per Kuis</h5>
                        </div>
                        <div class="card-block">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="25%">Nama Kuis</th>
                                            <th width="15%">Modul</th>
                                            <th class="text-center" width="10%">Students</th>
                                            <th class="text-center" width="10%">Attempts</th>
                                            <th class="text-center" width="10%">Tab</th>
                                            <th class="text-center" width="10%">Camera</th>
                                            <th class="text-center" width="10%">Total</th>
                                            <th class="text-center" width="5%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($quizData as $index => $data)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $data['quiz']->title }}</strong>
                                                @if($data['quiz']->securitySetting)
                                                    <br>
                                                    <small>
                                                        @if($data['quiz']->securitySetting->enable_tab_detection)
                                                            <span class="badge badge-info">Tab Detection</span>
                                                        @endif
                                                        @if($data['quiz']->securitySetting->enable_camera_detection)
                                                            <span class="badge badge-primary">Camera Detection</span>
                                                        @endif
                                                        @if($data['quiz']->securitySetting->enable_question_shuffle)
                                                            <span class="badge badge-secondary">Shuffle</span>
                                                        @endif
                                                    </small>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $data['quiz']->lesson->module->title }}</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-info">{{ $data['unique_students'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-dark">{{ $data['attempt_count'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-{{ $data['tab_violations'] > 0 ? 'warning' : 'secondary' }}">
                                                    {{ $data['tab_violations'] }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge badge-{{ $data['camera_violations'] > 0 ? 'danger' : 'secondary' }}">
                                                    {{ $data['camera_violations'] }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <strong class="text-{{ $data['total_violations'] > 0 ? 'danger' : 'muted' }}">
                                                    {{ $data['total_violations'] }}
                                                </strong>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('instructor.quiz.monitoring.review', $data['quiz']->id) }}" 
                                                   class="btn btn-sm btn-info"
                                                   title="Detail Monitoring">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="9" class="text-center">
                                                <p class="text-muted">Belum ada kuis dengan pelanggaran dalam course ini</p>
                                            </td>
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
@endsection
