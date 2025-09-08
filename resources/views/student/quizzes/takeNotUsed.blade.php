@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10" id="quiz-title">{{ $attempt->quiz->title }}</h5>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-right font-weight-bold" id="quiz-timer"></div>
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
                            @if($is_preview)
                                <div class="alert alert-warning text-center">
                                    <strong>Mode Pratinjau</strong>
                                </div>
                            @endif

                            <form id="quiz-form" action="{{ route('student.quiz.submit', $is_preview ? 0 : $attempt->id) }}" method="POST">
                                @csrf
                                @if($is_preview)
                                    <input type="hidden" name="is_preview" value="true">
                                    <input type="hidden" name="quiz_id_preview" value="{{ $attempt->quiz->id }}">
                                @endif
                                
                                <div class="card">
                                    <div class="card-header">
                                        <div class="progress">
                                            <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <div class="card-block">
                                        {{-- SEMUA SOAL DI-RENDER DI SINI OLEH BLADE --}}
                                        @foreach($attempt->quiz->questions as $index => $question)
                                            <div class="question-slide" id="question-{{ $index }}" style="{{ $index > 0 ? 'display: none;' : '' }}; min-height: 300px;">
                                                <h5>Soal {{ $index + 1 }} dari {{ $attempt->quiz->questions->count() }}</h5>
                                                <p class="lead">{!! nl2br(e(str_replace(preg_match_all('/(\[\[BLANK_\d+\]\])/', $question->question_text, $matches) ? $matches[0] : [], '___', $question->question_text))) !!}</p>
                                                <hr>
                                                <div class="options-list">
                                                    @if($question->question_type === 'multiple_choice_single' || $question->question_type === 'true_false')
                                                        @foreach($question->options as $option)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" id="opt-{{ $option->id }}" value="{{ $option->id }}">
                                                            <label class="form-check-label" for="opt-{{ $option->id }}">{{ $option->option_text }}</label>
                                                        </div>
                                                        @endforeach
                                                    @elseif($question->question_type === 'multiple_choice_multiple')
                                                        @foreach($question->options as $option)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="answers[{{ $question->id }}][]" id="opt-{{ $option->id }}" value="{{ $option->id }}">
                                                            <label class="form-check-label" for="opt-{{ $option->id }}">{{ $option->option_text }}</label>
                                                        </div>
                                                        @endforeach
                                                    @elseif($question->question_type === 'drag_and_drop')
                                                        <div class="drag-and-drop-container" style="line-height: 2.5;">
                                                            {!!
                                                                preg_replace_callback('/\[\[(BLANK_\d+)\]\]/', function($matches) use ($question) {
                                                                    $blankId = $matches[1];
                                                                    $optionsHtml = '<option value="">-- Pilih Jawaban --</option>';
                                                                    foreach ($question->options as $option) {
                                                                        $optionsHtml .= '<option value="' . $option->id . '">' . e($option->option_text) . '</option>';
                                                                    }
                                                                    return '<select name="answers[' . $question->id . '][' . $blankId . ']" class="form-control d-inline-block" style="width: auto;">' . $optionsHtml . '</select>';
                                                                }, e($question->question_text))
                                                            !!}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="card-footer d-flex justify-content-between">
                                        <button type="button" id="prev-btn" class="btn btn-secondary" style="display: none;">Sebelumnya</button>
                                        <button type="button" id="next-btn" class="btn btn-primary">Selanjutnya</button>
                                        <button type="submit" id="finish-btn" class="btn btn-success" style="display: none;">Selesaikan Kuis</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const questions = document.querySelectorAll('.question-slide');
    let currentQuestionIndex = 0;

    const progressBar = document.getElementById('progress-bar');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const finishBtn = document.getElementById('finish-btn');

    function showQuestion(index) {
        questions.forEach((question, i) => {
            question.style.display = i === index ? 'block' : 'none';
        });
        updateUI();
    }

    function updateUI() {
        const progress = ((currentQuestionIndex + 1) / questions.length) * 100;
        progressBar.style.width = `${progress}%`;
        progressBar.innerText = `${Math.round(progress)}%`;

        prevBtn.style.display = currentQuestionIndex > 0 ? 'inline-block' : 'none';
        nextBtn.style.display = currentQuestionIndex < questions.length - 1 ? 'inline-block' : 'none';
        finishBtn.style.display = currentQuestionIndex === questions.length - 1 ? 'inline-block' : 'none';
    }

    nextBtn.addEventListener('click', () => {
        if (currentQuestionIndex < questions.length - 1) {
            currentQuestionIndex++;
            showQuestion(currentQuestionIndex);
        }
    });

    prevBtn.addEventListener('click', () => {
        if (currentQuestionIndex > 0) {
            currentQuestionIndex--;
            showQuestion(currentQuestionIndex);
        }
    });

    // Inisialisasi tampilan
    showQuestion(0);
});
</script>
@endpush