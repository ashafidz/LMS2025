@extends('layouts.home-layout')

@section('title', 'Pembayaran Berhasil')

@section('content')
<section class="py-5" style="margin-top: 60px; background-color: #f8f9fa;">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4 p-md-5">
                        <div class="text-center mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                            <h2 class="fw-bold mt-3">Pembelian Berhasil</h2>
                            <p class="text-muted">Terima kasih! Berikut detail pembelian Anda.</p>
                        </div>

                        @if (session('success_message'))
                            <div class="alert alert-success">{{ session('success_message') }}</div>
                        @endif

                        <div class="bg-light rounded p-3 mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">No. Transaksi:</span>
                                <span class="fw-bold">{{ $order->order_code }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Tanggal:</span>
                                <span>{{ $order->updated_at->format('d F Y, H:i') }} WIB</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Status Pembayaran:</span>
                                <span class="fw-bold text-success">Lunas</span>
                            </div>
                        </div>

                        <h5 class="fw-bold mb-3">Detail Pembayaran</h5>
                        
                        @foreach($order->items as $item)
                        <div class="d-flex justify-content-between">
                            <span>1 &times; {{ $item->course->title }}</span>
                            <span>Rp{{ number_format($item->price_at_purchase, 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                        
                        <hr>

                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Subtotal</span>
                            <span>Rp{{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>

                        @if($order->discount_amount > 0)
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Diskon Kupon</span>
                            <span>- Rp{{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                        </div>
                        @endif

                        @if($order->vat_amount > 0)
                        <div class="d-flex justify-content-between">
                            {{-- DIPERBARUI: Menggunakan kolom baru yang sudah pasti benar --}}
                            <span class="text-muted">PPN ({{ rtrim(rtrim($order->vat_percentage_at_purchase, '0'), '.') }}%)</span>
                            <span>+ Rp{{ number_format($order->vat_amount, 0, ',', '.') }}</span>
                        </div>
                        @endif

                        @if($order->transaction_fee_amount > 0)
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Biaya Layanan</span>
                            <span>+ Rp{{ number_format($order->transaction_fee_amount, 0, ',', '.') }}</span>
                        </div>
                        @endif

                        <hr>

                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Total Dibayar:</span>
                            <span class="text-primary">Rp{{ number_format($order->final_amount, 0, ',', '.') }}</span>
                        </div>

                        <div class="mt-4">
                            <a href="#" class="btn btn-primary w-100">
                                <i class="bi bi-book-fill me-2"></i> Lihat Kursus Saya
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection