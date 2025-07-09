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
                                Question Topics
                            </h5>
                            <p class="m-b-0">
                                Manage your question topics for the question bank.
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
                                        <h5>Topic List</h5>
                                        <span>A list of all your question topics.</span>
                                        <div class="card-header-right">
                                            <a href="{{ route('instructor.question-bank.topics.create') }}" class="btn btn-primary">Create Topic</a>
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
                                                        <th>Topic Name</th>
                                                        <th class="text-center">Questions</th>
                                                        <th class="text-center">Status</th>
                                                        <th class="text-center">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($topics as $topic)
                                                        <tr>
                                                            <th scope="row">{{ $loop->iteration + $topics->firstItem() - 1 }}</th>
                                                            <td>
                                                                <h6>{{ $topic->name }}</h6>
                                                                <p class="text-muted m-b-0">{{ Str::limit($topic->description, 70) }}</p>
                                                            </td>
                                                            <td class="text-center">{{ $topic->questions_count }}</td>
                                                            <td class="text-center">
                                                                @if ($topic->is_locked)
                                                                    <label class="label label-danger">Locked</label>
                                                                @else
                                                                    <label class="label label-success">Editable</label>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                <a href="{{ route('instructor.question-bank.questions.index', $topic) }}" class="btn btn-success btn-sm">View Questions</a>
                                                                <a href="{{ route('instructor.question-bank.topics.edit', $topic) }}" class="btn btn-info btn-sm">Edit</a>
                                                                @if ($topic->is_locked)
                                                                    <button class="btn btn-danger btn-sm" disabled>Delete</button>
                                                                @else
                                                                    <form action="{{ route('instructor.question-bank.topics.destroy', $topic) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this topic and all its questions?');">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                                    </form>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center">No topics found. Create one to get started!</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex justify-content-center">
                                            {{ $topics->links() }}
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
