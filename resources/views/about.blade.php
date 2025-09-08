@extends('layouts.home-layout')

@section('title', 'catalog')

@section('content')
<!-- Features Section -->
    <section id="features" class="features section light-background" style="padding-top: 120px;">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Fitur Unggulan</h2>
        <p>Temukan keunggulan platform kursus Idn yang dirancang khusus untuk mendukung pembelajaran digital di Indonesia.</p>
      </div><!-- End Section Title -->

      <div class="container">

        <div class="d-flex justify-content-center">

          <ul class="nav nav-tabs" data-aos="fade-up" data-aos-delay="100">

            <li class="nav-item">
              <a class="nav-link active show" data-bs-toggle="tab" data-bs-target="#features-tab-1">
                <h4>Belajar Fleksibel</h4>
              </a>
            </li><!-- End tab nav item -->

            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" data-bs-target="#features-tab-2">
                <h4>Kursus Berkualitas</h4>
              </a><!-- End tab nav item -->

            </li>
            <li class="nav-item">
              <a class="nav-link" data-bs-toggle="tab" data-bs-target="#features-tab-3">
                <h4>Dukungan Karier</h4>
              </a>
            </li><!-- End tab nav item -->

          </ul>

        </div>

        <div class="tab-content" data-aos="fade-up" data-aos-delay="200">
          <!-- Tab 1 -->
          <div class="tab-pane fade active show" id="features-tab-1">
            <div class="row">
              <div class="col-lg-6 order-2 order-lg-1 mt-3 mt-lg-0 d-flex flex-column justify-content-center">
                <h3>Akses Materi Kapan Saja</h3>
                <p class="fst-italic">
                  Belajar tidak lagi terikat waktu dan tempat. Kamu bisa mengakses seluruh materi melalui perangkat apa pun.
                </p>
                <ul>
                  <li><i class="bi bi-check2-all"></i> <span>Video pembelajaran yang dapat diulang kapan saja.</span></li>
                  <li><i class="bi bi-check2-all"></i> <span>Tersedia aplikasi mobile dan desktop.</span></li>
                  <li><i class="bi bi-check2-all"></i> <span>Cocok untuk pelajar, mahasiswa, hingga profesional.</span></li>
                </ul>
              </div>
              <div class="col-lg-6 order-1 order-lg-2 text-center">
                <img src="home-page/assets/img/features-illustration-1.webp" alt="" class="img-fluid">
              </div>
            </div>
          </div><!-- End tab content item -->

          <div class="tab-pane fade" id="features-tab-2">
            <div class="row">
              <div class="col-lg-6 order-2 order-lg-1 mt-3 mt-lg-0 d-flex flex-column justify-content-center">
                <h3>Instruktur Berpengalaman</h3>
                <p class="fst-italic">
                  Semua kursus dikurasi dan diajarkan oleh praktisi industri dan akademisi terpercaya.
                </p>
                <ul>
                  <li><i class="bi bi-check2-all"></i> <span>Sertifikat resmi setelah menyelesaikan kursus.</span></li>
                  <li><i class="bi bi-check2-all"></i> <span>Konten interaktif dengan studi kasus nyata.</span></li>
                  <li><i class="bi bi-check2-all"></i> <span>Update materi berkala sesuai tren industri.</span></li>
                  <li><i class="bi bi-check2-all"></i> <span>Forum diskusi dan komunitas pembelajar.</span></li>
                </ul>
              </div>
              <div class="col-lg-6 order-1 order-lg-2 text-center">
                <img src="home-page/assets/img/features-illustration-2.webp" alt="" class="img-fluid">
              </div>
            </div>
          </div><!-- End tab content item -->

          <div class="tab-pane fade" id="features-tab-3">
            <div class="row">
              <div class="col-lg-6 order-2 order-lg-1 mt-3 mt-lg-0 d-flex flex-column justify-content-center">
                <h3>Siap Menghadapi Dunia Kerja</h3>
                <ul>
                  <li><i class="bi bi-check2-all"></i> <span>Konsultasi karier dan pembuatan CV profesional.</span></li>
                  <li><i class="bi bi-check2-all"></i> <span>Akses ke lowongan kerja eksklusif mitra kursus Idn.</span></li>
                  <li><i class="bi bi-check2-all"></i> <span> Pelatihan interview dan pengembangan soft skill.</span></li>
                </ul>
                <p class="fst-italic">
                  kursus Idn mendukung kariermu sejak proses belajar hingga siap terjun ke industri.
                </p>
              </div>
              <div class="col-lg-6 order-1 order-lg-2 text-center">
                <img src="home-page/assets/img/features-illustration-3.webp" alt="" class="img-fluid">
              </div>
            </div>
          </div><!-- End tab content item -->

        </div>

      </div>

    </section><!-- /Features Section -->

    <!-- Features Cards Section -->
    <section id="features-cards" class="features-cards section">

      <div class="container">

        <div class="row gy-4">

          <div class="col-xl-3 col-md-6" data-aos="zoom-in" data-aos-delay="100">
            <div class="feature-box orange">
              <i class="bi bi-award"></i>
              <h4>Sertifikat Resmi</h4>
              <p>Dapatkan sertifikat setelah menyelesaikan kursus untuk menunjang CV dan portofolio profesionalmu.</p>
            </div>
          </div><!-- End Feature Borx-->

          <div class="col-xl-3 col-md-6" data-aos="zoom-in" data-aos-delay="200">
            <div class="feature-box blue">
              <i class="bi bi-patch-check"></i>
              <h4>Instruktur Terpercaya</h4>
              <p>Belajar langsung dari praktisi industri dan akademisi berpengalaman di bidangnya.</p>
            </div>
          </div><!-- End Feature Borx-->

          <div class="col-xl-3 col-md-6" data-aos="zoom-in" data-aos-delay="300">
            <div class="feature-box green">
              <i class="bi bi-sunrise"></i>
              <h4>Akses Seumur Hidup</h4>
              <p>Setelah membeli kursus, kamu bisa mengakses materi kapan saja dan selamanya tanpa batas.</p>
            </div>
          </div><!-- End Feature Borx-->

          <div class="col-xl-3 col-md-6" data-aos="zoom-in" data-aos-delay="400">
            <div class="feature-box red">
              <i class="bi bi-shield-check"></i>
              <h4>Komunitas Belajar</h4>
              <p>Gabung bersama ribuan pelajar lainnya untuk saling diskusi dan belajar bersama dalam forum komunitas.</p>
            </div>
          </div><!-- End Feature Borx-->

        </div>

      </div>

    </section><!-- /Features Cards Section -->

    <!-- Features 2 Section -->
    <!-- /Features 2 Section -->




<section id="call-to-action-2" class="call-to-action-2 section dark-background text-white py-5">
    <div class="container">
        <div class="row justify-content-center" data-aos="zoom-in" data-aos-delay="100">
            <div class="col-xl-10">
                <div class="text-center">
                    <h3>Periksa Semua Testimoni</h3>
                    <p>Kami menerima banyak feedback positif dari pelanggan kami. Yuk, lihat pengalaman mereka menggunakan layanan kami!</p>
                    <a class="cta-btn btn btn-outline-light mt-3 px-4 py-2" href="{{ route('testimonials') }}">Lihat Semua Testimoni</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials Section -->
<section id="testimonials" class="testimonials section light-background">

    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
        <h2>Testimoni Peserta</h2>
        <p>Berbagai pengalaman nyata dari peserta yang telah menggunakan platform kami.</p>
    </div><!-- End Section Title -->

    <div class="container">
        <div class="row g-5">
            @if(isset($platformReviews) && $platformReviews->isNotEmpty())
                @foreach($platformReviews as $review)
                <div class="col-lg-6" data-aos="fade-up" data-aos-delay="{{ ($loop->index + 1) * 100 }}">
                    <div class="testimonial-item">
                        <a href="{{ route('profile.show', $review->user->id) }}">
                          <img src="{{ asset($review->user->profile_picture_url ?? 'assets/profile-images/avatar-1.png') }}" class="testimonial-img" alt="">
                          <h3>{{ $review->user->name }}</h3>
                        </a>

                        <h4>{{ $review->user->studentProfile->headline ?? 'Siswa' }}</h4>
                        <div class="stars">
                            @for ($i = 1; $i <= 5; $i++)
                                <i class="bi {{ $i <= $review->rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                            @endfor
                        </div>
                        <p>
                            <i class="bi bi-quote quote-icon-left"></i>
                            <span>{{ $review->comment }}</span>
                            <i class="bi bi-quote quote-icon-right"></i>
                        </p>
                    </div>
                </div><!-- End testimonial item -->
                @endforeach
            @else
                <div class="col-12 text-center">
                    <p class="text-muted">Jadilah yang pertama memberikan testimoni untuk platform ini!</p>
                </div>
            @endif
        </div>
    </div>
</section><!-- /Testimonials Section -->

@guest
      <!-- Call To Action 2 Section -->
    <section id="call-to-action-2" class="call-to-action-2 section dark-background">

      <div class="container">
        <div class="row justify-content-center" data-aos="zoom-in" data-aos-delay="100">
          <div class="col-xl-10">
            <div class="text-center">
              <h3>Sudah Siap Jadi Bagian dari Mereka?</h3>
              <p>Bergabunglah bersama ribuan peserta lainnya yang telah merasakan manfaat kursus kami. Mulailah langkah barumu hari ini!</p>
              <a class="cta-btn" href="{{ route('register') }}">Registrasi</a>
            </div>
          </div>
        </div>
      </div>

    </section><!-- /Call To Action 2 Section -->
@endguest

@endsection