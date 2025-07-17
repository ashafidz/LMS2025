@extends('layouts.home-layout')

@section('title', 'catalog')

@section('content')

{{-- Hero: Sekarang bg-nya putih --}}
<section class="hero-section bg-white text-center" style="padding-top: 60px;">
  <div class="container mt-5" data-aos="fade-up">
    <h2 class="display-6 fw-bold mb-3">Temukan Kursus Impianmu</h2>
    <div class="mx-auto mb-3" style="width: 50px; height: 4px; background-color: #0d6efd;"></div>
    <p class="text-muted fs-5">Jelajahi berbagai pilihan kursus untuk meningkatkan keterampilan dan karier Anda.</p>
  </div>
</section>

{{-- Section utama: Sekarang pakai bg #edf8fd --}}
<section class="py-5" style="background-color: #edf8fd;">
  <div class="container">
    <div class="row justify-content-between align-items-center mb-4" data-aos="fade-up">
      <div class="col-md-6">
        <h2 class="fw-bold">Kelas Terbaru</h2>
        <p class="text-muted mb-0">Kami menyediakan kelas dengan materi yang dibuat sesuai kebutuhan di dunia Profesional.</p>
      </div>
      <div class="col-lg-3 col-md-4">
        <input type="text" class="form-control" placeholder="Cari judul kursus...">
      </div>
    </div>

    <div class="row g-4">
      <div class="col-md-3" data-aos="fade-up" data-aos-delay="100">
        <div class="card border-0 shadow-sm rounded-4">
          <div class="card-body">
            <h5 class="fw-bold mb-3">Filter Kategori</h5>
            @foreach (['Programming', 'Design', 'Marketing', 'Data Science', 'Office Productivity'] as $index => $kategori)
            <div class="form-check mb-2">
              <input class="form-check-input" type="checkbox" id="kategori{{ $index + 1 }}">
              <label class="form-check-label" for="kategori{{ $index + 1 }}">{{ $kategori }}</label>
            </div>
            @endforeach
            <button class="btn btn-primary w-100 mt-3">Terapkan</button>
          </div>
        </div>
      </div>

      <div class="col-md-9">
            <div class="row g-4">
              <div class="col-md-3" data-aos="fade-up" data-aos-delay="200">
                <a href="/details-course" class="text-decoration-none text-dark">
                  <div class="card h-100 shadow-sm border-0 rounded-4">
                  <img src="{{ asset('images/course.jpg') }}" 
                    class="card-img-top rounded-top-4" 
                    style="height: 120px; object-fit: cover;" 
                    alt="Kursus1">
                    <div class="card-body">
                      <span class="badge bg-warning text-dark mb-2">Online Course</span>
                      <h5 class="card-title fw-bold text-primary">Rahasia Produktivitas Tinggi</h5>
                      <p class="card-text text-muted">Bregas Parikesit</p>
                    </div>
                    <div class="card-footer bg-white border-0">
                      <span class="fw-bold text-dark">Rp 100.000</span>
                    </div>
                  </div>
                </a>
              </div>

              <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                <a href="" class="text-decoration-none text-dark">
                  <div class="card h-100 shadow-sm border-0 rounded-4">
                  <img src="{{ asset('images/indomaret.jpg') }}" 
                    class="card-img-top rounded-top-4" 
                    style="height: 120px; object-fit: cover;" 
                    alt="Kursus2">
                    <div class="card-body">
                      <span class="badge bg-warning text-dark mb-2">Online Course</span>
                      <h5 class="card-title fw-bold text-primary">Mahir Microsoft Excel</h5>
                      <p class="card-text text-muted">M. Farhan Syihab</p>
                    </div>
                    <div class="card-footer bg-white border-0">
                      <span class="fw-bold text-dark">Rp 19.000</span>
                    </div>
                  </div>
                </a>
              </div>

              <div class="col-md-3" data-aos="fade-up" data-aos-delay="300">
                <a href="" class="text-decoration-none text-dark">
                  <div class="card h-100 shadow-sm border-0 rounded-4">
                    <img src="{{ asset('images/indomaret.jpg') }}" class="card-img-top rounded-top-4" style="height: 120px; object-fit: cover;" alt="Kursus 2">
                    <div class="card-body">
                      <span class="badge bg-warning text-dark mb-2">Online Course</span>
                      <h5 class="card-title fw-bold text-primary">Mahir Microsoft Excel</h5>
                      <p class="card-text text-muted">M. Farhan Syihab</p>
                    </div>
                    <div class="card-footer bg-white border-0">
                      <span class="fw-bold text-dark">Rp 19.000</span>
                    </div>
                  </div>
                </a>
              </div>

              <div class="col-md-3" data-aos="fade-up" data-aos-delay="400">
                <a href="" class="text-decoration-none text-dark">
                  <div class="card h-100 shadow-sm border-0 rounded-4">
                    <img src="{{ asset('images/alfa.jpg') }}" class="card-img-top rounded-top-4" style="height: 120px; object-fit: cover;" alt="Kursus 3">
                    <div class="card-body">
                      <span class="badge bg-warning text-dark mb-2">Online Course</span>
                      <h5 class="card-title fw-bold text-primary">Belajar Kotlin untuk Pemula</h5>
                      <p class="card-text text-muted">Fahrel Ardzaky Erlyanputra</p>
                    </div>
                    <div class="card-footer bg-white border-0">
                      <span class="fw-bold text-dark">Rp 39.000</span>
                    </div>
                  </div>
                </a>
              </div>

          {{-- Kursus tambahan (awalnya disembunyikan) --}}
          <div class="col-md-3 d-none" id="extra-course-1">
            <a href="" class="text-decoration-none text-dark">
              <div class="card h-100 shadow-sm border-0 rounded-4">
                <img src="{{ asset('images/course.jpg') }}" class="card-img-top rounded-top-4" style="height: 120px; object-fit: cover;" alt="Kursus Lanjutan 1">
                <div class="card-body">
                  <span class="badge bg-warning text-dark mb-2">Online Course</span>
                  <h5 class="card-title fw-bold text-primary">Manajemen Waktu Efektif</h5>
                  <p class="card-text text-muted">Natasya Dwi</p>
                </div>
                <div class="card-footer bg-white border-0">
                  <span class="fw-bold text-dark">Rp 49.000</span>
                </div>
              </div>
            </a>
          </div>

          <div class="col-md-3 d-none" id="extra-course-2">
            <a href="" class="text-decoration-none text-dark">
              <div class="card h-100 shadow-sm border-0 rounded-4">
                <img src="{{ asset('images/alfa.jpg') }}" class="card-img-top rounded-top-4" style="height: 120px; object-fit: cover;" alt="Kursus Lanjutan 2">
                <div class="card-body">
                  <span class="badge bg-warning text-dark mb-2">Online Course</span>
                  <h5 class="card-title fw-bold text-primary">Dasar Desain UI/UX</h5>
                  <p class="card-text text-muted">Dewi Ramadhani</p>
                </div>
                <div class="card-footer bg-white border-0">
                  <span class="fw-bold text-dark">Rp 55.000</span>
                </div>
              </div>
            </a>
          </div>
        </div>

        <div class="text-center mt-5" data-aos="fade-up">
          <button id="load-more" class="btn btn-primary px-5 py-3 rounded-3">Lihat Lebih Banyak</button>
        </div>
      </div>
    </div>
  </div>
</section>

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById('load-more');
    const extraCourses = [document.getElementById('extra-course-1'), document.getElementById('extra-course-2')];

    btn.addEventListener('click', function () {
      extraCourses.forEach(course => course.classList.remove('d-none'));
      btn.style.display = 'none';
    });
  });
</script>
@endpush

@endsection
