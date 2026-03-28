@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Hasil Kuis: {{ $attempt->quiz->title }}</h5>
                        <p class="m-b-0">Kursus: {{ $attempt->quiz->lesson->module->course->title }}</p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="#"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('student.courses.show', $attempt->quiz->lesson->module->course->slug) }}">Kursus</a></li>
                        <li class="breadcrumb-item"><a href="#!">Hasil Kuis</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
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
                                    @if ($is_preview)
                                        <h2 class="text-warning">Hasil Preview Quiz</h2>
                                    @endif
                                    @if($attempt->status == 'passed')
                                        <h2 class="text-success">Selamat, Anda Lulus!</h2>
                                        <i class="fa fa-check-circle fa-4x text-success mb-3"></i>
                                    @else
                                        <h2 class="text-danger">Sayang sekali, Anda Gagal.</h2>
                                        <i class="fa fa-times-circle fa-4x text-danger mb-3"></i>
                                    @endif

                                    @if(!$is_preview && $attempt->expelled_by_violation)
                                        <div class="alert alert-danger mt-3 mb-0">
                                            <i class="fa fa-ban"></i>
                                            <strong>Kuis ini diakhiri paksa</strong> karena Anda melebihi batas pelanggaran integritas yang diizinkan.
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
                                                        <h4 class="text-primary mb-0"><strong>{{ rtrim(rtrim(number_format($studentScoreScaled, 2, ',', '.'), '0'), ',') }}</strong></h4>
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
                                        <a href="{{ route('student.courses.show', ['course' => $attempt->quiz->lesson->module->course->slug, 'preview' => 'true']) }}" class="btn btn-primary">Kembali ke Preview Kursus</a>
                                    @else
                                        <a href="{{ route('student.courses.show', $attempt->quiz->lesson->module->course->slug) }}" class="btn btn-primary">Kembali ke Kursus</a>
                                    @endif

                                </div>
                            </div>

                            @if ($attempt->quiz->reveal_answers)
                                                            <!-- Rincian Jawaban -->
                            <div class="card">
                                <div class="card-header">
                                    <h5>Rincian Jawaban</h5>
                                </div>
                                <div class="card-block">
                                    @foreach($attempt->quiz->questions as $index => $question)
                                        <div class="mb-5">
                                            <h6>Soal {{ $index + 1 }}:</h6>
                                            <p class="lead">{!! nl2br(e(str_replace(preg_match_all('/(\[\[BLANK_\d+\]\])/', $question->question_text, $matches) ? $matches[0] : [], '___', $question->question_text))) !!}</p>

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
                                                            <span class="answer-option__text">{{ $option->option_text }}</span>
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
                                                    {!! nl2br(e($question->explanation)) !!}
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