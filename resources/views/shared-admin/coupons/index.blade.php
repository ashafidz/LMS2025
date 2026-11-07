@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <x-dash.page-head :crumbs="[['__home', route(Auth::user()->getRoleNames()->first() . '.dashboard')], ['Kupon', null]]">
        <x-slot:title>Manajemen Kupon</x-slot:title>
        <x-slot:subtitle>Buat, lihat, dan kelola semua kupon diskon.</x-slot:subtitle>
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
                                    <h5>Daftar Kupon</h5>
                                    <span>Menampilkan semua kupon diskon</span>
                                    <div class="card-header-right">
                                        <a href="{{ route(Auth::user()->getRoleNames()->first() . '.coupons.create') }}" class="btn btn-primary">Buat Kupon Baru</a>
                                    </div>
                                </div>
                                <div class="card-block table-border-style">
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
                                                        <td><strong class="rs-mono">{{ $coupon->code }}</strong></td>
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
                                                            <a href="{{ route(Auth::user()->getRoleNames()->first() . '.coupons.edit', $coupon->id) }}" class="btn btn-secondary btn-sm">
                                                                <i class="fa fa-pencil"></i>
                                                            </a>
                                                            <form action="{{ route(Auth::user()->getRoleNames()->first() . '.coupons.destroy', $coupon->id) }}" method="POST" class="d-inline" data-confirm="Apakah Anda yakin ingin menghapus kupon ini?" data-confirm-danger data-confirm-ok="Ya, hapus">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                                    <i class="fa fa-trash"></i> 
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