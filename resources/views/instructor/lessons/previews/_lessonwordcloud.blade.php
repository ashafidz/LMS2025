@php
    $wordcloud = $lesson->lessonable;
    $hasVoted = false;
    if(auth()->check()) {
        $hasVoted = $wordcloud->responses()->where('user_id', auth()->id())->exists();
    }
    
    $now = Carbon\Carbon::now();
    $isActive = $wordcloud->is_active;
    
    if ($wordcloud->start_time && $now->isBefore($wordcloud->start_time)) {
        $isActive = false;
    }
    if ($wordcloud->end_time && $now->isAfter($wordcloud->end_time)) {
        $isActive = false;
    }
@endphp

<div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
    <div class="card-header text-white" style="background: linear-gradient(135deg, #6f42c1, #007bff); padding: 20px 25px;">
        <h4 class="mb-0 text-white font-weight-bold" style="font-family: 'Inter', sans-serif;">
            <i class="bi bi-cloud-fill mr-2"></i> {{ $wordcloud->question }}
        </h4>
    </div>
    
    <div class="card-body p-4" id="wordcloud-container-{{ $lesson->id }}">
        @if($wordcloud->description)
            <p class="text-muted mb-4" style="font-size: 1.1rem; line-height: 1.6;">{{ $wordcloud->description }}</p>
        @endif

        @if(!$isActive && !$hasVoted)
            <div class="alert alert-warning rounded-lg" style="border-left: 5px solid #ffc107;">
                Word Cloud ini sedang tidak aktif atau sudah ditutup.
            </div>
        @elseif(!$hasVoted)
            <form id="form-wordcloud-{{ $lesson->id }}" action="{{ route('student.lessons.wordcloud.submit', $lesson->id) }}" data-results-url="{{ route('student.lessons.wordcloud.results.ajax', $lesson->id) }}" method="POST" onsubmit="
                event.preventDefault();
                var form = this;
                var btn = form.querySelector('button[type=submit]');
                var lessonId = {{ $lesson->id }};
                var originalBtnHtml = btn.innerHTML;
                
                btn.disabled = true;
                btn.innerHTML = '<i class=\'fa fa-spinner fa-spin mr-2\'></i>Mengirim...';
                
                var formData = new FormData(form);
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Success, load results
                        var resultsUrl = form.getAttribute('data-results-url');
                        fetch(resultsUrl)
                        .then(res => res.text())
                        .then(html => {
                            document.getElementById('wordcloud-container-' + lessonId).innerHTML = html;
                            
                            // Re-execute scripts in injected HTML
                            const scripts = document.getElementById('wordcloud-container-' + lessonId).querySelectorAll('script');
                            scripts.forEach(oldScript => {
                                const newScript = document.createElement('script');
                                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                                if (oldScript.src) {
                                    newScript.src = oldScript.src;
                                } else {
                                    newScript.textContent = oldScript.textContent;
                                }
                                oldScript.parentNode.replaceChild(newScript, oldScript);
                            });
                        });
                        
                        // Fire event if defined globally
                        if (typeof window.onLessonCompleted === 'function') {
                            window.onLessonCompleted(lessonId);
                        }
                    } else {
                        alert(data.message || 'Terjadi kesalahan.');
                        btn.disabled = false;
                        btn.innerHTML = originalBtnHtml;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan jaringan.');
                    btn.disabled = false;
                    btn.innerHTML = originalBtnHtml;
                });
            ">
                @csrf
                <div class="form-group mb-4">
                    <div class="input-group input-group-lg" style="border-radius: 50px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                        <input type="text" name="word" class="form-control border-0 px-4" placeholder="Ketik jawaban Anda..." required style="height: 60px; font-size: 1.1rem; background-color: #f8f9fa;">
                        <div class="input-group-append">
                            <button class="btn btn-primary px-5" type="submit" style="font-weight: 600; letter-spacing: 1px; text-transform: uppercase;">Kirim</button>
                        </div>
                    </div>
                    <small class="form-text text-muted mt-2 ml-3">Kirimkan 1 kata yang mewakili jawaban Anda.</small>
                </div>
            </form>
        @else
            <!-- Load results automatically if already voted -->
            <div id="wordcloud-results-loader-{{ $lesson->id }}" class="text-center py-5">
                <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                    <span class="sr-only">Memuat hasil...</span>
                </div>
                <p class="mt-3 text-muted">Memuat hasil Word Cloud...</p>
            </div>
            <script>
                // Self-executing function to load results
                (function() {
                    var containerId = 'wordcloud-container-{{ $lesson->id }}';
                    var resultsUrl = '{{ route("student.lessons.wordcloud.results.ajax", $lesson->id) }}';
                    
                    fetch(resultsUrl)
                    .then(response => response.text())
                    .then(html => {
                        var container = document.getElementById(containerId);
                        if (container) {
                            container.innerHTML = html;
                            
                            // execute script tags
                            const scripts = container.querySelectorAll('script');
                            scripts.forEach(oldScript => {
                                const newScript = document.createElement('script');
                                Array.from(oldScript.attributes).forEach(attr => newScript.setAttribute(attr.name, attr.value));
                                if (oldScript.src) {
                                    newScript.src = oldScript.src;
                                } else {
                                    newScript.textContent = oldScript.textContent;
                                }
                                oldScript.parentNode.replaceChild(newScript, oldScript);
                            });
                        }
                    })
                    .catch(error => {
                        console.error("Error loading wordcloud results:", error);
                        document.getElementById('wordcloud-results-loader-{{ $lesson->id }}').innerHTML = '<div class="alert alert-danger">Gagal memuat hasil.</div>';
                    });
                })();
            </script>
        @endif
    </div>
</div>
