@extends('layouts.app-layout')

@section('content')
<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Progress Bar -->
            <div class="mb-4">
                <h6 class="text-muted">Progres Profiling Test</h6>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 25%;"></div>
                </div>
                <div class="d-flex justify-content-between mt-2 text-muted small">
                    <span>Mulai</span>
                    <span>Selesai</span>
                </div>
            </div>

            <div class="card shadow border-0">
                <div class="card-header bg-white p-4 text-center border-bottom-0">
                    <h3 class="mb-1 text-primary">Bagian 2: Prior Knowledge</h3>
                    <p class="text-muted mb-0">Uji pengetahuan awal Anda terkait materi kursus ini.</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    
                    <form action="{{ route('student.profiling-test.save-mcq', $course->slug) }}" method="POST">
                        @csrf
                        
                        @foreach($questions as $qIndex => $question)
                        <div class="mb-5 p-4 rounded bg-light border">
                            <h5 class="mb-3">{{ $qIndex + 1 }}. {{ $question->question_text }}</h5>
                            
                            <div class="pl-3">
                                @foreach($question->options as $option)
                                <div class="radio radio-primary mb-3">
                                    <label style="font-size: 1.1rem; cursor:pointer; width: 100%;">
                                        <input type="radio" id="opt_{{ $option->id }}" name="answers[{{ $question->id }}]" value="{{ $option->id }}" required>
                                        <i class="helper"></i> {{ $option->option_text }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                        <div class="text-right mt-4">
                            <button type="submit" class="btn btn-primary btn-lg px-5">Simpan & Lanjutkan <i class="fa fa-arrow-right ml-2"></i></button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
