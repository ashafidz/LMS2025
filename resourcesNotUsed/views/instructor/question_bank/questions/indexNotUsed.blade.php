@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="page-header-title">
                            <h5 class="m-b-10">
                                Questions for: {{ $topic->name }}
                            </h5>
                            <p class="m-b-0">
                                Manage the questions for this topic.
                            </p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item">
                                <a href="{{ route('instructor.dashboard') }}">
                                    <i class="fa fa-home"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('instructor.question-bank.topics.index') }}">Question Topics</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#!">{{ Str::limit($topic->name, 20) }}</a>
                            </li>
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
                                        <h5>Question List</h5>
                                        <span>Here is a list of all questions for this topic.</span>
                                        <div class="card-header-right">
                                            <button type="button" class="btn btn-primary">Create Question</button>
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
                                                            $isLocked = $question->quizzes_count > 0;
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
                                                                <a href="{{ route('instructor.question-bank.questions.edit', $question->id) }}" class="btn btn-info btn-sm">Edit</a>
                                                                
                                                                <form action="{{ route('instructor.question-bank.questions.clone', $question->id) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-primary btn-sm">Clone</button>
                                                                </form>

                                                                <span {{ $isLocked ? 'data-toggle=tooltip' : '' }} title="{{ $isLocked ? 'This question is used in a quiz and cannot be deleted.' : '' }}">
                                                                    <form action="{{ route('instructor.question-bank.questions.destroy', $question->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this question?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-danger btn-sm" {{ $isLocked ? 'disabled' : '' }}>Delete</button>
                                                                    </form>
                                                                </span>
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
@endsection


@endpush
