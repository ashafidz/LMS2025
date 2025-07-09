@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="page-header-title">
                            <h5 class="m-b-10">
                                Bank Soal
                            </h5>
                            <p class="m-b-0">
                                Edit Soal: {{ Str::limit($question->question_text, 50) }}
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('instructor.question-bank.topics.index') }}">Topik Soal</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('instructor.question-bank.questions.index', $question->topic->id) }}">List Soal</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#!">Edit Soal</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- Page-header end -->
        <div class="pcoded-inner-content">
            <!-- Main-body start -->
            <div class="main-body">
                <div class="page-wrapper">
                    <!-- Page-body start -->
                    <div class="page-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Edit Soal</h5>
                                        <span>Perbarui detail soal dan opsi jawaban.</span>
                                    </div>
                                    <div class="card-block">
                                        <h4 class="sub-title">Form Edit Soal</h4>
                                        <form action="{{ route('instructor.question-bank.questions.update', $question->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')

                                            {{-- Question Text --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Teks Soal</label>
                                                <div class="col-sm-10">
                                                    <textarea name="question_text" id="question_text" rows="3" class="form-control @error('question_text') is-invalid @enderror" placeholder="Masukkan teks soal">{{ old('question_text', $question->question_text) }}</textarea>
                                                    @error('question_text')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                    <small class="form-text text-muted" id="drag_and_drop_hint" style="display: none;">
                                                        Untuk soal Drag & Drop, gunakan `[[BLANK_1]]`, `[[BLANK_2]]` dst. sebagai placeholder.
                                                    </small>
                                                </div>
                                            </div>

                                            {{-- Score --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Skor</label>
                                                <div class="col-sm-10">
                                                    <input type="number" name="score" id="score" class="form-control @error('score') is-invalid @enderror" value="{{ old('score', $question->score) }}" min="1" required>
                                                    @error('score')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Question Type (Read-only) --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Tipe Soal</label>
                                                <div class="col-sm-10">
                                                    <input type="text" class="form-control" value="{{ ucfirst(str_replace('_', ' ', $question->question_type)) }}" readonly>
                                                    <input type="hidden" name="question_type" id="question_type" value="{{ $question->question_type }}">
                                                </div>
                                            </div>

                                            {{-- Options Section (Dynamic) --}}
                                            <div id="options_section" style="display: none;">
                                                <h4 class="sub-title">Opsi Jawaban</h4>
                                                <div id="options_container">
                                                    {{-- Options will be pre-populated and added here by JavaScript --}}
                                                </div>
                                                <button type="button" id="add_option_button" class="btn btn-success btn-sm mt-2">Tambah Opsi</button>
                                            </div>

                                            {{-- True/False Options (Fixed) --}}
                                            <div id="true_false_options" style="display: none;">
                                                <h4 class="sub-title">Opsi Benar/Salah</h4>
                                                <div class="form-group row">
                                                    <label class="col-sm-2 col-form-label">Jawaban Benar</label>
                                                    <div class="col-sm-10">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="true_false_answer" id="true_answer" value="true" {{ old('true_false_answer', $question->options->where('option_text', 'true')->first()->is_correct ?? false) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="true_answer">Benar</label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="true_false_answer" id="false_answer" value="false" {{ old('true_false_answer', $question->options->where('option_text', 'false')->first()->is_correct ?? false) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="false_answer">Salah</label>
                                                        </div>
                                                        @error('true_false_answer')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex mt-4">
                                                <a href="{{ route('instructor.question-bank.questions.index', $question->topic->id) }}" class="btn btn-danger mr-3">
                                                    <i class="icofont icofont-close"></i>
                                                    Batal
                                                </a>
                                                <button class="btn btn-primary" type="submit" >
                                                    <i class="icofont icofont-save"></i>
                                                    Simpan Perubahan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Page-body end -->
                </div>
                <div id="styleSelector"></div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const questionTypeInput = document.getElementById('question_type'); // Hidden input for type
            const questionType = questionTypeInput.value; // Get the stored type
            const optionsSection = document.getElementById('options_section');
            const optionsContainer = document.getElementById('options_container');
            const addOptionButton = document.getElementById('add_option_button');
            const trueFalseOptions = document.getElementById('true_false_options');
            const dragAndDropHint = document.getElementById('drag_and_drop_hint');
            const questionTextarea = document.getElementById('question_text');

            let optionIndex = 0; // To keep track of option fields

            function showHideFields() {
                // Reset visibility
                optionsSection.style.display = 'none';
                trueFalseOptions.style.display = 'none';
                dragAndDropHint.style.display = 'none';
                optionsContainer.innerHTML = ''; // Clear dynamic options (will be re-populated)

                if (questionType === 'multiple_choice_single' || questionType === 'multiple_choice_multiple') {
                    optionsSection.style.display = 'block';
                    addOptionButton.style.display = 'inline-block';
                    // Pre-populate existing options
                    @if($question->options->isNotEmpty())
                        @foreach($question->options as $option)
                            addOption(
                                questionType,
                                "{{ $option->option_text }}",
                                {{ $option->is_correct ? 'true' : 'false' }},
                                "" // No gapId for MC
                            );
                        @endforeach
                    @else
                        // Add initial empty options if none exist
                        addOption(questionType);
                        addOption(questionType);
                    @endif
                } else if (questionType === 'true_false') {
                    trueFalseOptions.style.display = 'block';
                } else if (questionType === 'drag_and_drop') {
                    optionsSection.style.display = 'block';
                    dragAndDropHint.style.display = 'block';
                    addOptionButton.style.display = 'inline-block';
                    // Pre-populate existing options
                    @if($question->options->isNotEmpty())
                        @foreach($question->options as $option)
                            addOption(
                                questionType,
                                "{{ $option->option_text }}",
                                {{ $option->is_correct ? 'true' : 'false' }},
                                "{{ $option->correct_gap_identifier }}"
                            );
                        @endforeach
                    @else
                        // Add initial empty options if none exist
                        addOption(questionType);
                        addOption(questionType);
                    @endif
                }
            }

            function addOption(type, optionText = '', isCorrect = false, gapId = '') {
                const currentOptionIndex = optionIndex++; // Use and then increment
                const optionDiv = document.createElement('div');
                optionDiv.classList.add('form-group', 'row', 'align-items-center', 'option-row');
                optionDiv.innerHTML = `
                    <label class="col-sm-2 col-form-label">Opsi ${currentOptionIndex + 1}</label>
                    <div class="col-sm-7">
                        <input type="text" name="options[${currentOptionIndex}][text]" class="form-control" placeholder="Teks Opsi" value="${optionText}" required>
                    </div>
                    <div class="col-sm-1">
                        ${type === 'multiple_choice_single' ?
                            `<div class="form-check">
                                <input class="form-check-input" type="radio" name="options_correct" value="${currentOptionIndex}" ${isCorrect ? 'checked' : ''}>
                                <label class="form-check-label">Benar</label>
                            </div>`
                        : type === 'multiple_choice_multiple' ?
                            `<div class="form-check">
                                <input class="form-check-input" type="checkbox" name="options[${currentOptionIndex}][is_correct]" value="1" ${isCorrect ? 'checked' : ''}>
                                <label class="form-check-label">Benar</label>
                            </div>`
                        : type === 'drag_and_drop' ?
                            `<input type="text" name="options[${currentOptionIndex}][gap_id]" class="form-control form-control-sm" placeholder="BLANK_X" value="${gapId}">
                            `
                        : ''}
                    </div>
                    ${type === 'drag_and_drop' ? 
                    `<div class="col-sm-1">
                        <div class="form-check mt-1 mr-5">
                            <input class="form-check-input" type="checkbox" name="options[${currentOptionIndex}][is_correct]" value="1" ${isCorrect ? 'checked' : ''}>
                            <label class="form-check-label">Salah?</label>
                        </div>    
                    </div>`
                    : ''}
                    <div class="col-sm-1 text-right">
                        <button type="button" class="btn btn-danger btn-sm remove-option-button">X</button>
                    </div>
                `;
                optionsContainer.appendChild(optionDiv);

                // Add event listener for remove button
                optionDiv.querySelector('.remove-option-button').addEventListener('click', function() {
                    optionDiv.remove();
                });
            }

            // Event Listeners
            addOptionButton.addEventListener('click', function() {
                addOption(questionType); // Use the fixed questionType
            });

            // Initial call to set up fields based on existing question data
            showHideFields();

            // Handle old input for dynamic options (if validation failed)
            @if(old('options'))
                optionsContainer.innerHTML = ''; // Clear pre-populated options
                optionIndex = 0; // Reset index for old input
                @foreach(old('options') as $index => $option)
                    addOption(
                        questionType,
                        "{{ $option['text'] ?? '' }}",
                        {{ isset($option['is_correct']) && $option['is_correct'] ? 'true' : 'false' }},
                        "{{ $option['gap_id'] ?? '' }}"
                    );
                @endforeach
            @endif

            // Handle old input for true/false (if validation failed)
            @if(old('true_false_answer'))
                document.getElementById('{{ old('true_false_answer') == 'true' ? 'true_answer' : 'false_answer' }}').checked = true;
            @endif
        });
    </script>
    @endpush
@endsection