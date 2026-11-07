@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content rs-page-form">
        <!-- Page-header start -->
        <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], ['Bank Soal', route('instructor.question-bank.topics.index')], [(Str::limit($topic->name, 20)), route('instructor.question-bank.questions.index', $topic)], ['Buat Pertanyaan', null]]">
            <x-slot:title>Buat Pertanyaan Baru</x-slot:title>
            <x-slot:subtitle>Tipe Soal: Drag and Drop (Isi Bagian yang Kosong)</x-slot:subtitle>
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
                                            <input type="hidden" name="question_type" value="drag_and_drop">

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Teks Pertanyaan</label>
                                                <div class="col-sm-10">
                                                    <textarea rows="5" name="question_text" class="form-control" data-rich="question" placeholder="Contoh: Paris adalah ibu kota [[BLANK_1]], dan Roma adalah ibu kota [[BLANK_2]].">{{ old('question_text') }}</textarea>
                                                    <small class="form-text text-muted">
                                                        <strong>Penting:</strong> Gunakan penanda seperti <code>[[BLANK_1]]</code>, <code>[[BLANK_2]]</code>, dan seterusnya untuk bagian yang harus diisi dengan jawaban. Ketik penanda tanpa format tebal/miring.
                                                    </small>
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
                                                    <textarea rows="3" name="explanation" class="form-control" data-rich="explanation" placeholder="Jelaskan kalimat lengkap yang benar atau berikan konteksnya.">{{ old('explanation') }}</textarea>
                                                </div>
                                            </div>

                                            <hr>

                                            <h5 class="mt-4">Answer Words</h5>
                                            <p class="mb-4">
                                                Buat jawaban yang sesuai untuk setiap bagian kosong dalam pertanyaan.
                                                <br>Anda juga dapat menambahkan kata-kata tambahan yang tidak cocok dengan bagian kosong mana pun; kata-kata ini akan berfungsi sebagai pengecoh.
                                            </p>

                                            {{-- This container will hold all the dynamic answer options --}}
                                            <div id="options-container">
                                                {{-- We will dynamically add option rows here using JavaScript --}}
                                            </div>

                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <button type="button" class="btn btn-success" onclick="addOption()">
                                                        <i class="fa fa-plus"></i> Masukkan Kata Tambahan
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
    // Add an option row. `data` ({text, gap_id}) restores a row after a validation error.
    function addOption(data) {
        const container = document.getElementById('options-container');

        // Create a new div for the option row
        const optionRow = document.createElement('div');
        optionRow.className = 'form-group row align-items-center option-row';
        optionRow.innerHTML = `
            <div class="col-sm-6">
                <label>Jawaban</label>
                <input type="text" name="options[${optionIndex}][text]" class="form-control" placeholder="e.g., France" required>
            </div>
            <div class="col-sm-4">
                <label>Cocokkan dengan ID Kosong (Opsional)</label>
                <input type="text" name="options[${optionIndex}][gap_id]" class="form-control gap-id-input" placeholder="e.g., BLANK_1">
                <small class="form-text text-muted">Kosongkan jika digunakan sebagai pengecoh.</small>
            </div>
            <div class="col-sm-2">
                <label>&nbsp;</label>
                <button type="button" class="btn btn-danger btn-block" onclick="removeOption(this)">
                    <i class="fa fa-trash"></i> Hapus
                </button>
            </div>
            <!-- This hidden input is not needed for drag and drop, the gap_id serves its purpose -->
        `;

        // Append the new row to the container and increment the index
        container.appendChild(optionRow);
        if (data) {
            // Set values by DOM property, not inside the template literal.
            optionRow.querySelector('input[name$="[text]"]').value = data.text || '';
            optionRow.querySelector('input[name$="[gap_id]"]').value = data.gap_id || '';
        }
        optionIndex++;
    }

    // Function to remove an option row
    function removeOption(button) {
        // Find the parent '.option-row' and remove it
        button.closest('.option-row').remove();
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