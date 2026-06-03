@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Kelola Harga Kursus</h5>
                        <p class="m-b-0">Edit harga tiap kursus berbayar (Rupiah) secara individual.</p>
                    </div>
                </div>
                <div class="col-md-12 d-flex mt-3">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('superadmin.settings.edit') }}">Pengaturan</a></li>
                        <li class="breadcrumb-item">Kelola Harga Kursus</li>
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
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fa fa-exclamation-circle mr-2"></i> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-sm-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h5 class="mb-0"><i class="fa fa-tags"></i> Daftar Kursus Berbayar Rupiah
                                        <span class="badge badge-primary ml-2">{{ $courses->count() }} Kursus</span>
                                    </h5>
                                    <input type="text" id="searchInput" class="form-control w-25" placeholder="Cari nama kursus...">
                                </div>
                                <div class="card-block table-border-style">
                                    @if($courses->isEmpty())
                                        <div class="p-4 text-center text-muted">
                                            <i class="fa fa-inbox fa-3x mb-3 d-block"></i>
                                            Belum ada kursus dengan metode pembayaran Rupiah.
                                        </div>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover" id="courseTable">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th width="5%">#</th>
                                                        <th>Judul Kursus</th>
                                                        <th width="15%">Instruktur</th>
                                                        <th width="10%">Status</th>
                                                        <th width="20%" class="text-right">Harga Saat Ini</th>
                                                        <th width="25%">Ubah Harga</th>
                                                        <th width="10%">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($courses as $i => $course)
                                                    <tr>
                                                        <td>{{ $i + 1 }}</td>
                                                        <td>
                                                            <span class="font-weight-bold">{{ $course->title }}</span>
                                                        </td>
                                                        <td>
                                                            <small class="text-muted">{{ $course->instructor->name ?? '-' }}</small>
                                                        </td>
                                                        <td>
                                                            @if($course->status === 'published')
                                                                <span class="badge badge-success">Published</span>
                                                            @else
                                                                <span class="badge badge-warning">{{ ucfirst($course->status) }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-right">
                                                            <strong>Rp {{ number_format($course->price, 0, ',', '.') }}</strong>
                                                        </td>
                                                        <td>
                                                            <form action="{{ route('superadmin.courses.update-price', $course->id) }}" method="POST" class="d-flex align-items-center gap-2 price-form">
                                                                @csrf
                                                                @method('PATCH')
                                                                <div class="input-group input-group-sm">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text">Rp</span>
                                                                    </div>
                                                                    <input type="number"
                                                                        name="price"
                                                                        class="form-control price-input"
                                                                        value="{{ $course->price }}"
                                                                        min="0"
                                                                        step="1000"
                                                                        required>
                                                                </div>
                                                        </td>
                                                        <td>
                                                                <button type="submit" class="btn btn-primary btn-sm">
                                                                    <i class="fa fa-save"></i> Simpan
                                                                </button>
                                                            </form>
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
    // Live search filter
    document.getElementById('searchInput')?.addEventListener('keyup', function () {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#courseTable tbody tr');
        rows.forEach(row => {
            const title = row.querySelector('td:nth-child(2)')?.textContent.toLowerCase() || '';
            row.style.display = title.includes(filter) ? '' : 'none';
        });
    });
</script>
@endpush
