@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Edit Pelajaran</h5>
                            <p class="m-b-0">Tipe: Kuis</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        {{-- Breadcrumbs --}}
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
                                        <h5>Edit Detail Pelajaran Kuis</h5>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.lessons.update', $lesson->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')

                                            <h6 class="font-weight-bold">Informasi Pelajaran Umum</h6>
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Judul Pelajaran</label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="title" class="form-control"
                                                        value="{{ old('title', $lesson->title) }}" required>
                                                </div>
                                            </div>

                                            <hr>

                                            <h6 class="font-weight-bold mt-4">Informasi Spesifik Kuis</h6>
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Judul Kuis</label>
                                                <div class="col-sm-9">
                                                    <input type="text" name="quiz_title" class="form-control"
                                                        value="{{ old('quiz_title', $lesson->lessonable->title) }}"
                                                        required>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Deskripsi Kuis (Opsional)</label>
                                                <div class="col-sm-9">
                                                    <textarea rows="3" name="quiz_description" class="form-control">{{ old('quiz_description', $lesson->lessonable->description) }}</textarea>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Nilai Kelulusan (%)</label>
                                                <div class="col-sm-9">
                                                    <input type="number" name="pass_mark" class="form-control"
                                                        value="{{ old('pass_mark', $lesson->lessonable->pass_mark) }}"
                                                        required min="0" max="100">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Batas Waktu (Menit)</label>
                                                <div class="col-sm-9">
                                                    <input type="number" name="time_limit" class="form-control"
                                                        value="{{ old('time_limit', $lesson->lessonable->time_limit) }}"
                                                        min="1" placeholder="Kosongkan jika tidak ada batas waktu">
                                                </div>
                                            </div>

                                            {{-- BAGIAN BARU: Toggle untuk batas waktu --}}
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Opsi Batas Waktu</label>
                                                <div class="col-sm-9">
                                                    <div class="form-check form-switch">
                                                        <input type="hidden" name="allow_exceed_time_limit" value="0">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="allow_exceed_time_limit" value="1"
                                                            id="allowExceedTime"
                                                            {{ old('allow_exceed_time_limit', $lesson->lessonable->allow_exceed_time_limit) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="allowExceedTime">Izinkan siswa
                                                            tetap mengirim jawaban setelah waktu habis (tidak akan mendapat
                                                            poin).</label>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- BAGIAN BARU: Toggle untuk tampilkan jawaban --}}
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Opsi Hasil Kuis</label>
                                                <div class="col-sm-9">
                                                    <div class="form-check form-switch">
                                                        <input type="hidden" name="reveal_answers" value="0">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="reveal_answers" value="1" id="revealAnswers"
                                                            {{ old('reveal_answers', $lesson->lessonable->reveal_answers) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="revealAnswers">Tampilkan
                                                            rincian jawaban (benar/salah) kepada siswa di halaman
                                                            hasil.</label>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- BAGIAN BARU: Batas Pengerjaan --}}
                                            <div class="form-group row">
                                                <label class="col-sm-3 col-form-label">Batas Pengerjaan</label>
                                                <div class="col-sm-9">
                                                    @php
                                                        $max_attempts = old(
                                                            'max_attempts',
                                                            $lesson->lessonable->max_attempts,
                                                        );
                                                        $limit_type = old(
                                                            'attempt_limit_type',
                                                            is_null($max_attempts) ? 'unlimited' : 'limited',
                                                        );
                                                    @endphp
                                                    <div class="form-radio">
                                                        <div class="radio radio-inline">
                                                            <label>
                                                                <input type="radio" name="attempt_limit_type"
                                                                    value="unlimited"
                                                                    {{ $limit_type == 'unlimited' ? 'checked' : '' }}
                                                                    onchange="toggleMaxAttempts(this.value)">
                                                                <i class="helper"></i>Tanpa Batas
                                                            </label>
                                                        </div>
                                                        <div class="radio radio-inline">
                                                            <label>
                                                                <input type="radio" name="attempt_limit_type"
                                                                    value="limited"
                                                                    {{ $limit_type == 'limited' ? 'checked' : '' }}
                                                                    onchange="toggleMaxAttempts(this.value)">
                                                                <i class="helper"></i>Dibatasi
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div id="max-attempts-container"
                                                        style="{{ $limit_type == 'limited' ? 'display: block;' : 'display: none;' }}"
                                                        class="mt-2">
                                                        <input type="number" name="max_attempts" class="form-control"
                                                            placeholder="Masukkan jumlah percobaan"
                                                            value="{{ $max_attempts }}" min="1">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row mt-4">
                                                <div class="col-sm-12 text-right">
                                                    <a href="{{ route('instructor.modules.lessons.index', $lesson->module) }}"
                                                        class="btn btn-secondary">Batal</a>
                                                    <button type="submit" class="btn btn-primary">Simpan
                                                        Perubahan</button>
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
    function toggleMaxAttempts(value) {
        const container = document.getElementById('max-attempts-container');
        const input = container.querySelector('input');
        if (value === 'limited') {
            container.style.display = 'block';
            input.required = true;
        } else {
            container.style.display = 'none';
            input.required = false;
            input.value = '';
        }
    }
    // Panggil saat halaman dimuat untuk set keadaan awal
    document.addEventListener('DOMContentLoaded', function() {
        const initialValue = document.querySelector('input[name="attempt_limit_type"]:checked').value;
        toggleMaxAttempts(initialValue);
    });
</script>
@endpush