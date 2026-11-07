@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content rs-page-form">
        <!-- Page-header start -->
        <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], ['Kursus Saya', route('instructor.courses.index')], [\Illuminate\Support\Str::limit($module->course->title, 30), route('instructor.courses.modules.index', $module->course)], ['Edit', null]]">
            <x-slot:title>Edit Modul</x-slot:title>
            <x-slot:subtitle>Perbarui Detail Modul Anda.</x-slot:subtitle>
        </x-dash.page-head>
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
                                        <h5>Edit Detail Modul</h5>
                                        <span>Perbarui Judul Modul Anda.</span>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.modules.update', $module) }}" method="POST" class="rs-form">
                                            @csrf
                                            @include('instructor.lessons.partials._form-errors', ['title' => 'Modul belum tersimpan.'])
                                            @method('PUT')

                                            {{-- Title --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Judul Modul</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $module->title) }}" required>
                                                    @error('title')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- BAGIAN BARU: Input untuk Poin yang Dibutuhkan --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Poin yang Dibutuhkan</label>
                                                <div class="col-sm-10">
                                                    <input type="number" name="points_required" class="form-control" value="{{ old('points_required', $module->points_required) }}" required min="0">
                                                    <small class="form-text text-muted">Masukkan jumlah poin yang harus dimiliki siswa untuk membuka modul ini. Masukkan <strong>0</strong> agar modul tidak terkunci.</small>
                                                </div>
                                            </div>

                                                                                        <div class="rs-form-actions">
                                                <a href="{{ route('instructor.courses.modules.index', $module->course) }}" class="btn btn-secondary">Batal</a>
                                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                            </div>
                                        </form>
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
