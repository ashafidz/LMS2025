{{-- File ini hanya berisi konten pratinjau untuk pelajaran dokumen (PDF) --}}

<h4 class="font-weight-bold">{{ $lesson->title }}</h4>
<hr>

@if($lesson->lessonable->file_path)
    {{-- Menggunakan <embed> untuk menampilkan PDF secara langsung di browser --}}
    <div class="rs-doc-viewer" data-doc-viewer>
        <embed src="{{ route('files.lesson.document', $lesson) }}" type="application/pdf" width="100%" height="100%" />
    </div>
    <p class="mt-2">
        <a href="{{ route('files.lesson.document', $lesson) }}" target="_blank" rel="noopener">Buka dokumen di tab baru</a>
    </p>
@else
    <p class="text-muted text-center">Tidak ada file dokumen yang terhubung dengan pelajaran ini.</p>
@endif