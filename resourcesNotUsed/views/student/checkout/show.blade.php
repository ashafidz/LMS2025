@extends('layouts.home-layout')

@section('title', 'Konfirmasi Pesanan')

@section('content')
<section class="py-5" style="margin-top: 60px; background-color: #f8f9fa;">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-white text-center border-0 pt-4 pb-0">
                        <i class="bi bi-receipt-cutoff fs-1 text-primary"></i>
                        <h3 class="fw-bold mt-2">Ringkasan Pesanan Anda</h3>
                        <p class="text-muted">Harap periksa kembali detail pesanan Anda sebelum melanjutkan ke pembayaran.</p>
                    </div>
                    <div class="card-body p-4">
                        {{-- Detail Pesanan --}}
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Kode Pesanan:</span>
                            <span class="fw-bold">{{ $order->order_code }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Tanggal:</span>
                            <span>{{ $order->created_at->format('d F Y') }}</span>
                        </div>

                        <hr>
                        
                        {{-- Daftar Item --}}
                        <h6 class="fw-bold mb-3">Item yang Dipesan:</h6>
                        <ul class="list-group list-group-flush mb-3">
                            @foreach($order->items as $item)
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span>{{ $item->course->title }}</span>
                                <span>Rp{{ number_format($item->price_at_purchase, 0, ',', '.') }}</span>
                            </li>
                            @endforeach
                        </ul>

                        <hr>

                        {{-- Rincian Harga --}}
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Subtotal</span>
                            <span>Rp{{ number_format($order->total_amount, 0, ',', '.') }}</span>
                        </div>
                        @if($order->discount_amount > 0)
                        <div class="d-flex justify-content-between text-success">
                            <span class="text-muted">Diskon ({{ $order->coupon->code ?? '' }})</span>
                            <span>- Rp{{ number_format($order->discount_amount, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Total Pembayaran</span>
                            <span class="text-primary">Rp{{ number_format($order->final_amount, 0, ',', '.') }}</span>
                        </div>
                        
                        <div class="text-center mt-4">
                            <p class="text-muted">Anda akan melakukan transaksi sebesar <strong>Rp{{ number_format($order->final_amount, 0, ',', '.') }}</strong></p>
                            
                            {{-- Tombol ini sekarang akan memicu popup Midtrans --}}
                            <button id="pay-button" class="btn btn-success btn-lg w-100">
                                <i class="bi bi-shield-check"></i> Bayar Sekarang
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
    {{-- 1. Muat library Midtrans Snap.js --}}
    <script type="text/javascript"
            src="https://app.sandbox.midtrans.com/snap/snap.js"
            data-client-key="{{ $midtransClientKey }}"></script>
            
    {{-- 2. Skrip untuk memicu pembayaran --}}
    <script type="text/javascript">
      var payButton = document.getElementById('pay-button');
      payButton.addEventListener('click', function () {
        snap.pay('{{ $snapToken }}', {
          onSuccess: function(result){
            console.log(result);
            window.location.href = "{{ route('payment.success', $order->id) }}";
          },
          onPending: function(result){
            console.log(result);
            window.location.href = "{{ route('payment.pending', $order->id) }}";
          },
          onError: function(result){
            console.log(result);
            window.location.href = "{{ route('payment.failed', $order->id) }}";
          },
          onClose: function(){
            /* Pengguna menutup popup tanpa menyelesaikan pembayaran */
            window.location.href = "{{ route('payment.cancelled', $order->id) }}";
          }
        });
      });
    </script>
@endpush