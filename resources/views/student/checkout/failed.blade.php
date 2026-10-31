@extends('layouts.home-layout')

@section('title', 'Pembayaran Gagal')

@section('content')
<div class="rs-page">
    <section class="rs-section rs-section--paper">
        <div class="rs-wrap">
            <x-rs.status tone="danger" icon="bi-x-circle-fill" title="Pembayaran Gagal">
                <p>
                    Maaf, transaksi Anda tidak dapat diproses atau telah dibatalkan. Dana tidak akan ditarik dari rekening Anda. Silakan coba lagi.
                </p>
                <div class="rs-status__actions">
                    <a href="{{ route('student.cart.index') }}" class="rs-btn rs-btn--block">Kembali ke Keranjang</a>
                </div>
            </x-rs.status>
        </div>
    </section>
</div>
@endsection
