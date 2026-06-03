@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Kelola: {{ $component->name }}</h5>
                        <p class="m-b-0"><a href="{{ route('superadmin.profiling-components.index') }}">Kembali ke Daftar Komponen</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Section Dimensi -->
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Daftar Dimensi</h5>
                            <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#modalAddDimension">
                                + Tambah Dimensi
                            </button>
                        </div>
                        <div class="card-block table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Nama Dimensi</th>
                                        <th>Deskripsi</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($component->dimensions as $dim)
                                    <tr>
                                        <td>{{ $dim->order }}</td>
                                        <td>{{ $dim->name }}</td>
                                        <td>{{ $dim->description }}</td>
                                        <td>
                                            <!-- Aksi bisa dikembangkan (Edit/Hapus) -->
                                            <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalEditDimension{{ $dim->id }}">Edit</button>
                                            <form action="{{ route('superadmin.profiling-dimensions.destroy', $dim->id) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-danger btn-sm" onclick="return confirm('Hapus dimensi ini?')">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Belum ada dimensi</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Section Soal -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5>Daftar Soal</h5>
                            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#modalAddQuestion">
                                + Tambah Soal
                            </button>
                        </div>
                        <div class="card-block table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Dimensi</th>
                                        <th>Pertanyaan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($component->dimensions->flatMap->questions->sortBy('order') as $question)
                                    <tr>
                                        <td>{{ $question->order }}</td>
                                        <td>{{ $question->dimension->name ?? '-' }}</td>
                                        <td>{{ $question->question_text }}</td>
                                        <td>
                                            <span class="badge badge-{{ $question->is_active ? 'success' : 'secondary' }}">
                                                {{ $question->is_active ? 'Aktif' : 'Non-aktif' }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#modalEditQuestion{{ $question->id }}">Edit</button>
                                            <form action="{{ route('superadmin.profiling-questions.toggle', $question->id) }}" method="POST" class="d-inline">
                                                @csrf @method('PATCH')
                                                <button class="btn btn-warning btn-sm">Toggle Status</button>
                                            </form>
                                            <form action="{{ route('superadmin.profiling-questions.destroy', $question->id) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-danger btn-sm" onclick="return confirm('Hapus soal ini?')">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Belum ada soal</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add Dimension -->
<div class="modal fade" id="modalAddDimension" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="{{ route('superadmin.profiling-dimensions.store', $component->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah Dimensi</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Dimensi</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>
                <div class="form-group">
                    <label>Order (Urutan)</label>
                    <input type="number" name="order" class="form-control" value="1" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Add Question -->
<div class="modal fade" id="modalAddQuestion" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="{{ route('superadmin.profiling-questions.store', $component->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah Soal</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Dimensi Terkait</label>
                    <select name="dimension_id" class="form-control" required>
                        @foreach($component->dimensions as $dim)
                            <option value="{{ $dim->id }}">{{ $dim->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Pertanyaan</label>
                    <textarea name="question_text" class="form-control" required></textarea>
                </div>
                <div class="form-group">
                    <label>Order (Urutan)</label>
                    <input type="number" name="order" class="form-control" value="{{ $component->dimensions->flatMap->questions->max('order') + 1 }}" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Question -->
@foreach($component->dimensions->flatMap->questions as $question)
<div class="modal fade" id="modalEditQuestion{{ $question->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="{{ route('superadmin.profiling-questions.update', $question->id) }}" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Soal</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Dimensi Terkait</label>
                    <select name="dimension_id" class="form-control" required>
                        @foreach($component->dimensions as $dim)
                            <option value="{{ $dim->id }}" {{ $question->dimension_id == $dim->id ? 'selected' : '' }}>
                                {{ $dim->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Pertanyaan</label>
                    <textarea name="question_text" class="form-control" required>{{ $question->question_text }}</textarea>
                </div>
                <div class="form-group">
                    <label>Order (Urutan)</label>
                    <input type="number" name="order" class="form-control" value="{{ $question->order }}" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endforeach

<!-- Modal Edit Dimension -->
@foreach($component->dimensions as $dim)
<div class="modal fade" id="modalEditDimension{{ $dim->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="{{ route('superadmin.profiling-dimensions.update', $dim->id) }}" method="POST" class="modal-content">
            @csrf @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Dimensi</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Dimensi</label>
                    <input type="text" name="name" class="form-control" value="{{ $dim->name }}" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="description" class="form-control">{{ $dim->description }}</textarea>
                </div>
                <div class="form-group">
                    <label>Order (Urutan)</label>
                    <input type="number" name="order" class="form-control" value="{{ $dim->order }}" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endforeach

@endsection
