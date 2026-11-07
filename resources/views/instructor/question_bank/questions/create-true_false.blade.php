@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content rs-page-form">
        <!-- Page-header start -->
        <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], ['Bank Soal', route('instructor.question-bank.topics.index')], [(Str::limit($topic->name, 20)), route('instructor.question-bank.questions.index', $topic)], ['Buat Pertanyaan', null]]">
            <x-slot:title>Buat Pertanyaan Baru</x-slot:title>
            <x-slot:subtitle>Tipe Soal: Benar / Salah</x-slot:subtitle>
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
                                        <form action="{{ route('instructor.question-bank.questions.store', $topic) }}" method="POST" id="true-false-form" class="rs-form rs-form--bank">
                                            @csrf
                                            @include('instructor.question_bank.questions.partials._form-errors')
                                            {{-- This hidden input tells the controller what type of question we are creating --}}
                                            <input type="hidden" name="question_type" value="true_false">

                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Teks Pertanyaan</label>
                                                <div class="col-sm-10">
                                                    <textarea rows="5" name="question_text" class="form-control" data-rich="question" placeholder="Masukkan pernyataan untuk dinilai sebagai benar atau salah...">{{ old('question_text') }}</textarea>
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
                                                    <textarea rows="3" name="explanation" class="form-control" data-rich="explanation" placeholder="Jelaskan mengapa pernyataan itu benar atau salah.">{{ old('explanation') }}</textarea>
                                                    <small class="form-text text-muted">Pesan ini akan muncul kepada siswa setelah mereka menjawab dengan benar.</small>
                                                </div>
                                            </div>

                                            <hr>

                                            <h5 class="mt-4">Jawaban yang Benar</h5>
                                            <p class="mb-4">Pilih apakah pernyataan yang Anda masukkan benar atau salah.</p>

                                            {{-- Static options for True/False --}}
                                            <div class="form-group row align-items-center">
                                                <label class="rs-correct-pick">
                                                    <input type="radio" name="correct_option" value="true" class="form-check-input" required> <span>Jawaban benar</span></label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="options[0][text]" class="form-control" value="Benar" readonly>
                                                    <input type="hidden" name="options[0][is_correct]" value="0">
                                                </div>
                                            </div>

                                            <div class="form-group row align-items-center">
                                                <label class="rs-correct-pick">
                                                    <input type="radio" name="correct_option" value="false" class="form-check-input" required> <span>Jawaban benar</span></label>
                                                <div class="col-sm-8">
                                                    <input type="text" name="options[1][text]" class="form-control" value="Salah" readonly>
                                                    <input type="hidden" name="options[1][is_correct]" value="0">
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
    // Event listener to handle radio button selection
    document.getElementById('true-false-form').addEventListener('change', function(e) {
        // Check if the changed element is one of our radio buttons
        if (e.target && e.target.name === 'correct_option') {
            // Get the hidden inputs for 'is_correct'
            const trueHiddenInput = document.querySelector('input[name="options[0][is_correct]"]');
            const falseHiddenInput = document.querySelector('input[name="options[1][is_correct]"]');

            // Set the value based on which radio was selected
            if (e.target.value === 'true') {
                trueHiddenInput.value = '1';
                falseHiddenInput.value = '0';
            } else {
                trueHiddenInput.value = '0';
                falseHiddenInput.value = '1';
            }
        }
    });

    // Restore the chosen answer after a validation error, through the same change handler
    (function () {
        const oldAnswer = @json(old('correct_option'));
        if (oldAnswer === 'true' || oldAnswer === 'false') {
            const radio = document.querySelector('input[name="correct_option"][value="' + oldAnswer + '"]');
            radio.checked = true;
            radio.dispatchEvent(new Event('change', { bubbles: true }));
        }
    })();
</script>
@endpush