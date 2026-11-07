@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], ['Hasil Kuis', route('instructor.quiz.results', $attempt->quiz_id)], ['Periksa', null]]">
        <x-slot:title>Periksa Jawaban Kuis</x-slot:title>
        <x-slot:subtitle>Siswa: <strong>{{ $attempt->student->name }}</strong> | Kuis: <strong>{{ $attempt->quiz->title }}</strong></x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('instructor.quiz.monitoring.detail', $attempt) }}" class="btn btn-secondary btn-sm" title="Lihat Detail Monitoring">
             <i class="fa fa-eye"></i> Detail Pelanggaran
             </a>
            <a href="{{ route('instructor.quiz.results', $attempt->quiz_id) }}" class="btn btn-secondary btn-sm">
             <i class="fa fa-arrow-left"></i> Kembali
             </a>
        </x-slot:actions>
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
                                    @if($attempt->status == 'passed')
                                        <h4 class="text-success">Status: Lulus</h4>
                                    @else
                                        <h4 class="text-danger">Status: Gagal</h4>
                                    @endif
                                    
                                    <!-- Informasi Skor -->
                                    {{-- <div class="row mt-4">
                                        <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body text-center py-3">
                                                    <h6 class="card-title mb-1">Skor Siswa</h6>
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
                                    </div> --}}

                                    <!-- Informasi Nilai -->
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="card bg-light">
                                                <div class="card-body text-center py-3">
                                                    <h6 class="card-title mb-1">Nilai Siswa</h6>
                                                    <h4 class="text-primary mb-0"><strong>{{ rtrim(rtrim(number_format($studentScoreScaled, 2, ',', '.'), '0'), ',') }}</strong></h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card bg-light">
                                                <div class="card-body text-center py-3">
                                                    <h6 class="card-title mb-1">Nilai Minimum</h6>
                                                    <h4 class="text-warning mb-0"><strong>{{ rtrim(rtrim(number_format($minimumScoreScaled, 2, ',', '.'), '0'), ',') }}</strong></h4>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- <div class="col-md-4">
                                            <div class="card bg-light">
                                                <div class="card-body text-center py-3">
                                                    <h6 class="card-title mb-1">Passing Grade</h6>
                                                    <h4 class="text-info mb-0"><strong>{{ $attempt->quiz->pass_mark }}%</strong></h4>
                                                </div>
                                            </div>
                                        </div> --}}
                                    </div>

                                    <!-- Informasi Revisi Skor -->
                                    <div class="row mt-3">
                                        <div class="col-md-3">
                                            <div class="card {{ $attempt->revised_score !== null ? 'bg-info text-white' : 'bg-light' }}">
                                                <div class="card-body text-center py-3">
                                                    <h6 class="card-title mb-1">Skor Revisi</h6>
                                                    <h4 class="mb-0"><strong>{{ $attempt->revised_score !== null ? rtrim(rtrim(number_format($attempt->revised_score, 2, ',', '.'), '0'), ',') : '-' }}</strong></h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-light">
                                                <div class="card-body text-center py-3">
                                                    <h6 class="card-title mb-1">Direvisi Oleh</h6>
                                                    <h6 class="mb-0"><strong>{{ $attempt->revisedBy->name ?? '-' }}</strong></h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-light">
                                                <div class="card-body text-center py-3">
                                                    <h6 class="card-title mb-1">Waktu Revisi</h6>
                                                    <h6 class="mb-0"><strong>{{ $attempt->revised_at ? $attempt->revised_at->format('d M Y H:i') : '-' }}</strong></h6>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="card bg-light">
                                                <div class="card-body text-center py-3">
                                                    <h6 class="card-title mb-1">Catatan Revisi</h6>
                                                    <small><strong>{{ $attempt->revision_note ?? '-' }}</strong></small>
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
                                                        $isCorrectAnswer = $option->is_correct;
                                                        $labelClass = '';
                                                        if ($isStudentAnswer && $isCorrectAnswer) { $labelClass = 'answer-option--correct'; } 
                                                        elseif ($isStudentAnswer && !$isCorrectAnswer) { $labelClass = 'answer-option--incorrect'; } 
                                                        elseif (!$isStudentAnswer && $isCorrectAnswer) { $labelClass = 'answer-option--key'; }
                                                    @endphp
                                                    <div class="answer-option {{ $labelClass }}">
                                                        <div class="answer-option__content">
                                                            <span class="answer-option__text rs-rich">{!! $question->displayOption($option) !!}</span>
                                                            <span class="answer-option__label">
                                                                @if($isStudentAnswer)
                                                                    <i class="fa fa-check-circle-o"></i> <strong>Jawaban Siswa</strong>
                                                                @elseif($isCorrectAnswer)
                                                                    <i class="fa fa-check"></i> <strong>Kunci Jawaban</strong>
                                                                @else
                                                                    <i class="fa fa-circle-o" style="opacity: 0.5;"></i>
                                                                @endif
                                                            </span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @if($question->explanation)
                                                <div class="alert alert-info mt-3">
                                                    <strong><i class="fa fa-lightbulb-o"></i> Penjelasan:</strong><br>
                                                    {!! $question->displayExplanation() !!}
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