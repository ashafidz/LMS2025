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
                    <div class="text-right font-weight-bold" id="quiz-timer" style="font-size: 1.2rem;"></div>
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

                    {{-- Layout 2 Kolom Baru --}}
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
                                                        {{-- <div class="col  mb-2">
                                                            <div class="checkbox-fade fade-in-primary">
                                                                <label class="form-check-label" for="opt-{{ $option->id }}">
                                                                    <input class="form-check-input" type="checkbox" name="answers[{{ $question->id }}][]" id="opt-{{ $option->id }}" value="{{ $option->id }}">
                                                                    <span class="cr">
                                                                        <i class="cr-icon icofont icofont-ui-check txt-primary"></i>
                                                                    </span>
                                                                    <span class="text-inverse">{{ $option->option_text }}</span>
                                                                </label>
                                                            </div>
                                                        </div> --}}
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
                                        <button type="submit" id="finish-btn" class="btn btn-success" style="display: none;">Selesaikan Kuis</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- KOLOM KANAN: NAVIGASI SOAL -->
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
@endsection

{{-- @push('scripts')
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
        const progress = ((currentQuestionIndex + 1) / questions.length) * 100;
        progressBar.style.width = `${progress}%`;
        progressBar.innerText = `${Math.round(progress)}%`;

        prevBtn.style.display = currentQuestionIndex > 0 ? 'inline-block' : 'none';
        nextBtn.style.display = currentQuestionIndex < questions.length - 1 ? 'inline-block' : 'none';
        finishBtn.style.display = currentQuestionIndex === questions.length - 1 ? 'inline-block' : 'none';
        
        updateNavigationStatus();
    }

   
    function updateNavigationStatus() {
        const navButtons = questionNavContainer.querySelectorAll('button');
        navButtons.forEach((btn, index) => {
            const questionSlide = document.getElementById(`question-${index}`);
            const inputs = questionSlide.querySelectorAll('input[type="radio"], input[type="checkbox"], select');
            let isAnswered = false;

            inputs.forEach(input => {
                if ((input.type === 'radio' || input.type === 'checkbox') && input.checked) {
                    isAnswered = true;
                }
                if (input.tagName === 'SELECT' && input.value !== '') {
                    isAnswered = true;
                }
            });

            // Logika pewarnaan tombol navigasi
            if (index === currentQuestionIndex) {
                btn.className = 'btn btn-primary m-1'; 
            } else if (isAnswered) {
                btn.className = 'btn btn-secondary m-1'; /
            } else {
                btn.className = 'btn btn-outline-secondary m-1'; 
            }
        });
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
    });


    createNavigation();
    showQuestion(0);
});
</script>
@endpush --}}

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- BAGIAN NAVIGASI SOAL (TIDAK BERUBAH) ---
    const questions = document.querySelectorAll('.question-slide');
    let currentQuestionIndex = 0;
    const progressBar = document.getElementById('progress-bar');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const finishBtn = document.getElementById('finish-btn');
    const questionNavContainer = document.getElementById('question-navigation');
    const form = document.getElementById('quiz-form');

    const isPreview = @json($is_preview);
    console.log(isPreview);

    if (!isPreview) {
            // --- LOGIKA TIMER BARU YANG PERSISTENT ---
    const timerEl = document.getElementById('quiz-timer');
    // Ambil endTime dari server yang sudah kita kirim dari controller
    const endTimeISO = '{{ $endTime }}';
    const allowExceedTime = {{ $attempt->quiz->allow_exceed_time ? 'true' : 'false' }};
    let timerInterval;

    function startPersistentTimer() {
        // Jika tidak ada endTime (kuis tanpa batas waktu), tampilkan pesan dan hentikan fungsi.
        if (!endTimeISO) {
            timerEl.innerText = "Tanpa Batas Waktu";
            return;
        }

        const endTime = new Date(endTimeISO);

        // Fungsi yang akan dijalankan setiap detik
        const updateTimer = () => {
            const now = new Date();
            // Hitung sisa waktu dalam detik
            const timeRemaining = Math.round((endTime - now) / 1000);

            // Jika waktu sudah habis
            if (timeRemaining <= 0) {
                if (!allowExceedTime) {
                    // Jika tidak boleh melewati batas waktu, hentikan timer dan submit form
                    clearInterval(timerInterval);
                    timerEl.innerText = "00:00";
                    timerEl.classList.add('text-danger');
                    // Beri sedikit jeda agar user tahu waktu habis sebelum form disubmit
                    setTimeout(() => {
                        alert('Waktu habis! Jawaban Anda akan dikirim secara otomatis.');
                        form.submit();
                    }, 500);
                    return; // Hentikan eksekusi lebih lanjut
                }
            }
            
            // Format tampilan waktu (menit dan detik)
            const minutes = Math.floor(Math.abs(timeRemaining) / 60);
            const seconds = Math.abs(timeRemaining) % 60;
            const displayTime = `${timeRemaining < 0 ? '-' : ''}${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            
            timerEl.innerText = displayTime;

            // Tambahkan warna merah jika waktu sudah minus (melewati batas)
            if (timeRemaining < 0) {
                timerEl.classList.add('text-danger');
            } else {
                timerEl.classList.remove('text-danger');
            }
        };

        // Jalankan updateTimer sekali saat halaman dimuat agar tidak ada jeda 1 detik
        updateTimer(); 
        // Set interval untuk mengupdate timer setiap detik
        timerInterval = setInterval(updateTimer, 1000);
    }

    }
    

    // --- FUNGSI UI LAINNYA (TIDAK BERUBAH BANYAK) ---
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

    // --- INISIALISASI ---
    createNavigation();
    showQuestion(0);
    // Panggil fungsi timer yang baru
    if (!isPreview) {
        startPersistentTimer(); 
    }
});
</script>
@endpush