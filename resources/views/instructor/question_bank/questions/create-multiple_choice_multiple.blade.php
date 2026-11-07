@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content rs-page-form">
        <!-- Page-header start -->
        <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], ['Bank Soal', route('instructor.question-bank.topics.index')], [(Str::limit($topic->name, 20)), route('instructor.question-bank.questions.index', $topic)], ['Buat Pertanyaan', null]]">
            <x-slot:title>Buat Pertanyaan Baru</x-slot:title>
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
                                        <span>Lengkapi informasi untuk pertanyaan baru.</span>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.question-bank.questions.store', $topic) }}" method="POST" class="rs-form rs-form--bank">
                                            @csrf
                                            @include('instructor.question_bank.questions.partials._form-errors')
                                            {{-- This hidden input tells the controller what type of question we are creating --}}
                                            <input type="hidden" name="question_type" value="multiple_choice_multiple">

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Teks Pertanyaan</label>
                                                <div class="col-sm-10">
                                                    <textarea rows="5" name="question_text" class="form-control" data-rich="question" placeholder="Masukkan teks pertanyaan lengkap di sini...">{{ old('question_text') }}</textarea>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Nilai</label>
                                                <div class="col-sm-10">
                                                    <input type="number" name="score" class="form-control" value="{{ old('score', 10) }}" required>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Penjelasan (Opsional)</label>
                                                <div class="col-sm-10">
                                                    <textarea rows="3" name="explanation" class="form-control" data-rich="explanation" placeholder="Berikan penjelasan mengapa jawaban ini benar.">{{ old('explanation') }}</textarea>
                                                    <small class="form-text text-muted">Pesan ini akan muncul jika siswa menjawab dengan benar.</small>
                                                </div>
                                            </div>

                                            <hr>

                                            <h5 class="mt-4">Opsi Jawaban</h5>
                                            <p class="mb-4">Tambahkan minimal dua pilihan dan pilih satu atau lebih jawaban yang benar menggunakan kotak centang (checkbox).</p>

                                            {{-- This container will hold all the dynamic answer options --}}
                                            <div id="options-container">
                                                {{-- We will dynamically add option rows here using JavaScript --}}
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <button type="button" class="btn btn-success" onclick="addOption()">
                                                        <i class="fa fa-plus"></i> Tambahkan Opsi Baru
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="form-group row mt-5">
                                                <div class="col-sm-12 text-right">
                                                    <a href="{{ route('instructor.question-bank.questions.index', $topic) }}" class="btn btn-secondary">Batal</a>
                                                    <button type="submit" class="btn btn-primary">Simpan Pertanyaan</button>
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
@include('instructor.question_bank.questions.partials._question-editor-scripts')
<script>
    // Counter to ensure unique IDs and names for new options
    let optionIndex = 0;

    // Function to add a new option row to the container
    // Add an option row. `data` ({text, is_correct}) restores a row after a validation error.
    function addOption(data) {
        const container = document.getElementById('options-container');

        // Create a new div for the option row
        const optionRow = document.createElement('div');
        optionRow.className = 'form-group row align-items-center option-row';
        optionRow.innerHTML = `
            <label class="rs-correct-pick">
                {{-- Using a checkbox allows for multiple selections --}}
                <input type="checkbox" name="options[${optionIndex}][is_correct]" value="1" class="form-check-input"> <span>Jawaban benar</span></label>
            <div class="col-sm-8">
                <textarea name="options[${optionIndex}][text]" data-rich="option"></textarea>
            </div>
            <div class="col-sm-2">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this)">
                    <i class="fa fa-trash"></i> Hapus
                </button>
            </div>
        `;

        // Append the new row to the container and increment the index
        container.appendChild(optionRow);
        const textarea = optionRow.querySelector('textarea[data-rich="option"]');
        if (data) {
            // Set values by DOM property (never inside the template literal), before the editor reads the textarea.
            textarea.value = data.text || '';
            optionRow.querySelector('input[type="checkbox"]').checked = String(data.is_correct) === '1';
        }
        rsMakeOptionEditor(textarea);
        optionIndex++;
    }

    // Function to remove an option row
    function removeOption(button) {
        // Find the parent '.option-row' and remove it
        const row = button.closest('.option-row');
        rsDestroyOptionEditor(row);
        row.remove();
    }

    // Add two default options when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        const oldOptions = @json(array_values(old('options', [])));
        if (oldOptions.length) {
            oldOptions.forEach(o => addOption(o));
        } else {
            addOption();
            addOption();
        }
    });
</script>
@endpush