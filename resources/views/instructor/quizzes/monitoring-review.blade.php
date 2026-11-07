@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <x-dash.page-head>
        <x-slot:context>Kuis: {{ $quiz->title }}</x-slot:context>
        <x-slot:title>Tinjauan Pelanggaran Kuis</x-slot:title>
        <x-slot:subtitle>Review pelanggaran pengerjaan kuis oleh student</x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('instructor.modules.lessons.index', $quiz->lesson->module_id) }}" class="btn btn-secondary">
             <i class="fa fa-arrow-left"></i> Kembali
             </a>
        </x-slot:actions>
    </x-dash.page-head>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">

                    {{-- Statistics Cards --}}
                    <div class="row">
                        <div class="col-md-3 col-6 mb-3"><div class="rs-stat h-100"><span class="rs-tile" aria-hidden="true"><i class="fa fa-bar-chart"></i></span><div><strong>{{ $stats['total_attempts'] }}</strong><span>Total Percobaan</span></div></div></div>
                        <div class="col-md-3 col-6 mb-3"><div class="rs-stat h-100"><span class="rs-tile" aria-hidden="true"><i class="fa fa-exclamation-triangle"></i></span><div><strong>{{ $stats['total_tab_violations'] }}</strong><span>Total Tab Switches</span></div></div></div>
                        <div class="col-md-3 col-6 mb-3"><div class="rs-stat h-100"><span class="rs-tile" aria-hidden="true"><i class="fa fa-camera"></i></span><div><strong>{{ $stats['total_camera_violations'] }}</strong><span>Total Pelanggaran Kamera</span></div></div></div>
                        <div class="col-md-3 col-6 mb-3"><div class="rs-stat h-100"><span class="rs-tile" aria-hidden="true"><i class="fa fa-ban"></i></span><div><strong>{{ $stats['total_expelled'] ?? 0 }}</strong><span>Dikeluarkan Dari Quiz</span></div></div></div>
                    </div>

                    {{-- Attempts Table --}}
                    <div class="card">
                        <div class="card-header">
                            <h5>Daftar Siswa &  Sesi Terakhir</h5>
                        </div>
                        <div class="card-block">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Siswa</th>
                                            <th>Waktu Pengerjaan Terakhir</th>
                                            <th>Skor Terakhir</th>
                                            <th class="text-center">Tab Switch Terakhir</th>
                                            <th class="text-center">Pelanggaran Kamera Terakhir</th>
                                            <th class="text-center">Total Pelanggaran Terakhir</th>
                                            <th class="text-center">Dikeluarkan Dari Quiz Terakhir</th>
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
                                                    {{ $allAttempts->count() }} percobaan
                                                </small>
                                            </td>
                                            <td>
                                                <small>
                                                    {{ $attempt->start_time ? $attempt->start_time->format('d M Y H:i') : '-' }}
                                                    {{-- <br>
                                                    <span class="text-muted">
                                                        @if($attempt->start_time && $attempt->end_time)
                                                            Durasi: {{ $attempt->start_time->diffInMinutes($attempt->end_time) }} menit
                                                        @endif
                                                    </span> --}}
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
                                                @if($attempt->expelled_by_violation)
                                                    <span class="badge badge-danger"><i class="fa fa-ban"></i> Ya</span>
                                                @else
                                                    <span class="badge badge-secondary">Tidak</span>
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
                                                   class="btn btn-sm btn-secondary"
                                                   title="Lihat Detail Latest Attempt">
                                                    <i class="fa fa-eye"></i> Latest
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="8" class="text-center">
                                                <p class="text-muted">Belum ada siswa yang mengerjakan kuis ini</p>
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
                    <i class="fa fa-history"></i> Riwayat Percobaan - {{ $student->name }}
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
                                <th width="25%">Waktu Mulai</th>
                                <th width="12%">Skor</th>
                                <th width="12%">Status</th>
                                <th width="15%">Pelanggaran</th>
                                <th width="12%">Dikeluarkan Dari Quiz</th>
                                <th width="19%">Aksi</th>
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
                                <td class="text-center">
                                    @if($historyAttempt->expelled_by_violation)
                                        <span class="badge badge-danger"><i class="fa fa-ban"></i> Ya</span>
                                    @else
                                        <span class="badge badge-secondary">Tidak</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('instructor.quiz.monitoring.detail', $historyAttempt) }}" 
                                       class="btn btn-sm btn-secondary"
                                       title="Lihat Detail">
                                        <i class="fa fa-eye"></i> Detail
                                    </a>
                                    <a href="{{ route('instructor.quiz.review_attempt', $historyAttempt) }}" 
                                       class="btn btn-sm btn-secondary"
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
