{{-- File ini hanya berisi konten pratinjau untuk pelajaran dokumen (PDF) --}}

<h4 class="font-weight-bold">{{ $lesson->title }}</h4>
<hr>

@if($lesson->lessonable->file_path)
    {{-- Menggunakan <embed> untuk menampilkan PDF secara langsung di browser --}}
    <div style="border: 1px solid #ddd; height: 70vh;">
        <embed src="{{ Storage::url($lesson->lessonable->file_path) }}" type="application/pdf" width="100%" height="100%" />
    </div>
    <p class="mt-2">
        <a href="{{ Storage::url($lesson->lessonable->file_path) }}" target="_blank">(Unduh Dokumen)</a>
    </p>
@else
    <p class="text-muted text-center">Tidak ada file dokumen yang terhubung dengan pelajaran ini.</p>
@endif