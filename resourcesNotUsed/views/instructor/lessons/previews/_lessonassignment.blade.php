{{-- File ini hanya berisi konten pratinjau untuk pelajaran tugas --}}

{{-- <h4 class="font-weight-bold">{{ $lesson->title }}</h4>
<p>Ini adalah pratinjau halaman instruksi tugas yang akan dilihat oleh siswa.</p>
<hr>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Instruksi Tugas</h5>
        <div class="assignment-instructions">
            {!! nl2br(e($lesson->lessonable->instructions)) !!}
        </div>
        <hr>
        <p>
            <strong>Batas Waktu Pengumpulan:</strong> 
            {{ $lesson->lessonable->due_date ? $lesson->lessonable->due_date->format('d F Y, H:i') : 'Tidak ada' }}
        </p>
        <div class="text-center mt-4">
            <button class="btn btn-primary btn-lg" disabled>Kumpulkan Tugas</button>
        </div>
    </div>
</div>

<style>
    .assignment-instructions {
        line-height: 1.6;
        font-size: 15px;
        white-space: pre-wrap; 
    }
</style> --}}

{{-- resources/views/instructor/lessons/previews/_lessonassignment.blade.php --}}

{{-- @php
    // Cek apakah pengguna saat ini sudah mengumpulkan tugas untuk pelajaran ini
    $submission = null;
    if(Auth::check() && !$is_preview) {
        $submission = $lesson->lessonable->submissions()->where('user_id', Auth::id())->first();
    }
@endphp --}}

{{-- Menampilkan judul pelajaran di dalam modal --}}
{{-- <h4 class="font-weight-bold">{{ $lesson->title }}</h4>
<hr> --}}

{{-- <div class="assignment-container">

    <h5 class="font-weight-bold">Instruksi Tugas</h5>
    <div class="instructions mb-4" style="white-space: pre-wrap;">
        {!! nl2br(e($lesson->lessonable->instructions)) !!}
    </div>
    <p>
        <strong>Batas Waktu:</strong> 
        {{ $lesson->lessonable->due_date ? $lesson->lessonable->due_date->format('d F Y, H:i') : 'Tidak ada' }}
    </p>
    <hr>

  
    @if($submission)

        <div class="alert alert-success">
            <h5 class="font-weight-bold"><i class="fa fa-check-circle"></i> Anda Sudah Mengumpulkan Tugas</h5>
            <p>Berikut adalah detail pengumpulan Anda:</p>
            <ul>
                <li><strong>Nama File:</strong> <code>{{ basename($submission->file_path) }}</code></li>
                <li><strong>Waktu Mengumpulkan:</strong> {{ $submission->submitted_at->format('d F Y, H:i') }}</li>
            </ul>
      
        </div>
        
 
        @if(!is_null($submission->grade))
            <div class="card mt-3">
                <div class="card-header">
                    <strong>Penilaian dari Instruktur</strong>
                </div>
                <div class="card-block">
                    <p><strong>Nilai:</strong> {{ $submission->grade }}</p>
                    <p class="mb-0"><strong>Umpan Balik:</strong><br>{!! nl2br(e($submission->feedback)) !!}</p>
                </div>
            </div>
        @endif

    @else
  
        @if(!$is_preview)
            <h5 class="font-weight-bold">Kumpulkan Tugas Anda</h5>
            <form action="{{ route('student.assignment.submit', $lesson->lessonable->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="submission_file">Pilih File (PDF atau ZIP)</label>
                    <input type="file" name="submission_file" class="form-control" required accept=".pdf,.zip">
                    <small class="form-text text-muted">Ukuran file maksimal: 20MB.</small>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-upload"></i> Kumpulkan Tugas
                </button>
            </form>
        @else
     
            <div class="text-center text-muted">
                <p>Ini adalah pratinjau. Siswa akan melihat tombol untuk mengunggah file tugas di sini.</p>
                <button class="btn btn-primary" disabled><i class="fa fa-upload"></i> Kumpulkan Tugas</button>
            </div>
        @endif
    @endif
</div> --}}

@php
    $submission = null;
    if(Auth::check() && !$is_preview) {
        $submission = $lesson->lessonable->submissions()->where('user_id', Auth::id())->first();
    }
    $assignment = $lesson->lessonable;
@endphp

<h4 class="font-weight-bold">{{ $lesson->title }}</h4>
<hr>

<div class="assignment-container">


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
    <p><strong>Batas Waktu:</strong> {{ $assignment->due_date ? $assignment->due_date->format('d F Y, H:i') : 'Tidak ada' }}</p>
    <p><strong>Nilai Kelulusan Minimum:</strong> {{ $assignment->pass_mark }}</p>
    <hr>

</div>