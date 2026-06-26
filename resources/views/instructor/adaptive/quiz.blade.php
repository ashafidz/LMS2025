@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">

    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Kelola Soal Quiz</h5>
                        <p class="m-b-0">Lesson: <strong>{{ $lesson->title }}</strong> | Modul: <strong>{{ $lesson->module->title }}</strong></p>
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('instructor.adaptive.index', [$course, 'archetype' => $lesson->module->archetype_name]) }}" class="btn btn-light btn-sm">
                        <i class="fa fa-arrow-left"></i> Kembali ke Kurikulum
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    
                    @if(session('success'))
                        <div class="alert alert-success border-success">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <i class="icofont icofont-close-line-circled"></i>
                            </button>
                            <strong>Berhasil!</strong> {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger border-danger">
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <i class="icofont icofont-close-line-circled"></i>
                            </button>
                            <strong>Gagal!</strong> Periksa kembali isian soal Anda.
                            <ul class="mb-0 mt-2">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="card">
                        <div class="card-header bg-white border-bottom p-3">
                            <h6 class="m-0 text-dark"><i class="fa fa-list-ol text-primary"></i> Daftar Soal Pilihan Ganda</h6>
                        </div>
                        <div class="card-block">
                            <form action="{{ route('instructor.adaptive.lessons.quiz.update', [$course, $lesson]) }}" method="POST" id="quiz-form">
                                @csrf @method('PUT')
                                
                                <div id="questions-container">
                                    {{-- JS will render questions here --}}
                                </div>
                                
                                <div class="mt-4 text-center">
                                    <button type="button" class="btn btn-outline-success" id="btn-add-question">
                                        <i class="fa fa-plus"></i> Tambah Soal
                                    </button>
                                </div>

                                <hr>
                                <div class="text-right">
                                    <button type="submit" class="btn btn-primary" id="btn-save-quiz">
                                        <i class="fa fa-save"></i> Simpan Semua Soal
                                    </button>
                                </div>
                            </form>
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
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('questions-container');
    const btnAdd = document.getElementById('btn-add-question');
    
    // Load existing questions or create one empty question by default
    let questions = {!! json_encode(old('questions', $lesson->quiz_data ?? [])) !!};
    if (!Array.isArray(questions) || questions.length === 0) {
        questions = [{
            text: '',
            options: ['', '', '', ''],
            correct_index: 0,
            explanation: ''
        }];
    }

    function renderQuestions() {
        container.innerHTML = '';
        questions.forEach((q, qIndex) => {
            const card = document.createElement('div');
            card.className = 'card border mb-4 shadow-sm';
            
            // Default 4 options if not provided
            if (!q.options || q.options.length < 2) {
                q.options = ['', '', '', ''];
            }

            let optionsHtml = '';
            const labels = ['A', 'B', 'C', 'D', 'E', 'F'];
            
            q.options.forEach((opt, optIndex) => {
                const label = labels[optIndex] || (optIndex + 1);
                const isChecked = parseInt(q.correct_index) === optIndex ? 'checked' : '';
                
                optionsHtml += `
                    <div class="input-group mb-2 align-items-center">
                        <div class="input-group-prepend">
                            <div class="input-group-text bg-light">
                                <input type="radio" name="questions[${qIndex}][correct_index]" value="${optIndex}" ${isChecked} required title="Pilih sebagai jawaban benar">
                                <span class="ml-2 font-weight-bold">${label}</span>
                            </div>
                        </div>
                        <input type="text" name="questions[${qIndex}][options][]" class="form-control" value="${escapeHtml(opt)}" placeholder="Teks pilihan jawaban..." required>
                    </div>
                `;
            });

            card.innerHTML = `
                <div class="card-header bg-light d-flex justify-content-between align-items-center p-2 border-bottom">
                    <h6 class="m-0 font-weight-bold">Soal #${qIndex + 1}</h6>
                    <button type="button" class="btn btn-sm btn-danger btn-delete-question" data-index="${qIndex}" title="Hapus Soal">
                        <i class="fa fa-trash m-0"></i>
                    </button>
                </div>
                <div class="card-body p-3">
                    <div class="form-group">
                        <label class="font-weight-bold">Pertanyaan <span class="text-danger">*</span></label>
                        <textarea name="questions[${qIndex}][text]" class="form-control" rows="3" required placeholder="Tuliskan pertanyaan di sini...">${escapeHtml(q.text)}</textarea>
                    </div>
                    
                    <div class="form-group mt-3">
                        <label class="font-weight-bold">Pilihan Jawaban <span class="text-danger">*</span></label>
                        <p class="text-muted small mb-2"><i class="fa fa-info-circle"></i> Pilih *radio button* di sebelah kiri untuk menandai kunci jawaban yang benar.</p>
                        ${optionsHtml}
                    </div>

                    <div class="form-group mt-3">
                        <label class="font-weight-bold">Penjelasan (Opsional)</label>
                        <textarea name="questions[${qIndex}][explanation]" class="form-control" rows="2" placeholder="Penjelasan mengapa jawaban tersebut benar (akan muncul setelah siswa menjawab)...">${escapeHtml(q.explanation || '')}</textarea>
                    </div>
                </div>
            `;
            container.appendChild(card);
        });

        // Attach delete events
        document.querySelectorAll('.btn-delete-question').forEach(btn => {
            btn.addEventListener('click', function() {
                if(confirm('Yakin ingin menghapus soal ini?')) {
                    const idx = this.getAttribute('data-index');
                    questions.splice(idx, 1);
                    renderQuestions();
                }
            });
        });
    }

    btnAdd.addEventListener('click', function() {
        questions.push({
            text: '',
            options: ['', '', '', ''],
            correct_index: 0,
            explanation: ''
        });
        renderQuestions();
    });

    // Helper to prevent XSS/breakage in JS template literal
    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    // Initial render
    renderQuestions();
});
</script>
@endpush
