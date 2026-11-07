@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content rs-page-form">
        <!-- Page-header start -->
        <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], ['Bank Soal', route('instructor.question-bank.topics.index')], [(Str::limit($question->topic->name, 20)), route('instructor.question-bank.questions.index', $question->topic)], ['Edit Pertanyaan', null]]">
            <x-slot:title>Edit Pertanyaan</x-slot:title>
            <x-slot:subtitle>Tipe Soal: Pilihan Ganda (Jawaban Lebih dari Satu)</x-slot:subtitle>
        </x-dash.page-head>
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
                                        <form action="{{ route('instructor.question-bank.questions.update', $question) }}" method="POST" class="rs-form rs-form--bank">
                                            @csrf
                                            @include('instructor.question_bank.questions.partials._form-errors')
                                            @method('PUT')

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Question Text</label>
                                                <div class="col-sm-10">
                                                    <textarea rows="5" name="question_text" class="form-control" data-rich="question">{{ old('question_text', $question->editorText()) }}</textarea>
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
                                                    <textarea rows="3" name="explanation" class="form-control" data-rich="explanation">{{ old('explanation', $question->editorExplanation()) }}</textarea>
                                                </div>
                                            </div>

                                            <hr>

                                            <h5 class="mt-4">Opsi Jawaban</h5>
                                            <p class="mb-4">Ubah opsi dan pilih satu atau lebih jawaban yang benar.</p>

                                            <div id="options-container">
                                                {{-- Pre-populate with existing/old options --}}
                                                @php
                                                    $options = old('options', $question->options->map(fn($opt) => ['text' => $question->editorOption($opt), 'is_correct' => $opt->is_correct]));
                                                @endphp

                                                @foreach ($options as $index => $option)
                                                    <div class="form-group row align-items-center option-row">
                                                        <label class="rs-correct-pick">
                                                            {{-- Checkbox allows multiple selections --}}
                                                            <input type="checkbox" name="options[{{ $index }}][is_correct]" value="1" class="form-check-input" {{ (bool)($option['is_correct'] ?? false) ? 'checked' : '' }}> <span>Jawaban benar</span></label>
                                                        <div class="col-sm-8">
                                                            <textarea name="options[{{ $index }}][text]" data-rich="option">{{ $option['text'] ?? '' }}</textarea>
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this)">
                                                                <i class="fa fa-trash"></i> Hapus
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <button type="button" class="btn btn-success" id="add-option-btn">
                                                        <i class="fa fa-plus"></i> Tambahkan Opsi Baru
                                                    </button>
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

    {{-- HIDDEN TEMPLATE FOR NEW OPTIONS --}}
    <template id="option-template">
        <div class="form-group row align-items-center option-row">
            <label class="rs-correct-pick">
                <input type="checkbox" name="options[__INDEX__][is_correct]" value="1" class="form-check-input"> <span>Jawaban benar</span></label>
            <div class="col-sm-8">
                <textarea name="options[__INDEX__][text]" data-rich="option"></textarea>
            </div>
            <div class="col-sm-2">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this)">
                    <i class="fa fa-trash"></i> Hapus
                </button>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
@include('instructor.question_bank.questions.partials._question-editor-scripts')
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
        const rows = document.querySelectorAll('#options-container .option-row');
        rsMakeOptionEditor(rows[rows.length - 1].querySelector('textarea[data-rich="option"]'));
    });

    function removeOption(button) {
        const row = button.closest('.option-row');
        rsDestroyOptionEditor(row);
        row.remove();
    }
</script>
@endpush