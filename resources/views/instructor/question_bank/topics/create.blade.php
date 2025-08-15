@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Buat Topik Soal Baru</h5>
                        <p class="m-b-0">Buat folder baru untuk mengelompokkan soal Anda.</p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('instructor.question-bank.topics.index') }}">Bank Soal</a></li>
                        <li class="breadcrumb-item"><a href="#!">Buat Topik</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- Page-header end -->

    <div class="pcoded-inner-content">
        <div class="main-body"><div class="page-wrapper"><div class="page-body"><div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header"><h5>Detail Topik</h5></div>
                    <div class="card-block">
                        <form action="{{ route('instructor.question-bank.topics.store') }}" method="POST">
                            @csrf
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Nama Topik</label>
                                <div class="col-sm-10">
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Deskripsi (Opsional)</label>
                                <div class="col-sm-10">
                                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                                </div>
                            </div>

                            {{-- BAGIAN BARU: Ketersediaan Kursus --}}
                            <hr>
                            <h6 class="font-weight-bold mt-4">Ketersediaan Topik</h6>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Opsi Ketersediaan</label>
                                <div class="col-sm-10">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="available_for_all_courses" value="1" id="all-courses-checkbox">
                                        <label class="form-check-label" for="all-courses-checkbox">
                                            Tersedia untuk semua kursus saya.
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row" id="course-list-container">
                                <label class="col-sm-2 col-form-label">Pilih Kursus Spesifik</label>
                                <div class="col-sm-10">
                                    {{-- <div class=" p-3" style="max-height: 200px; overflow-y: auto;"> --}}
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="select-all-courses">
                                            <label class="form-check-label font-weight-bold" for="select-all-courses">
                                                Pilih Semua Kursus
                                            </label>
                                        </div>
                                        @forelse($courses as $course)
                                            <div class="form-check">
                                                <input class="form-check-input course-checkbox" type="checkbox" name="course_ids[]" value="{{ $course->id }}" id="course-{{ $course->id }}">
                                                <label class="form-check-label" for="course-{{ $course->id }}">
                                                    {{ $course->title }}
                                                </label>
                                            </div>
                                        @empty
                                            <p class="text-muted">Anda belum memiliki kursus.</p>
                                        @endforelse
                                    {{-- </div> --}}
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-sm-12 text-right">
                                    <a href="{{ route('instructor.question-bank.topics.index') }}" class="btn btn-secondary">Batal</a>
                                    <button type="submit" class="btn btn-primary">Simpan Topik</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div></div></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const allCoursesCheckbox = document.getElementById('all-courses-checkbox');
    const courseListContainer = document.getElementById('course-list-container');
    const selectAllCourses = document.getElementById('select-all-courses');
    const courseCheckboxes = document.querySelectorAll('.course-checkbox');

    function toggleCourseList() {
        courseListContainer.style.display = allCoursesCheckbox.checked ? 'none' : 'flex';
    }

    allCoursesCheckbox.addEventListener('change', toggleCourseList);

    selectAllCourses.addEventListener('change', function() {
        courseCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Panggil saat halaman dimuat untuk set keadaan awal
    toggleCourseList();
});
</script>
@endpush