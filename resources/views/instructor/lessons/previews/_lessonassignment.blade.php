@php
    $submission = null;
    if(Auth::check() && !$is_preview) {
        $submission = $lesson->lessonable->submissions()->where('user_id', Auth::id())->first();
    }
    $assignment = $lesson->lessonable;

        // Variabel untuk mengecek apakah sudah melewati batas waktu
    $isLate = $assignment->due_date && now()->isAfter($assignment->due_date);
@endphp


<h4 class="font-weight-bold">{{ $lesson->title }}</h4>
<hr>

<div class="assignment-container">


    <p class="text-danger" ><strong>Batas Waktu:</strong> {{ $assignment->due_date ? $assignment->due_date->format('d F Y, H:i') : 'Tidak ada' }}</p>
    @if($isLate && !$submission)
        <div class="alert alert-danger">
            <h5 class="font-weight-bold"><i class="fa fa-exclamation-triangle"></i> Batas Waktu Sudah Lewat</h5>
            <p>Batas waktu untuk tugas ini telah lewat, cepat kumpulkan tugas Anda.</p>
        </div>
    @endif


    @if($submission)
        {{-- Jika siswa sudah pernah mengumpulkan --}}
        <div class="alert alert-info">
            <h5 class="font-weight-bold"><i class="fa fa-info-circle"></i> Status Pengumpulan Anda</h5>
            <ul>
                <li><strong>File Terakhir Diunggah:</strong> <code>{{ basename($submission->file_path) }}</code> <a href="{{ Storage::url($submission->file_path) }}" download> (Unduh)</a></li>
                <li><strong>Waktu Mengumpulkan:</strong> {{ $submission->submitted_at->format('d F Y, H:i') }}</li>
            </ul>
        </div>

        @if($submission->status === 'submitted')
            <p class="text-muted mt-3">Tugas Anda sedang menunggu penilaian dari instruktur.</p>
            
            {{-- Form edit jika belum terlambat --}}
            @if(!$isLate && !$is_preview)
                <div class="alert alert-warning mt-3">
                    <strong>Ingin mengganti file tugas Anda?</strong><br>
                    Anda masih bisa mengganti file tugas selama belum melewati batas waktu.
                </div>
                @include('student.courses.partials._assignment_form', ['assignment' => $assignment])
            @endif
            
        @elseif($submission->status === 'revision_required')
            <div class="card mt-3">
                <div class="card-header bg-danger text-white"><strong>Penilaian & Revisi dari Instruktur</strong></div>
                <div class="card-block">
                    <p><strong>Nilai:</strong> <span class="font-weight-bold text-danger">{{ $submission->grade }} / 100</span></p>
                    <p class="mb-0"><strong>Umpan Balik:</strong><br>{!! nl2br(e($submission->feedback)) !!}</p>
                </div>
            </div>
            <div class="alert alert-warning mt-4">
                <strong>Nilai Anda belum mencapai standar kelulusan.</strong><br>
                Silakan perbaiki tugas Anda dan unggah kembali file yang baru di bawah ini.
            </div>
            @include('student.courses.partials._assignment_form', ['assignment' => $assignment])
        @elseif($submission->status === 'passed')
            <div class="card mt-3">
                <div class="card-header bg-success text-white"><strong>Penilaian dari Instruktur</strong></div>
                <div class="card-block">
                    <p><strong>Nilai:</strong> <span class="font-weight-bold text-success">{{ $submission->grade }} / 100</span></p>
                    <p class="mb-0"><strong>Umpan Balik:</strong><br>{!! nl2br(e($submission->feedback)) !!}</p>
                </div>
            </div>
        @endif

    @else
        {{-- Jika siswa belum mengumpulkan sama sekali --}}
        @if(!$is_preview)
            @include('student.courses.partials._assignment_form', ['assignment' => $assignment])
        @else
            <div class="text-center text-muted">
                <p>Ini adalah pratinjau. Siswa akan melihat tombol untuk mengunggah file tugas di sini.</p>
                <button class="btn btn-primary" disabled><i class="fa fa-upload"></i> Kumpulkan Tugas</button>
            </div>
        @endif
    @endif


    
    <h5 class="font-weight-bold mt-5">Instruksi Tugas</h5>
    <div class="instructions mb-4" style="white-space: pre-wrap;">{!! nl2br(e($assignment->instructions)) !!}</div>
    <p class="text-danger" ><strong>Batas Waktu:</strong> {{ $assignment->due_date ? $assignment->due_date->format('d F Y, H:i') : 'Tidak ada' }}</p>
    <p><strong>Nilai Kelulusan Minimum:</strong> {{ $assignment->pass_mark }}</p>
    <hr>

</div>