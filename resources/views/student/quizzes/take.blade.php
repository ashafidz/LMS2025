@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10" id="quiz-title">{{ $attempt->quiz->title }}</h5>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-right font-weight-bold" id="quiz-timer" style="font-size: 1.2rem;"></div>
                </div>
            </div>
        </div>
    </div>
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
                                            <div id="progress-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <div class="card-block">
                                        {{-- Render semua soal di sini, tapi disembunyikan --}}
                                        @foreach($attempt->quiz->questions as $index => $question)
                                            <div class="question-slide" id="question-{{ $index }}" data-question-id="{{ $question->id }}" style="{{ $index > 0 ? 'display: none;' : '' }}; min-height: 300px;">
                                                <h5>Soal {{ $index + 1 }} dari {{ $attempt->quiz->questions->count() }}</h5>
                                                <p class="lead">{!! nl2br(e(str_replace(preg_match_all('/(\[\[BLANK_\d+\]\])/', $question->question_text, $matches) ? $matches[0] : [], '___', $question->question_text))) !!}</p>
                                                <hr>
                                                <div class="options-list">
                                                    @if($question->question_type === 'multiple_choice_single' || $question->question_type === 'true_false')
                                                        @foreach($question->options as $option)
                                                        <div class="form-check ml-5"><input class="form-check-input" type="radio" name="answers[{{ $question->id }}]" id="opt-{{ $option->id }}" value="{{ $option->id }}"><label class="form-check-label" for="opt-{{ $option->id }}">{{ $option->option_text }}</label></div>
                                                        @endforeach
                                                    @elseif($question->question_type === 'multiple_choice_multiple')
                                                        @foreach($question->options as $option)
                                                        <div class="form-check ml-5">
                                                            <input class="form-check-input" type="checkbox" name="answers[{{ $question->id }}][]" id="opt-{{ $option->id }}" value="{{ $option->id }}">
                                                            <label class="form-check-label" for="opt-{{ $option->id }}">{{ $option->option_text }}</label>
                                                        </div>
                                                        @endforeach
                                                    @elseif($question->question_type === 'drag_and_drop')
                                                        <div class="drag-and-drop-container" style="line-height: 2.5;">{!! preg_replace_callback('/\[\[(BLANK_\d+)\]\]/', function($matches) use ($question) { $blankId = $matches[1]; $optionsHtml = '<option value="">-- Pilih Jawaban --</option>'; foreach ($question->options as $option) { $optionsHtml .= '<option value="' . $option->id . '">' . e($option->option_text) . '</option>'; } return '<select name="answers[' . $question->id . '][' . $blankId . ']" class="form-control d-inline-block" style="width: auto;">' . $optionsHtml . '</select>'; }, e($question->question_text)) !!}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="card-footer d-flex justify-content-between">
                                        <button type="button" id="prev-btn" class="btn btn-secondary" style="display: none;">Sebelumnya</button>
                                        <button type="button" id="next-btn" class="btn btn-primary">Selanjutnya</button>
                                        {{-- DIUBAH: type dari "submit" menjadi "button" --}}
                                        <button type="button" id="finish-btn" class="btn btn-success" style="display: none;">Selesaikan Kuis</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="col-lg-3">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Navigasi Soal</h5>
                                </div>
                                <div class="card-block">
                                    <div id="question-navigation" class="d-flex flex-wrap">
                                        {{-- Tombol navigasi akan dibuat oleh JavaScript di sini --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- BARU: Tambahkan HTML untuk Modal Konfirmasi dan Modal Waktu Habis di sini --}}
<div class="modal fade" id="confirmSubmitModal" tabindex="-1" role="dialog" aria-labelledby="confirmSubmitModalLabel" aria-hidden="true">
    {{-- DIUBAH: Tambahkan kelas 'modal-dialog-centered' di sini --}}
    <div class="modal-dialog modal-dialog-centered" role="document"> 
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmSubmitModalLabel">Konfirmasi Penyelesaian Kuis</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menyelesaikan kuis ini? Jawaban tidak dapat diubah lagi setelahnya.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" id="confirm-submit-btn" class="btn btn-success">Ya, Selesaikan</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="timeUpModal" tabindex="-1" role="dialog" aria-labelledby="timeUpModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    {{-- DIUBAH: Tambahkan kelas 'modal-dialog-centered' di sini --}}
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="timeUpModalLabel">Waktu Habis!</h5>
            </div>
            <div class="modal-body">
                Waktu pengerjaan kuis telah habis. Jawaban Anda akan dikirim secara otomatis.
            </div>
            <div class="modal-footer">
                <button type="button" id="time-up-ok-btn" class="btn btn-primary">OK</button>
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
    const isPreview = @json($is_preview);
    
    // BARU: Ambil elemen tombol dari modal
    const confirmSubmitBtn = document.getElementById('confirm-submit-btn');
    const timeUpOkBtn = document.getElementById('time-up-ok-btn');

    if (!isPreview) {
        const timerEl = document.getElementById('quiz-timer');
        const endTimeISO = '{{ $endTime }}';
        const allowExceedTime = {{ $attempt->quiz->allow_exceed_time_limit ? 'true' : 'false' }};
        let timerInterval;

        function startPersistentTimer() {
            if (!endTimeISO) {
                timerEl.innerText = "Tanpa Batas Waktu";
                return;
            }
            const endTime = new Date(endTimeISO);
            
            const updateTimer = () => {
                const now = new Date();
                const timeRemaining = Math.round((endTime - now) / 1000);

                if (timeRemaining <= 0) {
                    if (!allowExceedTime) {
                        clearInterval(timerInterval);
                        timerEl.innerText = "00:00";
                        timerEl.classList.add('text-danger');
                        
                        // DIUBAH: Ganti alert dengan modal
                        $('#timeUpModal').modal('show');
                        
                        // Form akan disubmit saat pengguna menekan OK di modal
                        return;
                    }
                }
                
                const minutes = Math.floor(Math.abs(timeRemaining) / 60);
                const seconds = Math.abs(timeRemaining) % 60;
                const displayTime = `${timeRemaining < 0 ? '-' : ''}${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                
                timerEl.innerText = displayTime;
                if (timeRemaining < 0) {
                    timerEl.classList.add('text-danger');
                } else {
                    timerEl.classList.remove('text-danger');
                }
            };
            
            updateTimer(); 
            timerInterval = setInterval(updateTimer, 1000);
        }
    }

    function createNavigation() {
        questions.forEach((question, index) => {
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
            const questionSlide = document.getElementById(`question-${index}`);
            const inputs = questionSlide.querySelectorAll('input[type="radio"], input[type="checkbox"], select');
            let isAnswered = false;
            for (const input of inputs) {
                if (((input.type === 'radio' || input.type === 'checkbox') && input.checked) || (input.tagName === 'SELECT' && input.value !== '')) {
                    isAnswered = true;
                    break;
                }
            }
            if (index === currentQuestionIndex) {
                btn.className = 'btn btn-primary m-1';
            } else if (isAnswered) {
                btn.className = 'btn btn-success m-1';
            } else {
                btn.className = 'btn btn-outline-secondary m-1';
            }
        });
    }

    function updateProgressBar() {
        let answeredCount = 0;
        const totalQuestions = questions.length;
        questions.forEach((questionSlide) => {
            const inputs = questionSlide.querySelectorAll('input[type="radio"], input[type="checkbox"], select');
            let isAnswered = false;
            for (const input of inputs) {
                if (((input.type === 'radio' || input.type === 'checkbox') && input.checked) || (input.tagName === 'SELECT' && input.value !== '')) {
                    isAnswered = true;
                    break; 
                }
            }
            if (isAnswered) {
                answeredCount++;
            }
        });
        const progress = totalQuestions > 0 ? (answeredCount / totalQuestions) * 100 : 0;
        progressBar.style.width = `${progress}%`;
        progressBar.innerText = `${Math.round(progress)}%`;
        progressBar.setAttribute('aria-valuenow', progress);
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
    
    form.addEventListener('change', function() {
        updateNavigationStatus();
        updateProgressBar();
    });

    // --- BARU: Event Listeners untuk Modal ---

    // 1. Saat tombol "Selesaikan Kuis" diklik, tampilkan modal konfirmasi
    finishBtn.addEventListener('click', function() {
        $('#confirmSubmitModal').modal('show');
    });

    // 2. Saat tombol "Ya, Selesaikan" di dalam modal diklik, kirim form
    confirmSubmitBtn.addEventListener('click', function() {
        form.submit();
    });

    // 3. Saat tombol "OK" di modal waktu habis diklik, kirim form
    timeUpOkBtn.addEventListener('click', function() {
        form.submit();
    });

    // --- INISIALISASI ---
    createNavigation();
    showQuestion(0);
    if (!isPreview) {
        startPersistentTimer(); 
    }
});
</script>
@endpush