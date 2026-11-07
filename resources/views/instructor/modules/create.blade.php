@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content rs-page-form">
        <!-- Page-header start -->
        <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], ['Kursus Saya', route('instructor.courses.index')], [\Illuminate\Support\Str::limit($course->title, 30), route('instructor.courses.modules.index', $course)], ['Buat', null]]">
            <x-slot:title>Buat Modul Baru</x-slot:title>
            <x-slot:subtitle>Tambahkan Modul baru didalam Kursus: {{ $course->title }}</x-slot:subtitle>
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
                                        <h5>Detail Modul Baru</h5>
                                        <span>Isi formulir di bawah ini untuk membuat modul baru.</span>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.courses.modules.store', $course) }}" method="POST" class="rs-form">
                                            @csrf
                                            @include('instructor.lessons.partials._form-errors', ['title' => 'Modul belum tersimpan.'])

                                            {{-- Title --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Judul Modul</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Masukkan Judul Modul" required>
                                                    @error('title')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            {{-- BAGIAN BARU: Input untuk Poin yang Dibutuhkan --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Poin yang Dibutuhkan</label>
                                                <div class="col-sm-10">
                                                    <input type="number" name="points_required" class="form-control" value="{{ old('points_required', 0) }}" required min="0">
                                                    <small class="form-text text-muted">Masukkan jumlah poin yang harus dimiliki siswa untuk membuka modul ini. Masukkan <strong>0</strong> agar modul tidak terkunci.</small>
                                                </div>
                                            </div>

                                                                                        <div class="rs-form-actions">
                                                <a href="{{ route('instructor.courses.modules.index', $course) }}" class="btn btn-secondary">Batal</a>
                                                <button type="submit" class="btn btn-primary">Buat Modul</button>
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
