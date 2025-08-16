@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Edit Kupon</h5>
                        <p class="m-b-0">Perbarui detail untuk kupon: <strong>{{ $coupon->code }}</strong></p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route(Auth::user()->getRoleNames()->first() . '.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route(Auth::user()->getRoleNames()->first() . '.coupons.index') }}">Kupon</a></li>
                        <li class="breadcrumb-item"><a href="#!">Edit</a></li>
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
                                    <h5>Detail Kupon</h5>
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

                                    <form action="{{ route(Auth::user()->getRoleNames()->first() . '.coupons.update', $coupon->id) }}" method="POST">
                                        @csrf
                                        @method('PUT') {{-- Wajib untuk metode update --}}

                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Kode Kupon</label>
                                            <div class="col-sm-10">
                                                <input type="text" name="code" class="form-control" value="{{ old('code', $coupon->code) }}" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Deskripsi (Opsional)</label>
                                            <div class="col-sm-10">
                                                <textarea name="description" class="form-control" rows="3">{{ old('description', $coupon->description) }}</textarea>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Tipe Diskon</label>
                                            <div class="col-sm-4">
                                                <select name="type" class="form-control" required>
                                                    <option value="fixed" {{ old('type', $coupon->type) == 'fixed' ? 'selected' : '' }}>Potongan Harga Tetap (Rp)</option>
                                                    <option value="percent" {{ old('type', $coupon->type) == 'percent' ? 'selected' : '' }}>Persentase (%)</option>
                                                </select>
                                            </div>
                                            <label class="col-sm-2 col-form-label">Nilai Diskon</label>
                                            <div class="col-sm-4">
                                                <input type="number" name="value" class="form-control" value="{{ old('value', $coupon->value) }}" required>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Berlaku Untuk</label>
                                            <div class="col-sm-10">
                                                <select name="course_id" class="form-control">
                                                    <option value="">-- Semua Kursus --</option>
                                                    @foreach($courses as $course)
                                                        <option value="{{ $course->id }}" {{ old('course_id', $coupon->course_id) == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

<div class="form-group row">
    <label class="col-sm-2 col-form-label">Tipe Kupon</label>
    <div class="col-sm-10">
        <div class="form-check form-switch">
            <input type="hidden" name="is_public" value="0">
            <input class="form-check-input" type="checkbox" name="is_public" value="1" id="is_public" {{ old('is_public', $coupon->is_public) ? 'checked' : '' }}>
            <label class="form-check-label" for="is_public">Jadikan Kupon Publik (terlihat oleh semua pengguna di keranjang).</label>
        </div>
    </div>
</div>
<hr>
<div class="form-group row">
    <label class="col-sm-2 col-form-label">Batas Penggunaan Total</label>
    <div class="col-sm-10">
        <input type="number" name="max_uses" class="form-control" value="{{ old('max_uses', $coupon->max_uses) }}" placeholder="Kosongkan jika tidak terbatas">
    </div>
</div>
<div class="form-group row">
    <label class="col-sm-2 col-form-label">Batas per Pengguna</label>
    <div class="col-sm-10">
        <input type="number" name="max_uses_per_user" class="form-control" value="{{ old('max_uses_per_user', $coupon->max_uses_per_user) }}" placeholder="Kosongkan jika tidak terbatas">
    </div>
</div>


                                        <hr>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Batas Penggunaan</label>
                                            <div class="col-sm-10">
                                                <input type="number" name="max_uses" class="form-control" value="{{ old('max_uses', $coupon->max_uses) }}" placeholder="Kosongkan jika tidak terbatas">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Masa Berlaku</label>
                                            <div class="col-sm-5">
                                                <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', $coupon->starts_at ? $coupon->starts_at->format('Y-m-d\TH:i') : '') }}">
                                                <small class="form-text text-muted">Tanggal Mulai (Opsional)</small>
                                            </div>
                                            <div class="col-sm-5">
                                                <input type="datetime-local" name="expires_at" class="form-control" value="{{ old('expires_at', $coupon->expires_at ? $coupon->expires_at->format('Y-m-d\TH:i') : '') }}">
                                                <small class="form-text text-muted">Tanggal Kedaluwarsa (Opsional)</small>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <label class="col-sm-2 col-form-label">Status</label>
                                            <div class="col-sm-10">
                                                <div class="form-radio">
                                                    <div class="radio radio-inline">
                                                        <label><input type="radio" name="is_active" value="1" {{ old('is_active', $coupon->is_active) == 1 ? 'checked' : '' }}><i class="helper"></i>Aktif</label>
                                                    </div>
                                                    <div class="radio radio-inline">
                                                        <label><input type="radio" name="is_active" value="0" {{ old('is_active', $coupon->is_active) == 0 ? 'checked' : '' }}><i class="helper"></i>Tidak Aktif</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-sm-12 text-right">
                                                <a href="{{ route(Auth::user()->getRoleNames()->first() . '.coupons.index') }}" class="btn btn-secondary">Batal</a>
                                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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