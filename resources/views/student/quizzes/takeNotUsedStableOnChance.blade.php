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
                    @if($is_preview)
                        <div class="alert alert-warning text-center">
                            <strong>Mode Pratinjau</strong>
                        </div>
                    @endif

                    <div class="row">
                        <!-- KOLOM KIRI: KONTEN KUIS -->
                        <div class="col-lg-9">
                            <form id="quiz-form" action="{{ route('student.quiz.submit', $is_preview ? 0 : $attempt->id) }}" method="POST">
                                @csrf
                                @if($is_preview)
                                    <input type="hidden" name="is_preview" value="true">
                                    <input type="hidden" name="quiz_id_preview" value="{{ $attempt->quiz->id }}">
                                @endif
                                
                                <div class="card">
                                    <div class="card-header">
                                        <div class="progress">
                                            <div id="progress-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0% Terjawab</div>
                                        </div>
                                    </div>
                                    <div class="card-block">
                                        {{-- Render semua soal di sini, tapi disembunyikan --}}
                                        @foreach($attempt->quiz->questions as $index => $question)
                                            <div class="question-slide" id="question-{{ $index }}" data-question-id="{{ $question->id }}" style="{{ $index > 0 ? 'display: none;' : '' }}; min-height: 300px;">
                                                <h5>Soal {{ $index + 1 }} dari {{ $attempt->quiz->questions->count() }}</h5>
                                                <p class="lead">{!! nl2br(e(str_replace(preg_match_all('/(\[\[BLANK_\d+\]\])/', $question->question_text, $matches) ? $matches[0] : [], '___', $question->question_text))) !!}</p>
                                                <hr>
                                                <div class="options-list" id="options-for-{{ $question->id }}">
                                                    @if($question->question_type === 'multiple_choice_single' || $question->question_type === 'true_false')
                                                        @foreach($question->options as $option)
                                                        <div class="form-check"><input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" id="opt-{{ $option->id }}" value="{{ $option->id }}"><label class="form-check-label" for="opt-{{ $option->id }}">{{ $option->option_text }}</label></div>
                                                        @endforeach
                                                    @elseif($question->question_type === 'multiple_choice_multiple')
                                                        @foreach($question->options as $option)
                                                        <div class="form-check"><input class="form-check-input" type="checkbox" name="answers[{{ $question->id }}][]" id="opt-{{ $option->id }}" value="{{ $option->id }}"><label class="form-check-label" for="opt-{{ $option->id }}">{{ $option->option_text }}</label></div>
                                                        @endforeach
                                                        <button type="button" class="btn btn-sm btn-info mt-2 check-answer-btn">Cek Jawaban</button>
                                                    @elseif($question->question_type === 'drag_and_drop')
                                                        <div class="drag-and-drop-container" style="line-height: 2.5;">{!! preg_replace_callback('/\[\[(BLANK_\d+)\]\]/', function($matches) use ($question) { $blankId = $matches[1]; $optionsHtml = '<option value="">-- Pilih Jawaban --</option>'; foreach ($question->options as $option) { $optionsHtml .= '<option value="' . $option->id . '">' . e($option->option_text) . '</option>'; } return '<select name="answers[' . $question->id . '][' . $blankId . ']" class="form-control d-inline-block" style="width: auto;">' . $optionsHtml . '</select>'; }, e($question->question_text)) !!}</div>
                                                        <button type="button" class="btn btn-sm btn-info mt-2 check-answer-btn">Cek Jawaban</button>
                                                    @endif
                                                </div>
                                                {{-- Kontainer untuk menampilkan feedback instan --}}
                                                <div class="feedback-container mt-3"></div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="card-footer d-flex justify-content-between">
                                        <button type="button" id="prev-btn" class="btn btn-secondary" style="display: none;">Sebelumnya</button>
                                        <button type="button" id="next-btn" class="btn btn-primary">Selanjutnya</button>
                                        <button type="button" id="finish-btn" class="btn btn-success" style="display: none;">Selesaikan Kuis</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- KOLOM KANAN: NAVIGASI SOAL -->
                        <div class="col-lg-3">
                            <div class="card">
                                <div class="card-header"><h5>Navigasi Soal</h5></div>
                                <div class="card-block"><div id="question-navigation" class="d-flex flex-wrap"></div></div>
                            </div>
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
    const questionNavContainer = document.getElementById('question-navigation');
    const form = document.getElementById('quiz-form');

    function createNavigation() {
        questions.forEach((_, index) => {
            const navBtn = document.createElement('button');
            navBtn.type = 'button';
            navBtn.className = 'btn btn-outline-secondary m-1';
            navBtn.innerText = index + 1;
            navBtn.dataset.index = index;
            navBtn.id = `nav-btn-${index}`;
            navBtn.addEventListener('click', () => {
                currentQuestionIndex = index;
                showQuestion(currentQuestionIndex);
            });
            questionNavContainer.appendChild(navBtn);
        });
    }

    function showQuestion(index) {
        questions.forEach((question, i) => {
            question.style.display = i === index ? 'block' : 'none';
        });
        updateUI();
    }

    function updateUI() {
        prevBtn.style.display = currentQuestionIndex > 0 ? 'inline-block' : 'none';
        nextBtn.style.display = currentQuestionIndex < questions.length - 1 ? 'inline-block' : 'none';
        finishBtn.style.display = currentQuestionIndex === questions.length - 1 ? 'inline-block' : 'none';
        updateNavigationStatus();
        updateProgressBar();
    }

    function updateNavigationStatus() {
        const navButtons = questionNavContainer.querySelectorAll('button');
        navButtons.forEach((btn, index) => {
            const feedbackContainer = document.querySelector(`#question-${index} .feedback-container`);
            const isAnswered = feedbackContainer && feedbackContainer.innerHTML.trim() !== '';

            if (index === currentQuestionIndex) {
                btn.className = 'btn btn-primary m-1';
            } else if (isAnswered) {
                btn.className = 'btn btn-secondary m-1';
            } else {
                btn.className = 'btn btn-outline-secondary m-1';
            }
        });
    }

    function updateProgressBar() {
        let answeredCount = 0;
        questions.forEach((_, index) => {
            const feedbackContainer = document.querySelector(`#question-${index} .feedback-container`);
            if (feedbackContainer && feedbackContainer.innerHTML.trim() !== '') {
                answeredCount++;
            }
        });

        const progress = questions.length > 0 ? (answeredCount / questions.length) * 100 : 0;
        progressBar.style.width = `${progress}%`;
        progressBar.innerText = `${Math.round(progress)}% Terjawab`;
    }

    function checkAnswer(questionId, answer) {
        const feedbackContainer = document.querySelector(`#question-${currentQuestionIndex} .feedback-container`);
        feedbackContainer.innerHTML = `<p class="text-muted">Mengecek jawaban...</p>`;
        fetch('{{ route("student.quiz.check_answer") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ question_id: questionId, answer: answer })
        })
        .then(response => response.json())
        .then(data => {
            displayFeedback(feedbackContainer, data.correct, data.explanation);
            disableInputsForCurrentQuestion();
            updateUI(); // Panggil updateUI untuk memperbarui progress bar & navigasi
        })
        .catch(error => {
            console.error('Error:', error);
            feedbackContainer.innerHTML = `<div class="alert alert-danger">Gagal terhubung ke server.</div>`;
        });
    }

    function displayFeedback(container, isCorrect, explanation) {
        if (isCorrect) {
            let feedbackHtml = `<div class="alert alert-success mt-3"><strong>Jawaban Anda Benar!</strong></div>`;
            if (explanation) {
                feedbackHtml += `<div class="alert alert-info"><strong>Penjelasan:</strong><br>${explanation.replace(/\n/g, '<br>')}</div>`;
            }
            container.innerHTML = feedbackHtml;
        } else {
            container.innerHTML = `<div class="alert alert-danger mt-3"><strong>Jawaban Anda Salah.</strong></div>`;
        }
    }

    function disableInputsForCurrentQuestion() {
        const currentSlide = document.getElementById(`question-${currentQuestionIndex}`);
        const inputs = currentSlide.querySelectorAll('input, select, button.check-answer-btn');
        inputs.forEach(input => input.disabled = true);
    }

    form.addEventListener('change', function(e) {
        const target = e.target;
        if (target.type === 'radio') {
            const questionSlide = target.closest('.question-slide');
            const questionId = questionSlide.dataset.questionId;
            checkAnswer(questionId, target.value);
        }
    });

    form.addEventListener('click', function(e) {
        if (e.target.classList.contains('check-answer-btn')) {
            const questionSlide = e.target.closest('.question-slide');
            const questionId = questionSlide.dataset.questionId;
            const inputs = questionSlide.querySelectorAll(`[name^="answers[${questionId}]"]`);
            let answer;
            if (inputs.length > 0 && inputs[0].type === 'checkbox') {
                answer = Array.from(inputs).filter(i => i.checked).map(i => i.value);
            } else {
                answer = {};
                inputs.forEach(select => {
                    const match = select.name.match(/\[(BLANK_\d+)\]$/);
                    if (match && match[1]) {
                        const blankId = match[1];
                        if (select.value) { answer[blankId] = select.value; }
                    }
                });
            }
            checkAnswer(questionId, answer);
        }
    });

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

    finishBtn.addEventListener('click', function(e) {
        e.preventDefault();
        if (confirm('Apakah Anda yakin ingin menyelesaikan dan mengirimkan kuis ini?')) {
            form.querySelectorAll('input, select').forEach(input => {
                input.disabled = false;
            });
            form.submit();
        }
    });
    
    createNavigation();
    showQuestion(0);
});
</script>
@endpush