@extends('layouts.home-layout')

@section('title', 'Pembayaran Dibatalkan')

@section('content')
<section class="py-5" style="margin-top: 60px; background-color: #f8f9fa;">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 text-center">
                    <div class="card-body p-5">
                        <i class="bi bi-info-circle-fill text-secondary" style="font-size: 5rem;"></i>
                        <h2 class="fw-bold mt-3">Pembayaran Dibatalkan</h2>
                        <p class="text-muted">
                            Anda telah menutup jendela pembayaran. Pesanan Anda telah dibatalkan.
                        </p>
                        <hr>
                        <a href="{{ route('student.transactions.index') }}" class="btn btn-primary w-100 mt-3">Lihat Riwayat Transaksi</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection