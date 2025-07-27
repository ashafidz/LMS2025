@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Pengaturan Situs</h5>
                        <p class="m-b-0">Kelola informasi umum, logo, pajak, dan biaya transaksi untuk situs Anda.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        {{-- DIUBAH: Route sekarang langsung ke superadmin.dashboard --}}
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Pengaturan</a></li>
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
                                    <h5>Form Pengaturan Situs</h5>
                                </div>
                                <div class="card-block">
                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    {{-- DIUBAH: Action form sekarang langsung ke superadmin.settings.update --}}
                                    <form action="{{ route('superadmin.settings.update') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')

                                        {{-- ... (semua form-group tetap sama) ... --}}
                                        <h6 class="font-weight-bold">Informasi Umum & Perusahaan</h6>
                                        <hr>
                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label">Nama Website</label>
                                            <div class="col-sm-9">
                                                <input type="text" name="site_name" class="form-control" value="{{ old('site_name', $settings->site_name) }}" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label">Logo Saat Ini</label>
                                            <div class="col-sm-9">
                                                @if($settings->logo_path)
                                                    <img src="{{ Storage::url($settings->logo_path) }}" alt="Logo" style="max-height: 50px; background-color: #f2f2f2; padding: 5px; border-radius: 5px;">
                                                @else
                                                    <p class="text-muted">Belum ada logo.</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label">Unggah Logo Baru (Opsional)</label>
                                            <div class="col-sm-9">
                                                <input type="file" name="logo" class="form-control" accept="image/*">
                                                <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah logo.</small>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label">Nama Perusahaan</label>
                                            <div class="col-sm-9">
                                                <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $settings->company_name) }}">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label">Alamat</label>
                                            <div class="col-sm-9">
                                                <textarea name="address" class="form-control" rows="3">{{ old('address', $settings->address) }}</textarea>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label">No. Telepon</label>
                                            <div class="col-sm-9">
                                                <input type="text" name="phone" class="form-control" value="{{ old('phone', $settings->phone) }}">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label">NPWP</label>
                                            <div class="col-sm-9">
                                                <input type="text" name="npwp" class="form-control" value="{{ old('npwp', $settings->npwp) }}">
                                            </div>
                                        </div>

                                        <h6 class="font-weight-bold mt-5">Pengaturan Keuangan</h6>
                                        <hr>
                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label">PPN / VAT (%)</label>
                                            <div class="col-sm-9">
                                                <input type="number" name="vat_percentage" class="form-control" value="{{ old('vat_percentage', $settings->vat_percentage) }}" required step="0.01">
                                                <small class="form-text text-muted">Masukkan nilai persentase. Contoh: 11 untuk 11%.</small>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label">Biaya Transaksi Tetap (Rp)</label>
                                            <div class="col-sm-9">
                                                <input type="number" name="transaction_fee_fixed" class="form-control" value="{{ old('transaction_fee_fixed', $settings->transaction_fee_fixed) }}" required>
                                                <small class="form-text text-muted">Biaya tetap yang ditambahkan ke setiap transaksi. Masukkan 0 jika tidak ada.</small>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label">Biaya Transaksi Persen (%)</label>
                                            <div class="col-sm-9">
                                                <input type="number" name="transaction_fee_percentage" class="form-control" value="{{ old('transaction_fee_percentage', $settings->transaction_fee_percentage) }}" required step="0.01">
                                                <small class="form-text text-muted">Biaya persentase yang ditambahkan ke setiap transaksi. Masukkan 0 jika tidak ada.</small>
                                            </div>
                                        </div>

                                                                                {{-- BAGIAN BARU: PENGATURAN POIN --}}
                                        <h6 class="font-weight-bold mt-5">Pengaturan Gamifikasi Poin</h6>
                                        <hr>
                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label">Poin Saat Membeli Kursus</label>
                                            <div class="col-sm-9"><input type="number" name="points_for_purchase" class="form-control" value="{{ old('points_for_purchase', $settings->points_for_purchase) }}" required></div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label">Poin Selesai Pelajaran (Artikel)</label>
                                            <div class="col-sm-9"><input type="number" name="points_for_article" class="form-control" value="{{ old('points_for_article', $settings->points_for_article) }}" required></div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label">Poin Selesai Pelajaran (Video)</label>
                                            <div class="col-sm-9"><input type="number" name="points_for_video" class="form-control" value="{{ old('points_for_video', $settings->points_for_video) }}" required></div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label">Poin Selesai Pelajaran (Dokumen)</label>
                                            <div class="col-sm-9"><input type="number" name="points_for_document" class="form-control" value="{{ old('points_for_document', $settings->points_for_document) }}" required></div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label">Poin Lulus Kuis</label>
                                            <div class="col-sm-9"><input type="number" name="points_for_quiz" class="form-control" value="{{ old('points_for_quiz', $settings->points_for_quiz) }}" required></div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-3 col-form-label">Poin Lulus Tugas</label>
                                            <div class="col-sm-9"><input type="number" name="points_for_assignment" class="form-control" value="{{ old('points_for_assignment', $settings->points_for_assignment) }}" required></div>
                                        </div>

                                        <div class="form-group row mt-4">
                                            <div class="col-sm-12 text-right">
                                                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
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
@endsection