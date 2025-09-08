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
                            <p class="m-b-0">Tipe Soal: Drag and Drop (Isi Bagian yang Kosong)</p>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex mt-3">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.question-bank.topics.index') }}">Bank</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.question-bank.questions.index', $question->topic) }}">{{ Str::limit($question->topic->name, 20) }}</a></li>
                            <li class="breadcrumb-item"><a href="#!">Edit Pertanyaan</a></li>
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
                                        <form action="{{ route('instructor.question-bank.questions.update', $question->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Teks Pertanyaan</label>
                                                <div class="col-sm-10">
                                                    <textarea rows="5" name="question_text" class="form-control" required>{{ old('question_text', $question->question_text) }}</textarea>
                                                    <small class="form-text text-muted">
                                                        <strong>Penting:</strong> Gunakan contoh seperti <code>[[BLANK_1]]</code>, <code>[[BLANK_2]]</code>, dan sebagainya.
                                                    </small>
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

                                            <h5 class="mt-4">Jawaban</h5>
                                            <p class="mb-4">Ubah kata jawaban dan ID kosong yang sesuai. Kosongkan kolom 'Cocok dengan ID Kosong' untuk jawaban pengecoh.</p>

                                            <div id="options-container">
                                                {{-- Pre-populate with existing/old options --}}
                                                @php
                                                    $options = old('options', $question->options->map(fn($opt) => ['text' => $opt->option_text, 'gap_id' => $opt->correct_gap_identifier]));
                                                @endphp

                                                @foreach ($options as $index => $option)
                                                    <div class="form-group row align-items-center option-row">
                                                        <div class="col-sm-6">
                                                            <label>Jawaban</label>
                                                            <input type="text" name="options[{{ $index }}][text]" class="form-control" value="{{ $option['text'] ?? '' }}" required>
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <label>ID Kosong yang Cocok (Optional)</label>
                                                            <input type="text" name="options[{{ $index }}][gap_id]" class="form-control" value="{{ $option['gap_id'] ?? '' }}">
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <label>&nbsp;</label>
                                                            <button type="button" class="btn btn-danger btn-block" onclick="this.closest('.option-row').remove()">
                                                                <i class="fa fa-trash"></i> Hapus
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <button type="button" class="btn btn-success" id="add-option-btn">
                                                        <i class="fa fa-plus"></i> Tambahkan Kata Lain
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