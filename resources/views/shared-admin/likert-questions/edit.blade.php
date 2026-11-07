@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content rs-page-form">
    <!-- Page-header start -->
    <x-dash.page-head :crumbs="[['__home', route(Auth::user()->getRoleNames()->first() . '.dashboard')], ['Pertanyaan Ulasan', route(Auth::user()->getRoleNames()->first() . '.likert-questions.index')], ['Edit', null]]">
        <x-slot:title>Edit Pertanyaan Ulasan</x-slot:title>
        <x-slot:subtitle>Perbarui detail untuk pertanyaan skala Likert.</x-slot:subtitle>
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
                                    <h5>Detail Pertanyaan</h5>
                                </div>
                                <div class="card-block">
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <form action="{{ route(Auth::user()->getRoleNames()->first() . '.likert-questions.update', $likertQuestion->id) }}" method="POST" class="rs-form">
                                        @csrf
                                        @method('PUT') {{-- Wajib untuk metode update --}}

                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Teks Pertanyaan</label>
                                            <div class="col-sm-10">
                                                <textarea name="question_text" class="form-control" rows="4" required>{{ old('question_text', $likertQuestion->question_text) }}</textarea>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Kategori Pertanyaan</label>
                                            <div class="col-sm-10">
                                                <select name="category" class="form-control" required>
                                                    <option value="course" {{ old('category', $likertQuestion->category) == 'course' ? 'selected' : '' }}>Untuk Menilai Kursus</option>
                                                    <option value="instructor" {{ old('category', $likertQuestion->category) == 'instructor' ? 'selected' : '' }}>Untuk Menilai Instruktur</option>
                                                    <option value="platform" {{ old('category', $likertQuestion->category) == 'platform' ? 'selected' : '' }}>Untuk Menilai Platform</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Status</label>
                                            <div class="col-sm-10">
                                                <div class="form-radio">
                                                    <div class="radio radio-inline">
                                                        <label><input type="radio" name="is_active" value="1" {{ old('is_active', $likertQuestion->is_active) == 1 ? 'checked' : '' }}><i class="helper"></i>Aktif</label>
                                                    </div>
                                                    <div class="radio radio-inline">
                                                        <label><input type="radio" name="is_active" value="0" {{ old('is_active', $likertQuestion->is_active) == 0 ? 'checked' : '' }}><i class="helper"></i>Tidak Aktif</label>
                                                    </div>
                                                </div>
                                                <small class="form-text text-muted">Hanya pertanyaan yang aktif yang akan ditampilkan kepada siswa.</small>
                                            </div>
                                        </div>

                                                                                <div class="rs-form-actions">
                                            <a href="{{ route(Auth::user()->getRoleNames()->first() . '.likert-questions.index') }}" class="btn btn-secondary">Batal</a>
                                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                        </div>
                                    </form>
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