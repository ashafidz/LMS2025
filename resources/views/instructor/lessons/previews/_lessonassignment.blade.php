@php
    $submission = null;
    if(Auth::check() && !$is_preview) {
        $submission = $lesson->lessonable->submissions()->where('user_id', Auth::id())->first();
    }
    $assignment = $lesson->lessonable;

    // Variabel untuk mengecek apakah sudah melewati batas waktu
    $isLate = $assignment->due_date && now()->isAfter($assignment->due_date);
@endphp

<div class="assignment-header mb-4 border-bottom pb-3">
    <h3 class="font-weight-bold text-primary mb-2"><i class="fa fa-tasks mr-2"></i>{{ $lesson->title }}</h3>
    
    <div class="d-flex flex-wrap align-items-center mt-3 text-muted">
        <div class="mr-4 mb-2">
            <i class="fa fa-calendar-alt mr-1"></i> <strong>Batas Waktu:</strong> 
            <span class="{{ $isLate ? 'text-danger font-weight-bold' : '' }}">
                {{ $assignment->due_date ? $assignment->due_date->format('d F Y, H:i') : 'Tidak ada' }}
            </span>
        </div>
        <div class="mb-2">
            <i class="fa fa-check-circle mr-1 text-success"></i> <strong>Nilai Kelulusan Minimum:</strong> {{ $assignment->pass_mark }} / 100
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4 bg-light">
    <div class="card-body">
        <h5 class="font-weight-bold text-dark mb-3"><i class="fa fa-info-circle mr-2 text-info"></i>Instruksi Tugas</h5>
        <div class="instructions text-secondary" style="white-space: pre-wrap; font-size: 15px; line-height: 1.6;">{!! nl2br(e($assignment->instructions)) !!}</div>
    </div>
</div>

<div class="assignment-submission-section mt-4">
    @if($isLate && !$submission)
        <div class="alert alert-danger shadow-sm border-0 border-left-danger">
            <h5 class="font-weight-bold mb-2"><i class="fa fa-exclamation-triangle mr-2"></i>Batas Waktu Sudah Lewat</h5>
            <p class="mb-0">Batas waktu untuk tugas ini telah lewat, segera kumpulkan tugas Anda jika masih diizinkan.</p>
        </div>
    @endif

    @if($submission)
        {{-- Jika siswa sudah pernah mengumpulkan --}}
        <div class="card shadow-sm border-0 border-left-info mb-4">
            <div class="card-body">
                <h5 class="font-weight-bold text-info mb-3"><i class="fa fa-file-alt mr-2"></i>Status Pengumpulan Anda</h5>
                <ul class="list-unstyled mb-0">
                    <li class="mb-2"><i class="fa fa-file-pdf-o text-danger mr-2"></i><strong>File Terakhir Diunggah:</strong> <code>{{ basename($submission->file_path) }}</code> <a href="{{ Storage::url($submission->file_path) }}" class="btn btn-sm btn-outline-primary ml-2 py-0 px-2" download><i class="fa fa-download"></i> Unduh</a></li>
                    <li><i class="fa fa-clock-o text-muted mr-2"></i><strong>Waktu Mengumpulkan:</strong> {{ $submission->submitted_at->format('d F Y, H:i') }}</li>
                </ul>
            </div>
        </div>

        @if($submission->status === 'submitted')
            <div class="alert alert-secondary border-0 shadow-sm text-center py-3">
                <i class="fa fa-hourglass-half fa-2x text-muted mb-2"></i>
                <p class="mb-0 font-weight-bold text-dark">Tugas Anda sedang menunggu penilaian dari instruktur.</p>
            </div>
            
            {{-- Form edit jika belum terlambat --}}
            @if(!$isLate && !$is_preview)
                <div class="card border-warning shadow-sm mt-4">
                    <div class="card-header bg-warning text-dark font-weight-bold">
                        <i class="fa fa-edit mr-2"></i>Ingin mengganti file tugas Anda?
                    </div>
                    <div class="card-body bg-light">
                        <p class="text-muted small mb-3">Anda masih bisa mengganti file tugas selama belum melewati batas waktu.</p>
                        @include('student.courses.partials._assignment_form', ['assignment' => $assignment])
                    </div>
                </div>
            @endif
            
        @elseif($submission->status === 'revision_required')
            <div class="card shadow-sm border-0 border-left-danger mt-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="font-weight-bold text-danger mb-0"><i class="fa fa-times-circle mr-2"></i>Penilaian & Revisi dari Instruktur</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <h1 class="display-4 text-danger mb-0 mr-3">{{ $submission->grade }}</h1>
                        <span class="text-muted h5 mb-0"> / 100</span>
                    </div>
                    <div class="bg-light p-3 rounded">
                        <strong><i class="fa fa-commenting-o mr-1"></i> Umpan Balik:</strong>
                        <p class="mb-0 mt-2">{!! nl2br(e($submission->feedback)) !!}</p>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm border-0 border-left-warning mt-4">
                <div class="card-body">
                    <h5 class="font-weight-bold text-warning mb-2"><i class="fa fa-refresh mr-2"></i>Revisi Diperlukan</h5>
                    <p class="text-muted mb-3">Nilai Anda belum mencapai standar kelulusan. Silakan perbaiki tugas Anda dan unggah kembali file yang baru di bawah ini.</p>
                    @include('student.courses.partials._assignment_form', ['assignment' => $assignment])
                </div>
            </div>
            
        @elseif($submission->status === 'passed')
            <div class="card shadow-sm border-0 border-left-success mt-4">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="font-weight-bold text-success mb-0"><i class="fa fa-check-circle mr-2"></i>Penilaian dari Instruktur (Lulus)</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <h1 class="display-4 text-success mb-0 mr-3">{{ $submission->grade }}</h1>
                        <span class="text-muted h5 mb-0"> / 100</span>
                    </div>
                    @if($submission->feedback)
                    <div class="bg-light p-3 rounded">
                        <strong><i class="fa fa-commenting-o mr-1"></i> Umpan Balik:</strong>
                        <p class="mb-0 mt-2">{!! nl2br(e($submission->feedback)) !!}</p>
                    </div>
                    @endif
                </div>
            </div>
        @endif

    @else
        {{-- Jika siswa belum mengumpulkan sama sekali --}}
        @if(!$is_preview)
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header bg-white pb-0 border-bottom-0">
                    <h5 class="font-weight-bold text-primary mb-0"><i class="fa fa-upload mr-2"></i>Pengumpulan Tugas</h5>
                </div>
                <div class="card-body">
                    @include('student.courses.partials._assignment_form', ['assignment' => $assignment])
                </div>
            </div>
        @else
            <div class="card mt-4 border-primary border-dashed bg-light">
                <div class="card-body text-center py-5">
                    <div class="rounded-circle bg-white shadow-sm mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fa fa-cloud-upload text-primary fa-2x"></i>
                    </div>
                    <p class="text-muted mb-3">Ini adalah pratinjau. Siswa akan melihat formulir untuk mengunggah file tugas di bagian ini.</p>
                    <button class="btn btn-outline-primary" disabled><i class="fa fa-upload mr-2"></i> Kumpulkan Tugas</button>
                </div>
            </div>
        @endif
    @endif
</div>