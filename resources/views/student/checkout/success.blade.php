@extends('layouts.home-layout')

@section('title', 'Pembayaran Berhasil')

@section('content')
@php
    $tz = auth()->user()->timezone ?: config('app.timezone');
@endphp
<div class="rs-page">
    <section class="rs-section rs-section--paper">
        <div class="rs-wrap">
            <div class="rs-stack">
                @if ($order->status === 'paid')
                    <x-rs.status tone="ok" icon="bi-check-circle-fill" title="Pembelian Berhasil">
                        <p>Terima kasih! Berikut detail pembelian Anda.</p>
                    </x-rs.status>
                @else
                    <x-rs.status tone="wait" icon="bi-clock-history" title="Pembayaran sedang diproses">
                        <p>Kami sedang menunggu konfirmasi pembayaran Anda.</p>
                    </x-rs.status>
                @endif

                <div class="rs-receipt">
                    @if (session('success_message'))
                        <div class="rs-alert rs-alert--ok" role="status">{{ session('success_message') }}</div>
                    @endif

                    <dl class="rs-rows">
                        <div>
                            <dt>No. Transaksi</dt>
                            <dd>{{ $order->order_code }}</dd>
                        </div>
                        <div>
                            <dt>Tanggal</dt>
                            <dd>{{ $order->updated_at->copy()->timezone($tz)->format('d/m/Y H:i T') }}</dd>
                        </div>
                        <div>
                            <dt>Status Pembayaran</dt>
                            @if ($order->status === 'paid')
                                <dd>Lunas</dd>
                            @else
                                <dd>Menunggu Konfirmasi</dd>
                            @endif
                        </div>
                    </dl>

                    <h2>Detail Pembayaran</h2>
                    <ul class="rs-receipt__items">
                        @foreach($order->items as $item)
                            <li>
                                <span>1 &times; {{ $item->course->title }}</span>
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
                                <dt>Diskon Kupon</dt>
                                <dd>- Rp{{ number_format($order->discount_amount, 0, ',', '.') }}</dd>
                            </div>
                        @endif
                        @if($order->vat_amount > 0)
                            {{-- DIPERBARUI: Menggunakan kolom baru yang sudah pasti benar --}}
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
                            <dt>Total Dibayar</dt>
                            <dd>Rp{{ number_format($order->final_amount, 0, ',', '.') }}</dd>
                        </div>
                    </dl>

                    @if ($order->status === 'paid')
                        <a href="{{ route('student.my_courses') }}" class="rs-btn rs-btn--block"><i class="bi bi-book-fill" aria-hidden="true"></i>Lihat Kursus Saya</a>
                    @else
                        <a href="{{ route('student.transactions.index') }}" class="rs-btn rs-btn--block"><i class="bi bi-receipt" aria-hidden="true"></i>Lihat Riwayat Transaksi</a>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
