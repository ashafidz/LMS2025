@extends('layouts.home-layout')

@section('title', 'Pembayaran Gagal')

@section('content')
<section class="py-5" style="margin-top: 60px; background-color: #f8f9fa;">
    <div class="container">
        <div class="row d-flex justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 text-center">
                    <div class="card-body p-5">
                        <i class="bi bi-x-circle-fill text-danger" style="font-size: 5rem;"></i>
                        <h2 class="fw-bold mt-3">Pembayaran Gagal</h2>
                        <p class="text-muted">
                            Maaf, transaksi Anda tidak dapat diproses atau telah dibatalkan. Dana tidak akan ditarik dari rekening Anda. Silakan coba lagi.
                        </p>
                        <hr>
                        <a href="{{ route('student.cart.index') }}" class="btn btn-primary w-100 mt-3">Kembali ke Keranjang</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection