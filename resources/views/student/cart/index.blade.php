@extends('layouts.home-layout')

@section('title', 'Keranjang Belanja')

@section('content')
<div class="rs-page">
    <section class="rs-pagehead">
        <div class="rs-wrap">
            <p class="rs-crumbs"><a href="{{ route('home') }}">Beranda</a> / Keranjang</p>
            <h1 class="rs-h1">Keranjang Belanja Anda</h1>
        </div>
    </section>

    <section class="rs-section">
        <div class="rs-wrap">
            @if (session('success'))
                <div class="rs-alert rs-alert--ok" role="status">{{ session('success') }}</div>
            @endif
            @if (session('info'))
                <div class="rs-alert rs-alert--info" role="status">{{ session('info') }}</div>
            @endif
            @if (session('error'))
                <div class="rs-alert rs-alert--danger" role="alert">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="rs-alert rs-alert--danger" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($cartItems->isEmpty())
                <div class="rs-empty">
                    <h2 class="rs-h3">Keranjang Anda Kosong</h2>
                    <p>Sepertinya Anda belum menambahkan kursus apapun ke keranjang.</p>
                    <a href="{{ route('courses') }}" class="rs-btn">Jelajahi Kursus</a>
                </div>
            @else
                <div class="rs-cart">
                    <ul class="rs-cart-list">
                        @foreach ($cartItems as $item)
                            <li class="rs-cart-item">
                                <div class="rs-cart-item__thumb rs-cover--c{{ ($item->course->category_id ?? 0) % 5 }}">
                                    @if ($item->course->thumbnail_url)
                                        <img src="{{ Storage::url($item->course->thumbnail_url) }}" alt="" loading="lazy">
                                    @endif
                                </div>
                                <div>
                                    <h2><a href="{{ route('courses.show', $item->course->slug) }}">{{ $item->course->title }}</a></h2>
                                    <p>{{ $item->course->instructor->name }}</p>
                                    <div class="rs-cart-item__foot">
                                        <button type="button" class="rs-btn rs-btn--sm rs-btn--danger-outline" data-rs-dialog="hapus-{{ $item->id }}"><i class="bi bi-trash" aria-hidden="true"></i>Hapus</button>
                                        <x-rs.price :course="$item->course" />
                                    </div>
                                </div>
                                <dialog id="hapus-{{ $item->id }}" class="rs-dialog" aria-labelledby="hapus-{{ $item->id }}-judul">
                                    <div class="rs-dialog__head">
                                        <h2 id="hapus-{{ $item->id }}-judul">Konfirmasi Hapus</h2>
                                        <form method="dialog"><button class="rs-dialog__close" aria-label="Tutup"><i class="bi bi-x-lg" aria-hidden="true"></i></button></form>
                                    </div>
                                    <div class="rs-dialog__body">
                                        <p>Yakin ingin menghapus <strong>{{ $item->course->title }}</strong> dari keranjang?</p>
                                    </div>
                                    <div class="rs-dialog__foot">
                                        <form method="dialog"><button class="rs-btn rs-btn--outline">Batal</button></form>
                                        <form action="{{ route('student.cart.remove', $item) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rs-btn rs-btn--danger">Hapus</button>
                                        </form>
                                    </div>
                                </dialog>
                            </li>
                        @endforeach
                    </ul>

                    <aside class="rs-summary" aria-labelledby="ringkasan-judul">
                        <h2 id="ringkasan-judul">Ringkasan Belanja</h2>

                        @if ($coupon)
                            <p class="rs-mb-0">Kupon diterapkan: <strong>{{ $coupon->code }}</strong></p>
                            <form action="{{ route('student.cart.remove_coupon') }}" method="POST">
                                @csrf
                                <button type="submit" class="rs-btn rs-btn--sm rs-btn--danger-outline rs-btn--block">Hapus Kupon</button>
                            </form>
                        @else
                            {{-- <form action="{{ route('student.cart.apply_coupon') }}" method="POST">
                                @csrf
                                <label for="coupon_code" class="form-label">Punya Kupon?</label>
                                <div class="input-group">
                                    <input type="text" name="code" id="coupon_code" class="form-control" placeholder="Masukkan kode kupon">
                                    <button class="btn btn-outline-secondary" type="submit">Terapkan</button>
                                </div>
                            </form>
                             <button type="button" class="btn btn-primary rounded-pill w-100 d-flex align-items-center justify-content-center gap-2 mt-2" data-bs-toggle="modal" data-bs-target="#modalKupon">
                                <i class="bi bi-percent"></i> Lihat Promo
                            </button> --}}

                            <form action="{{ route('student.cart.apply_coupon') }}" method="POST" id="coupon-form" class="rs-field">
                                @csrf
                                <label for="coupon_code">Punya kupon?</label>
                                <div class="rs-inline-form">
                                    <input type="text" name="code" id="coupon_code" class="rs-input" placeholder="Masukkan kode kupon">
                                    <button class="rs-btn rs-btn--outline" type="submit">Terapkan</button>
                                </div>
                            </form>
                            @if($publicCoupons->isNotEmpty())
                                <button type="button" class="rs-linkbtn" data-rs-dialog="kupon-tersedia">Lihat kupon tersedia</button>
                            @endif
                        @endif

                        <dl class="rs-rows">
                            <div>
                                <dt>Subtotal</dt>
                                <dd>Rp{{ number_format($subtotal, 0, ',', '.') }}</dd>
                            </div>
                            @if (session()->has('coupon'))
                                <div class="rs-rows__discount">
                                    <dt>Diskon</dt>
                                    <dd>- Rp{{ number_format($discount, 0, ',', '.') }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt>PPN ({{ rtrim(rtrim(number_format($vatPercentage, 2, '.', ''), '0'), '.') }}%)</dt>
                                <dd>+ Rp{{ number_format($vatAmount, 0, ',', '.') }}</dd>
                            </div>
                            <div>
                                <dt>Biaya Layanan</dt>
                                <dd>+ Rp{{ number_format($transactionFee, 0, ',', '.') }}</dd>
                            </div>
                            <div class="rs-rows__total">
                                <dt>Total</dt>
                                <dd>Rp{{ number_format($finalTotal, 0, ',', '.') }}</dd>
                            </div>
                        </dl>

                        <form action="{{ route('checkout.process') }}" method="POST" id="checkout-form">
                            @csrf
                            <button type="button" class="rs-btn rs-btn--block" data-rs-dialog="konfirmasi-checkout">Lanjut ke Pembayaran</button>
                        </form>
                    </aside>
                </div>

                <dialog id="konfirmasi-checkout" class="rs-dialog" aria-labelledby="konfirmasi-checkout-judul">
                    <div class="rs-dialog__head">
                        <h2 id="konfirmasi-checkout-judul">Konfirmasi Pesanan</h2>
                        <form method="dialog"><button class="rs-dialog__close" aria-label="Tutup"><i class="bi bi-x-lg" aria-hidden="true"></i></button></form>
                    </div>
                    <div class="rs-dialog__body">
                        <p>Apakah Anda yakin ingin membuat pesanan dan lanjut ke tahap pembayaran?</p>
                    </div>
                    <div class="rs-dialog__foot">
                        <form method="dialog"><button class="rs-btn rs-btn--outline">Batal</button></form>
                        <button type="submit" form="checkout-form" class="rs-btn">Ya, Lanjutkan</button>
                    </div>
                </dialog>
            @endif
        </div>
    </section>

{{-- MODAL BARU: Untuk Kupon Publik --}}
{{-- @if($cartItems->isNotEmpty() && $publicCoupons->isNotEmpty())
<div class="modal fade" id="publicCouponsModal" tabindex="-1" aria-labelledby="publicCouponsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="publicCouponsModalLabel">Kupon yang Tersedia Untuk Anda</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        @foreach($publicCoupons as $pCoupon)
            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title font-weight-bold">{{ $pCoupon->code }}</h5>
                    <p class="card-text">{{ $pCoupon->description }}</p>
                    <button class="btn btn-primary btn-sm use-coupon-btn" data-code="{{ $pCoupon->code }}">Gunakan</button>
                </div>
            </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endif --}}

    @if($cartItems->isNotEmpty() && $publicCoupons->isNotEmpty())
        <dialog id="kupon-tersedia" class="rs-dialog" aria-labelledby="kupon-tersedia-judul">
            <div class="rs-dialog__head">
                <h2 id="kupon-tersedia-judul">Promo Tersedia</h2>
                <form method="dialog"><button class="rs-dialog__close" aria-label="Tutup"><i class="bi bi-x-lg" aria-hidden="true"></i></button></form>
            </div>
            <div class="rs-dialog__body">
                @foreach($publicCoupons as $pCoupon)
                    <div class="rs-coupon">
                        <span class="rs-coupon__tag">
                            @if ($pCoupon->type === 'percent')
                                Diskon {{ (int) $pCoupon->value }}%
                            @else
                                Potongan Rp{{ number_format($pCoupon->value, 0, ',', '.') }}
                            @endif
                        </span>
                        <h3>Promo {{ $pCoupon->code }}</h3>
                        @if($pCoupon->description)
                            <p>{{ $pCoupon->description }}</p>
                        @endif
                        <div class="rs-coupon__foot">
                            <span class="rs-note">Kode: <code>{{ $pCoupon->code }}</code></span>
                            <button type="button" class="rs-btn rs-btn--sm use-coupon-btn" data-code="{{ $pCoupon->code }}">Gunakan</button>
                        </div>
                    </div>
                @endforeach
            </div>
        </dialog>
    @endif

{{-- Pastikan section dan container untuk rekomendasi tetap ada --}}
{{-- Hanya tampilkan bagian ini jika ada kursus populer DAN ada item di keranjang --}}
@if($popularCourses->isNotEmpty())
    <section class="rs-section rs-section--paper">
        <div class="rs-wrap">
            <h2 class="rs-h2">Rekomendasi Kursus Untuk Anda</h2>
            <div class="rs-books">
                @foreach ($popularCourses as $course)
                    <x-rs.course-book :course="$course" />
                @endforeach
            </div>
            <p class="rs-mt-4"><a class="rs-btn rs-btn--outline" href="{{ route('courses') }}">Lihat Lainnya</a></p>
        </div>
    </section>
@endif
</div>
@endsection
