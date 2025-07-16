{{-- resources/views/student/quizzes/partials/_quiz_preview_in_lesson.blade.php --}}
<div class="text-center">
    <h4>{{ $lesson->lessonable->title }}</h4>
    <p class="text-muted">{{ $lesson->lessonable->description }}</p>
    <hr>
    <p>Ini adalah sebuah kuis. Klik tombol di bawah untuk memulai.</p>
    <a href="{{ route('student.quiz.start', ['quiz' => $lesson->lessonable->id, 'preview' => $is_preview]) }}" 
       class="btn btn-primary btn-lg" 
       target="_blank">
       Mulai Kuis (di Tab Baru)
    </a>
</div>