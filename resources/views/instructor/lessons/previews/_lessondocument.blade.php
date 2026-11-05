{{-- File ini hanya berisi konten pratinjau untuk pelajaran dokumen (PDF) --}}

<h4 class="font-weight-bold">{{ $lesson->title }}</h4>
<hr>

@if($lesson->lessonable->file_path)
    @php
        $docUrl = route('files.lesson.document', [
            'lesson' => $lesson,
            'v' => substr(md5((string) $lesson->lessonable->file_path), 0, 8),
        ]);
    @endphp
    {{-- Menggunakan <embed> untuk menampilkan PDF secara langsung di browser --}}
    <div class="rs-doc-viewer" data-doc-viewer>
        <embed src="{{ $docUrl }}" type="application/pdf" width="100%" height="100%" />
    </div>
    <p class="mt-2">
        <a href="{{ $docUrl }}" target="_blank" rel="noopener">Buka dokumen di tab baru</a>
    </p>
@else
    <p class="text-muted text-center">Tidak ada file dokumen yang terhubung dengan pelajaran ini.</p>
@endif