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

@php
    // Cek apakah pengguna saat ini sudah mengumpulkan tugas untuk pelajaran ini
    $submission = null;
    if(Auth::check() && !$is_preview) {
        $submission = $lesson->lessonable->submissions()->where('user_id', Auth::id())->first();
    }
@endphp

{{-- Menampilkan judul pelajaran di dalam modal --}}
<h4 class="font-weight-bold">{{ $lesson->title }}</h4>
<hr>

<div class="assignment-container">
    {{-- Tampilkan instruksi tugas --}}
    <h5 class="font-weight-bold">Instruksi Tugas</h5>
    <div class="instructions mb-4" style="white-space: pre-wrap;">
        {!! nl2br(e($lesson->lessonable->instructions)) !!}
    </div>
    <p>
        <strong>Batas Waktu:</strong> 
        {{ $lesson->lessonable->due_date ? $lesson->lessonable->due_date->format('d F Y, H:i') : 'Tidak ada' }}
    </p>
    <hr>

    {{-- Tampilkan form unggah atau detail pengumpulan --}}
    @if($submission)
        {{-- Jika siswa sudah mengumpulkan --}}
        <div class="alert alert-success">
            <h5 class="font-weight-bold"><i class="fa fa-check-circle"></i> Anda Sudah Mengumpulkan Tugas</h5>
            <p>Berikut adalah detail pengumpulan Anda:</p>
            <ul>
                <li><strong>Nama File:</strong> <code>{{ basename($submission->file_path) }}</code></li>
                <li><strong>Waktu Mengumpulkan:</strong> {{ $submission->submitted_at->format('d F Y, H:i') }}</li>
            </ul>
            {{-- Anda bisa menambahkan logika untuk mengizinkan unggah ulang di sini jika perlu --}}
        </div>
        
        {{-- Tampilkan nilai jika sudah dinilai oleh instruktur --}}
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
        {{-- Jika siswa belum mengumpulkan (dan bukan mode pratinjau) --}}
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
            {{-- Tampilan untuk mode pratinjau --}}
            <div class="text-center text-muted">
                <p>Ini adalah pratinjau. Siswa akan melihat tombol untuk mengunggah file tugas di sini.</p>
                <button class="btn btn-primary" disabled><i class="fa fa-upload"></i> Kumpulkan Tugas</button>
            </div>
        @endif
    @endif
</div>