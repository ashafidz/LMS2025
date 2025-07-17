@extends('layouts.app')

@section('title', 'FAQ - iLanding')

@section('content')
<!-- Banner Course -->
<section style="background: linear-gradient(to right, #ffffff, #e0edff);">
  <div class="container">
    <div class="row align-items-center pt-5">
      <!-- Kiri: Info -->
      <div class="col-md-8">
        <a href="/catalog" class="text-muted text-decoration-none d-inline-block mb-2">
          <i class="bi bi-chevron-left"></i> Back to Course
        </a>
        <h3 class="fw-bold mb-1 fs-1">Rahasia Produktivitas Tinggi :</h3>
        <h4 class="fw-semibold text-secondary fs-3">Manfaatkan ChatGPT Secara Optimal</h4>
      </div>

      <!-- Kanan: Harga & Button -->
      <div class="col-lg-4">
        <div class="bg-white border rounded p-4 shadow-sm">
          <h5 class="mb-1">Total Pembayaran:</h5>
          <h3 class="fw-bold text-dark">Rp.100.000</h3>
          <a href="/cart" class="btn btn-primary w-100 mt-3">Lanjut ke Pembayaran</a>

          <!-- Kupon -->
          <div class="mt-4 p-3 border rounded bg-light-subtle">
            <label for="coupon" class="form-label mb-2 fw-semibold text-secondary small">Punya kode kupon?</label>
            <div class="input-group input-group-sm">
              <input type="text" id="coupon" name="coupon" class="form-control" placeholder="Masukkan kode...">
              <button class="btn btn-outline-primary" type="submit">Terapkan</button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Konten Detail Course -->
<section class="py-5 bg-white">
  <div class="container">
    <div class="row gy-5">
      <!-- Kiri: Tentang Course dan Materi -->
      <div class="col-lg-8">
        <!-- Tentang Course -->
        <div class="mb-4">
          <h5 class="fw-bold mb-3">Tentang Course</h5>
          <p class="text-muted">
            Kursus ini dirancang khusus bagi pemula hingga pengguna tingkat lanjut yang ingin menggali potensi ChatGPT secara optimal untuk produktivitas yang lebih tinggi. Dengan metode pembelajaran yang mudah dipahami, Anda akan diajak memahami dasar-dasar ChatGPT, mulai dari cara kerja hingga fitur-fitur utamanya.
          </p>
        </div>

        <!-- Materi -->
        <div class="mb-4">
          <h6 class="fw-bold">Kamu akan mempelajari</h6>
          <div class="accordion" id="accordionMateri">

            <!-- Materi 1 -->
            <div class="accordion-item border-0 mb-2 shadow-sm">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#materi1">
                  <i class="bi bi-lightbulb me-2 text-warning"></i>
                  Pengantar Tentang ChatGPT
                </button>
              </h2>
              <div id="materi1" class="accordion-collapse collapse" data-bs-parent="#accordionMateri">
                <div class="accordion-body">
                  <p class="mb-2 text-muted">Pahami sejarah, kemampuan, dan cara kerja ChatGPT dalam membantu pekerjaan sehari-hari.</p>
                  <div class="d-flex gap-2">
                    <span class="badge bg-primary-subtle text-primary">3 Video</span>
                    <span class="badge bg-success-subtle text-success">Level: Pemula</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Materi 2 -->
            <div class="accordion-item border-0 mb-2 shadow-sm">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#materi2">
                  <i class="bi bi-code-slash me-2 text-info"></i>
                  ChatGPT Prompt Engineering
                </button>
              </h2>
              <div id="materi2" class="accordion-collapse collapse" data-bs-parent="#accordionMateri">
                <div class="accordion-body">
                  <p class="mb-2 text-muted">Belajar membuat prompt efektif untuk hasil optimal dari ChatGPT.</p>
                  <div class="d-flex gap-2">
                    <span class="badge bg-primary-subtle text-primary">2 Video</span>
                    <span class="badge bg-warning-subtle text-warning">3 Quiz</span>
                    <span class="badge bg-success-subtle text-success">Level: Menengah</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Materi 3 -->
            <div class="accordion-item border-0 shadow-sm">
              <h2 class="accordion-header">
                <button class="accordion-button collapsed" data-bs-toggle="collapse" data-bs-target="#materi3">
                  <i class="bi bi-rocket-takeoff me-2 text-danger"></i>
                  Memaksimalkan ChatGPT untuk berbagai keperluan
                </button>
              </h2>
              <div id="materi3" class="accordion-collapse collapse" data-bs-parent="#accordionMateri">
                <div class="accordion-body">
                  <p class="mb-2 text-muted">Gunakan ChatGPT untuk menulis, merangkum, membuat laporan, hingga riset.</p>
                  <div class="d-flex gap-2">
                    <span class="badge bg-primary-subtle text-primary">4 Video</span>
                    <span class="badge bg-warning-subtle text-warning">4 Quiz</span>
                    <span class="badge bg-danger-subtle text-danger">Level: Lanjutan</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Kanan: Coach dan Skill -->
      <div class="col-lg-4">
        <!-- Coach -->
        <div class="mb-4">
          <h6 class="fw-bold mb-3">Coach</h6>
          <div class="d-flex align-items-center">
            <img src="{{ asset('images/minion.jpg') }}" alt="Coach Brian Adinata"
              class="rounded-circle me-3" style="width: 45px; height: 45px; object-fit: cover;">
            <div>
              <strong>Brian Adinata</strong>
            </div>
          </div>
        </div>

        <!-- Skills -->
        <hr>
        <div>
          <h6 class="fw-bold mb-3">Skill yang kamu dapat</h6>
          <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill mb-2 d-inline-block">Excel</span>
          <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill d-inline-block">Algoritma</span>
        </div>
      </div>
    </div>
  </div>
</section>

@endsection