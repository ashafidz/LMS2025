@extends('layouts.home-layout')

@section('title', 'Pembayaran Tertunda')

@section('content')
<div class="rs-page">
    <section class="rs-section rs-section--paper">
        <div class="rs-wrap">
            <x-rs.status tone="wait" icon="bi-clock-history" title="Pembayaran Tertunda">
                <p>
                    Kami sedang menunggu konfirmasi pembayaran Anda. Silakan selesaikan pembayaran sesuai instruksi. Anda bisa melihat detail pesanan di riwayat transaksi.
                </p>
                <div class="rs-status__actions">
                    <a href="{{ route('student.transactions.index') }}" class="rs-btn rs-btn--block">Lihat Riwayat Transaksi</a>
                </div>
            </x-rs.status>
        </div>
    </section>
</div>
@endsection
