@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    @php
        [$previewHome, $previewList] = $is_preview ? \App\Services\LessonAccess::previewCrumbs(session('active_role')) : [null, null];
    @endphp
    <x-dash.page-head :crumbs="[$is_preview ? $previewHome : ['__home', route('student.dashboard')], $is_preview ? $previewList : ['Kursus Saya', route('student.my_courses')], [$attempt->quiz->lesson->module->course->title, route('student.courses.show', $attempt->quiz->lesson->module->course->slug) . ($is_preview ? '?preview=true' : '')], ['Hasil Kuis', null]]">
        <x-slot:title>Hasil Kuis: {{ $attempt->quiz->title }}</x-slot:title>
        <x-slot:subtitle>Kursus: {{ $attempt->quiz->lesson->module->course->title }}</x-slot:subtitle>
    </x-dash.page-head>
    <!-- Page-header end -->

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="row d-flex justify-content-center">
                        <div class="col-md-10 col-lg-8">
                            <!-- Kartu Hasil Ringkas -->
                            <div class="card">
                                <div class="card-body text-center">
                                    <div class="rs-result-head"@if ($attempt->status == 'passed' && ! $is_preview) data-rs-celebrate="quiz-{{ $attempt->id }}"@endif>
                                        <x-rs.illustration :name="$attempt->status == 'passed' ? 'rs-13-kuis-lulus' : 'rs-14-kuis-coba-lagi'" size="sm" />
                                        <div class="rs-result-head__text">
                                            @if ($is_preview)
                                                <h2 class="text-warning">Hasil Pratinjau Kuis</h2>
                                            @endif
                                            @if($attempt->status == 'passed')
                                                <h2 class="text-success">Selamat, Anda Lulus!</h2>
                                                <i class="fa fa-check-circle fa-4x text-success mb-3"></i>
                                            @else
                                                <h2 class="text-danger">Sayang sekali, Anda Gagal.</h2>
                                                <i class="fa fa-times-circle fa-4x text-danger mb-3"></i>
                                            @endif
                                        </div>
                                    </div>

                                    @if(!$is_preview && $attempt->expelled_by_violation)
                                        <div class="alert alert-danger mt-3 mb-0">
                                            <i class="fa fa-ban"></i>
                                            <strong>Kuis ini diakhiri paksa</strong> karena Anda melebihi batas pelanggaran yang diizinkan.
                                        </div>
                                    @endif

                                    @if(!$is_preview && $attempt->failedBecauseLate())
                                        <div class="alert alert-warning mt-3 mb-0">
                                            <i class="fa fa-clock-o"></i>
                                            <strong>Waktu pengerjaan sudah habis.</strong> Jawaban Anda terkirim setelah batas waktu kuis.
                                            @if($attempt->scaled_score !== null && $attempt->scaled_score >= $attempt->quiz->pass_mark)
                                                Karena itu kuis ini dinyatakan tidak lulus walaupun nilainya mencukupi.
                                            @endif
                                        </div>
                                    @endif

                                    {{-- <h4>Skor Anda: <strong>{{ rtrim(rtrim(number_format($attempt->score, 2, ',', '.'), '0'), ',') }}</strong></h4> --}}
                                    
                                    <!-- Informasi Nilai Baru -->
                                    <div class="mt-4">
                                        <div class="row">
                                            {{-- Skor Student --}}
                                            {{-- <div class="col-md-4">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center py-3">
                                                        <h6 class="card-title mb-1">Skor Anda</h6>
                                                        <h4 class="text-primary mb-0"><strong>{{ rtrim(rtrim(number_format($attempt->score, 2, ',', '.'), '0'), ',') }}</strong></h4>
                                                    </div>
                                                </div>
                                            </div> --}}
                                            {{-- Skor Minimum --}}
                                            {{-- <div class="col-md-4">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center py-3">
                                                        <h6 class="card-title mb-1">Skor Minimum</h6>
                                                        <h4 class="text-info mb-0"><strong>{{ rtrim(rtrim(number_format($minimumScore, 2, ',', '.'), '0'), ',') }}</strong></h4>
                                                    </div>
                                                </div>
                                            </div> --}}

                                            {{-- Skor Maksimum --}}
                                            {{-- <div class="col-md-4">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center py-3">
                                                        <h6 class="card-title mb-1">Skor Maksimum</h6>
                                                        <h4 class="text-info mb-0"><strong>{{ rtrim(rtrim(number_format($maxPossibleScore, 2, ',', '.'), '0'), ',') }}</strong></h4>
                                                    </div>
                                                </div>
                                            </div> --}}
                                            {{--  --}}
                                            
                                            {{-- Nilai Student --}}
                                            <div class="col-md-6">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center py-3">
                                                        <h6 class="card-title mb-1">Nilai Anda</h6>
                                                        <h4 class="text-primary mb-0"><strong @if ($studentScoreScaled !== null) data-rs-countup="{{ round((float) $studentScoreScaled, 2) }}" @endif>{{ rtrim(rtrim(number_format($studentScoreScaled, 2, ',', '.'), '0'), ',') }}</strong></h4>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Nilai Minimum --}}
                                            <div class="col-md-6">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center py-3">
                                                        <h6 class="card-title mb-1">Nilai Minimum</h6>
                                                        <h4 class="text-info mb-0"><strong>{{ rtrim(rtrim(number_format($minimumScoreScaled, 2, ',', '.'), '0'), ',') }}</strong></h4>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Passing Grade --}}
                                            {{-- <div class="col-md-12">
                                                <div class="card bg-light">
                                                    <div class="card-body text-center py-3">
                                                        <h6 class="card-title mb-1">Passing Grade</h6>
                                                        <h4 class="text-info mb-0"><strong>{{ rtrim(rtrim(number_format($attempt->quiz->pass_mark, 2, ',', '.'), '0'), ',') }} %</strong></h4>
                                                    </div>
                                                </div>
                                            </div> --}}

                                        </div>
                                    </div>
                                    
                                    {{-- <p class="">Nilai Kelulusan Minimum: {{ $attempt->quiz->pass_mark }}%</p>
                                    <p class="" >Nilai Kelulusan Maksimum : {{ $maxPossibleScore }}</p>
                                    <p class="" >Nilai Kelulusan Minimum (dalam score) : {{ $minimumScore }}</p> --}}
                                    <hr>
                                    @if ($is_preview)
                                        <a href="{{ route('student.courses.show', ['course' => $attempt->quiz->lesson->module->course->slug, 'preview' => 'true']) }}" class="btn btn-primary">Kembali ke Pratinjau Kursus</a>
                                    @else
                                        <a href="{{ route('student.courses.show', $attempt->quiz->lesson->module->course->slug) }}" class="btn btn-primary">Kembali ke Kursus</a>
                                    @endif

                                </div>
                            </div>

                            @if ($attempt->quiz->reveal_answers || $is_preview)
                                @if ($is_preview && ! $attempt->quiz->reveal_answers)
                                    <div class="alert alert-info rs-preview-note">Siswa tidak melihat rincian ini karena pengaturan kuis menyembunyikan jawaban.</div>
                                @endif
                                                            <!-- Rincian Jawaban -->
                            <div class="card">
                                <div class="card-header">
                                    <h5>Rincian Jawaban</h5>
                                </div>
                                <div class="card-block">
                                    @foreach($attempt->quiz->questions as $index => $question)
                                        <div class="mb-5">
                                            <h6>Soal {{ $index + 1 }}:</h6>
                                            <div class="lead rs-rich">{!! $question->displayText() !!}</div>

                                            @php
                                                $studentAnswersForThisQuestion = $attempt->answers->where('question_id', $question->id);
                                                $studentAnswerIds = $studentAnswersForThisQuestion->pluck('selected_option_id')->toArray();
                                                $isQuestionCorrect = $studentAnswersForThisQuestion->isNotEmpty() && $studentAnswersForThisQuestion->first()->is_correct;
                                            @endphp

                                            <div class="options-review">
                                                @foreach($question->options as $option)
                                                    @php
                                                        $isStudentAnswer = in_array($option->id, $studentAnswerIds);
                                                        // LOGIKA DISEMPURNAKAN: Sekarang kita bisa percaya pada $option->is_correct
                                                        $isCorrectAnswer = $option->is_correct;
                                                        $labelClass = '';

                                                        if ($isStudentAnswer && $isCorrectAnswer) {
                                                            $labelClass = 'answer-option--correct'; // Jawaban siswa, dan itu benar
                                                        } elseif ($isStudentAnswer && !$isCorrectAnswer) {
                                                            $labelClass = 'answer-option--incorrect'; // Jawaban siswa, tapi salah
                                                        } elseif (!$isStudentAnswer && $isCorrectAnswer) {
                                                            $labelClass = 'answer-option--key'; // Bukan jawaban siswa, tapi ini kunci jawaban
                                                        }
                                                    @endphp
                                                    <div class="answer-option {{ $labelClass }}">
                                                        <div class="answer-option__content">
                                                            <span class="answer-option__text rs-rich">{!! $question->displayOption($option) !!}</span>
                                                            <span class="answer-option__label">
                                                                @if($isStudentAnswer && $isCorrectAnswer)
                                                                    <i class="fa fa-check-circle-o"></i> <strong>Jawaban Anda (Benar)</strong>
                                                                @elseif($isStudentAnswer && !$isCorrectAnswer)
                                                                    <i class="fa fa-times-circle-o"></i> <strong>Jawaban Anda (Salah)</strong>
                                                                @elseif(!$isStudentAnswer && $isCorrectAnswer)
                                                                    <i class="fa fa-check"></i> <strong>Kunci Jawaban</strong>
                                                                @else
                                                                    <i class="fa fa-circle-o" style="opacity: 0.5;"></i>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            @if($isQuestionCorrect && $question->explanation)
                                                <div class="alert alert-success mt-3">
                                                    <strong><i class="fa fa-lightbulb-o"></i> Penjelasan:</strong><br>
                                                    {!! $question->displayExplanation() !!}
                                                </div>
                                            @endif
                                        </div>
                                        @if(!$loop->last)
                                            <hr>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                            @else
                                <div class="alert alert-info">
                                    Jawaban Anda telah disembunyikan oleh penyelenggara kursus ini.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection