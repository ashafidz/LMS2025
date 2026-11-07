@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content rs-page-form">
        <!-- Page-header start -->
        <x-dash.page-head :crumbs="[['__home', route('instructor.dashboard')], ['Kursus Saya', route('instructor.courses.index')], ['Edit Kursus', null]]">
            <x-slot:title>Edit Kursus</x-slot:title>
            <x-slot:subtitle>Perbarui detail untuk kursus: {{ $course->title }}</x-slot:subtitle>
        </x-dash.page-head>
        <!-- Page-header end -->

        <div class="pcoded-inner-content">
            <div class="main-body">
                <div class="page-wrapper">
                    <div class="page-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card rs-form-card">
                                    <div class="card-header">
                                        <h5>Detail Kursus</h5>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.courses.update', $course) }}" method="POST"
                                            enctype="multipart/form-data" class="rs-form">
                                            @csrf
                                            @include('instructor.courses.partials._form-errors')
                                            @method('PUT')
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Judul Kursus</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="title" class="form-control"
                                                        value="{{ old('title', $course->title) }}" required>
                                                </div>
                                            </div>
                                            <div class="form-group row rs-col-half">
                                                <label class="col-sm-2 col-form-label">Kategori</label>
                                                <div class="col-sm-10">
                                                    <select name="category_id" class="form-control" required>
                                                        @foreach ($categories as $category)
                                                            <option value="{{ $category->id }}"
                                                                {{ old('category_id', $course->category_id) == $category->id ? 'selected' : '' }}>
                                                                {{ $category->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Deskripsi</label>
                                                <div class="col-sm-10">
                                                    {{-- Menambahkan class is-invalid jika ada error --}}
                                                    <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="5">{{ old('description', $course->description) }}</textarea>

                                                    {{-- Menampilkan pesan error validasi --}}
                                                    @error('description')
                                                        <div class="invalid-feedback">
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="rs-form-section"><h3>Tampilan</h3></div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Thumbnail</label>
                                                <div class="col-sm-10">
                                                    <input type="file" name="thumbnail" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp"
                                                        data-rs-shrink data-max-bytes="2097152" data-max-side="1600" data-alpha-type="image/png"
                                                        data-accept="image/jpeg,image/png,image/gif" data-label="Thumbnail">
                                                    <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah
                                                        thumbnail saat ini.</small>
                                                    @if ($course->thumbnail_url)
                                                        <img src="{{ Storage::url($course->thumbnail_url) }}"
                                                            alt="Thumbnail" class="img-thumbnail mt-2" width="200">
                                                    @endif
                                                </div>
                                            </div>


                                            <div class="rs-form-section"><h3>Harga & Akses</h3></div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Tipe Pembayaran</label>
                                                <div class="col-sm-10">
                                                    <div class="form-radio">
                                                        <div class="radio radio-inline">
                                                            <label>
                                                                <input type="radio" name="payment_type" value="money" {{ old('payment_type', $course->payment_type) == 'money' ? 'checked' : '' }}>
                                                                <i class="helper"></i>Bayar dengan Uang
                                                            </label>
                                                        </div>
                                                        <div class="radio radio-inline">
                                                            {{-- DIUBAH: value menjadi 'diamonds' dan teks menjadi 'Diamond' --}}
                                                            <label>
                                                                <input type="radio" name="payment_type" value="diamonds" {{ old('payment_type', $course->payment_type) == 'diamonds' ? 'checked' : '' }}>
                                                                <i class="helper"></i>Bayar dengan Diamond
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Jika Anda mengubah tipe pembayaran, harga yang sudah ada akan direset dan perlu diatur ulang oleh Admin.</small>
                                                </div>
                                            </div>



                                            <div class="form-group row rs-col-half">
                                                <label class="col-sm-2 col-form-label">Tipe Ketersediaan</label>
                                                <div class="col-sm-10">
                                                    <select name="availability_type" id="availability-type"
                                                        class="form-control">
                                                        <option value="lifetime"
                                                            {{ old('availability_type', $course->availability_type) == 'lifetime' ? 'selected' : '' }}>
                                                            Selamanya (Lifetime)</option>
                                                        <option value="period"
                                                            {{ old('availability_type', $course->availability_type) == 'period' ? 'selected' : '' }}>
                                                            Periode Tertentu</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div id="period-fields" class="form-group row"
                                                style="{{ old('availability_type', $course->availability_type) == 'period' ? '' : 'display: none;' }}">
                                                <label class="col-sm-2 col-form-label">Tanggal Periode</label>
                                                <div class="col-sm-5">
                                                    <input type="date" name="start_date" class="form-control"
                                                        value="{{ old('start_date', $course->start_date ? \Carbon\Carbon::parse($course->start_date)->format('Y-m-d') : '') }}">
                                                    <small class="form-text text-muted">Tanggal Mulai</small>
                                                </div>
                                                <div class="col-sm-5">
                                                    <input type="date" name="end_date" class="form-control"
                                                        value="{{ old('end_date', $course->end_date ? \Carbon\Carbon::parse($course->end_date)->format('Y-m-d') : '') }}">
                                                    <small class="form-text text-muted">Tanggal Selesai</small>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Status</label>
                                                <div class="col-sm-10">
                                                    <input type="text" class="form-control"
                                                        value="{{ $course->status_label }}" readonly>
                                                    <small class="form-text text-muted">Status hanya bisa diubah melalui
                                                        tombol aksi di halaman daftar kursus.</small>
                                                </div>
                                            </div>
                                                                                        <div class="rs-form-actions">
                                                <a href="{{ route('instructor.courses.index') }}"
                                                class="btn btn-secondary">Batal</a>
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

{{-- Diubah: Menghapus @push('scripts') untuk TinyMCE --}}
@push('scripts')
@include('instructor.courses.partials._description-editor')
    <script src="{{ asset('js/rs-image-shrink.js') }}"></script>
    <script>
        // Hanya menyisakan script untuk availability
        document.getElementById('availability-type').addEventListener('change', function() {
            const periodFields = document.getElementById('period-fields');
            if (this.value === 'period') {
                periodFields.style.display = '';
            } else {
                periodFields.style.display = 'none';
            }
        });
    </script>
@endpush
