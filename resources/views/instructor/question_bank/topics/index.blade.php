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
                                                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#topicInfoModal{{ $topic->id }}" title="Informasi Topik">
                                                                <i class="fa fa-info-circle"></i>
                                                            </button>
                                                            <a href="{{ route('instructor.question-bank.questions.index', $topic) }}" class="btn btn-dark  btn-sm"><i class="bi bi-eye me-1"></i></a>
                                                            {{-- <a href="{{ route('instructor.question-bank.topics.edit', $topic->id) }}" class="btn btn-info btn-sm {{ $topic->is_locked ? 'disabled' : '' }}" {{ $topic->is_locked ? 'onclick="return false;"' : '' }}><i class="fa fa-pencil"></i>Edit</a> --}}
                                                            <a href="{{ route('instructor.question-bank.topics.edit', $topic->id) }}" class="btn btn-info btn-sm"><i class="fa fa-pencil"></i></a>
                                                            <form action="{{ route('instructor.question-bank.topics.destroy', $topic->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus topik ini?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" 
                                                                        class="btn btn-danger btn-sm {{ $topic->is_locked ? 'disabled' : '' }}" 
                                                                        {{ $topic->is_locked ? 'disabled' : '' }}>
                                                                    <i class="fa fa-trash"></i>
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

{{-- Modals untuk setiap topik --}}
@foreach ($topics as $topic)
<!-- Topic Information Modal for {{ $topic->name }} -->
<div class="modal fade" id="topicInfoModal{{ $topic->id }}" tabindex="-1" role="dialog" aria-labelledby="topicInfoModalLabel{{ $topic->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="topicInfoModalLabel{{ $topic->id }}">
                    <i class="fa fa-info-circle text-info"></i> Informasi Topik: {{ $topic->name }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <!-- Nama Topik -->
                        <div class="form-group">
                            <label><strong>Nama Topik:</strong></label>
                            <p class="form-control-static">{{ $topic->name }}</p>
                        </div>

                        <!-- Deskripsi -->
                        <div class="form-group">
                            <label><strong>Deskripsi:</strong></label>
                            <p class="form-control-static">{{ $topic->description ?: 'Tidak ada deskripsi' }}</p>
                        </div>

                        <!-- Total Soal -->
                        <div class="form-group">
                            <label><strong>Total Soal:</strong></label>
                            <p class="form-control-static">
                                <span class="badge badge-primary">{{ $topic->questions->count() }} soal</span>
                            </p>
                        </div>

                        <!-- Breakdown Tipe Soal -->
                        <div class="form-group">
                            <label><strong>Breakdown Berdasarkan Tipe Soal:</strong></label>
                            <div class="row">
                                @php
                                    $questionTypes = $topic->questions->groupBy('question_type');
                                    $typeLabels = [
                                        'multiple_choice_single' => 'Pilihan Ganda (Tunggal)',
                                        'multiple_choice_multiple' => 'Pilihan Ganda (Multiple)',
                                        'true_false' => 'Benar/Salah',
                                        'drag_and_drop' => 'Drag & Drop'
                                    ];
                                @endphp
                                @foreach($typeLabels as $type => $label)
                                    <div class="col-md-6 mb-2">
                                        <div class="card bg-light">
                                            <div class="card-body py-2">
                                                <small><strong>{{ $label }}:</strong></small>
                                                <span class="badge badge-secondary float-right">
                                                    {{ $questionTypes->get($type, collect())->count() }} soal
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Status Terkunci -->
                        <div class="form-group">
                            <label><strong>Status:</strong></label>
                            <p class="form-control-static">
                                @if($topic->is_locked)
                                    <span class="badge badge-danger">
                                        <i class="fa fa-lock"></i> Terkunci (digunakan dalam kuis)
                                    </span>
                                @else
                                    <span class="badge badge-success">
                                        <i class="fa fa-unlock"></i> Dapat Diedit
                                    </span>
                                @endif
                            </p>
                        </div>

                        <!-- Ketersediaan Kursus -->
                        <div class="form-group">
                            <label><strong>Tersedia di Kursus:</strong></label>
                            @if($topic->available_for_all_courses)
                                <p class="form-control-static">
                                    <span class="badge badge-success">
                                        <i class="fa fa-globe"></i> Semua Kursus Saya
                                    </span>
                                </p>
                            @else
                                <div class="form-control-static">
                                    @if($topic->courses && $topic->courses->count() > 0)
                                        @foreach($topic->courses as $course)
                                            <span class="badge badge-info mr-1 mb-1">{{ $course->title }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">Tidak tersedia di kursus manapun</span>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Tanggal Dibuat dan Diperbarui -->
                        <div class="form-group">
                            <label><strong>Informasi Waktu:</strong></label>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <strong>Dibuat:</strong><br>
                                        {{ $topic->created_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <strong>Terakhir Diperbarui:</strong><br>
                                        {{ $topic->updated_at->format('d/m/Y H:i') }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                <a href="{{ route('instructor.question-bank.questions.index', $topic) }}" class="btn btn-primary">
                    <i class="bi bi-eye me-1"></i>Lihat Soal
                </a>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection