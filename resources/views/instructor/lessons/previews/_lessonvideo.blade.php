{{-- File ini hanya berisi konten pratinjau untuk pelajaran video --}}

<h4 class="font-weight-bold">{{ $lesson->title }}</h4>
<hr>

@if($lesson->lessonable->video_path)
    @if($lesson->lessonable->source_type == 'upload')
        {{-- Tampilkan pemutar untuk video yang diunggah --}}
        <video width="100%" controls controlsList="nodownload">
            <source src="{{ Storage::url($lesson->lessonable->video_path) }}" type="video/mp4">
            Browser Anda tidak mendukung tag video.
        </video>

    @elseif($lesson->lessonable->source_type == 'youtube')
        {{-- Sematkan video dari YouTube --}}
        @php
            // Ekstrak ID video dari berbagai format URL YouTube
            preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $lesson->lessonable->video_path, $match);
            $youtube_id = $match[1] ?? null;
        @endphp

        @if($youtube_id)
            <div class="embed-responsive embed-responsive-16by9">
                <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/{{ $youtube_id }}" allowfullscreen></iframe>
            </div>
        @else
            <div class="alert alert-warning">
                URL YouTube tidak valid atau tidak dapat diproses. URL: {{ $lesson->lessonable->video_path }}
            </div>
        @endif
    @endif
@else
    <p class="text-muted text-center">Tidak ada video yang terhubung dengan pelajaran ini.</p>
@endif