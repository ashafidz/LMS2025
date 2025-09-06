@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Periksa Jawaban Kuis</h5>
                        <p class="m-b-0">Siswa: <strong>{{ $attempt->student->name }}</strong> | Kuis: <strong>{{ $attempt->quiz->title }}</strong></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('instructor.quiz.results', $attempt->quiz_id) }}">Hasil Kuis</a></li>
                        <li class="breadcrumb-item"><a href="#!">Periksa</a></li>
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
                                        <h4 class="text-success">Status: Lulus</h4>
                                    @else
                                        <h4 class="text-danger">Status: Gagal</h4>
                                    @endif
                                    
                                    <!-- Informasi Skor -->
                                    <div class="row mt-4">
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body text-center py-3">
                                                    <h6 class="card-title mb-1">Skor Student</h6>
                                                    <h4 class="text-primary mb-0"><strong>{{ rtrim(rtrim(number_format($attempt->score, 2, ',', '.'), '0'), ',') }}</strong></h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body text-center py-3">
                                                    <h6 class="card-title mb-1">Skor Minimum</h6>
                                                    <h4 class="text-warning mb-0"><strong>{{ rtrim(rtrim(number_format($minimumScore, 2, ',', '.'), '0'), ',') }}</strong></h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body text-center py-3">
                                                    <h6 class="card-title mb-1">Skor Maksimum</h6>
                                                    <h4 class="text-info mb-0"><strong>{{ rtrim(rtrim(number_format($totalMaxScore, 2, ',', '.'), '0'), ',') }}</strong></h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Informasi Nilai -->
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body text-center py-3">
                                                    <h6 class="card-title mb-1">Nilai Student</h6>
                                                    <h4 class="text-primary mb-0"><strong>{{ rtrim(rtrim(number_format($studentScoreScaled, 2, ',', '.'), '0'), ',') }}</strong></h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body text-center py-3">
                                                    <h6 class="card-title mb-1">Nilai Minimum</h6>
                                                    <h4 class="text-warning mb-0"><strong>{{ rtrim(rtrim(number_format($minimumScoreScaled, 2, ',', '.'), '0'), ',') }}</strong></h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body text-center py-3">
                                                    <h6 class="card-title mb-1">Passing Grade</h6>
                                                    <h4 class="text-info mb-0"><strong>{{ $attempt->quiz->pass_mark }}%</strong></h4>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Rincian Jawaban -->
                            <div class="card">
                                <div class="card-header">
                                    <h5>Rincian Jawaban Siswa</h5>
                                </div>
                                <div class="card-block">
                                    @foreach($attempt->quiz->questions as $index => $question)
                                        <div class="mb-5">
                                            <h6>Soal {{ $index + 1 }}:</h6>
                                            <p class="lead">{!! nl2br(e($question->question_text)) !!}</p>
                                            @php
                                                $studentAnswersForThisQuestion = $attempt->answers->where('question_id', $question->id);
                                                $studentAnswerIds = $studentAnswersForThisQuestion->pluck('selected_option_id')->toArray();
                                                $isQuestionCorrect = $studentAnswersForThisQuestion->isNotEmpty() && $studentAnswersForThisQuestion->first()->is_correct;
                                            @endphp
                                            <div class="options-review">
                                                @foreach($question->options as $option)
                                                    @php
                                                        $isStudentAnswer = in_array($option->id, $studentAnswerIds);
                                                        $isCorrectAnswer = $option->is_correct;
                                                        $labelClass = '';
                                                        if ($isStudentAnswer && $isCorrectAnswer) { $labelClass = 'bg-success text-white'; } 
                                                        elseif ($isStudentAnswer && !$isCorrectAnswer) { $labelClass = 'bg-danger text-white'; } 
                                                        elseif (!$isStudentAnswer && $isCorrectAnswer) { $labelClass = 'bg-info text-white'; }
                                                    @endphp
                                                    <div class="p-2 rounded mb-2 {{ $labelClass }}">
                                                        @if($isStudentAnswer)
                                                            <i class="fa fa-check-circle-o mr-2"></i> <strong>Jawaban Siswa</strong>
                                                        @elseif($isCorrectAnswer)
                                                            <i class="fa fa-check mr-2"></i> <strong>Kunci Jawaban</strong>
                                                        @else
                                                            <i class="fa fa-circle-o mr-2" style="opacity: 0.5;"></i>
                                                        @endif
                                                        {{ $option->option_text }}
                                                    </div>
                                                @endforeach
                                            </div>
                                            @if($question->explanation)
                                                <div class="alert alert-info mt-3">
                                                    <strong><i class="fa fa-lightbulb-o"></i> Penjelasan:</strong><br>
                                                    {!! nl2br(e($question->explanation)) !!}
                                                </div>
                                            @endif
                                        </div>
                                        @if(!$loop->last)<hr>@endif
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