@php
    $polling = $lesson->lessonable;
    $hasVoted = false;
    if(auth()->check()) {
        $hasVoted = $polling->responses()->where('user_id', auth()->id())->exists();
    }
    
    $now = Carbon\Carbon::now();
    $isActive = $polling->is_active;
    
    if ($polling->start_time && $now->isBefore($polling->start_time)) {
        $isActive = false;
    }
    if ($polling->end_time && $now->isAfter($polling->end_time)) {
        $isActive = false;
    }
@endphp

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0 text-white"><i class="bi bi-bar-chart-fill mr-2"></i>{{ $polling->question }}</h5>
    </div>
    <div class="card-body" id="polling-container-{{ $lesson->id }}">
        @if($polling->description)
            <p class="text-muted">{{ $polling->description }}</p>
        @endif

        @if(!$isActive && !$hasVoted)
            <div class="alert alert-warning">
                Polling ini sedang tidak aktif atau sudah ditutup.
            </div>
        @elseif(!$hasVoted)
            <style>
                .poll-option-card {
                    display: block;
                    padding: 16px 20px;
                    margin: 0;
                    border: 2px solid #e9ecef;
                    border-radius: 12px;
                    cursor: pointer;
                    transition: all 0.2s ease-in-out;
                    background-color: #ffffff;
                }
                .poll-option-card:hover {
                    border-color: #b8daff;
                    background-color: #f1f7fd;
                }
                .poll-radio-input:checked + .poll-option-card {
                    border-color: #007bff;
                    background-color: #eaf4ff;
                    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
                }
                .poll-radio-circle {
                    width: 22px;
                    height: 22px;
                    border: 2px solid #adb5bd;
                    border-radius: 50%;
                    display: inline-block;
                    position: relative;
                    transition: all 0.2s ease-in-out;
                }
                .poll-radio-input:checked + .poll-option-card .poll-radio-circle {
                    border-color: #007bff;
                }
                .poll-radio-input:checked + .poll-option-card .poll-radio-circle::after {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    width: 12px;
                    height: 12px;
                    background-color: #007bff;
                    border-radius: 50%;
                }
                .poll-option-text {
                    font-size: 1.05rem;
                    font-weight: 500;
                    color: #495057;
                    transition: color 0.2s;
                }
                .poll-radio-input:checked + .poll-option-card .poll-option-text {
                    color: #0056b3;
                }
                .btn-poll-submit {
                    border-radius: 8px;
                    padding: 10px 24px;
                    font-weight: 600;
                    letter-spacing: 0.5px;
                }
            </style>
            
            <form id="form-polling-{{ $lesson->id }}" action="{{ route('student.lessons.polling.submit', $lesson->id) }}" data-results-url="{{ route('student.lessons.polling.results.ajax', $lesson->id) }}" onsubmit="submitPolling(event, {{ $lesson->id }})">
                @csrf
                <div class="form-group mb-4">
                    @foreach($polling->options as $option)
                    <div class="mb-3">
                        <input type="radio" id="polling-option-{{ $option->id }}" name="polling_option_id" class="poll-radio-input d-none" value="{{ $option->id }}" required>
                        <label class="poll-option-card" for="polling-option-{{ $option->id }}">
                            <div class="d-flex align-items-center">
                                <div class="poll-radio-circle mr-3"></div>
                                <span class="poll-option-text">{{ $option->text }}</span>
                            </div>
                        </label>
                    </div>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-primary btn-poll-submit shadow-sm" id="btn-submit-polling-{{ $lesson->id }}">
                    <i class="bi bi-send-fill mr-1"></i> Kirim Jawaban
                </button>
            </form>
        @else
            <!-- Hasil Polling setelah memilih (atau jika sudah memilih) -->
            <div class="alert alert-success">Anda sudah memberikan suara pada polling ini. Berikut adalah hasil saat ini:</div>
            
            <div id="polling-results-{{ $lesson->id }}" data-results-url="{{ route('student.lessons.polling.results.ajax', $lesson->id) }}">
                <div class="text-center my-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p>Memuat hasil...</p>
                </div>
            </div>
            
            <script>
                // Load hasil otomatis jika sudah vote
                document.addEventListener('DOMContentLoaded', function() {
                    loadPollingResults({{ $lesson->id }});
                });
            </script>
        @endif
    </div>
</div>

<script>
    if (typeof window.submitPolling !== 'function') {
        window.submitPolling = function(e, lessonId) {
            e.preventDefault();
            const form = document.getElementById('form-polling-' + lessonId);
            const formData = new FormData(form);
            const btn = document.getElementById('btn-submit-polling-' + lessonId);
            const submitUrl = form.getAttribute('action');
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Mengirim...';

            fetch(submitUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    const container = document.getElementById('polling-container-' + lessonId);
                    const resultsUrl = form.getAttribute('data-results-url');
                    container.innerHTML = '<div class="alert alert-success">Berhasil mengirim jawaban! Memuat hasil...</div><div id="polling-results-'+lessonId+'" data-results-url="'+resultsUrl+'"></div>';
                    window.loadPollingResults(lessonId);
                } else {
                    alert(data.message || 'Terjadi kesalahan');
                    btn.disabled = false;
                    btn.innerHTML = 'Kirim Jawaban';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan jaringan.');
                btn.disabled = false;
                btn.innerHTML = 'Kirim Jawaban';
            });
        };

        window.loadPollingResults = function(lessonId) {
            const resultsDiv = document.getElementById('polling-results-' + lessonId);
            if(!resultsDiv) return;
            
            const resultsUrl = resultsDiv.getAttribute('data-results-url');
            
            fetch(resultsUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                resultsDiv.innerHTML = html;
                
                const scriptTags = resultsDiv.getElementsByTagName('script');
                for (let i = 0; i < scriptTags.length; i++) {
                    eval(scriptTags[i].innerText);
                }
            });
        };
    }
</script>
