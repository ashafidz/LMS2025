@extends('layouts.home-layout')

@section('title', 'Konfirmasi Pesanan')

@section('content')
@php
    $tz = auth()->user()->timezone ?: config('app.timezone');
@endphp
<div class="rs-page">
    <section class="rs-pagehead">
        <div class="rs-wrap">
            <p class="rs-crumbs"><a href="{{ route('home') }}">Beranda</a> / <a href="{{ route('student.cart.index') }}">Keranjang</a> / Ringkasan pesanan</p>
            <h1 class="rs-h1">Ringkasan Pesanan Anda</h1>
            <p class="rs-lead">Harap periksa kembali detail pesanan Anda sebelum melanjutkan ke pembayaran.</p>
        </div>
    </section>

    <section class="rs-section">
        <div class="rs-wrap">
            <div class="rs-receipt">
                <dl class="rs-rows">
                    <div>
                        <dt>Kode Pesanan</dt>
                        <dd>{{ $order->order_code }}</dd>
                    </div>
                    <div>
                        <dt>Tanggal</dt>
                        <dd>{{ $order->created_at->copy()->timezone($tz)->format('d/m/Y') }}</dd>
                    </div>
                </dl>

                <h2>Item yang dipesan</h2>
                <ul class="rs-receipt__items">
                    @foreach($order->items as $item)
                        <li>
                            <span>{{ $item->course->title }}</span>
                            <span>Rp{{ number_format($item->price_at_purchase, 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>

                <dl class="rs-rows">
                    <div>
                        <dt>Subtotal</dt>
                        <dd>Rp{{ number_format($order->total_amount, 0, ',', '.') }}</dd>
                    </div>
                    @if($order->discount_amount > 0)
                        <div class="rs-rows__discount">
                            <dt>Diskon ({{ $order->coupon->code ?? '' }})</dt>
                            <dd>- Rp{{ number_format($order->discount_amount, 0, ',', '.') }}</dd>
                        </div>
                    @endif
                    @if($order->vat_amount > 0)
                        <div>
                            <dt>PPN ({{ rtrim(rtrim($order->vat_percentage_at_purchase, '0'), '.') }}%)</dt>
                            <dd>+ Rp{{ number_format($order->vat_amount, 0, ',', '.') }}</dd>
                        </div>
                    @endif
                    @if($order->transaction_fee_amount > 0)
                        <div>
                            <dt>Biaya Layanan</dt>
                            <dd>+ Rp{{ number_format($order->transaction_fee_amount, 0, ',', '.') }}</dd>
                        </div>
                    @endif
                    <div class="rs-rows__total">
                        <dt>Total Pembayaran</dt>
                        <dd>Rp{{ number_format($order->final_amount, 0, ',', '.') }}</dd>
                    </div>
                </dl>

                <p class="rs-note">Anda akan melakukan transaksi sebesar <strong>Rp{{ number_format($order->final_amount, 0, ',', '.') }}</strong></p>

                {{-- Tombol ini sekarang akan memicu popup Midtrans --}}
                <button type="button" id="pay-button" class="rs-btn rs-btn--ok rs-btn--block"><i class="bi bi-shield-check" aria-hidden="true"></i>Bayar Sekarang</button>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
    {{-- 1. Muat library Midtrans Snap.js --}}
    <script type="text/javascript"
            src="{{ config('midtrans.is_production') ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}"
            data-client-key="{{ $midtransClientKey }}"></script>


    {{-- 2. Skrip untuk memicu pembayaran --}}
    <script type="text/javascript">
      var payButton = document.getElementById('pay-button');
      payButton.addEventListener('click', function () {
        snap.pay('{{ $snapToken }}', {
          onSuccess: function(result){
            console.log(result);
            window.location.href = "{{ route('payment.success', $order) }}";
          },
          onPending: function(result){
            console.log(result);
            window.location.href = "{{ route('payment.pending', $order) }}";
          },
          onError: function(result){
            console.log(result);
            window.location.href = "{{ route('payment.failed', $order) }}";
          },
          onClose: function(){
            /* Pengguna menutup popup tanpa menyelesaikan pembayaran */
            window.location.href = "{{ route('payment.pending', $order) }}";
          }
        });
      });
    </script>
@endpush