@extends('layouts.app-layout')

@section('content')
<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Progress Bar -->
            <div class="mb-4">
                <h6 class="text-muted">Progres Profiling Test</h6>
                <div class="progress" style="height: 10px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ ($componentOrder - 1) * 25 }}%;"></div>
                </div>
                <div class="d-flex justify-content-between mt-2 text-muted small">
                    <span>Mulai</span>
                    <span>Selesai</span>
                </div>
            </div>

            <div class="card shadow border-0">
                <div class="card-header bg-white p-4 text-center border-bottom-0">
                    <h3 class="mb-1 text-primary">Bagian {{ $componentOrder }}: {{ $component->name }}</h3>
                    <p class="text-muted mb-0">{{ $component->description }}</p>
                </div>
                <div class="card-body p-4 p-md-5">
                    
                    <form action="{{ route('student.profiling-test.save-likert', ['course' => $course->slug, 'componentOrder' => $componentOrder]) }}" method="POST">
                        @csrf
                        
                        @foreach($component->dimensions as $dimIndex => $dimension)
                            @if($dimension->questions->count() > 0)
                            <div class="mb-5">
                                <h5 class="border-bottom pb-2 mb-3 text-secondary">{{ $dimension->name }}</h5>
                                
                                @foreach($dimension->questions as $qIndex => $question)
                                <div class="mb-4 p-3 rounded bg-light border">
                                    <p class="font-weight-bold mb-3">{{ $qIndex + 1 }}. {{ $question->question_text }}</p>
                                    
                                    <div class="d-flex justify-content-between px-2 px-md-5">
                                        @for($i = $component->scale_min; $i <= $component->scale_max; $i++)
                                        <div class="text-center">
                                            <div class="radio radio-primary d-inline-block mb-2">
                                                <label>
                                                    <input type="radio" id="q_{{ $question->id }}_{{ $i }}" name="answers[{{ $question->id }}]" value="{{ $i }}" required>
                                                    <i class="helper"></i>
                                                </label>
                                            </div>
                                            <small class="d-block text-muted">{{ $i }}</small>
                                        </div>
                                        @endfor
                                    </div>
                                    <div class="d-flex justify-content-between px-1 px-md-4 mt-1">
                                        <small class="text-muted font-weight-bold">Sangat Tidak Setuju</small>
                                        <small class="text-muted font-weight-bold">Sangat Setuju</small>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
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
