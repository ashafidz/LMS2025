@extends('layouts.home-layout')

@section('title', 'Pembayaran Tertunda')

@section('content')
<section class="py-5" style="margin-top: 60px; background-color: #f8f9fa;">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 text-center">
                    <div class="card-body p-5">
                        <i class="bi bi-clock-history text-warning" style="font-size: 5rem;"></i>
                        <h2 class="fw-bold mt-3">Pembayaran Tertunda</h2>
                        <p class="text-muted">
                            Kami sedang menunggu konfirmasi pembayaran Anda. Silakan selesaikan pembayaran sesuai instruksi. Anda bisa melihat detail pesanan di riwayat transaksi.
                        </p>
                        <hr>
                        <a href="{{ route('student.transactions.index') }}" class="btn btn-primary w-100 mt-2">Lihat Riwayat Transaksi</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection