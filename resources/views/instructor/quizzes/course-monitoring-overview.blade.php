@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <x-dash.page-head>
        <x-slot:context>Kursus: {{ $course->title }}</x-slot:context>
        <x-slot:title>Ringkasan Pelanggaran Kuis</x-slot:title>
        <x-slot:subtitle>Rekap pelanggaran untuk semua kuis dalam course ini</x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('instructor.courses.index') }}" class="btn btn-secondary">
             <i class="fa fa-arrow-left"></i> Kembali ke Manajemen Course
             </a>
        </x-slot:actions>
    </x-dash.page-head>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">

                    {{-- Statistics Cards --}}
                    <div class="row">
                        <div class="col-md-2 col-6 mb-3"><div class="rs-stat h-100"><span class="rs-tile" aria-hidden="true"><i class="fa fa-bar-chart"></i></span><div><strong>{{ $stats['total_quizzes'] }}</strong><span>Total Kuis</span></div></div></div>
                        <div class="col-md-2 col-6 mb-3"><div class="rs-stat h-100"><span class="rs-tile" aria-hidden="true"><i class="fa fa-camera"></i></span><div><strong>{{ $stats['total_students'] }}</strong><span>Student</span></div></div></div>
                        <div class="col-md-2 col-6 mb-3"><div class="rs-stat h-100"><span class="rs-tile" aria-hidden="true"><i class="fa fa-bar-chart"></i></span><div><strong>{{ $stats['total_attempts'] }}</strong><span>Total Percobaan</span></div></div></div>
                        <div class="col-md-2 col-6 mb-3"><div class="rs-stat h-100"><span class="rs-tile" aria-hidden="true"><i class="fa fa-exclamation-triangle"></i></span><div><strong>{{ $stats['total_tab_violations'] }}</strong><span>Tab Switches</span></div></div></div>
                        <div class="col-md-2 col-6 mb-3"><div class="rs-stat h-100"><span class="rs-tile" aria-hidden="true"><i class="fa fa-ban"></i></span><div><strong>{{ $stats['total_camera_violations'] }}</strong><span>Pelanggaran Kamera</span></div></div></div>
                        <div class="col-md-2">
                            <div class="card bg-dark">
                                <div class="card-block text-center text-white">
                                    <h3>{{ $stats['total_violations'] }}</h3>
                                    <p class="m-b-0">Total Pelanggaran</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quizzes Table --}}
                    <div class="card">
                        <div class="card-header">
                            <h5>Pelanggaran per Kuis</h5>
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
                                            <th class="text-center" width="10%">Percobaan</th>
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
                                                   class="btn btn-sm btn-secondary"
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
