@extends('layouts.home-layout')

@section('title', 'Keranjang Belanja')

@section('content')
<section class="py-5" style="margin-top: 60px; background-color: #edf8fd;">
  <div class="container">
    <h2 class="fw-bold mb-4">Keranjang Belanja Anda</h2>
    <div class="row align-items-start">
      
      <!-- Keranjang Items -->
      <div class="col-lg-8">

        <!-- Item 1 -->
        <!-- Item 1 -->
        <div class="d-flex border rounded p-3 mb-3 bg-white">
          <div class="form-check me-3">
            <input class="form-check-input" type="checkbox" id="selectCourse1">
          </div>
          <img src="{{ asset('images/indomaret.jpg') }}"
              alt="Kursus"
              class="rounded-2 me-3"
              style="width: 120px; height: 80px; object-fit: cover; object-position: center;">
          <div class="flex-grow-1 d-flex flex-column justify-content-between">
            <div>
              <h5 class="mb-1">Nama Kursus Contoh</h5>
              <p class="text-muted mb-1">Tom Hollan</p>
              <div class="mb-2">
                <span class="text-warning">★ 4.5</span>
                <span class="text-muted">(175)</span>
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-end mt-2">
              <a href="#" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1">
                <i class="bi bi-trash"></i> Hapus
              </a>
              <strong class="text-primary fs-6">Rp100.000</strong>
            </div>
          </div>
        </div>

        <!-- Item 2 -->
        <div class="d-flex border rounded p-3 mb-3 bg-white">
          <div class="form-check me-3">
            <input class="form-check-input" type="checkbox" id="selectCourse2">
          </div>
          <img src="{{ asset('images/course.jpg') }}"
              alt="Kursus"
              class="rounded-2 me-3"
              style="width: 120px; height: 80px; object-fit: cover; object-position: center;">
          <div class="flex-grow-1 d-flex flex-column justify-content-between">
            <div>
              <h5 class="mb-1">Kursus JavaScript Lanjutan</h5>
              <p class="text-muted mb-1">Jane Doe</p>
              <div class="mb-2">
                <span class="text-warning">★ 4.8</span>
                <span class="text-muted">(320)</span>
              </div>
            </div>
            <div class="d-flex justify-content-between align-items-end mt-2">
              <a href="#" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1">
                <i class="bi bi-trash"></i> Hapus
              </a>
              <strong class="text-primary fs-6">Rp399.000</strong>
            </div>
          </div>
        </div>
      </div>

      <!-- Ringkasan Pembayaran -->
      <div class="col-lg-4">
        <div class="bg-white border rounded p-4">
          <h5>Total Pembayaran:</h5>
          <h3 class="fw-bold">Rp698.000</h3>
          <a href="/payment" class="btn btn-primary w-100 mt-3">Lanjut ke Pembayaran</a>
        </div>
      </div>

    </div>
  </div>
</section>
@endsection
