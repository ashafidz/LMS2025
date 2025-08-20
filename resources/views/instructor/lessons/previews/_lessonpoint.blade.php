{{-- resources/views/instructor/lessons/previews/_lessonpoin.blade.php --}}

{{-- @php
    $totalPointsFromThisLesson = 0;
    if(Auth::check() && !$is_preview) {
        // Ambil total poin yang didapat user dari pelajaran ini saja
        $totalPointsFromThisLesson = Auth::user()->pointHistories()
            ->where('description', 'like', 'Poin manual dari pelajaran: ' . $lesson->title)
            ->sum('points');
    }
@endphp --}}

{{-- <h4 class="font-weight-bold">{{ $lesson->lessonable->title }}</h4>
<p class="text-muted">{{ $lesson->lessonable->description }}</p>
<hr>

<div class="text-center p-4">
    <div class="card" style="max-width: 300px; margin: auto;">
        <div class="card-body">
            <p class="mb-2">Total Poin yang Anda Peroleh di Sesi Ini:</p>
            <h2 class="font-weight-bold text-warning d-flex justify-content-center align-items-center">
                <i class="ti-medall-alt me-2"></i> {{ number_format($totalPointsFromThisLesson, 0, ',', '.') }}
            </h2>
        </div>
    </div>
</div> --}}

@php
    $totalPointsFromThisLesson = 0;
    if(Auth::check() && !$is_preview) {
        // AMBIL DATA DARI TABEL BARU YANG LEBIH ANDAL
        $totalPointsFromThisLesson = \App\Models\LessonPointAward::where('lesson_id', $lesson->id)
            ->where('student_id', Auth::id())
            ->sum('points');
    }
@endphp


<h4 class="font-weight-bold">{{ $lesson->lessonable->title }}</h4>
<p class="text-muted">{{ $lesson->lessonable->description }}</p>
<hr>

<div class="text-center p-4">
    <div class="card" style="max-width: 300px; margin: auto;">
        <div class="card-body">
            <p class="mb-2">Total Poin yang Anda Peroleh di Sesi Ini:</p>
            <h2 class="font-weight-bold text-warning d-flex justify-content-center align-items-center">
                <i class="fa fa-diamond mr-2"></i> {{ number_format($totalPointsFromThisLesson, 0, ',', '.') }}
            </h2>
        </div>
    </div>
</div>