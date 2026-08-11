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
            
            <form id="form-polling-{{ $lesson->id }}" action="{{ route('student.lessons.polling.submit', $lesson->id) }}" data-results-url="{{ route('student.lessons.polling.results.ajax', $lesson->id) }}" method="POST" onsubmit="
                event.preventDefault();
                var form = this;
                var btn = form.querySelector('button[type=submit]');
                var lessonId = {{ $lesson->id }};
                var originalBtnHtml = btn.innerHTML;
                
                btn.disabled = true;
                btn.innerHTML = '<span class=\'spinner-border spinner-border-sm\' role=\'status\' aria-hidden=\'true\'></span> Mengirim...';
                
                fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.success) {
                        var container = document.getElementById('polling-container-' + lessonId);
                        var resultsUrl = form.getAttribute('data-results-url');
                        var showResults = {{ $polling->show_results ? 'true' : 'false' }};
                        
                        if (showResults) {
                            container.innerHTML = '<div class=\'alert alert-success\'>Berhasil mengirim jawaban! Memuat hasil...</div><div id=\'polling-results-'+lessonId+'\'></div>';
                            
                            fetch(resultsUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(function(r) { return r.text(); })
                            .then(function(html) {
                                var resDiv = document.getElementById('polling-results-' + lessonId);
                                resDiv.innerHTML = html;
                                var scripts = resDiv.getElementsByTagName('script');
                                for(var i=0; i<scripts.length; i++) eval(scripts[i].innerText);
                            });
                        } else {
                            container.innerHTML = '<div class=\'alert alert-success\'>Berhasil mengirim jawaban! Terima kasih atas partisipasi Anda.</div>';
                        }
                    } else {
                        alert(data.message || 'Terjadi kesalahan');
                        btn.disabled = false;
                        btn.innerHTML = originalBtnHtml;
                    }
                })
                .catch(function(err) {
                    console.error(err);
                    alert('Terjadi kesalahan jaringan.');
                    btn.disabled = false;
                    btn.innerHTML = originalBtnHtml;
                });
            ">
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
            <div class="alert alert-success">Anda sudah memberikan suara pada polling ini. @if($polling->show_results) Berikut adalah hasil saat ini: @endif</div>
            
            @if($polling->show_results)
            <div id="polling-results-{{ $lesson->id }}" data-results-url="{{ route('student.lessons.polling.results.ajax', $lesson->id) }}">
                <div class="text-center my-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p>Memuat hasil...</p>
                </div>
                <img src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" onload="
                    var resDiv = this.parentElement;
                    var lessonId = {{ $lesson->id }};
                    var resultsUrl = resDiv.getAttribute('data-results-url');
                    fetch(resultsUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function(r) { return r.text(); })
                    .then(function(html) {
                        resDiv.innerHTML = html;
                        var scripts = resDiv.getElementsByTagName('script');
                        for(var i=0; i<scripts.length; i++) eval(scripts[i].innerText);
                    });
                " style="display:none;">
            </div>
            @endif
        @endif
    </div>
</div>
