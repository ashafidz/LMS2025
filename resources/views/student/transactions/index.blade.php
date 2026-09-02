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
                <div class="col-md-12 d-flex mt-3">
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
                                <div class="card-block">
                                    @if ($orders->isEmpty())
                                        <div class="text-center py-5">
                                            <div class="mb-4">
                                                <div class="d-inline-flex align-items-center justify-content-center bg-light" style="width: 100px; height: 100px; border-radius: 50%;">
                                                    <i class="fa fa-receipt text-muted f-40 opacity-50"></i>
                                                </div>
                                            </div>
                                            <h5 class="font-weight-bold text-muted mb-2">Belum Ada Transaksi</h5>
                                            <p class="text-muted mb-4">Anda belum melakukan pembelian kursus apapun. Yuk, jelajahi kursus menarik sekarang!</p>
                                            <a href="{{ route('courses') }}" class="btn btn-primary btn-round px-4 shadow-sm">
                                                <i class="fa fa-search mr-1"></i> Jelajahi Kursus
                                            </a>
                                        </div>
                                    @else
                                        <div class="transaction-list">
                                            @foreach ($orders as $order)
                                                @php
                                                    $statusColor = $order->status == 'paid' ? '#28a745' : ($order->status == 'pending' ? '#ffc107' : '#dc3545');
                                                @endphp
                                                <div class="card shadow-sm mb-3 border-0" style="border-radius: 12px; border-left: 4px solid {{ $statusColor }} !important;">
                                                    <div class="card-body p-3 p-md-4">
                                                        <div class="row align-items-center">
                                                            <!-- Left Info (Order Code & Date) -->
                                                            <div class="col-12 col-md-5 mb-3 mb-md-0">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="d-none d-md-flex align-items-center justify-content-center bg-light text-muted mr-3" style="width: 50px; height: 50px; border-radius: 50%;">
                                                                        <i class="fa fa-shopping-bag f-20"></i>
                                                                    </div>
                                                                    <div>
                                                                        <span class="text-uppercase text-muted font-weight-bold" style="font-size: 11px;">KODE PESANAN</span>
                                                                        <h6 class="font-weight-bold mb-1">{{ $order->order_code }}</h6>
                                                                        <span class="text-muted small"><i class="fa fa-clock-o mr-1"></i> {{ $order->created_at->format('d M Y, H:i') }}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- Middle Info (Amount & Status) -->
                                                            <div class="col-7 col-md-4">
                                                                <div class="d-flex flex-column">
                                                                    <span class="text-uppercase text-muted font-weight-bold" style="font-size: 11px;">TOTAL BELANJA</span>
                                                                    <h6 class="font-weight-bold mb-1 text-primary">Rp{{ number_format($order->final_amount, 0, ',', '.') }}</h6>
                                                                    <div>
                                                                        @if($order->status == 'paid')
                                                                            <span class="badge badge-success px-2 py-1"><i class="fa fa-check-circle mr-1"></i> LUNAS</span>
                                                                        @elseif($order->status == 'pending')
                                                                            <span class="badge badge-warning px-2 py-1"><i class="fa fa-clock-o mr-1"></i> PENDING</span>
                                                                        @else
                                                                            <span class="badge badge-danger px-2 py-1"><i class="fa fa-times-circle mr-1"></i> {{ strtoupper($order->status) }}</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- Right Action -->
                                                            <div class="col-5 col-md-3 text-right">
                                                                @if($order->status == 'pending')
                                                                    <a href="{{ route('checkout.show', $order) }}" class="btn btn-warning btn-sm btn-round shadow-sm w-100">
                                                                        <i class="fas fa-money-bill-wave mr-1"></i> Bayar
                                                                    </a>
                                                                @else
                                                                    <button type="button" class="btn btn-outline-primary btn-sm btn-round w-100" data-toggle="modal" data-target="#invoiceModal-{{ $order->id }}">
                                                                        <i class="fa fa-file-text-o mr-1"></i> Invoice
                                                                    </button>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="d-flex justify-content-center mt-4">
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
                            {{-- <div class="col-md-6 mb-2 mb-md-0">
                                <a href="#" class="btn btn-secondary btn-block" target="_blank"><i class="fa fa-eye"></i> Lihat PDF</a>
                            </div> --}}
                            <div class="col-md-12">
                                <a href="{{ route('student.transactions.download', $order) }}" class="btn btn-primary btn-block"><i class="fa fa-download"></i> Unduh PDF</a>
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