@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <x-dash.page-head :crumbs="[['__home', route(Auth::user()->getRoleNames()->first() . '.dashboard')], ['Pertanyaan Ulasan', null]]">
        <x-slot:title>Manajemen Pertanyaan Ulasan</x-slot:title>
        <x-slot:subtitle>Kelola pertanyaan skala Likert untuk ulasan kursus dan instruktur.</x-slot:subtitle>
    </x-dash.page-head>
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
                                        <a href="{{ route(Auth::user()->getRoleNames()->first() . '.likert-questions.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg text-white"></i> Buat Pertanyaan Baru</a>
                                    </div>
                                </div>
                                <div class="card-block table-border-style">
                                    <x-dash.list-toolbar placeholder="Cari teks pertanyaan..." label="Cari pertanyaan" />
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
                                                            <a href="{{ route(Auth::user()->getRoleNames()->first() . '.likert-questions.edit', $question->id) }}" class="btn btn-secondary btn-sm">
                                                                <i class="fa fa-pencil"></i> 
                                                            </a>
                                                            <form action="{{ route(Auth::user()->getRoleNames()->first() . '.likert-questions.destroy', $question->id) }}" method="POST" class="d-inline" data-confirm="Apakah Anda yakin ingin menghapus pertanyaan ini?" data-confirm-danger data-confirm-ok="Ya, hapus">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                                    <i class="fa fa-trash"></i> 
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center">{{ request()->filled('search') ? 'Tidak ada data yang cocok dengan pencarian.' : 'Belum ada pertanyaan yang dibuat.' }}</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <x-dash.pagination :items="$questions" />
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