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
            
            @if($cartItems->isEmpty())
                <div class="col-12">
                    <div class="bg-white border rounded p-5 text-center" style="max-width: 600px; margin: auto;">
                        <i class="bi bi-cart-x fs-1 text-muted"></i>
                        <h4 class="mt-3">Keranjang Anda Kosong</h4>
                        <p class="text-muted">Sepertinya Anda belum menambahkan kursus apapun ke keranjang.</p>
                        <a href="{{ route('courses') }}" class="btn btn-primary rounded-pill w-50 mt-3">
                            Jelajahi Kursus
                        </a>
                    </div>
                </div>
            @else
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
                                    <p class="text-muted mb-1">{{ $item->course->instructor->name }}</p>
                                </div>
                                <div class="d-flex justify-content-between align-items-end mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1"
                                        data-bs-toggle="modal" data-bs-target="#deleteModal{{ $item->id }}">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                    <strong class="text-primary fs-5">Rp{{ number_format($item->course->price, 0, ',', '.') }}</strong>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $item->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4 shadow-sm border-0">
                                    <div class="modal-header border-0 pb-0">
                                        <h5 class="modal-title text-danger d-flex align-items-center" id="deleteModalLabel{{ $item->id }}">
                                            <i class="bi bi-trash-fill me-2"></i> Konfirmasi Hapus
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        <p class="mb-3 fs-6">
                                            Yakin ingin menghapus <strong>{{ $item->course->title }}</strong> dari keranjang?
                                        </p>
                                    </div>
                                    <div class="modal-footer border-0 justify-content-center pb-4">
                                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                        <form action="{{ route('student.cart.remove', $item->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger rounded-pill px-4 ms-2">
                                                <i class="bi bi-check-circle me-1"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

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

                                                        {{-- BAGIAN BARU: Form Kupon dengan Tombol Modal --}}
                            <form action="{{ route('student.cart.apply_coupon') }}" method="POST" id="coupon-form">
                                @csrf
                                <label for="coupon_code" class="form-label">Punya Kupon?</label>
                                <div class="input-group">
                                    <input type="text" name="code" id="coupon_code" class="form-control" placeholder="Masukkan kode kupon">
                                    <button class="btn btn-outline-secondary" type="submit">Terapkan</button>
                                </div>
                            </form>
                            @if($publicCoupons->isNotEmpty())
                            <div class="text-center mt-2">
                                <a href="#" class="btn btn-outline-primary btn-sm w-100 d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#publicCouponsModal">Lihat Kupon Tersedia</a>
                            </div>
                            @endif
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

                        {{-- DIUBAH: Tambahkan id pada form dan ubah tombol menjadi type="button" --}}
                        <form action="{{ route('checkout.process') }}" method="POST" id="checkout-form">
                            @csrf
                            <button type="button" id="checkout-btn" class="btn btn-primary rounded-pill w-100 mt-3">
                                Lanjut ke Pembayaran
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

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


{{-- MODAL BARU: Untuk Kupon Publik (Tampilan Baru) --}}
@if($cartItems->isNotEmpty() && $publicCoupons->isNotEmpty())
<div class="modal fade" id="publicCouponsModal" tabindex="-1" aria-labelledby="publicCouponsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="publicCouponsModalLabel">Promo Tersedia</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        
        @foreach($publicCoupons as $pCoupon)
          {{-- Kartu Promo --}}
          <div class="border rounded p-3 mb-3 shadow-sm">
            
            {{-- Badge Diskon --}}
            <span class="badge bg-info mb-2">Diskon {{ (int)$pCoupon->value }}%</span>
            
            {{-- Judul Promo --}}
            <h6 class="fw-bold mb-1">Promo {{ $pCoupon->code }}</h6>
            
            {{-- Deskripsi (hanya tampil jika ada) --}}
            @if($pCoupon->description)
            <ul class="mb-2 small text-muted ps-3">
              <li>{{ $pCoupon->description }}</li>
            </ul>
            @endif

            <div class="d-flex justify-content-between align-items-center mt-3">
              {{-- Kode Kupon --}}
              <span class="text-muted small">Kode: <code>{{ $pCoupon->code }}</code></span>
              
              {{-- Tombol Gunakan --}}
              <button class="btn btn-sm btn-primary use-coupon-btn" data-code="{{ $pCoupon->code }}">Gunakan</button>
            </div>
          </div>
        @endforeach

      </div>
    </div>
  </div>
</div>
@endif

</section>

{{-- Pastikan section dan container untuk rekomendasi tetap ada --}}
{{-- Hanya tampilkan bagian ini jika ada kursus populer DAN ada item di keranjang --}}
@if($popularCourses->isNotEmpty())
<section class="py-5" style="background-color: #f8f9fa;">
    <div class="container">
        <div class="mt-5">
            <h4 class="fw-bold text-primary mb-4">Rekomendasi Kursus Untuk Anda</h4>
            <div class="row g-4">

                {{-- Looping untuk setiap kursus populer --}}
                @foreach ($popularCourses as $course)
                <div class="col-md-4 col-lg-3">
                    {{-- Link ke halaman detail kursus --}}
                    <a href="{{ route('courses.show', $course->slug) }}" class="text-decoration-none">
                        <div class="card h-100 shadow-sm border-0 rounded-4">
                            
                            {{-- Gambar Thumbnail --}}
                            <img src="{{ $course->thumbnail_url ? asset('storage/' . $course->thumbnail_url) : 'https://placehold.co/600x400/e0edff/007bff?text=Kursus' }}" 
                                 class="card-img-top rounded-top-4" 
                                 style="height: 150px; object-fit: cover;" 
                                 alt="{{ $course->title }}">
                                 
                            <div class="card-body d-flex flex-column">
                                {{-- Judul Kursus --}}
                                <h5 class="card-title flex-grow-1">{{ Str::limit($course->title, 50) }}</h5>

                                {{-- Nama Instruktur --}}
                                <p class="text-muted mb-2">{{ $course->instructor->name ?? 'Tanpa Instruktur' }}</p>

                                {{-- Harga Kursus --}}
                                <span class="fw-bold text-dark">
                                    @if($course->price > 0)
                                        Rp{{ number_format($course->price, 0, ',', '.') }}
                                    @else
                                        Gratis
                                    @endif
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
                @endforeach

            </div>
        </div>
        <div class="text-center mt-5">
            <a href="{{ route('courses') }}" class="btn btn-outline-primary rounded-pill px-4 py-2">
                Lihat Lainnya <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    </div>
</section>
@endif




<div class="modal fade" id="modalKupon" tabindex="-1" aria-labelledby="modalKuponLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modalKuponLabel">Promo Tersedia</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
            {{-- Contoh Promo --}}
            <div class="border rounded p-3 mb-3 shadow-sm">
                <span class="badge bg-info mb-2">Diskon hingga 30%</span>
                <h6 class="fw-bold mb-1">Promo JAGOCODING - Full Stack</h6>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Kode: <code>JAGOCODING</code></span>
                    <button class="btn btn-sm btn-primary" onclick="pilihKupon('JAGOCODING')">Gunakan</button>
                </div>
            </div>
        </div>
    </div>
  </div>
</div>

{{-- BARU: Modal Konfirmasi Checkout --}}
<div class="modal fade" id="checkoutConfirmModal" tabindex="-1" aria-labelledby="checkoutConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-sm border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="checkoutConfirmModalLabel">Konfirmasi Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body py-4 text-center">
                <p class="fs-6">
                    Apakah Anda yakin ingin membuat pesanan dan lanjut ke tahap pembayaran?
                </p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="confirm-checkout-btn" class="btn btn-primary rounded-pill px-4 ms-2">
                    Ya, Lanjutkan
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function pilihKupon(kode) {
        document.getElementById('coupon_code').value = kode;
        var modal = bootstrap.Modal.getInstance(document.getElementById('modalKupon'));
        modal.hide();
    }

    // BARU: Logika untuk modal konfirmasi checkout
    document.addEventListener('DOMContentLoaded', function() {
        const checkoutBtn = document.getElementById('checkout-btn');
        
        // Cek jika tombol checkout ada (saat keranjang tidak kosong)
        if (checkoutBtn) {
            const checkoutForm = document.getElementById('checkout-form');
            const confirmCheckoutBtn = document.getElementById('confirm-checkout-btn');
            const checkoutModal = new bootstrap.Modal(document.getElementById('checkoutConfirmModal'));

            // 1. Saat tombol "Lanjut ke Pembayaran" diklik, tampilkan modal
            checkoutBtn.addEventListener('click', function() {
                checkoutModal.show();
            });

            // 2. Saat tombol "Ya, Lanjutkan" di modal diklik, submit form
            confirmCheckoutBtn.addEventListener('click', function() {
                checkoutForm.submit();
            });
        }
    });
</script>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const useCouponButtons = document.querySelectorAll('.use-coupon-btn');
    const couponInput = document.getElementById('coupon_code');
    const couponForm = document.getElementById('coupon-form');

    useCouponButtons.forEach(button => {
        button.addEventListener('click', function() {
            const couponCode = this.dataset.code;
            if (couponInput && couponForm) {
                couponInput.value = couponCode;
                couponForm.submit();
            }
        });
    });
});
</script>

@endpush