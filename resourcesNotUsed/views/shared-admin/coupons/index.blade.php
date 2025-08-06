@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Manajemen Kupon</h5>
                        <p class="m-b-0">Buat, lihat, dan kelola semua kupon diskon.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        {{-- Menggunakan Auth::user()->role akan lebih sederhana jika role hanya satu string --}}
                        <li class="breadcrumb-item"><a href="{{ route(Auth::user()->getRoleNames()->first() . '.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Kupon</a></li>
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
                                    <h5>Daftar Kupon</h5>
                                    <span>Menampilkan semua kupon diskon</span>
                                    <div class="card-header-right">
                                        <a href="{{ route(Auth::user()->getRoleNames()->first() . '.coupons.create') }}" class="btn btn-primary">Buat Kupon Baru</a>
                                    </div>
                                </div>
                                <div class="card-block table-border-style">
                                    @if (session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif
                                    @if (session('error'))
                                        <div class="alert alert-danger">{{ session('error') }}</div>
                                    @endif
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Kode</th>
                                                    <th>Tipe</th>
                                                    <th>Nilai</th>
                                                    <th>Berlaku Untuk</th>
                                                    <th>Penggunaan</th>
                                                    <th>Status</th>
                                                    <th>Kedaluwarsa</th>
                                                    <th class="text-center">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($coupons as $coupon)
                                                    <tr>
                                                        <th scope="row">{{ $loop->iteration + $coupons->firstItem() - 1 }}</th>
                                                        <td><strong>{{ $coupon->code }}</strong></td>
                                                        <td>{{ ucfirst($coupon->type) }}</td>
                                                        <td>
                                                            @if($coupon->type == 'percent')
                                                                {{ $coupon->value }}%
                                                            @else
                                                                Rp {{ number_format($coupon->value, 0, ',', '.') }}
                                                            @endif
                                                        </td>
                                                        <td>{{ $coupon->course->title ?? 'Semua Kursus' }}</td>
                                                        <td>{{ $coupon->uses_count }} / {{ $coupon->max_uses ?? '∞' }}</td>
                                                        <td>
                                                            @if($coupon->is_active)
                                                                <label class="label label-success">Aktif</label>
                                                            @else
                                                                <label class="label label-default">Tidak Aktif</label>
                                                            @endif
                                                        </td>
                                                        <td>{{ $coupon->expires_at ? $coupon->expires_at->format('d M Y') : 'Tidak ada' }}</td>
                                                        <td class="text-center">
                                                            <a href="{{ route(Auth::user()->getRoleNames()->first() . '.coupons.edit', $coupon->id) }}" class="btn btn-info btn-sm">
                                                                <i class="fa fa-pencil"></i> Edit
                                                            </a>
                                                            <form action="{{ route(Auth::user()->getRoleNames()->first() . '.coupons.destroy', $coupon->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kupon ini?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm">
                                                                    <i class="fa fa-trash"></i> Hapus
                                                                </button>
                                                            </form>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="9" class="text-center">Belum ada kupon yang dibuat.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        {{ $coupons->links() }}
                                    </div>
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