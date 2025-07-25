{{-- resources/views/student/quizzes/partials/_quiz_preview_in_lesson.blade.php --}}

<div class="card">
    <div class="card-body">
        <h5 class="card-title">{{ $lesson->lessonable->title }}</h5>
        <p class="card-text">
            {!! nl2br(e($lesson->lessonable->description)) !!}
        </p>
        <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between">
                <span><i class="fa fa-question-circle-o mr-2"></i> Jumlah Soal</span>
                <strong>{{ $lesson->lessonable->questions->count() }} soal</strong>
            </li>

            {{-- BARIS BARU: Menampilkan Total Skor Maksimal --}}
            @if(isset($maxScore))
            <li class="list-group-item d-flex justify-content-between">
                <span><i class="fa fa-trophy mr-2"></i> Total Skor Maksimal</span>
                <strong>{{ $maxScore }} poin</strong>
            </li>
            @endif

            <li class="list-group-item d-flex justify-content-between">
                <span><i class="fa fa-check-square-o mr-2"></i> Nilai Kelulusan</span>
                <strong>{{ $lesson->lessonable->pass_mark }}%</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span><i class="fa fa-clock-o mr-2"></i> Batas Waktu</span>
                <strong>{{ $lesson->lessonable->time_limit ? $lesson->lessonable->time_limit . ' menit' : 'Tidak ada' }}</strong>
            </li>
            
            @if(isset($attemptCount))
                <li class="list-group-item d-flex justify-content-between">
                    <span><i class="fa fa-repeat mr-2"></i> Jumlah Percobaan</span>
                    <strong>{{ $attemptCount }} kali</strong>
                </li>
                @if($lastAttempt)
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fa fa-star mr-2"></i> Skor Terakhir Anda</span>
                        <strong>
                            {{ rtrim(rtrim($lastAttempt->score, '0'), '.') }}
                            @if($lastAttempt->status == 'passed')
                                <span class="badge badge-success ml-2">Lulus</span>
                            @else
                                <span class="badge badge-danger ml-2">Gagal</span>
                            @endif
                        </strong>
                    </li>
                @endif
            @endif
        </ul>
        <div class="text-center mt-4">
            @if(isset($lastAttempt) && $lastAttempt->status != 'failed')
                <a href="{{ route('student.quiz.result', $lastAttempt->id) }}" class="btn btn-info btn-lg">Lihat Hasil Terakhir</a>
            @else
                <a href="{{ route('student.quiz.start', ['quiz' => $lesson->lessonable->id, 'preview' => $is_preview]) }}" 
                   class="btn btn-primary btn-lg" 
                   @if(!$is_preview) target="_blank" @endif>
                   Mulai Kuis
                </a>
            @endif
        </div>
    </div>
</div>