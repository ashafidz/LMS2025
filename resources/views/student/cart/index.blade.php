@extends('layouts.home-layout')

@section('title', 'Keranjang Belanja')

@section('content')
<section class="py-5" style="margin-top: 60px; background-color: #f8f9fa;">
    <div class="container">
        <h2 class="fw-bold mb-4">Keranjang Belanja Anda</h2>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </div>
        @endif
        
        <div class="row align-items-start">
            
            {{-- DIUBAH: Logika if/else dipindahkan ke luar --}}
            @if($cartItems->isEmpty())
                {{-- Jika keranjang kosong, tampilkan pesan ini di tengah --}}
                <div class="col-12">
                    <div class="bg-white border rounded p-5 text-center" style="max-width: 600px; margin: auto;">
                        <i class="bi bi-cart-x fs-1 text-muted"></i>
                        <h4 class="mt-3">Keranjang Anda Kosong</h4>
                        <p class="text-muted">Sepertinya Anda belum menambahkan kursus apapun ke keranjang.</p>
                        <a href="{{ route('courses') }}" class="btn btn-primary mt-2">Jelajahi Kursus</a>
                    </div>
                </div>
            @else
                {{-- Jika keranjang ada isinya, tampilkan layout 2 kolom --}}
                <!-- Keranjang Items -->
                <div class="col-lg-8">
                    @foreach ($cartItems as $item)
                        <div class="d-flex border rounded p-3 mb-3 bg-white shadow-sm">
                            <img src="{{ $item->course->thumbnail_url ? Storage::url($item->course->thumbnail_url) : 'https://placehold.co/120x80' }}"
                                 alt="{{ $item->course->title }}"
                                 class="rounded-2 me-3"
                                 style="width: 120px; height: 80px; object-fit: cover; object-position: center;">
                            
                            <div class="flex-grow-1 d-flex flex-column justify-content-between">
                                <div>
                                    <h5 class="mb-1">{{ $item->course->title }}</h5>
                                    <p class="text-muted mb-1">Oleh: {{ $item->course->instructor->name }}</p>
                                </div>
                                <div class="d-flex justify-content-between align-items-end mt-2">
                                    <form action="{{ route('student.cart.remove', $item->id) }}" method="POST" onsubmit="return confirm('Hapus kursus ini dari keranjang?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                    <strong class="text-primary fs-5">Rp{{ number_format($item->course->price, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Ringkasan Pembayaran -->
                <div class="col-lg-4">
                    <div class="bg-white border rounded p-4 shadow-sm">
                        <h5 class="fw-bold">Ringkasan Belanja</h5>
                        <hr>
                        
                        @if (session()->has('coupon'))
                            <div class="d-flex justify-content-between">
                                <span>Kupon diterapkan:</span>
                                <strong>{{ session('coupon')->code }}</strong>
                            </div>
                            <form action="{{ route('student.cart.remove_coupon') }}" method="POST" class="mt-2">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger w-100">Hapus Kupon</button>
                            </form>
                        @else
                            <form action="{{ route('student.cart.apply_coupon') }}" method="POST">
                                @csrf
                                <label for="coupon_code" class="form-label">Punya Kupon?</label>
                                <div class="input-group">
                                    <input type="text" name="code" id="coupon_code" class="form-control" placeholder="Masukkan kode kupon">
                                    <button class="btn btn-outline-secondary" type="submit">Terapkan</button>
                                </div>
                            </form>
                        @endif
                        
                        <hr>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal:</span>
                            <span>Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        @if (session()->has('coupon'))
                            <div class="d-flex justify-content-between mb-2 text-success">
                                <span class="text-muted">Diskon:</span>
                                <span>- Rp{{ number_format($discount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">PPN (11%):</span>
                            <span>+ Rp{{ number_format($vatAmount, 0, ',', '.') }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Biaya Layanan:</span>
                            <span>+ Rp{{ number_format($transactionFee, 0, ',', '.') }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Total:</span>
                            <span>Rp{{ number_format($finalTotal, 0, ',', '.') }}</span>
                        </div>

                        <form action="{{ route('checkout.process') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100 mt-3">Lanjut ke Pembayaran</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection