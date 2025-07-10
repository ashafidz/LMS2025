@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Create New Question</h5>
                            <p class="m-b-0">Type: Drag and Drop (Fill in the Blanks)</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.question-bank.topics.index') }}">Question Topics</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.question-bank.questions.index', $topic) }}">{{ Str::limit($topic->name, 20) }}</a></li>
                            <li class="breadcrumb-item"><a href="#!">Create</a></li>
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
                                        <h5>Question Details</h5>
                                        <span>Fill in the details for your new question.</span>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.question-bank.questions.store', $topic) }}" method="POST">
                                            @csrf
                                            {{-- This hidden input tells the controller what type of question we are creating --}}
                                            <input type="hidden" name="question_type" value="drag_and_drop">

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Question Text</label>
                                                <div class="col-sm-10">
                                                    <textarea rows="5" name="question_text" class="form-control" required placeholder="Example: Paris is the capital of [[BLANK_1]], and Rome is the capital of [[BLANK_2]].">{{ old('question_text') }}</textarea>
                                                    <small class="form-text text-muted">
                                                        <strong>Important:</strong> Use placeholders like <code>[[BLANK_1]]</code>, <code>[[BLANK_2]]</code>, etc., for the gaps where answers should be dropped.
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Score</label>
                                                <div class="col-sm-10">
                                                    <input type="number" name="score" class="form-control" value="{{ old('score', 10) }}" required>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Explanation (Optional)</label>
                                                <div class="col-sm-10">
                                                    <textarea rows="3" name="explanation" class="form-control" placeholder="Explain the full correct sentence or provide context.">{{ old('explanation') }}</textarea>
                                                </div>
                                            </div>

                                            <hr>

                                            <h5 class="mt-4">Answer Words</h5>
                                            <p class="mb-4">
                                                For each blank in your question text, create a corresponding answer word.
                                                <br>You can also add extra words that don't match any blank; these will act as distractors.
                                            </p>

                                            {{-- This container will hold all the dynamic answer options --}}
                                            <div id="options-container">
                                                {{-- We will dynamically add option rows here using JavaScript --}}
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <button type="button" class="btn btn-success" onclick="addOption()">
                                                        <i class="fa fa-plus"></i> Add Another Word
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="form-group row mt-5">
                                                <div class="col-sm-12 text-right">
                                                    <a href="{{ route('instructor.question-bank.questions.index', $topic) }}" class="btn btn-secondary">Cancel</a>
                                                    <button type="submit" class="btn btn-primary">Save Question</button>
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
    // Counter to ensure unique IDs and names for new options
    let optionIndex = 0;

    // Function to add a new option row to the container
    function addOption() {
        const container = document.getElementById('options-container');

        // Create a new div for the option row
        const optionRow = document.createElement('div');
        optionRow.className = 'form-group row align-items-center option-row';
        optionRow.innerHTML = `
            <div class="col-sm-6">
                <label>Answer Word</label>
                <input type="text" name="options[${optionIndex}][text]" class="form-control" placeholder="e.g., France" required>
            </div>
            <div class="col-sm-4">
                <label>Matches Blank ID (Optional)</label>
                <input type="text" name="options[${optionIndex}][gap_id]" class="form-control gap-id-input" placeholder="e.g., BLANK_1">
                <small class="form-text text-muted">Leave empty for distractors.</small>
            </div>
            <div class="col-sm-2">
                <label>&nbsp;</label>
                <button type="button" class="btn btn-danger btn-block" onclick="removeOption(this)">
                    <i class="fa fa-trash"></i> Remove
                </button>
            </div>
            <!-- This hidden input is not needed for drag and drop, the gap_id serves its purpose -->
        `;

        // Append the new row to the container and increment the index
        container.appendChild(optionRow);
        optionIndex++;
    }

    // Function to remove an option row
    function removeOption(button) {
        // Find the parent '.option-row' and remove it
        button.closest('.option-row').remove();
    }

    // Add two default options when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        addOption();
        addOption();
    });
</script>
@endpush