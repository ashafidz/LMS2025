@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Pertanyaan Untuk Topik: {{ $topic->name }}</h5>
                            <p class="m-b-0">Kelola pertanyaan untuk topik ini.</p>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex mt-3">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item">
                                <a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.question-bank.topics.index') }}">Bank Soal</a></li>
                            <li class="breadcrumb-item"><a href="#!">{{ Str::limit($topic->name, 20) }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <!-- Page-header end -->
        <div class="pcoded-inner-content">
            <!-- Main-body start -->
            <div class="main-body">
                <div class="page-wrapper">
                    <!-- Page-body start -->
                    <div class="page-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>List Pertanyaan</h5>
                                        <span>Berikut adalah daftar semua pertanyaan untuk topik ini.</span>
                                        <div class="card-header-right">
                                            {{-- UPDATE: This button now triggers the modal --}}
                                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#selectQuestionTypeModal"><i class="bi bi-plus-lg text-white"></i>Buat Pertanyaan</button>
                                        </div>
                                    </div>
                                    <div class="card-block table-border-style">
                                        @if (session('success'))
                                            <div class="alert alert-success">
                                                {{ session('success') }}
                                            </div>
                                        @endif
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Question</th>
                                                        <th>Type</th>
                                                        <th class="text-center">Status</th>
                                                        <th class="text-center">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($questions as $question)
                                                        @php
                                                            // Check if the question is used in any quiz.
                                                            // This assumes your controller uses `with('quizzes')`.
                                                            $isLocked = $question->quizzes->isNotEmpty();
                                                        @endphp
                                                        <tr>
                                                            <th scope="row">{{ $loop->iteration + $questions->firstItem() - 1 }}</th>
                                                            <td>{{ Str::limit(strip_tags($question->question_text), 80) }}</td>
                                                            <td>{{ ucfirst(str_replace('_', ' ', $question->question_type)) }}</td>
                                                            <td class="text-center">
                                                                @if ($isLocked)
                                                                    <label class="label label-danger">Locked</label>
                                                                @else
                                                                    <label class="label label-success">Editable</label>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                {{-- Show Edit button only if not locked --}}
                                                                @if (!$isLocked)
                                                                    <a href="{{ route('instructor.question-bank.questions.edit', $question->id) }}" class="btn btn-info btn-sm"><i class="fas fa-edit"></i>Edit</a>
                                                                @endif

                                                                {{-- Always show Clone button --}}
                                                                <form action="{{ route('instructor.question-bank.questions.clone', $question->id) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-clone"></i>Duplikat</button>
                                                                </form>

                                                                {{-- Disable Delete button if locked --}}
                                                                <form action="{{ route('instructor.question-bank.questions.destroy', $question->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this question?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger btn-sm" {{ $isLocked ? 'disabled' : '' }} title="{{ $isLocked ? 'This question is locked and cannot be deleted.' : '' }}"><i class="fas fa-trash"></i>Hapus</button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center">No questions found for this topic.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex justify-content-center">
                                            {{ $questions->links() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Page-body end -->
                </div>
                <div id="styleSelector"></div>
            </div>
        </div>
    </div>

    <!-- ADDITION: Modal for Selecting Question Type -->
    <div class="modal fade" id="selectQuestionTypeModal" tabindex="-1" role="dialog" aria-labelledby="selectQuestionTypeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="selectQuestionTypeModalLabel">Choose Question Type</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Please select the type of question you would like to create.</p>
                    <div class="list-group">
                        {{-- Link to create Multiple Choice (Single) --}}
                        <a href="{{ route('instructor.question-bank.questions.create', ['topic' => $topic, 'type' => 'multiple_choice_single']) }}" class="list-group-item list-group-item-action">
                            <strong>Multiple Choice (Single Answer)</strong>
                            <br><small>Students can only select one correct answer from a list.</small>
                        </a>
                        {{-- Link to create Multiple Choice (Multiple) --}}
                        <a href="{{ route('instructor.question-bank.questions.create', ['topic' => $topic, 'type' => 'multiple_choice_multiple']) }}" class="list-group-item list-group-item-action">
                            <strong>Multiple Choice (Multiple Answers)</strong>
                            <br><small>Students can select more than one correct answer.</small>
                        </a>
                        {{-- Link to create True/False --}}
                        <a href="{{ route('instructor.question-bank.questions.create', ['topic' => $topic, 'type' => 'true_false']) }}" class="list-group-item list-group-item-action">
                            <strong>True / False</strong>
                            <br><small>A simple question with "True" or "False" as answers.</small>
                        </a>
                        {{-- Link to create Drag & Drop --}}
                        <a href="{{ route('instructor.question-bank.questions.create', ['topic' => $topic, 'type' => 'drag_and_drop']) }}" class="list-group-item list-group-item-action">
                            <strong>Drag and Drop (Fill in the Blanks)</strong>
                            <br><small>Students drag words into the correct blanks in a sentence.</small>
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>
@endsection