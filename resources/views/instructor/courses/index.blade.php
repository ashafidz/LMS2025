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
                                Courses
                            </h5>
                            <p class="m-b-0">
                                Manage your courses here.
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
                                <a href="{{ route('instructor.courses.index') }}">Courses</a>
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
                                        <h5>Course List</h5>
                                        <span>Here is a list of all your courses.</span>
                                        <div class="card-header-right">
                                            <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">Create Course</a>
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
                                                        <th>Title</th>
                                                        <th>Category</th>
                                                        <th>Status</th>
                                                        <th>Price</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($courses as $course)
                                                        <tr>
                                                            <th scope="row">{{ $loop->iteration }}</th>
                                                            <td>{{ $course->title }}</td>
                                                            <td>{{ $course->category->name }}</td>
                                                            <td>
                                                                @switch($course->status)
                                                                    @case('published')
                                                                        <label class="label label-success">Published</label>
                                                                        @break
                                                                    @case('draft')
                                                                        <label class="label label-warning">Draft</label>
                                                                        @break
                                                                    @case('pending_review')
                                                                        <label class="label label-info">Pending Review</label>
                                                                        @break
                                                                    @case('rejected')
                                                                        <label class="label label-danger">Rejected</label>
                                                                        @break
                                                                    @default
                                                                        <label class="label label-default">{{ ucfirst($course->status) }}</label>
                                                                @endswitch
                                                            </td>
                                                            <td>Rp {{ number_format($course->price, 0, ',', '.') }}</td>
                                                            <td>
                                                                <a href="{{ route('instructor.courses.modules.index', $course) }}" class="btn btn-success btn-sm">Modules</a>
                                                                <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-info btn-sm">Edit</a>
                                                                <form action="{{ route('instructor.courses.destroy', $course) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this course?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                                </form>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="6" class="text-center">No courses found.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="d-flex justify-content-center">
                                            {{ $courses->links() }}
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