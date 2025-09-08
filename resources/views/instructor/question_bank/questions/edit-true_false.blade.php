@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Edit Question</h5>
                            <p class="m-b-0">Type: True / False</p>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex mt-3">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.question-bank.topics.index') }}">Bank Soal</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.question-bank.questions.index', $question->topic) }}">{{ Str::limit($question->topic->name, 20) }}</a></li>
                            <li class="breadcrumb-item"><a href="#!">Edit Pertaanyaan</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- Page-header end -->

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <div class="page-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Detail Pertanyaan</h5>
                                        <span>Ubah detail pertanyaan di bawah ini.</span>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.question-bank.questions.update', $question->id) }}" method="POST" id="true-false-form">
                                            @csrf
                                            @method('PUT')

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Teks Pertanyaan</label>
                                                <div class="col-sm-10">
                                                    <textarea rows="5" name="question_text" class="form-control" required>{{ old('question_text', $question->question_text) }}</textarea>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Nilai</label>
                                                <div class="col-sm-10">
                                                    <input type="number" name="score" class="form-control" value="{{ old('score', $question->score) }}" required>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Keterangan (Opsional)</label>
                                                <div class="col-sm-10">
                                                    <textarea rows="3" name="explanation" class="form-control">{{ old('explanation', $question->explanation) }}</textarea>
                                                </div>
                                            </div>

                                            <hr>

                                            <h5 class="mt-4">Jawaban Benar</h5>
                                            <p class="mb-4">Pilih apakah pernyataan tersebut benar atau salah.</p>

                                            @php
                                                // Find out which option is the correct one to pre-check the radio button.
                                                // We check old input first, then the database record.
                                                $correctValue = '';
                                                if (is_array(old('options'))) {
                                                    foreach(old('options') as $key => $option) {
                                                        if (isset($option['is_correct']) && $option['is_correct']) {
                                                            $correctValue = old("options.{$key}.text");
                                                            break;
                                                        }
                                                    }
                                                } else {
                                                    $correctOption = $question->options->firstWhere('is_correct', true);
                                                    $correctValue = $correctOption ? $correctOption->option_text : '';
                                                }
                                            @endphp

                                            {{-- Static option for "True" --}}
                                            <div class="form-group row align-items-center">
                                                <label class="col-sm-2 col-form-label text-right">
                                                    <input type="radio" name="correct_option" value="true" class="form-check-input" required {{ $correctValue === 'True' ? 'checked' : '' }}>
                                                </label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="options[0][text]" class="form-control" value="True" readonly>
                                                    <input type="hidden" name="options[0][is_correct]" value="{{ $correctValue === 'True' ? '1' : '0' }}">
                                                </div>
                                            </div>

                                            {{-- Static option for "False" --}}
                                            <div class="form-group row align-items-center">
                                                <label class="col-sm-2 col-form-label text-right">
                                                    <input type="radio" name="correct_option" value="false" class="form-check-input" required {{ $correctValue === 'False' ? 'checked' : '' }}>
                                                </label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="options[1][text]" class="form-control" value="False" readonly>
                                                    <input type="hidden" name="options[1][is_correct]" value="{{ $correctValue === 'False' ? '1' : '0' }}">
                                                </div>
                                            </div>

                                            <div class="form-group row mt-5">
                                                <div class="col-sm-12 text-right">
                                                    <a href="{{ route('instructor.question-bank.questions.index', $question->topic) }}" class="btn btn-secondary">Batal</a>
                                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                                </div>
                                            </div>
                                        </form>
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

@push('scripts')
<script>
    // Event listener to handle radio button selection
    document.getElementById('true-false-form').addEventListener('change', function(e) {
        if (e.target && e.target.name === 'correct_option') {
            const trueHiddenInput = document.querySelector('input[name="options[0][is_correct]"]');
            const falseHiddenInput = document.querySelector('input[name="options[1][is_correct]"]');

            // Set the hidden input values based on which radio was selected
            if (e.target.value === 'true') {
                trueHiddenInput.value = '1';
                falseHiddenInput.value = '0';
            } else {
                trueHiddenInput.value = '0';
                falseHiddenInput.value = '1';
            }
        }
    });
</script>
@endpush