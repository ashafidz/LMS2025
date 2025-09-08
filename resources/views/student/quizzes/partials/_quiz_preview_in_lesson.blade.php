{{-- resources/views/student/quizzes/partials/_quiz_preview_in_lesson.blade.php --}}

<div class="card">
    <div class="card-body">
        <h5 class="card-title">{{ $lesson->lessonable->title }}</h5>
        <p class="card-text">
            {!! nl2br(e($lesson->lessonable->description)) !!}
        </p>
        <ul class="list-group list-group-flush">
            {{-- Informasi Kuis (Jumlah Soal, Skor, Waktu, dll.) --}}
            <li class="list-group-item d-flex justify-content-between">
                <span><i class="fa fa-question-circle-o mr-2"></i> Jumlah Soal</span>
                <strong>{{ $lesson->lessonable->questions->count() }} soal</strong>
            </li>
            @if(isset($maxScore))
            <li class="list-group-item d-flex justify-content-between">
                <span><i class="fa fa-trophy mr-2"></i> Total Skor Maksimal</span>
                <strong>{{ $maxScore }}</strong>
            </li>
            @endif
             <li class="list-group-item d-flex justify-content-between">
                <span><i class="fa fa-check-square-o mr-2"></i> Skor Minimum</span>
                <strong>{{ rtrim(rtrim(number_format($minimumScore, 2, ',', '.'), '0'), ',') }}</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span><i class="fa fa-check-square-o mr-2"></i> Passing Grade Kelulusan</span>
                <strong>{{ $lesson->lessonable->pass_mark }}%</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span><i class="fa fa-check-square-o mr-2"></i> Nilai Minimum</span>
                <strong>{{ rtrim(rtrim(number_format($minimumScoreScaled, 2, ',', '.'), '0'), ',') }}</strong>
            </li>



            <li class="list-group-item d-flex justify-content-between">
                <span><i class="fa fa-clock-o mr-2"></i> Batas Waktu</span>
                <strong>{{ $lesson->lessonable->time_limit ? $lesson->lessonable->time_limit . ' menit' : 'Tidak ada' }}</strong>
            </li>

            {{-- Informasi Percobaan (Hanya untuk Siswa) --}}
            @if(!$is_preview && isset($attemptCount))
                <li class="list-group-item d-flex justify-content-between">
                    <span><i class="fa fa-repeat mr-2"></i> Kesempatan Anda</span>
                    <strong>{{ $attemptCount }} / {{ $lesson->lessonable->max_attempts ?? '∞' }}</strong>
                </li>
                @if(isset($lastAttempt) && $lastAttempt)
                    <li class="list-group-item d-flex justify-content-between">
                        <span><i class="fa fa-star mr-2"></i> Skor Terakhir Anda</span>
                        <strong>
                            {{ rtrim(rtrim($lastAttempt->score, '0'), '.') }}
                            @if($lastAttempt->status == 'passed')
                                <span class="badge badge-success ml-2">Lulus</span>
                            @elseif($lastAttempt->status == 'in_progress')
                                <span class="badge badge-warning ml-2">Sedang Dikerjakan</span>
                            @else
                                <span class="badge badge-danger ml-2">Gagal</span>
                            @endif
                        </strong>
                    </li>
                @endif
            @endif

            <li class="list-group-item d-flex justify-content-between">
                <span><i class="fa fa-clock-o mr-2"></i> Boleh Melebihi Batas Waktu</span>
                <strong>{{ $lesson->lessonable->allow_exceed_time_limit == 1 ? 'Ya' : 'Tidak' }}</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span><i class="fa fa-clock-o mr-2"></i> Tersedia Mulai</span>
                <strong>{{ $lesson->lessonable->available_from ? $lesson->lessonable->available_from->format('d F Y, H:i') : '-' }}</strong>
            </li>
            <li class="list-group-item d-flex justify-content-between">
                <span><i class="fa fa-clock-o mr-2"></i> Tutup Pada</span>
                <strong>{{ $lesson->lessonable->available_to ? $lesson->lessonable->available_to->format('d F Y, H:i') : '-' }}</strong>
            </li>


        </ul>
        @if ($lesson->lessonable->allow_exceed_time_limit == 1)
            <p class="mt-2 text-center text-danger">
                Quiz ini boleh melebihi batas waktu, tetapi anda perlu menyelesaikan kuis sebelum waktu habis untuk mendapatkan poin dan dinyatakan selesai dalam lesson ini.
            </p>
        @endif

        {{-- Logika Tombol yang Diperbarui --}}
        <div class="text-center mt-4">
            @if($is_preview)
                {{-- Tombol untuk mode pratinjau (Admin/Instruktur) --}}
                <a href="{{ route('student.quiz.start', $quiz->id) }}" 
   class="btn btn-primary" 
   style="padding:6px 16px; font-size:14px;">
   Mulai Kuis
</a>
            @else
                {{-- Logika untuk siswa biasa --}}
                @php
                    $quiz = $lesson->lessonable;
                    // Pastikan attemptCount ada, jika tidak, anggap 0
                    $currentAttemptCount = $attemptCount ?? 0;
                    // Cek apakah siswa masih punya kesempatan
                    $canAttempt = is_null($quiz->max_attempts) || $currentAttemptCount < $quiz->max_attempts;
                @endphp

                {{-- Tombol "Lihat Hasil" akan selalu muncul jika sudah pernah mencoba --}}
                {{-- @if(isset($lastAttempt) && $lastAttempt)
                    <a href="{{ route('student.quiz.result', $lastAttempt->id) }}" class="btn btn-info btn-lg">Lihat Hasil Terakhir</a>
                @endif --}}

                @if ($isAvailable)
                    {{-- Tombol "Mulai Kuis/Coba Lagi" hanya muncul jika masih ada kesempatan --}}
                    @if ($canAttempt)
                        @if (!isset($lastAttempt) || $lastAttempt->status != 'in_progress')
                            <a href="{{ route('student.quiz.start', $quiz->id) }}" class="btn btn-primary btn-md">
                                {{-- Ganti teks tombol jika sudah pernah mencoba --}}
                                {{ $currentAttemptCount > 0 ? 'Coba Lagi' : 'Mulai Kuis' }}
                            </a>
                        @else
                            <a href="{{ route('student.quiz.start', $quiz->id) }}" class="btn btn-primary btn-md">
                                Lanjutkan
                            </a>
                        @endif
                    @elseif(!isset($lastAttempt))
                        {{-- Jika belum pernah mencoba tapi kesempatan habis (kasus aneh, tapi untuk jaga-jaga) --}}
                        <a href="{{ route('student.quiz.start', $quiz->id) }}" class="btn btn-primary btn-md">Mulai Kuis</a>
                    @else
                        {{-- Tampilkan pesan jika kesempatan sudah habis dan sudah pernah mencoba --}}
                        <p class="text-danger mt-2">Anda telah mencapai batas maksimal pengerjaan.</p>
                    @endif
                @else
                    <p class="text-danger mt-2">Kuis ini tidak tersedia saat ini.</p>
                @endif

            @endif
        </div>
    </div>
</div>


{{-- TAMBAHKAN KODE DI BAWAH INI --}}
@if (!$is_preview && isset($allAttempts) && $allAttempts->isNotEmpty())
    @include('student.quizzes.partials._quiz_attempt_history', ['allAttempts' => $allAttempts])
@endif
