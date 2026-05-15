@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Detail Pelanggaran - {{ $attempt->student->name }}</h5>
                        <p class="m-b-0">{{ $attempt->quiz->title }}</p>
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('instructor.quiz.review_attempt', $attempt) }}" class="btn btn-inverse btn-sm" title="Lihat Nilai Kuis">
                        <i class="fa fa-file-text-o"></i> Nilai Kuis
                    </a>
                    <a href="{{ route('instructor.quiz.monitoring.review', $attempt->quiz_id) }}" class="btn btn-secondary btn-sm">
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

                    {{-- Flash Messages --}}
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fa fa-check-circle"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa fa-exclamation-circle"></i>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    {{-- Summary Cards --}}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fa fa-user"></i> Informasi Student</h6>
                                </div>
                                <div class="card-block">
                                    <table class="table table-sm">
                                        <tr>
                                            <td><strong>Nama:</strong></td>
                                            <td>{{ $attempt->student->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>{{ $attempt->student->email }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Skor Asli:</strong></td>
                                            <td>
                                                <span class="{{ $attempt->status === 'passed' ? 'text-success' : 'text-danger' }}">
                                                    <strong>{{ number_format($attempt->score, 2) }}</strong>
                                                </span>
                                                <small class="text-muted">/ {{ number_format($totalMaxScore, 2) }}</small>
                                            </td>
                                        </tr>
                                        @if($attempt->revised_score !== null)
                                        <tr>
                                            <td><strong>Skor Revisi:</strong></td>
                                            <td>
                                                <span class="text-info">
                                                    <strong><i class="fa fa-pencil"></i> {{ number_format($attempt->revised_score, 2) }}</strong>
                                                </span>
                                                <small class="text-muted">/ {{ number_format($totalMaxScore, 2) }}</small>
                                            </td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td><strong>Status:</strong></td>
                                            <td>
                                                <span class="badge badge-{{ $attempt->status === 'passed' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($attempt->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Waktu Mulai:</strong></td>
                                            <td>{{ $attempt->start_time ? $attempt->start_time->format('d M Y H:i:s') : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Waktu Selesai:</strong></td>
                                            <td>{{ $attempt->end_time ? $attempt->end_time->format('d M Y H:i:s') : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Durasi:</strong></td>
                                            <td>
                                                @if($attempt->start_time && $attempt->end_time)
                                                    {{ $attempt->start_time->diffForHumans($attempt->end_time, true) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Dikeluarkan:</strong></td>
                                            <td>
                                                @if($attempt->expelled_by_violation)
                                                    <span class="badge badge-danger"><i class="fa fa-ban"></i> Ya - Pelanggaran</span>
                                                @else
                                                    <span class="badge badge-success"><i class="fa fa-check"></i> Tidak</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header bg-warning text-white">
                                    <h6 class="mb-0"><i class="fa fa-exclamation-triangle"></i>Violation Summary</h6>
                                </div>
                                <div class="card-block">
                                    @if($attempt->integritySummary)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-muted">Tab Switching</h6>
                                            <h3 class="text-warning">{{ $attempt->integritySummary->total_tab_switches }}</h3>
                                            <p class="text-muted mb-3">Total perpindahan tab</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-muted">Camera Violations</h6>
                                            <h3 class="text-danger">{{ $attempt->integritySummary->total_face_violations }}</h3>
                                            <p class="text-muted mb-3">Total pelanggaran kamera</p>
                                        </div>
                                        <div class="col-md-12">
                                            <hr>
                                            <h6 class="text-muted">Breakdown Camera Violations:</h6>
                                            <div class="row text-center mt-3">
                                                <div class="col">
                                                    <h4 class="text-dark">{{ $attempt->integritySummary->face_not_detected_count }}</h4>
                                                    <small class="text-muted">Wajah Tidak Terdeteksi</small>
                                                </div>
                                                <div class="col">
                                                    <h4 class="text-dark">{{ $attempt->integritySummary->look_left_count }}</h4>
                                                    <small class="text-muted">Pandang Kiri</small>
                                                </div>
                                                <div class="col">
                                                    <h4 class="text-dark">{{ $attempt->integritySummary->look_right_count }}</h4>
                                                    <small class="text-muted">Pandang Kanan</small>
                                                </div>
                                                <div class="col">
                                                    <h4 class="text-dark">{{ $attempt->integritySummary->look_down_count }}</h4>
                                                    <small class="text-muted">Pandang Bawah</small>
                                                </div>
                                                <div class="col">
                                                    <h4 class="text-dark">{{ $attempt->integritySummary->look_up_count }}</h4>
                                                    <small class="text-muted">Pandang Atas</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-3">
                                            <hr>
                                            <div class="text-center">
                                                <h6 class="text-muted mb-2">Total Violations:</h6>
                                                <h4 class="text-dark mb-0">
                                                    {{ $attempt->integritySummary->total_tab_switches + $attempt->integritySummary->total_face_violations }}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                    <p class="text-center text-muted">Tidak ada data integrity summary</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card Revisi Skor --}}
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fa fa-pencil-square-o"></i> Revisi Skor</h5>
                        </div>
                        <div class="card-block">
                            {{-- Info revisi sebelumnya (jika ada) --}}
                            @if($attempt->revised_score !== null)
                            <div class="alert alert-info">
                                <div class="row">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong><i class="fa fa-info-circle"></i> Skor telah direvisi sebelumnya:</strong></p>
                                        <table class="table table-sm table-borderless mb-0">
                                            <tr>
                                                <td width="140"><strong>Skor Asli:</strong></td>
                                                <td>
                                                    <span class="{{ $attempt->status === 'passed' ? 'text-success' : 'text-danger' }}">
                                                        {{ number_format($attempt->score, 2) }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Skor Revisi:</strong></td>
                                                <td>
                                                    <span class="text-info font-weight-bold">
                                                        {{ number_format($attempt->revised_score, 2) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-sm table-borderless mb-0 mt-4">
                                            <tr>
                                                <td width="140"><strong>Direvisi oleh:</strong></td>
                                                <td>{{ $attempt->revisedBy->name ?? '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Waktu revisi:</strong></td>
                                                <td>{{ $attempt->revised_at ? $attempt->revised_at->format('d M Y H:i:s') : '-' }}</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Catatan:</strong></td>
                                                <td>{{ $attempt->revision_note }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endif

                            {{-- Form Revisi Skor --}}
                            <form action="{{ route('instructor.quiz.attempt.revise_score', $attempt) }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="current_score"><strong>Skor Asli (Tidak Dapat Diubah)</strong></label>
                                            <input type="text" class="form-control" id="current_score"
                                                   value="{{ number_format($attempt->score, 2) }} / {{ number_format($totalMaxScore, 2) }}"
                                                   readonly disabled
                                                   style="background-color: #f5f5f5;">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="revised_score"><strong>Skor Revisi <span class="text-danger">*</span></strong></label>
                                            <input type="number" class="form-control {{ $errors->has('revised_score') ? 'is-invalid' : '' }}"
                                                   id="revised_score" name="revised_score"
                                                   value="{{ old('revised_score', $attempt->revised_score) }}"
                                                   step="0.01" min="0" max="{{ $totalMaxScore }}"
                                                   placeholder="Masukkan skor revisi (0 - {{ number_format($totalMaxScore, 2) }})"
                                                   required>
                                            <small class="form-text text-muted">Skor maksimal: {{ number_format($totalMaxScore, 2) }}</small>
                                            @if($errors->has('revised_score'))
                                            <div class="invalid-feedback">{{ $errors->first('revised_score') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="revision_note"><strong>Catatan Revisi <span class="text-danger">*</span></strong></label>
                                            <textarea class="form-control {{ $errors->has('revision_note') ? 'is-invalid' : '' }}"
                                                      id="revision_note" name="revision_note" rows="2"
                                                      placeholder="Alasan melakukan revisi skor..."
                                                      required maxlength="500">{{ old('revision_note') }}</textarea>
                                            @if($errors->has('revision_note'))
                                            <div class="invalid-feedback">{{ $errors->first('revision_note') }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <button type="submit" class="btn btn-info"
                                            onclick="return confirm('Apakah Anda yakin ingin merevisi skor student ini?')">
                                        <i class="fa fa-save"></i> Simpan Revisi Skor
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Monitoring Logs --}}
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fa fa-list"></i> Timeline Pelanggaran Logs ({{ $attempt->monitoringLogs->count() }} events)</h5>
                        </div>
                        <div class="card-block">
                            @if($attempt->monitoringLogs->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="15%">Waktu</th>
                                            <th width="15%">Jenis Pelanggaran</th>
                                            <th width="10%">Durasi (detik)</th>
                                            <th width="20%">Bukti Screenshot</th>
                                            <th width="35%">Data Tambahan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($attempt->monitoringLogs as $index => $log)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <small>{{ $log->violation_timestamp->format('d M Y H:i:s') }}</small>
                                                <br>
                                                <small class="text-muted">
                                                    @if($loop->first)
                                                        Start
                                                    @else
                                                        +{{ $attempt->monitoringLogs[$index - 1]->violation_timestamp->diffInSeconds($log->violation_timestamp) }}s
                                                    @endif
                                                </small>
                                            </td>
                                            <td>
                                                @php
                                                    $violationLabels = [
                                                        'tab_switch' => ['label' => 'Tab Switch', 'color' => 'warning', 'icon' => 'fa-exchange'],
                                                        'face_not_detected' => ['label' => 'Wajah Tidak Terdeteksi', 'color' => 'danger', 'icon' => 'fa-user-times'],
                                                        'look_left' => ['label' => 'Pandang Kiri', 'color' => 'info', 'icon' => 'fa-arrow-left'],
                                                        'look_right' => ['label' => 'Pandang Kanan', 'color' => 'info', 'icon' => 'fa-arrow-right'],
                                                        'look_down' => ['label' => 'Pandang Bawah', 'color' => 'info', 'icon' => 'fa-arrow-down'],
                                                        'look_up' => ['label' => 'Pandang Atas', 'color' => 'info', 'icon' => 'fa-arrow-up'],
                                                    ];
                                                    $violation = $violationLabels[$log->violation_type] ?? ['label' => $log->violation_type, 'color' => 'secondary', 'icon' => 'fa-question'];
                                                @endphp
                                                <span class="badge badge-{{ $violation['color'] }}">
                                                    <i class="fa {{ $violation['icon'] }}"></i>
                                                    {{ $violation['label'] }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $log->duration_seconds ?? '-' }}
                                            </td>
                                            <td>
                                                @if($log->screenshot_path)
                                                    <a href="{{ asset('storage/' . $log->screenshot_path) }}" target="_blank" data-toggle="tooltip" title="Klik untuk lihat full size">
                                                        <img src="{{ asset('storage/' . $log->screenshot_path) }}" 
                                                             alt="Screenshot Bukti Pelanggaran" 
                                                             class="img-thumbnail" 
                                                             style="max-width: 150px; max-height: 100px; cursor: pointer;">
                                                    </a>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fa fa-camera"></i> 
                                                        {{ \Illuminate\Support\Str::afterLast($log->screenshot_path, '/') }}
                                                    </small>
                                                @else
                                                    <span class="text-muted">
                                                        <i class="fa fa-minus-circle"></i> Tidak ada bukti
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($log->additional_data)
                                                    <small><pre class="mb-0">{{ json_encode($log->additional_data, JSON_PRETTY_PRINT) }}</pre></small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @else
                            <p class="text-center text-muted">Tidak ada logs pelanggaran tercatat</p>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
