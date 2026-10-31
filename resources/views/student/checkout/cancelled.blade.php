@extends('layouts.home-layout')

@section('title', 'Pembayaran Dibatalkan')

@section('content')
<div class="rs-page">
    <section class="rs-section rs-section--paper">
        <div class="rs-wrap">
            <x-rs.status tone="info" icon="bi-info-circle-fill" title="Pembayaran Dibatalkan">
                <p>
                    Anda telah menutup jendela pembayaran. Pesanan Anda telah dibatalkan.
                </p>
                <div class="rs-status__actions">
                    <a href="{{ route('student.transactions.index') }}" class="rs-btn rs-btn--block">Lihat Riwayat Transaksi</a>
                </div>
            </x-rs.status>
        </div>
    </section>
</div>
@endsection
