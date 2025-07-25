@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Manajemen Pertanyaan Ulasan</h5>
                        <p class="m-b-0">Kelola pertanyaan skala Likert untuk ulasan kursus dan instruktur.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route(Auth::user()->getRoleNames()->first() . '.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Pertanyaan Ulasan</a></li>
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
                                    <h5>Daftar Pertanyaan</h5>
                                    <div class="card-header-right">
                                        <a href="{{ route(Auth::user()->getRoleNames()->first() . '.likert-questions.create') }}" class="btn btn-primary">Buat Pertanyaan Baru</a>
                                    </div>
                                </div>
                                <div class="card-block table-border-style">
                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Teks Pertanyaan</th>
                                                    <th>Kategori</th>
                                                    <th>Status</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($questions as $question)
                                                    <tr>
                                                        <th scope="row">{{ $loop->iteration + $questions->firstItem() - 1 }}</th>
                                                        <td>{{ $question->question_text }}</td>
                                                        <td>
                                                            @if($question->category == 'course')
                                                                <label class="label label-info">Kursus</label>
                                                            @elseif ($question->category == 'instructor')
                                                                <label class="label label-primary">Instruktur</label>
                                                            @elseif ($question->category == 'platform')
                                                                <label class="label label-warning">Platform</label>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($question->is_active)
                                                                <label class="label label-success">Aktif</label>
                                                            @else
                                                                <label class="label label-default">Tidak Aktif</label>
                                                            @endif
                                                        </td>
                                                        <td class="text-center">
                                                            <a href="{{ route(Auth::user()->getRoleNames()->first() . '.likert-questions.edit', $question->id) }}" class="btn btn-info btn-sm">
                                                                <i class="fa fa-pencil"></i> Edit
                                                            </a>
                                                            <form action="{{ route(Auth::user()->getRoleNames()->first() . '.likert-questions.destroy', $question->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pertanyaan ini?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm">
                                                                    <i class="fa fa-trash"></i> Hapus
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center">Belum ada pertanyaan yang dibuat.</td>
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
            </div>
        </div>
    </div>
</div>
@endsection