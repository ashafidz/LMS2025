@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Ubah Harga Massal Kursus</h5>
                        <p class="m-b-0">Perbarui harga seluruh kursus berbayar (Rupiah) secara sekaligus.</p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.settings.edit') }}">Pengaturan</a></li>
                        <li class="breadcrumb-item">Ubah Harga Massal</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="pcoded-inner-content">
        <div class="main-body">
            <div class="page-wrapper">
                <div class="page-body">

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fa fa-check-circle mr-2"></i> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    @endif

                    <div class="row">
                        {{-- Kolom Kiri: Form Ubah Harga --}}
                        <div class="col-md-5">
                            <div class="card border-danger">
                                <div class="card-header bg-danger text-white">
                                    <h5 class="mb-0 text-white"><i class="fa fa-tags"></i> Form Ubah Harga Massal</h5>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-warning">
                                        <i class="fa fa-exclamation-triangle"></i>
                                        <strong>Perhatian!</strong> Aksi ini akan <strong>menimpa harga seluruh
                                        {{ $courseCount }} kursus berbayar (Rupiah)</strong> secara sekaligus
                                        dan tidak dapat dibatalkan. Pastikan angka yang dimasukkan sudah benar.
                                    </div>

                                    @if($courseCount === 0)
                                        <div class="alert alert-info">
                                            <i class="fa fa-info-circle"></i> Tidak ada kursus dengan metode pembayaran Uang (Rupiah) yang ditemukan.
                                        </div>
                                    @else
                                        <form action="{{ route('superadmin.courses.bulk-price.update') }}" method="POST" id="bulkPriceForm">
                                            @csrf
                                            <div class="form-group">
                                                <label class="font-weight-bold">Harga Baru (Rupiah)</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text font-weight-bold">Rp</span>
                                                    </div>
                                                    <input type="number"
                                                        id="new_price"
                                                        name="new_price"
                                                        class="form-control form-control-lg @error('new_price') is-invalid @enderror"
                                                        placeholder="Contoh: 150000"
                                                        min="0"
                                                        step="1000"
                                                        value="{{ old('new_price') }}"
                                                        required>
                                                    @error('new_price')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <small class="form-text text-muted mt-1">
                                                    Preview: <span id="pricePreview" class="font-weight-bold text-primary">Rp 0</span>
                                                </small>
                                            </div>

                                            <button type="button" class="btn btn-danger btn-block btn-lg mt-3" onclick="confirmUpdate()">
                                                <i class="fa fa-save"></i> Terapkan ke {{ $courseCount }} Kursus
                                            </button>
                                            <a href="{{ route('superadmin.settings.edit') }}" class="btn btn-secondary btn-block mt-2">
                                                Batal
                                            </a>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Kolom Kanan: Daftar Kursus yang Terdampak --}}
                        <div class="col-md-7">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fa fa-list"></i> Kursus yang Akan Terdampak
                                        <span class="badge badge-danger ml-2">{{ $courseCount }} Kursus</span>
                                    </h5>
                                </div>
                                <div class="card-body p-0">
                                    @if($courseCount === 0)
                                        <div class="p-4 text-center text-muted">
                                            <i class="fa fa-inbox fa-3x mb-3"></i>
                                            <p>Belum ada kursus berbayar Rupiah.</p>
                                        </div>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped table-sm mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Judul Kursus</th>
                                                        <th class="text-right">Harga Saat Ini</th>
                                                        <th class="text-right">Harga Baru</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($courses as $i => $course)
                                                    <tr>
                                                        <td>{{ $i + 1 }}</td>
                                                        <td>{{ $course->title }}</td>
                                                        <td class="text-right text-muted">
                                                            Rp {{ number_format($course->price, 0, ',', '.') }}
                                                        </td>
                                                        <td class="text-right font-weight-bold text-success preview-cell">
                                                            —
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
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

@push('scripts')
<script>
    // Live preview harga
    const priceInput = document.getElementById('new_price');
    const pricePreview = document.getElementById('pricePreview');
    const previewCells = document.querySelectorAll('.preview-cell');

    if (priceInput) {
        priceInput.addEventListener('input', function () {
            const val = parseInt(this.value) || 0;
            const formatted = 'Rp ' + val.toLocaleString('id-ID');
            if (pricePreview) pricePreview.textContent = formatted;
            previewCells.forEach(cell => {
                cell.textContent = formatted;
            });
        });
    }

    // Konfirmasi sebelum submit
    function confirmUpdate() {
        const val = parseInt(priceInput.value) || 0;
        if (val === 0) {
            if (!confirm('⚠️ Harga yang Anda masukkan adalah Rp 0. Apakah Anda yakin ingin menggratiskan semua kursus?')) return;
        } else {
            const formatted = 'Rp ' + val.toLocaleString('id-ID');
            if (!confirm('Anda akan mengubah harga SEMUA kursus berbayar (Rupiah) menjadi ' + formatted + '.\n\nAksi ini tidak dapat dibatalkan. Lanjutkan?')) return;
        }
        document.getElementById('bulkPriceForm').submit();
    }
</script>
@endpush
