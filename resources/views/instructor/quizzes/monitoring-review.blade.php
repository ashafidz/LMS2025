@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Monitoring Review - {{ $quiz->title }}</h5>
                        <p class="m-b-0">Review integritas pengerjaan kuis oleh mahasiswa</p>
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('instructor.modules.lessons.index', $quiz->lesson->module_id) }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Kembali
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
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-block text-center">
                                    <h3 class="text-primary">{{ $stats['total_attempts'] }}</h3>
                                    <p class="m-b-0">Total Attempts</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-block text-center">
                                    <h3 class="text-warning">{{ $stats['total_tab_violations'] }}</h3>
                                    <p class="m-b-0">Total Tab Switches</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-block text-center">
                                    <h3 class="text-info">{{ $stats['total_camera_violations'] }}</h3>
                                    <p class="m-b-0">Total Camera Violations</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Attempts Table --}}
                    <div class="card">
                        <div class="card-header">
                            <h5>Daftar Mahasiswa & Status Monitoring</h5>
                        </div>
                        <div class="card-block">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Mahasiswa</th>
                                            <th>Waktu Pengerjaan</th>
                                            <th>Skor</th>
                                            <th class="text-center">Tab Switch</th>
                                            <th class="text-center">Camera Violations</th>
                                            <th class="text-center">Total Violations</th>
                                            <th class="text-center">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($attemptsByStudent as $studentData)
                                        @php
                                            $attempt = $studentData['latest_attempt'];
                                            $allAttempts = $studentData['all_attempts'];
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $attempt->student->name }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $attempt->student->email }}</small>
                                                <br>
                                                <small class="badge badge-info">
                                                    {{ $allAttempts->count() }} attempt(s)
                                                </small>
                                            </td>
                                            <td>
                                                <small>
                                                    {{ $attempt->start_time ? $attempt->start_time->format('d M Y H:i') : '-' }}
                                                    <br>
                                                    <span class="text-muted">
                                                        @if($attempt->start_time && $attempt->end_time)
                                                            Durasi: {{ $attempt->start_time->diffInMinutes($attempt->end_time) }} menit
                                                        @endif
                                                    </span>
                                                </small>
                                            </td>
                                            <td>
                                                <strong class="{{ $attempt->status === 'passed' ? 'text-success' : 'text-danger' }}">
                                                    {{ number_format($attempt->score, 2) }}
                                                </strong>
                                                @if($attempt->revised_score !== null)
                                                <br>
                                                <small class="text-info" title="Skor Revisi">
                                                    <i class="fa fa-pencil"></i> Revisi: <strong>{{ number_format($attempt->revised_score, 2) }}</strong>
                                                </small>
                                                @endif
                                                <br>
                                                <small class="text-muted">{{ ucfirst($attempt->status) }}</small>
                                            </td>
                                            <td class="text-center">
                                                @if($attempt->integritySummary)
                                                    <span class="badge badge-{{ $attempt->integritySummary->total_tab_switches > 0 ? 'warning' : 'secondary' }}">
                                                        {{ $attempt->integritySummary->total_tab_switches }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary">0</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($attempt->integritySummary)
                                                    <span class="badge badge-{{ $attempt->integritySummary->total_face_violations > 0 ? 'warning' : 'secondary' }}">
                                                        {{ $attempt->integritySummary->total_face_violations }}
                                                    </span>
                                                    <br>
                                                    <small class="text-muted">
                                                        No Face: {{ $attempt->integritySummary->face_not_detected_count }},
                                                        L: {{ $attempt->integritySummary->look_left_count }},
                                                        R: {{ $attempt->integritySummary->look_right_count }},
                                                        D: {{ $attempt->integritySummary->look_down_count }},
                                                        U: {{ $attempt->integritySummary->look_up_count }}
                                                    </small>
                                                @else
                                                    <span class="badge badge-secondary">0</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($attempt->integritySummary)
                                                    <strong>
                                                        {{ $attempt->integritySummary->total_tab_switches + $attempt->integritySummary->total_face_violations }}
                                                    </strong>
                                                @else
                                                    <span class="text-muted">0</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <button type="button" 
                                                        class="btn btn-sm btn-primary mb-1"
                                                        data-toggle="modal" 
                                                        data-target="#historyModal{{ $attempt->student_id }}"
                                                        title="Lihat Semua History">
                                                    <i class="fa fa-history"></i> History
                                                </button>
                                                <br>
                                                <a href="{{ route('instructor.quiz.monitoring.detail', $attempt) }}" 
                                                   class="btn btn-sm btn-info"
                                                   title="Lihat Detail Latest Attempt">
                                                    <i class="fa fa-eye"></i> Latest
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                <p class="text-muted">Belum ada mahasiswa yang mengerjakan kuis ini</p>
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

{{-- Modals untuk History setiap Student --}}
@foreach($attemptsByStudent as $studentData)
@php
    $student = $studentData['student'];
    $allAttempts = $studentData['all_attempts'];
@endphp
<div class="modal fade" id="historyModal{{ $student->id }}" tabindex="-1" role="dialog" aria-labelledby="historyModalLabel{{ $student->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyModalLabel{{ $student->id }}">
                    <i class="fa fa-history"></i> History Attempts - {{ $student->name }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th width="5%">#</th>
                                <th width="30%">Waktu Mulai</th>
                                <th width="15%">Skor</th>
                                <th width="15%">Status</th>
                                <th width="20%">Violations</th>
                                <th width="15%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allAttempts as $index => $historyAttempt)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <small>
                                        {{ $historyAttempt->start_time ? $historyAttempt->start_time->format('d M Y H:i:s') : '-' }}
                                        @if($index === 0)
                                            <span class="badge badge-primary">Latest</span>
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <strong class="{{ $historyAttempt->status === 'passed' ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($historyAttempt->score, 2) }}
                                    </strong>
                                    @if($historyAttempt->revised_score !== null)
                                    <br>
                                    <small class="text-info" title="Skor Revisi">
                                        <i class="fa fa-pencil"></i> {{ number_format($historyAttempt->revised_score, 2) }}
                                    </small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $historyAttempt->status === 'passed' ? 'success' : 'danger' }}">
                                        {{ ucfirst($historyAttempt->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($historyAttempt->integritySummary)
                                        <small>
                                            Tab: {{ $historyAttempt->integritySummary->total_tab_switches }}<br>
                                            Cam: {{ $historyAttempt->integritySummary->total_face_violations }}<br>
                                            <strong>Total: {{ $historyAttempt->integritySummary->total_tab_switches + $historyAttempt->integritySummary->total_face_violations }}</strong>
                                        </small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('instructor.quiz.monitoring.detail', $historyAttempt) }}" 
                                       class="btn btn-sm btn-info"
                                       title="Lihat Detail">
                                        <i class="fa fa-eye"></i> Detail
                                    </a>
                                    <a href="{{ route('instructor.quiz.review_attempt', $historyAttempt) }}" 
                                       class="btn btn-sm btn-inverse"
                                       title="Lihat Nilai Kuis">
                                        <i class="fa fa-file-text-o"></i> Nilai Kuis
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endforeach

@endsection
