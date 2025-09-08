@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Edit Pertanyaan</h5>
                            <p class="m-b-0">Tipe Soal: Pilihan Ganda (Jawban Tunggal)</p>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex mt-3">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.question-bank.topics.index') }}">Bank Soal</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.question-bank.questions.index', $question->topic) }}">{{ Str::limit($question->topic->name, 20) }}</a></li>
                            <li class="breadcrumb-item"><a href="#!">Edit</a></li>
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
                                        <<h5>Detail Pertanyaan</h5>
                                        <span>Ubah detail pertanyaan di bawah ini.</span>
                                    </div>
                                    <div class="card-block">
                                        {{-- Form points to the UPDATE route --}}
                                        <form action="{{ route('instructor.question-bank.questions.update', $question->id) }}" method="POST">
                                            @csrf
                                            @method('PUT') {{-- Use PUT method for updates --}}

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Question Text</label>
                                                <div class="col-sm-10">
                                                    {{-- Pre-fill with existing data --}}
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

                                            <h5 class="mt-4">Opsi pertanyan</h5>
                                            <p class="mb-4">Ubah opsi dan pilih satu jawaban yang benar.</p>

                                            <div id="options-container">
                                                {{-- Pre-populate with existing options from the database, or with old input from a failed validation --}}
                                                @php
                                                    // Prepare the options array. Use old input if it exists, otherwise map from the database model.
                                                    $options = old('options', $question->options->map(fn($opt) => ['text' => $opt->option_text, 'is_correct' => $opt->is_correct]));
                                                @endphp

                                                @foreach ($options as $index => $option)
                                                    <div class="form-group row align-items-center option-row">
                                                        <label class="col-sm-2 col-form-label text-right">
                                                            {{-- The name 'correct_option' is just for the radio group behavior --}}
                                                            <input type="radio" name="correct_option" class="form-check-input" required {{ (bool)($option['is_correct'] ?? false) ? 'checked' : '' }} onchange="updateCorrectState(this)">
                                                        </label>
                                                        <div class="col-sm-8">
                                                            <input type="text" name="options[{{ $index }}][text]" class="form-control" value="{{ $option['text'] ?? '' }}" required>
                                                            {{-- The actual value is stored in this hidden input --}}
                                                            <input type="hidden" name="options[{{ $index }}][is_correct]" value="{{ (bool)($option['is_correct'] ?? false) ? '1' : '0' }}">
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.option-row').remove()">
                                                                <i class="fa fa-trash"></i> Hapus
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <button type="button" class="btn btn-success" id="add-option-btn">
                                                        <i class="fa fa-plus"></i> Tambahkan Opsi Lain
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="form-group row mt-5">
                                                <div class="col-sm-12 text-right">
                                                    <a href="{{ route('instructor.question-bank.questions.index', $question->topic) }}" class="btn btn-secondary">Cancel</a>
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

    {{-- THIS IS THE HIDDEN TEMPLATE FOR NEW OPTIONS --}}
    <template id="option-template">
        <div class="form-group row align-items-center option-row">
            <label class="col-sm-2 col-form-label text-right">
                <input type="radio" name="correct_option" class="form-check-input" required onchange="updateCorrectState(this)">
            </label>
            <div class="col-sm-8">
                <input type="text" name="options[__INDEX__][text]" class="form-control" value="" required>
                <input type="hidden" name="options[__INDEX__][is_correct]" value="0">
            </div>
            <div class="col-sm-2">
                <button type="button" class="btn btn-danger btn-sm" onclick="this.closest('.option-row').remove()">
                    <i class="fa fa-trash"></i> Hapus
                </button>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
<script>
    document.getElementById('add-option-btn').addEventListener('click', function() {
        // Get the template content
        const template = document.getElementById('option-template').innerHTML;
        // Create a unique index to avoid conflicts (using current timestamp)
        const newIndex = Date.now();
        // Replace the placeholder index with the new unique index
        const newRowHtml = template.replace(/__INDEX__/g, newIndex);

        // Append the new row to the container
        document.getElementById('options-container').insertAdjacentHTML('beforeend', newRowHtml);
    });

    function updateCorrectState(selectedRadio) {
        // Find all option rows within the container
        const allOptionRows = document.querySelectorAll('#options-container .option-row');

        allOptionRows.forEach(row => {
            // Find the hidden input and radio button inside this specific row
            const hiddenInput = row.querySelector('input[type="hidden"]');
            const radioInput = row.querySelector('input[type="radio"]');

            // Set the hidden input's value based on whether its corresponding radio button is the one that was selected
            if (hiddenInput && radioInput) {
                hiddenInput.value = (radioInput === selectedRadio) ? '1' : '0';
            }
        });
    }
</script>
@endpush