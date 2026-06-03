@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Kelola Soal Prior Knowledge</h5>
                        <p class="m-b-0">Kursus: {{ $course->title }}</p>
                    </div>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ route('instructor.courses.index') }}" class="btn btn-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </div>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Daftar Soal Pilihan Ganda</h5>
                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalAddQuestion">
                                + Tambah Soal
                            </button>
                        </div>
                        <div class="card-block">
                            @forelse($course->knowledgeQuestions as $question)
                            <div class="card mb-3 border">
                                <div class="card-header bg-light d-flex justify-content-between">
                                    <strong>{{ $question->order }}. {{ $question->question_text }}</strong>
                                    <div>
                                        <button class="btn btn-sm btn-warning text-dark" data-toggle="modal" data-target="#modalEditQuestion-{{ $question->id }}"><i class="fa fa-pencil"></i></button>
                                        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modalAddOption-{{ $question->id }}">+ Opsi</button>
                                        <form action="{{ route('instructor.knowledge-questions.destroy', $question->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-danger btn-sm" onclick="return confirm('Hapus soal ini?')"><i class="fa fa-trash"></i></button>
                                        </form>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group">
                                        @forelse($question->options as $option)
                                        <li class="list-group-item d-flex justify-content-between align-items-center {{ $option->is_correct ? 'list-group-item-success' : '' }}">
                                            <span>
                                                {{ $option->order }}. {{ $option->option_text }}
                                                @if($option->is_correct) <span class="badge badge-success ml-2">Jawaban Benar</span> @endif
                                            </span>
                                            <div>
                                                <button class="btn btn-outline-warning btn-sm" data-toggle="modal" data-target="#modalEditOption-{{ $option->id }}" title="Edit Opsi"><i class="fa fa-pencil"></i></button>
                                                <form action="{{ route('instructor.knowledge-options.destroy', $option->id) }}" method="POST" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Hapus opsi ini?')"><i class="fa fa-times"></i></button>
                                                </form>
                                            </div>
                                        </li>
                                        @empty
                                        <li class="list-group-item text-muted">Belum ada opsi jawaban.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>

                            <!-- Modal Add Option -->
                            <div class="modal fade" id="modalAddOption-{{ $question->id }}" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <form action="{{ route('instructor.knowledge-questions.options.store', $question->id) }}" method="POST" class="modal-content">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Tambah Opsi Jawaban</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Teks Opsi</label>
                                                <input type="text" name="option_text" class="form-control" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Order (Urutan)</label>
                                                <input type="number" name="order" class="form-control" value="{{ $question->options->count() + 1 }}" required>
                                            </div>
                                            <div class="checkbox-fade fade-in-primary mt-3">
                                                <label>
                                                    <input type="checkbox" name="is_correct" value="1" id="is_correct_{{ $question->id }}">
                                                    <span class="cr">
                                                        <i class="cr-icon icofont icofont-ui-check txt-primary"></i>
                                                    </span>
                                                    <span>Tandai sebagai jawaban benar</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-success">Simpan Opsi</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Modal Edit Question -->
                            <div class="modal fade" id="modalEditQuestion-{{ $question->id }}" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <form action="{{ route('instructor.knowledge-questions.update', $question->id) }}" method="POST" class="modal-content">
                                        @csrf @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Soal</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Pertanyaan</label>
                                                <textarea name="question_text" class="form-control" required>{{ $question->question_text }}</textarea>
                                            </div>
                                            <div class="form-group">
                                                <label>Order (Urutan)</label>
                                                <input type="number" name="order" class="form-control" value="{{ $question->order }}" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            @foreach($question->options as $option)
                            <!-- Modal Edit Option -->
                            <div class="modal fade" id="modalEditOption-{{ $option->id }}" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <form action="{{ route('instructor.knowledge-options.update', $option->id) }}" method="POST" class="modal-content">
                                        @csrf @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Edit Opsi Jawaban</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Teks Opsi</label>
                                                <input type="text" name="option_text" class="form-control" value="{{ $option->option_text }}" required>
                                            </div>
                                            <div class="form-group">
                                                <label>Order (Urutan)</label>
                                                <input type="number" name="order" class="form-control" value="{{ $option->order }}" required>
                                            </div>
                                            <div class="checkbox-fade fade-in-primary mt-3">
                                                <label>
                                                    <input type="checkbox" name="is_correct" value="1" id="edit_is_correct_{{ $option->id }}" {{ $option->is_correct ? 'checked' : '' }}>
                                                    <span class="cr">
                                                        <i class="cr-icon icofont icofont-ui-check txt-primary"></i>
                                                    </span>
                                                    <span>Tandai sebagai jawaban benar</span>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            @endforeach


                            @empty
                            <p class="text-center text-muted">Belum ada soal Prior Knowledge untuk kursus ini.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add Question -->
<div class="modal fade" id="modalAddQuestion" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="{{ route('instructor.courses.knowledge-questions.store', $course->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah Soal</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Pertanyaan</label>
                    <textarea name="question_text" class="form-control" required></textarea>
                </div>
                <div class="form-group">
                    <label>Order (Urutan)</label>
                    <input type="number" name="order" class="form-control" value="{{ $course->knowledgeQuestions->count() + 1 }}" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
