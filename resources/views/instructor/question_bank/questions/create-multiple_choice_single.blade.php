@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Buat Pertanyaan Baru</h5>
                            <p class="m-b-0">Tipe Soal: Pilihan Ganda Tunggal</p>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex mt-3">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.question-bank.topics.index') }}">Bank Soal</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.question-bank.questions.index', $topic) }}">{{ Str::limit($topic->name, 20) }}</a></li>
                            <li class="breadcrumb-item"><a href="#!">Buat Pertanyaan</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
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
                                        <form action="{{ route('instructor.question-bank.questions.store', $topic) }}" method="POST">
                                            @csrf
                                            {{-- This hidden input tells the controller what type of question we are creating --}}
                                            <input type="hidden" name="question_type" value="multiple_choice_single">

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Teks Pertanyaan</label>
                                                <div class="col-sm-10">
                                                    <textarea rows="5" name="question_text" class="form-control" required placeholder="Masukkan teks pertanyaan lengkap di sini...">{{ old('question_text') }}</textarea>
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
                                                    <textarea rows="3" name="explanation" class="form-control" placeholder="Berikan penjelasan mengapa jawaban ini benar.">{{ old('explanation') }}</textarea>
                                                    <small class="form-text text-muted">Pesan ini akan muncul jika siswa menjawab dengan benar.</small>
                                                </div>
                                            </div>

                                            <hr>

                                            <h5 class="mt-4">Opsi Jawaban</h5>
                                            <p class="mb-4">Masukkan minimal dua pilihan, dan pilih satu jawaban yang benar dengan tombol pilihan (radio button).</p>

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
            <label class="col-sm-2 col-form-label text-right">
                <input type="radio" name="correct_option" class="form-check-input" required>
            </label>
            <div class="col-sm-8">
                <input type="text" name="options[${optionIndex}][text]" class="form-control" placeholder="Masukkan pilihan jawaban..." required>
                {{-- This hidden input actually stores the 'is_correct' value for the controller --}}
                <input type="hidden" name="options[${optionIndex}][is_correct]" value="0">
            </div>
            <div class="col-sm-2">
                <button type="button" class="btn btn-danger btn-sm" onclick="removeOption(this)">
                    <i class="fa fa-trash"></i> Hapus
                </button>
            </div>
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

    // Event listener to handle radio button selection
    document.getElementById('options-container').addEventListener('change', function(e) {
        // Check if the changed element is one of our radio buttons
        if (e.target && e.target.name === 'correct_option') {
            // First, reset all hidden 'is_correct' inputs to 0
            document.querySelectorAll('.option-row input[type="hidden"]').forEach(hiddenInput => {
                hiddenInput.value = '0';
            });

            // Now, find the hidden input within the same row as the selected radio and set its value to 1
            const selectedRow = e.target.closest('.option-row');
            const hiddenInputInSelectedRow = selectedRow.querySelector('input[type="hidden"]');
            hiddenInputInSelectedRow.value = '1';
        }
    });

    // Add two default options when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        addOption();
        addOption();
    });
</script>
@endpush