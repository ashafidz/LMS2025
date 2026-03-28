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
                                <input type="hidden" name="expelled_by_violation" id="expelled-flag" value="0">
                                
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
                            @if(!$is_preview && $attempt->quiz->securitySetting && $attempt->quiz->securitySetting->enable_camera_detection)
                            {{-- Camera Monitoring Preview --}}
                            <div class="card mb-3">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fa fa-video-camera"></i> Monitoring Kamera</h6>
                                </div>
                                <div class="card-block p-2">
                                    <div class="position-relative">
                                        <video id="camera-preview" autoplay playsinline style="width: 100%; border-radius: 5px; background: #000;"></video>
                                        <canvas id="camera-canvas" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: none;"></canvas>
                                        
                                        {{-- Status Indicator --}}
                                        <div id="camera-status" class="mt-2">
                                            <small class="text-muted">
                                                <span id="camera-status-icon" class="badge badge-secondary">
                                                    <i class="fa fa-circle"></i> Memuat...
                                                </span>
                                            </small>
                                        </div>

                                        {{-- Violation Counter --}}
                                        <div id="violation-stats" class="mt-2 p-2 bg-light rounded" style="font-size: 0.85rem;">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>Wajah tidak terdeteksi:</span>
                                                <strong id="face-not-detected-count">0</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>Pandangan ke kiri:</span>
                                                <strong id="look-left-count">0</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>Pandangan ke kanan:</span>
                                                <strong id="look-right-count">0</strong>
                                            </div>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span>Pandangan ke bawah:</span>
                                                <strong id="look-down-count">0</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Pandangan ke atas:</span>
                                                <strong id="look-up-count">0</strong>
                                            </div>
                                            <hr class="my-2">
                                            <div class="d-flex justify-content-between">
                                                <span><strong>Total Pelanggaran:</strong></span>
                                                <strong class="text-danger" id="total-camera-violations">0</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif

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
@endsection


@push('scripts')
{{-- MediaPipe Face Mesh CDN for Real Face Detection --}}
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils"></script>
<script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils"></script>

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
                        
                        // Tampilkan alert dan submit form
                        alert('Waktu pengerjaan kuis telah habis. Jawaban Anda akan dikirim secara otomatis.');
                        form.submit();
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

    // --- INISIALISASI ---
    createNavigation();
    showQuestion(0);
    if (!isPreview) {
        startPersistentTimer(); 
    }

    // =====================================================================
    // TAB DETECTION - Page Visibility API
    // =====================================================================
    @php
        $hasTabDetection = !$is_preview 
            && $attempt->quiz->securitySetting 
            && $attempt->quiz->securitySetting->enable_tab_detection;
    @endphp
    
    console.log('🔍 Tab Detection Check:');
    console.log('  - Is Preview: {{ $is_preview ? "true" : "false" }}');
    console.log('  - Has Security Setting: {{ $attempt->quiz->securitySetting ? "true" : "false" }}');
    console.log('  - Tab Detection Enabled: {{ $hasTabDetection ? "true" : "false" }}');
    
    @if($hasTabDetection)
    
    console.log('✅ Tab Detection ACTIVE');
    
    let tabViolationCount = 0;
    const tabThreshold = {{ $attempt->quiz->securitySetting->tab_violation_threshold ?? 5 }};
    let isQuizBlocked = false;
    let isAutoSubmitting = false; // Flag untuk auto-submit

    console.log('  - Threshold: ' + tabThreshold);

    // Deteksi perpindahan tab/window
    document.addEventListener('visibilitychange', function() {
        console.log('👁️ Visibility changed. Document hidden:', document.hidden);
        
        if (document.hidden) {
            // User pindah tab atau minimize window
            console.log('⚠️ Tab switched or window minimized - Sending log to server...');
            
            // Kirim log ke server
            fetch('{{ route('student.quiz.log_tab_violation', $attempt) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    timestamp: new Date().toISOString()
                })
            })
            .then(response => {
                console.log('📡 Server response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('📦 Server response data:', data);
                
                if (data.success) {
                    tabViolationCount = data.violation_count;
                    
                    // Jika melebihi threshold, blokir quiz
                    if (data.should_block) {
                        isQuizBlocked = true;
                        
                        // Tandai kuis dikeluarkan karena pelanggaran
                        document.getElementById('expelled-flag').value = '1';
                        document.querySelectorAll('input[type="radio"], input[type="checkbox"], textarea, input[type="text"]').forEach(input => {
                            input.disabled = true;
                        });

                        // Disable tombol navigasi
                        prevBtn.disabled = true;
                        nextBtn.disabled = true;
                        finishBtn.disabled = true;

                        // Tampilkan alert dengan countdown timer 5 detik
                        let timerInterval;
                        Swal.fire({
                            icon: 'error',
                            title: 'Kuis Diblokir!',
                            html: data.message + '<br><br><strong>Jawaban Anda akan otomatis dikirim dalam <span id="timer-countdown">5</span> detik...</strong>',
                            timer: 5000,
                            timerProgressBar: true,
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            showConfirmButton: false,
                            didOpen: () => {
                                const timerElement = document.getElementById('timer-countdown');
                                timerInterval = setInterval(() => {
                                    const timeLeft = Math.ceil(Swal.getTimerLeft() / 1000);
                                    if (timerElement) {
                                        timerElement.textContent = timeLeft;
                                    }
                                }, 100);
                            },
                            willClose: () => {
                                clearInterval(timerInterval);
                            }
                        }).then((result) => {
                            // Setelah timer habis, submit form otomatis
                            console.log('⏰ Timer habis - Auto submitting quiz...');
                            
                            // Set flag auto-submit
                            isAutoSubmitting = true;
                            
                            // Tampilkan loading
                            Swal.fire({
                                title: 'Mengirim Jawaban...',
                                text: 'Mohon tunggu, jawaban Anda sedang diproses.',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                showConfirmButton: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            
                            // Submit form
                            setTimeout(() => {
                                form.submit();
                            }, 500);
                        });

                        // Update tampilan tombol submit
                        finishBtn.innerHTML = '<i class="fa fa-lock"></i> Kuis Diblokir';
                        finishBtn.classList.remove('btn-success');
                        finishBtn.classList.add('btn-danger');

                        // Tampilkan pesan permanen di halaman
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger text-center mt-3';
                        alertDiv.innerHTML = '<strong><i class="fa fa-exclamation-triangle"></i> Kuis telah diblokir karena terlalu banyak pelanggaran! Jawaban akan otomatis dikirim.</strong>';
                        document.querySelector('.card-block').prepend(alertDiv);
                        
                    } else {
                        // Tampilkan peringatan biasa (belum diblokir)
                        Swal.fire({
                            icon: 'warning',
                            title: 'Peringatan!',
                            text: data.message,
                            confirmButtonText: 'Mengerti',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                        });
                    }
                }
            })
            .catch(error => {
                console.error('❌ Error logging tab violation:', error);
            });
        } else {
            console.log('✅ Tab is now visible again');
        }
    });

    // Blokir submit jika quiz sudah diblokir (kecuali auto-submit)
    form.addEventListener('submit', function(e) {
        if (isQuizBlocked && !isAutoSubmitting) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Kuis Diblokir',
                text: 'Kuis telah diblokir! Silakan tunggu jawaban otomatis dikirim.',
                confirmButtonText: 'Tutup'
            });
        }
    });

    @endif
    // =====================================================================
    // END TAB DETECTION
    // =====================================================================
    
    @if(!$hasTabDetection)
    console.log('⚠️ Tab Detection NOT active');
    @endif

    // =====================================================================
    // CAMERA DETECTION - MediaPipe Face Detection
    // =====================================================================
    @php
        $hasCameraDetection = !$is_preview 
            && $attempt->quiz->securitySetting 
            && $attempt->quiz->securitySetting->enable_camera_detection;
    @endphp
    
    console.log('🎥 Camera Detection Check:');
    console.log('  - Has Camera Detection Setting: {{ $hasCameraDetection ? "true" : "false" }}');
    
    @if($hasCameraDetection)
    
    console.log('✅ Camera Detection ACTIVE');
    
    // Camera elements
    const cameraPreview = document.getElementById('camera-preview');
    const cameraStatusIcon = document.getElementById('camera-status-icon');
    
    // Violation counters
    const violationElements = {
        face_not_detected: document.getElementById('face-not-detected-count'),
        look_left: document.getElementById('look-left-count'),
        look_right: document.getElementById('look-right-count'),
        look_down: document.getElementById('look-down-count'),
        look_up: document.getElementById('look-up-count'),
        total: document.getElementById('total-camera-violations')
    };

    const cameraThreshold = {{ $attempt->quiz->securitySetting->camera_violation_threshold ?? 10 }};
    const detectionInterval = {{ $attempt->quiz->securitySetting->face_detection_interval_seconds ?? 5 }} * 1000;

    // Flag tipe pelanggaran yang aktif (dikonfigurasi instruktur)
    const detectFaceNotDetected = {{ ($attempt->quiz->securitySetting->detect_face_not_detected ?? true) ? 'true' : 'false' }};
    const detectLookLeft = {{ ($attempt->quiz->securitySetting->detect_look_left ?? true) ? 'true' : 'false' }};
    const detectLookRight = {{ ($attempt->quiz->securitySetting->detect_look_right ?? true) ? 'true' : 'false' }};
    const detectLookUp = {{ ($attempt->quiz->securitySetting->detect_look_up ?? true) ? 'true' : 'false' }};
    const detectLookDown = {{ ($attempt->quiz->securitySetting->detect_look_down ?? true) ? 'true' : 'false' }};
    const violationDuration = {{ $attempt->quiz->securitySetting->violation_duration_seconds ?? 3 }} * 1000;
    
    let isCameraBlocked = false;
    let violationCounts = {
        face_not_detected: 0,
        look_left: 0,
        look_right: 0,
        look_down: 0,
        look_up: 0,
        total: 0
    };

    console.log('  - Threshold:', cameraThreshold);
    console.log('  - Detection Interval:', detectionInterval / 1000, 'seconds');

    // MediaPipe Face Mesh variables
    let faceMesh = null;
    let camera = null;
    let lastViolationTime = 0;
    let noFaceDetectedCount = 0;
    const NO_FACE_THRESHOLD = 3; // Berapa kali deteksi tidak ada wajah sebelum log violation

    // Sustained violation tracking (durasi pelanggaran harus berlangsung sebelum dihitung)
    let sustainedViolationType = null;
    let sustainedViolationStart = 0;

    // Canvas untuk drawing
    const canvas = document.getElementById('camera-canvas');
    const canvasCtx = canvas ? canvas.getContext('2d') : null;

    // Real Face Detection dengan MediaPipe Face Mesh
    async function initializeCamera() {
        try {
            console.log('📷 Initializing MediaPipe Face Mesh...');
            
            // Initialize Face Mesh
            faceMesh = new FaceMesh({
                locateFile: (file) => {
                    return `https://cdn.jsdelivr.net/npm/@mediapipe/face_mesh/${file}`;
                }
            });

            faceMesh.setOptions({
                maxNumFaces: 1,
                refineLandmarks: true,
                minDetectionConfidence: 0.5,
                minTrackingConfidence: 0.5
            });

            faceMesh.onResults(onFaceMeshResults);

            console.log('📷 Requesting camera access...');
            
            // Initialize camera
            camera = new Camera(cameraPreview, {
                onFrame: async () => {
                    await faceMesh.send({image: cameraPreview});
                },
                width: 320,
                height: 240
            });

            await camera.start();
            
            cameraStatusIcon.className = 'badge badge-success';
            cameraStatusIcon.innerHTML = '<i class="fa fa-circle"></i> Aktif';
            
            console.log('✅ MediaPipe Face Mesh initialized successfully');
            
        } catch (error) {
            console.error('❌ Camera/MediaPipe initialization failed:', error);
            cameraStatusIcon.className = 'badge badge-danger';
            cameraStatusIcon.innerHTML = '<i class="fa fa-times-circle"></i> Gagal';
            
            Swal.fire({
                icon: 'warning',
                title: 'Akses Kamera Diperlukan',
                text: 'Mohon izinkan akses kamera untuk melanjutkan kuis ini.',
                confirmButtonText: 'Coba Lagi'
            }).then(() => {
                initializeCamera();
            });
        }
    }

    // Callback saat AI Face Mesh berhasil mendeteksi wajah di frame video
    function onFaceMeshResults(results) {
        // Jika kuis sudah diblokir, tidak perlu cek lagi
        if (isCameraBlocked) return;

        // Sesuaikan ukuran canvas dengan ukuran video kamera
        if (canvas && cameraPreview.videoWidth > 0) {
            canvas.width = cameraPreview.videoWidth;
            canvas.height = cameraPreview.videoHeight;
        }

        const now = Date.now();
        
        // Hanya cek setiap beberapa detik sesuai 'detectionInterval' (agar tidak membebani browser)
        if (now - lastViolationTime < detectionInterval) {
            return;
        }

        // Jika tidak ada wajah yang terlihat di kamera
        if (!results.multiFaceLandmarks || results.multiFaceLandmarks.length === 0) {
            noFaceDetectedCount++;
            console.log(`⚠️ Wajah tidak terlihat (${noFaceDetectedCount}/${NO_FACE_THRESHOLD})`);
            
            // Jika wajah hilang beberapa kali berturut-turut, catat sebagai pelanggaran
            if (noFaceDetectedCount >= NO_FACE_THRESHOLD) {
                if (detectFaceNotDetected) {
                    console.log('🚨 Pelanggaran: Wajah tidak terdeteksi');
                    logCameraViolation('face_not_detected'); // Kirim log ke server
                    lastViolationTime = now;
                }
                noFaceDetectedCount = 0;
            }
            return;
        }

        // Jika wajah terlihat, reset counter 'tidak ada wajah'
        noFaceDetectedCount = 0;

        // Ambil data koordinat titik-titik wajah (Landmarks)
        const landmarks = results.multiFaceLandmarks[0];
        
        // Hitung arah kepala (menoleh atau menunduk) berdasarkan titik koordinat wajah
        const headPose = calculateHeadPose(landmarks);
        
        console.log('👤 Wajah Terdeteksi - Pose Kepalanya:', headPose);

        // Periksa apakah arah kepala melanggar aturan (terlalu jauh ke kiri/kanan/atas/bawah)
        const violation = checkPoseViolation(headPose);
        
        if (violation) {
            if (violationDuration <= 0) {
                // Durasi 0 = langsung dihitung sebagai pelanggaran (perilaku lama)
                console.log(`🚨 Pelanggaran Terdeteksi: ${violation}`);
                logCameraViolation(violation);
                lastViolationTime = now;
            } else if (sustainedViolationType === violation) {
                // Pelanggaran yang sama masih berlangsung — cek apakah sudah cukup lama
                const elapsed = now - sustainedViolationStart;
                if (elapsed >= violationDuration) {
                    console.log(`🚨 Pelanggaran Terdeteksi: ${violation} (berlangsung ${(elapsed/1000).toFixed(1)}s)`);
                    logCameraViolation(violation);
                    lastViolationTime = now;
                    sustainedViolationType = null;
                    sustainedViolationStart = 0;
                } else {
                    console.log(`⏳ Pelanggaran ${violation} terdeteksi (${(elapsed/1000).toFixed(1)}s/${(violationDuration/1000)}s)`);
                }
            } else {
                // Pelanggaran baru terdeteksi — mulai hitung durasi
                sustainedViolationType = violation;
                sustainedViolationStart = now;
                console.log(`⏳ Mulai tracking pelanggaran: ${violation}`);
            }
        } else {
            // Tidak ada pelanggaran — reset tracking
            if (sustainedViolationType) {
                console.log(`✅ Pelanggaran ${sustainedViolationType} berhenti sebelum durasi tercapai`);
                sustainedViolationType = null;
                sustainedViolationStart = 0;
            }
        }
    }

    /**
     * Menghitung arah kepala (Head Pose) menggunakan rumus matematika sederhana
     * Berdasarkan posisi titik Hidung, Mata, Dagu, dan Mulut.
     */
    function calculateHeadPose(landmarks) {
        // Titik-titik referensi wajah dari MediaPipe
        const nose = landmarks[1];      // Ujung hidung
        const chin = landmarks[152];    // Dagu
        const leftEye = landmarks[33];  // Mata kiri (dari sisi kamera)
        const rightEye = landmarks[263]; // Mata kanan

        // Hitung Yaw (Gerakan horizontal: Kiri - Kanan)
        // Dihitung dari jarak hidung ke mata kiri vs jarak hidung ke mata kanan
        const eyeDistance = Math.abs(rightEye.x - leftEye.x);
        const noseToLeftEye = Math.abs(nose.x - leftEye.x);
        const noseToRightEye = Math.abs(nose.x - rightEye.x);
        
        let yaw = 0;
        if (eyeDistance > 0) {
            // Jika hidung lebih dekat ke salah satu mata, berarti kepala sedang menoleh
            yaw = (noseToRightEye - noseToLeftEye) / eyeDistance;
        }

        // Hitung Pitch (Gerakan vertikal: Atas - Bawah)
        // Dihitung dari perbandingan tinggi wajah dengan posisi mata vs hidung
        const faceHeight = Math.abs(chin.y - nose.y);
        const eyeToNose = Math.abs(nose.y - ((leftEye.y + rightEye.y) / 2));
        
        let pitch = 0;
        if (faceHeight > 0) {
            // Jika mata dan hidung terlalu dekat/jauh secara vertikal, berarti kepala menunduk/mendongak
            pitch = (eyeToNose / faceHeight) - 0.5; // Normalisasi dasar
        }

        return {
            yaw: yaw,   // Minus = Menoleh Kiri, Plus = Menoleh Kanan
            pitch: pitch // Minus = Melihat Atas, Plus = Melihat Bawah
        };
    }

    /**
     * Membandingkan hasil hitungan pose dengan batas toleransi (Threshold)
     */
    function checkPoseViolation(pose) {
        // BATAS TOLERANSI (Semakin kecil angkanya, semakin sensitif deteksinya)
        const YAW_THRESHOLD = 0.45;        // Ambang batas menoleh kiri/kanan (sebelumnya 0.3)
        const PITCH_UP_THRESHOLD = -0.3;   // Ambang batas melihat ke atas (sebelumnya -0.15)
        const PITCH_DOWN_THRESHOLD = 0.35; // Ambang batas melihat ke bawah/menunduk (sebelumnya 0.2)

        // Cek apakah menoleh ke kanan melewati batas
        if (detectLookRight && pose.yaw > YAW_THRESHOLD) {
            return 'look_right';
        }
        
        // Cek apakah menoleh ke kiri melewati batas
        if (detectLookLeft && pose.yaw < -YAW_THRESHOLD) {
            return 'look_left';
        }
        
        // Cek apakah melihat ke atas melewati batas
        if (detectLookUp && pose.pitch < PITCH_UP_THRESHOLD) {
            return 'look_up';
        }
        
        // Cek apakah menunduk melewati batas
        if (detectLookDown && pose.pitch > PITCH_DOWN_THRESHOLD) {
            return 'look_down';
        }

        return null; // Tidak ada pelanggaran yang signifikan
    }

    // Optional: Draw landmarks for debugging
    function drawFaceLandmarks(results) {
        if (!canvas || !canvasCtx) return;
        
        canvasCtx.save();
        canvasCtx.clearRect(0, 0, canvas.width, canvas.height);
        canvasCtx.drawImage(results.image, 0, 0, canvas.width, canvas.height);
        
        if (results.multiFaceLandmarks) {
            for (const landmarks of results.multiFaceLandmarks) {
                drawConnectors(canvasCtx, landmarks, FACEMESH_TESSELATION, {color: '#C0C0C070', lineWidth: 1});
                drawConnectors(canvasCtx, landmarks, FACEMESH_RIGHT_EYE, {color: '#FF3030'});
                drawConnectors(canvasCtx, landmarks, FACEMESH_LEFT_EYE, {color: '#30FF30'});
            }
        }
        
        canvasCtx.restore();
    }

    // Capture screenshot from video
    function captureScreenshot() {
        return new Promise((resolve, reject) => {
            try {
                // Create temporary canvas
                const tempCanvas = document.createElement('canvas');
                const tempCtx = tempCanvas.getContext('2d');
                
                // Set canvas size to match video
                tempCanvas.width = cameraPreview.videoWidth;
                tempCanvas.height = cameraPreview.videoHeight;
                
                // Draw current video frame
                tempCtx.drawImage(cameraPreview, 0, 0, tempCanvas.width, tempCanvas.height);
                
                // Convert to blob (JPEG format, 0.8 quality)
                tempCanvas.toBlob((blob) => {
                    if (blob) {
                        console.log('📸 Screenshot captured:', blob.size, 'bytes');
                        resolve(blob);
                    } else {
                        reject(new Error('Failed to create blob'));
                    }
                }, 'image/jpeg', 0.8);
                
            } catch (error) {
                console.error('❌ Screenshot capture failed:', error);
                reject(error);
            }
        });
    }

    // Log camera violation ke server dengan screenshot
    async function logCameraViolation(violationType) {
        console.log(`⚠️ Camera violation detected: ${violationType}`);
        
        // Capture screenshot
        let screenshot = null;
        try {
            screenshot = await captureScreenshot();
            console.log('✅ Screenshot ready for upload');
        } catch (error) {
            console.warn('⚠️ Could not capture screenshot, continuing without it:', error);
        }

        // Prepare FormData untuk kirim file
        const formData = new FormData();
        formData.append('violation_type', violationType);
        formData.append('timestamp', new Date().toISOString());
        
        if (screenshot) {
            formData.append('screenshot', screenshot, `violation_${Date.now()}.jpg`);
        }
        
        fetch('{{ route('student.quiz.log_camera_violation', $attempt) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log('📦 Camera violation response:', data);
            
            if (data.success) {
                // Update counters
                violationCounts = {
                    face_not_detected: data.violation_breakdown.face_not_detected,
                    look_left: data.violation_breakdown.look_left,
                    look_right: data.violation_breakdown.look_right,
                    look_down: data.violation_breakdown.look_down,
                    look_up: data.violation_breakdown.look_up,
                    total: data.violation_count
                };

                // Update UI
                Object.keys(violationElements).forEach(key => {
                    if (violationElements[key]) {
                        violationElements[key].textContent = violationCounts[key] || 0;
                    }
                });

                // Check if should block
                if (data.should_block && !isCameraBlocked) {
                    handleCameraBlock();
                }
            }
        })
        .catch(error => {
            console.error('❌ Error logging camera violation:', error);
        });
    }

    // Handle camera block (similar to tab block)
    function handleCameraBlock() {
        isCameraBlocked = true;
        isQuizBlocked = true;
        
        // Tandai kuis dikeluarkan karena pelanggaran kamera
        document.getElementById('expelled-flag').value = '1';
        document.querySelectorAll('input[type="radio"], input[type="checkbox"], textarea, input[type="text"]').forEach(input => {
            input.disabled = true;
        });

        prevBtn.disabled = true;
        nextBtn.disabled = true;
        finishBtn.disabled = true;

        // Show countdown alert
        let timerInterval;
        Swal.fire({
            icon: 'error',
            title: 'Kuis Diblokir - Pelanggaran Kamera!',
            html: 'Terlalu banyak pelanggaran terdeteksi pada kamera monitoring.<br><br><strong>Jawaban akan otomatis dikirim dalam <span id="camera-timer-countdown">5</span> detik...</strong>',
            timer: 5000,
            timerProgressBar: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                const timerElement = document.getElementById('camera-timer-countdown');
                timerInterval = setInterval(() => {
                    const timeLeft = Math.ceil(Swal.getTimerLeft() / 1000);
                    if (timerElement) {
                        timerElement.textContent = timeLeft;
                    }
                }, 100);
            },
            willClose: () => {
                clearInterval(timerInterval);
            }
        }).then(() => {
            console.log('⏰ Camera block timer expired - Auto submitting...');
            
            isAutoSubmitting = true;
            
            Swal.fire({
                title: 'Mengirim Jawaban...',
                text: 'Mohon tunggu, jawaban Anda sedang diproses.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            setTimeout(() => {
                form.submit();
            }, 500);
        });
    }

    // Initialize camera when page loads
    console.log('🎬 Initializing camera system...');
    console.log('   Threshold:', cameraThreshold, 'violations');
    console.log('   Interval:', detectionInterval / 1000, 'seconds');
    
    // Wait a bit for page to fully load
    setTimeout(() => {
        console.log('⏰ Starting camera initialization...');
        initializeCamera();
    }, 1000);

    @endif
    // =====================================================================
    // END CAMERA DETECTION
    // =====================================================================
    
    @if(!$hasCameraDetection)
    console.log('⚠️ Camera Detection NOT active');
    @endif
});
</script>
@endpush