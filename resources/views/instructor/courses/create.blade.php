@extends('layouts.app-layout')

@section('content')
    <div class="pcoded-content">
        <!-- Page-header start -->
        <div class="page-header">
            <div class="page-block">
                <div class="row align-items-center">
                    <div class="col-md-12">
                        <div class="page-header-title">
                            <h5 class="m-b-10">Buat Kursus Baru</h5>
                            <p class="m-b-0">Isi detail untuk kursus baru Anda.</p>
                        </div>
                    </div>
                    <div class="col-md-12 d-flex mt-3">
                        <ul class="breadcrumb-title">
                            <li class="breadcrumb-item"><a href="{{ route('instructor.dashboard') }}"><i class="fa fa-home"></i></a></li>
                            <li class="breadcrumb-item"><a href="{{ route('instructor.courses.index') }}">Kursus Saya</a></li>
                            <li class="breadcrumb-item"><a href="#!">Buat Kursus</a></li>
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
                                        <h5>Detail Kursus</h5>
                                    </div>
                                    <div class="card-block">
                                        <form action="{{ route('instructor.courses.store') }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Judul Kursus</label>
                                                <div class="col-sm-10">
                                                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Kategori</label>
                                                <div class="col-sm-10">
                                                    <select name="category_id" class="form-control" required>
                                                        <option value="">-- Pilih Kategori --</option>
                                                        @foreach($categories as $category)
                                                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Deskripsi</label>
                                                <div class="col-sm-10">
                                                    {{-- Diubah: Menggunakan textarea biasa tanpa ID khusus --}}
                                                    <textarea name="description" class="form-control" rows="5">{{ old('description') }}</textarea>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Thumbnail</label>
                                                <div class="col-sm-10">
                                                    <input type="file" name="thumbnail" class="form-control" required accept="image/*">
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Tipe Ketersediaan</label>
                                                <div class="col-sm-10">
                                                    <select name="availability_type" id="availability-type" class="form-control">
                                                        <option value="lifetime" {{ old('availability_type') == 'lifetime' ? 'selected' : '' }}>Selamanya (Lifetime)</option>
                                                        <option value="period" {{ old('availability_type') == 'period' ? 'selected' : '' }}>Periode Tertentu</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div id="period-fields" class="form-group row" style="{{ old('availability_type') == 'period' ? '' : 'display: none;' }}">
                                                <label class="col-sm-2 col-form-label">Tanggal Periode</label>
                                                <div class="col-sm-5">
                                                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date') }}">
                                                    <small class="form-text text-muted">Tanggal Mulai</small>
                                                </div>
                                                <div class="col-sm-5">
                                                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date') }}">
                                                    <small class="form-text text-muted">Tanggal Selesai</small>
                                                </div>
                                            </div>

                                                                                        {{-- BAGIAN BARU: Tipe Pembayaran --}}
                                            <div class="form-group row">
                                                <label class="col-sm-2 col-form-label">Tipe Pembayaran</label>
                                                <div class="col-sm-10">
                                                    <div class="form-radio">
                                                        <div class="radio radio-inline">
                                                            <label>
                                                                <input type="radio" name="payment_type" value="money" {{ old('payment_type', 'money') == 'money' ? 'checked' : '' }}>
                                                                <i class="helper"></i>Bayar dengan Uang
                                                            </label>
                                                        </div>
                                                        <div class="radio radio-inline">
                                                            {{-- DIUBAH: value menjadi 'diamonds' dan teks menjadi 'Diamond' --}}
                                                            <label>
                                                                <input type="radio" name="payment_type" value="diamonds" {{ old('payment_type') == 'diamonds' ? 'checked' : '' }}>
                                                                <i class="helper"></i>Bayar dengan Diamond
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <small class="form-text text-muted">Pilih bagaimana siswa akan membayar kursus ini. Harga akan ditentukan oleh Admin saat publikasi.</small>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-12 text-right">
                                                    <a href="{{ route('instructor.courses.index') }}" class="btn btn-secondary">Batal</a>
                                                    <button type="submit" class="btn btn-primary">Simpan Kursus</button>
                                                </div>
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

{{-- Diubah: Menghapus script TinyMCE --}}
@push('scripts')
<script>
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