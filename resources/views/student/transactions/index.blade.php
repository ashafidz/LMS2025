@extends('layouts.app-layout')

@section('content')
<div class="pcoded-content">
    <!-- Page-header start -->
    <div class="page-header">
        <div class="page-block">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="page-header-title">
                        <h5 class="m-b-10">Riwayat Transaksi</h5>
                        <p class="m-b-0">Lihat semua riwayat pembelian kursus Anda.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <ul class="breadcrumb-title">
                        <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}"><i class="fa fa-home"></i></a></li>
                        <li class="breadcrumb-item"><a href="#!">Riwayat Transaksi</a></li>
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
                                    <h5>Daftar Transaksi Anda</h5>
                                </div>
                                <div class="card-block table-border-style">
                                    @if ($orders -> isEmpty())
                                        <div class="col-sm-12">
                                            <div class="card">
                                                <div class="card-block text-center p-5">
                                                    <i class="fa fa-book fa-3x text-muted mb-3"></i>
                                                    <h4>Anda Belum Memiliki Riwayat Transaksi</h4>
                                                    <p>Jelajahi katalog kami untuk menemukan kursus yang cocok untuk Anda.</p>
                                                    <a href="{{ route('courses') }}" class="btn btn-primary mt-2">Lihat Katalog Kursus</a>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Kode Pesanan</th>
                                                        <th>Tanggal</th>
                                                        <th>Total Pembayaran</th>
                                                        <th>Status</th>
                                                        <th class="text-center">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($orders as $order)
                                                        <tr>
                                                            <td><strong>{{ $order->order_code }}</strong></td>
                                                            <td>{{ $order->created_at->format('d F Y, H:i') }}</td>
                                                            <td>Rp{{ number_format($order->final_amount, 0, ',', '.') }}</td>
                                                            <td>
                                                                @php
                                                                    $statusClasses = [
                                                                        'pending' => 'label-warning',
                                                                        'paid' => 'label-success',
                                                                        'failed' => 'label-danger',
                                                                        'cancelled' => 'label-default',
                                                                    ];
                                                                @endphp
                                                                <label class="label {{ $statusClasses[$order->status] ?? 'label-default' }}">
                                                                    {{ ucfirst($order->status) }}
                                                                </label>
                                                            </td>
                                                            <td class="text-center">
                                                                @if($order->status == 'pending')
                                                                    <a href="{{ route('checkout.show', $order->id) }}" class="btn btn-primary btn-sm">Bayar Sekarang</a>
                                                                @else
                                                                    <button type="button" class="btn btn-inverse btn-sm" data-toggle="modal" data-target="#invoiceModal-{{ $order->id }}">
                                                                    Lihat Invoice
                                                                </button>
                                                                @endif

                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="5" class="text-center">Anda belum memiliki riwayat transaksi.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                                                            <div class="d-flex justify-content-center">
                                        {{ $orders->links() }}
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

    <!-- Modal Invoice (dibuat untuk setiap pesanan) -->
    @foreach ($orders as $order)
    <div class="modal fade" id="invoiceModal-{{ $order->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Pesanan</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    @include('student.transactions._invoice', ['order' => $order])
                </div>
                {{-- FOOTER MODAL YANG DIPERBARUI --}}
                <div class="modal-footer">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-md-6 mb-2 mb-md-0">
                                <a href="#" class="btn btn-secondary btn-block" target="_blank"><i class="fa fa-eye"></i> Lihat PDF</a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('student.transactions.download', $order->id) }}" class="btn btn-primary btn-block"><i class="fa fa-download"></i> Unduh PDF</a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                 <button type="button" class="btn btn-inverse btn-block" data-dismiss="modal">
                                    <i class="fa fa-arrow-left"></i> Kembali ke Riwayat
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endsection