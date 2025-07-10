@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Edit Question</h5>
                            <p class="m-b-0">Type: Drag and Drop (Fill in the Blanks)</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.question-bank.topics.index') }}">Question Topics</a></li>
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
                                        <h5>Question Details</h5>
                                        <span>Modify the details for your question below.</span>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.question-bank.questions.update', $question->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Question Text</label>
                                                <div class="col-sm-10">
                                                    <textarea rows="5" name="question_text" class="form-control" required>{{ old('question_text', $question->question_text) }}</textarea>
                                                    <small class="form-text text-muted">
                                                        <strong>Important:</strong> Use placeholders like <code>[[BLANK_1]]</code>, <code>[[BLANK_2]]</code>, etc.
                                                    </small>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Score</label>
                                                <div class="col-sm-10">
                                                    <input type="number" name="score" class="form-control" value="{{ old('score', $question->score) }}" required>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Explanation (Optional)</label>
                                                <div class="col-sm-10">
                                                    <textarea rows="3" name="explanation" class="form-control">{{ old('explanation', $question->explanation) }}</textarea>
                                                </div>
                                            </div>

                                            <hr>

                                            <h5 class="mt-4">Answer Words</h5>
                                            <p class="mb-4">Modify the answer words and their corresponding blank IDs. Leave "Matches Blank ID" empty for distractors.</p>

                                            <div id="options-container">
                                                {{-- Pre-populate with existing/old options --}}
                                                @php
                                                    $options = old('options', $question->options->map(fn($opt) => ['text' => $opt->option_text, 'gap_id' => $opt->correct_gap_identifier]));
                                                @endphp

                                                @foreach ($options as $index => $option)
                                                    <div class="form-group row align-items-center option-row">
                                                        <div class="col-sm-6">
                                                            <label>Answer Word</label>
                                                            <input type="text" name="options[{{ $index }}][text]" class="form-control" value="{{ $option['text'] ?? '' }}" required>
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <label>Matches Blank ID (Optional)</label>
                                                            <input type="text" name="options[{{ $index }}][gap_id]" class="form-control" value="{{ $option['gap_id'] ?? '' }}">
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <label>&nbsp;</label>
                                                            <button type="button" class="btn btn-danger btn-block" onclick="this.closest('.option-row').remove()">
                                                                <i class="fa fa-trash"></i> Remove
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <button type="button" class="btn btn-success" id="add-option-btn">
                                                        <i class="fa fa-plus"></i> Add Another Word
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="form-group row mt-5">
                                                <div class="col-sm-12 text-right">
                                                    <a href="{{ route('instructor.question-bank.questions.index', $question->topic) }}" class="btn btn-secondary">Cancel</a>
                                                    <button type="submit" class="btn btn-primary">Save Changes</button>
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

    {{-- HIDDEN TEMPLATE FOR NEW OPTIONS --}}
    <template id="option-template">
        <div class="form-group row align-items-center option-row">
            <div class="col-sm-6">
                <label>Answer Word</label>
                <input type="text" name="options[__INDEX__][text]" class="form-control" value="" required>
            </div>
            <div class="col-sm-4">
                <label>Matches Blank ID (Optional)</label>
                <input type="text" name="options[__INDEX__][gap_id]" class="form-control">
            </div>
            <div class="col-sm-2">
                <label>&nbsp;</label>
                <button type="button" class="btn btn-danger btn-block" onclick="this.closest('.option-row').remove()">
                    <i class="fa fa-trash"></i> Remove
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
        // Create a unique index to avoid conflicts
        const newIndex = Date.now();
        // Replace the placeholder index with the new unique index
        const newRowHtml = template.replace(/__INDEX__/g, newIndex);

        // Append the new row to the container
        document.getElementById('options-container').insertAdjacentHTML('beforeend', newRowHtml);
    });
</script>
@endpush