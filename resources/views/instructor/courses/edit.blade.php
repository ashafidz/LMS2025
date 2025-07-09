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
                                Edit your course details.
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
                            <li class="breadcrumb-item">
                                <a href="#!">Edit</a>
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
                                        <h5>Edit Course</h5>
                                        <span>Update the details for your course.</span>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.courses.update', $course->id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')

                                            {{-- Title --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Title</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $course->title) }}" required>
                                                    @error('title')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Category --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Category</label>
                                                <div class="col-sm-10">
                                                    <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                                        @foreach ($categories as $category)
                                                            <option value="{{ $category->id }}" {{ old('category_id', $course->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('category_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Description --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Description</label>
                                                <div class="col-sm-10">
                                                    <textarea name="description" rows="5" class="form-control @error('description') is-invalid @enderror" required>{{ old('description', $course->description) }}</textarea>
                                                    @error('description')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Price --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Price</label>
                                                <div class="col-sm-10">
                                                    <input type="number" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $course->price) }}" min="0" step="0.01" required>
                                                    @error('price')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Status --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Status</label>
                                                <div class="col-sm-10">
                                                    <select name="status" class="form-control @error('status') is-invalid @enderror" required>
                                                        <option value="draft" {{ old('status', $course->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                                        <option value="pending_review" {{ old('status', $course->status) == 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                                                        <option value="published" {{ old('status', $course->status) == 'published' ? 'selected' : '' }}>Published</option>
                                                        <option value="rejected" {{ old('status', $course->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                                        <option value="private" {{ old('status', $course->status) == 'private' ? 'selected' : '' }}>Private</option>
                                                    </select>
                                                    @error('status')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Current Thumbnail --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Current Thumbnail</label>
                                                <div class="col-sm-10">
                                                    @if($course->thumbnail_url)
                                                        <img src="{{ Storage::url($course->thumbnail_url) }}" alt="Thumbnail" width="150">
                                                    @else
                                                        <p>No thumbnail uploaded.</p>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- New Thumbnail --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">New Thumbnail</label>
                                                <div class="col-sm-10">
                                                    <input type="file" name="thumbnail" class="form-control @error('thumbnail') is-invalid @enderror">
                                                    <small class="form-text text-muted">Leave blank if you don't want to change the thumbnail.</small>
                                                    @error('thumbnail')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Availability --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Availability</label>
                                                <div class="col-sm-10">
                                                    <select name="availability_type" id="availability_type" class="form-control @error('availability_type') is-invalid @enderror" required>
                                                        <option value="lifetime" {{ old('availability_type', $course->availability_type) == 'lifetime' ? 'selected' : '' }}>Lifetime</option>
                                                        <option value="period" {{ old('availability_type', $course->availability_type) == 'period' ? 'selected' : '' }}>Period</option>
                                                    </select>
                                                    @error('availability_type')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- Date Period Fields (Initially Hidden) --}}
                                            <div id="date_period_fields" style="{{ old('availability_type', $course->availability_type) == 'period' ? '' : 'display: none;' }}">
                                                <div class="form-group row">
                                                    <label class="col-sm-2 col-form-label">Start Date</label>
                                                    <div class="col-sm-4">
                                                        <input type="date" name="start_date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $course->start_date ? $course->start_date->format('Y-m-d') : '') }}">
                                                        @error('start_date')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <label class="col-sm-2 col-form-label">End Date</label>
                                                    <div class="col-sm-4">
                                                        <input type="date" name="end_date" class="form-control @error('end_date') is-invalid @enderror" value="{{ old('end_date', $course->end_date ? $course->end_date->format('Y-m-d') : '') }}">
                                                        @error('end_date')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group row">
                                                <label class="col-sm-2"></label>
                                                <div class="col-sm-10">
                                                    <button type="submit" class="btn btn-primary">Update Course</button>
                                                    <a href="{{ route('instructor.courses.index') }}" class="btn btn-secondary">Cancel</a>
                                                </div>
                                            </div>
                                        </form>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const availabilityType = document.getElementById('availability_type');
        const datePeriodFields = document.getElementById('date_period_fields');

        availabilityType.addEventListener('change', function () {
            if (this.value === 'period') {
                datePeriodFields.style.display = 'block';
            } else {
                datePeriodFields.style.display = 'none';
            }
        });
    });
</script>
@endpush
