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
                <div class="col-md-4">
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
                                    @if($attempt->status == 'passed')
                                        <h2 class="text-success">Selamat, Anda Lulus!</h2>
                                        <i class="fa fa-check-circle fa-4x text-success mb-3"></i>
                                    @else
                                        <h2 class="text-danger">Sayang sekali, Anda Gagal.</h2>
                                        <i class="fa fa-times-circle fa-4x text-danger mb-3"></i>
                                    @endif

                                    <h4>Skor Anda: <strong>{{ rtrim(rtrim(number_format($attempt->score, 2, ',', '.'), '0'), ',') }}</strong></h4>
                                    <p class="text-muted">Nilai Kelulusan Minimum: {{ $attempt->quiz->pass_mark }}%</p>
                                    <hr>
                                    <a href="{{ route('student.courses.show', $attempt->quiz->lesson->module->course->slug) }}" class="btn btn-primary">Kembali ke Kursus</a>
                                </div>
                            </div>

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
                                                            $labelClass = 'bg-success text-white'; // Jawaban siswa, dan itu benar
                                                        } elseif ($isStudentAnswer && !$isCorrectAnswer) {
                                                            $labelClass = 'bg-danger text-white'; // Jawaban siswa, tapi salah
                                                        } elseif (!$isStudentAnswer && $isCorrectAnswer) {
                                                            $labelClass = 'bg-info text-white'; // Bukan jawaban siswa, tapi ini kunci jawaban
                                                        }
                                                    @endphp
                                                    <div class="p-2 rounded mb-2 {{ $labelClass }}">
                                                        @if($isStudentAnswer && $isCorrectAnswer)
                                                            <i class="fa fa-check-circle-o mr-2"></i> <strong>Jawaban Anda (Benar)</strong>
                                                        @elseif($isStudentAnswer && !$isCorrectAnswer)
                                                            <i class="fa fa-times-circle-o mr-2"></i> <strong>Jawaban Anda (Salah)</strong>
                                                        @elseif(!$isStudentAnswer && $isCorrectAnswer)
                                                            <i class="fa fa-check mr-2"></i> <strong>Kunci Jawaban</strong>
                                                        @else
                                                            <i class="fa fa-circle-o mr-2" style="opacity: 0.5;"></i>
                                                        @endif
                                                        {{ $option->option_text }}
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection