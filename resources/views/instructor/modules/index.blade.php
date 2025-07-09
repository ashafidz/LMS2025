@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Course Modules</h5>
                        <p class="m-b-0">Manage modules for: <strong>{{ $course->title }}</strong></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item">
                            <a href="{{ route('instructor.dashboard') }}"> <i class="fa fa-home"></i> </a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('instructor.courses.index') }}">Courses</a>
                        </li>
                        <li class="breadcrumb-item"><a href="#!">Modules</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- Page-header end -->
    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <!-- Page-body start -->
                <div class="page-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Module List</h5>
                                    <span>Drag and drop modules to reorder them.</span>
                                    <div class="card-header-right">
                                        <a href="{{ route('instructor.courses.modules.create', $course->id) }}" class="btn btn-primary">Create Module</a>
                                    </div>
                                </div>
                                <div class="card-block">
                                    @if (session('success'))
                                        <div class="alert alert-success">
                                            {{ session('success') }}
                                        </div>
                                    @endif
                                    <div id="module-list">
                                        @forelse ($modules as $module)
                                            <div class="card" data-module-id="{{ $module->id }}">
                                                <div class="card-body d-flex justify-content-between align-items-center p-3">
                                                    <div>
                                                        <i class="fa fa-bars text-muted mr-3" style="cursor: move;"></i>
                                                        <strong>{{ $module->title }}</strong>
                                                        <span class="badge badge-info ml-2">{{ $module->lessons->count() }} Lessons</span>
                                                    </div>
                                                    <div>
                                                        <a href="#" class="btn btn-success btn-sm">View Lessons</a>
                                                        <a href="{{ route('instructor.modules.edit', $module->id) }}" class="btn btn-info btn-sm">Edit</a>
                                                        <form action="{{ route('instructor.modules.destroy', $module->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this module and all its lessons?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center">
                                                <p>No modules found for this course. Create one to get started!</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Page-body end -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- We can use jquery-ui sortable which is already included in the layout --}}
<script>
    $(document).ready(function () {
        $("#module-list").sortable({
            handle: '.fa-bars',
            update: function (event, ui) {
                let moduleIds = $(this).children().map(function () {
                    return $(this).data("module-id");
                }).get();

                $.ajax({
                    url: "{{ route('instructor.courses.modules.reorder', $course->id) }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        module_ids: moduleIds
                    },
                    success: function (response) {
                        // You can add a success notification here if you want
                        console.log(response.message);
                    },
                    error: function (xhr) {
                        console.error('Error reordering modules.');
                    }
                });
            }
        });
    });
</script>
@endpush
