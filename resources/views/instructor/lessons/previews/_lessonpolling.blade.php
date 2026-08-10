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
            <form id="form-polling-{{ $lesson->id }}" action="{{ route('student.lessons.polling.submit', $lesson->id) }}" data-results-url="{{ route('student.lessons.polling.results.ajax', $lesson->id) }}" onsubmit="submitPolling(event, {{ $lesson->id }})">
                @csrf
                <div class="form-group">
                    @foreach($polling->options as $option)
                    <div class="custom-control custom-radio mb-2">
                        <input type="radio" id="polling-option-{{ $option->id }}" name="polling_option_id" class="custom-control-input" value="{{ $option->id }}" required>
                        <label class="custom-control-label" for="polling-option-{{ $option->id }}" style="cursor:pointer">{{ $option->text }}</label>
                    </div>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-primary mt-3" id="btn-submit-polling-{{ $lesson->id }}">Kirim Jawaban</button>
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
