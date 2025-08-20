@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Bank Soal</h5>
                        <p class="m-b-0">Kelola semua topik untuk bank soal Anda.</p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Bank Soal</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- Page-header end -->

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Daftar Topik</h5>
                                    <span>Anda dapat menambah, mengedit, atau menghapus topik di bawah ini.</span>
                                    <div class="card-header-right">
                                        <a href="{{ route('instructor.question-bank.topics.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg text-white"></i>Buat Topik Baru</a>
                                    </div>
                                </div>
                                <div class="card-block">
                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif

                                    {{-- FORM FILTER BARU --}}
                                    <form action="{{ route('instructor.question-bank.topics.index') }}" method="GET" class="mb-4">
                                        <div class="form-group row align-items-end">
                                            <div class="col-md-4">
                                                <label for="filter_course">Tampilkan Topik Untuk Kursus:</label>
                                                <select name="filter_course" id="filter_course" class="form-control" onchange="this.form.submit()">
                                                    <option value="all" {{ request('filter_course') == 'all' ? 'selected' : '' }}>Tampilkan Semua Topik Saya</option>
                                                    <option value="global" {{ request('filter_course') == 'global' ? 'selected' : '' }}>Topik yang muncul di Semua Kursus Saya</option>
                                                    <optgroup label="Kursus Spesifik">
                                                        @foreach($courses as $course)
                                                            <option value="{{ $course->id }}" {{ request('filter_course') == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                </select>
                                            </div>
                                        </div>
                                    </form>

                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Nama Topik</th>
                                                    <th>Jumlah Soal</th>
                                                    <th>Ketersediaan</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($topics as $topic)
                                                    <tr>
                                                        <td>
                                                            <strong><a href="{{ route('instructor.question-bank.questions.index', $topic) }}">{{ $topic->name }}</a></strong>
                                                            <p class="text-muted mb-0">{{ Str::limit($topic->description, 70) }}</p>
                                                        </td>
                                                        <td>{{ $topic->questions_count }} Soal</td>
                                                        <td>
                                                            @if($topic->available_for_all_courses)
                                                                <label class="label label-info">Semua Kursus</label>
                                                            @else
                                                                <label class="label label-default">Kursus Spesifik</label>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <a href="{{ route('instructor.question-bank.questions.index', $topic) }}" class="btn btn-dark  btn-sm"><i class="bi bi-eye me-1"></i>Lihat Soal</a>
                                                            {{-- <a href="{{ route('instructor.question-bank.topics.edit', $topic->id) }}" class="btn btn-info btn-sm {{ $topic->is_locked ? 'disabled' : '' }}" {{ $topic->is_locked ? 'onclick="return false;"' : '' }}><i class="fa fa-pencil"></i>Edit</a> --}}
                                                            <a href="{{ route('instructor.question-bank.topics.edit', $topic->id) }}" class="btn btn-info btn-sm"><i class="fa fa-pencil"></i>Edit</a>
                                                            <form action="{{ route('instructor.question-bank.topics.destroy', $topic->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus topik ini?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" 
                                                                        class="btn btn-danger btn-sm {{ $topic->is_locked ? 'disabled' : '' }}" 
                                                                        {{ $topic->is_locked ? 'disabled' : '' }}>
                                                                    <i class="fa fa-trash"></i>Hapus
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-center">Tidak ada topik yang cocok dengan filter Anda.</td>
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
            </div>
        </div>
    </div>
</div>
@endsection