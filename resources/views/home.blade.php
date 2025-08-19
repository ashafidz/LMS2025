@extends('layouts.home-layout')

@section('title', 'Wahana Edukasi')

@section('content')
        <!-- Hero Section -->
    <section id="hero" class="hero section bg-white">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row align-items-center">
          <div class="col-lg-6">
            <div class="hero-content" data-aos="fade-up" data-aos-delay="200">
              <div class="company-badge mb-4">
                <i class="bi bi-gear-fill me-2"></i>
                Bekerja untuk kesuksesan kamu
              </div>

              <h1 class="mb-4">
                Raih Impian <br>
                Kembangkan Potensi <br>
                <span class="accent-text">Bersama Kami</span>
              </h1>

              <p class="mb-4 mb-md-5">
                Kami hadir untuk membantu kamu belajar dengan cara yang mudah dan menyenangkan.
                Materi lengkap, pembimbing berpengalaman, dan dukungan penuh untuk membuat kamu semakin percaya diri.
              </p>

              <div class="hero-buttons">
                <a href="#about" class="btn btn-primary me-0 me-sm-2 mx-1">Mulai</a>
                <a href="https://www.youtube.com/watch?v=Y7f98aduVJ8" class="btn btn-link mt-2 mt-sm-0 glightbox">
                  <i class="bi bi-play-circle me-1"></i>
                  Lihat Video
                </a>
              </div>
            </div>
          </div>

          <div class="col-lg-6">
            <div class="hero-image" data-aos="zoom-out" data-aos-delay="300">
              <img src="home-page/assets/img/illustration-1.webp" alt="Hero Image" class="img-fluid">
            </div>
          </div>
        </div>
      </div>

    </section><!-- /Hero Section -->

    <!-- About Section -->
    <section id="about" class="about section">

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row gy-4 align-items-center justify-content-between">

          <div class="col-xl-5" data-aos="fade-up" data-aos-delay="200">
            <span class="about-meta">TENTANG KAMI</span>
            <h2 class="about-title">Belajar Tanpa Batas untuk Masa Depan Lebih Baik</h2>
            <p class="about-description">Kursus Ind hadir sebagai platform pembelajaran digital untuk membantu kamu menguasai keterampilan yang dibutuhkan di era digital. Dengan mentor berpengalaman dan materi berkualitas, kami berkomitmen mendukung setiap langkah belajarmu.</p>

            <div class="row feature-list-wrapper">
              <div class="col-md-6">
                <ul class="feature-list">
                  <li><i class="bi bi-check-circle-fill"></i> Materi mudah dipahami</li>
                  <li><i class="bi bi-check-circle-fill"></i> Mentor profesional</li>
                  <li><i class="bi bi-check-circle-fill"></i> Sertifikat resmi</li>
                </ul>
              </div>
              <div class="col-md-6">
                <ul class="feature-list">
                  <li><i class="bi bi-check-circle-fill"></i> Akses seumur hidup</li>
                  <li><i class="bi bi-check-circle-fill"></i> Studi kasus nyata</li>
                  <li><i class="bi bi-check-circle-fill"></i> Komunitas diskusi</li>
                </ul>
              </div>
            </div>

            <div class="info-wrapper">
              <div class="row gy-4">
                <div class="col-lg-5">
                  <div class="profile d-flex align-items-center gap-3">
                    <img src="home-page/assets/img/avatar-1.webp" alt="CEO Profile" class="profile-image">
                    <div>
                      <h4 class="profile-name">Mario Smith</h4>
                      <p class="profile-position">CEO &amp; Founder</p>
                    </div>
                  </div>
                </div>
                <div class="col-lg-7">
                  <div class="contact-info d-flex align-items-center gap-2">
                    <i class="bi bi-telephone-fill"></i>
                    <div>
                      <p class="contact-label">Hubungi kami kapan saja</p>
                      <p class="contact-number">info@example.com</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-6" data-aos="fade-up" data-aos-delay="300">
            <div class="image-wrapper">
              <div class="images position-relative" data-aos="zoom-out" data-aos-delay="400">
                <img src="home-page/assets/img/about-5.webp" alt="Business Meeting" class="img-fluid main-image rounded-4">
                <img src="home-page/assets/img/about-2.webp" alt="Team Discussion" class="img-fluid small-image rounded-4">
              </div>
              <div class="experience-badge floating">
                <h3>3+ <span>Tahun</span></h3>
                <p>Pengalaman membantu ribuan peserta belajar</p>
              </div>
            </div>
          </div>
        </div>

      </div>

    </section><!-- /About Section -->

    <!-- Features Section -->
    <!-- /Features Section -->

    <!-- Features Cards Section -->
    <!-- /Features Cards Section -->

    <!-- Features 2 Section -->
    <!-- /Features 2 Section -->

    <!-- Call To Action Section -->
    <section id="call-to-action" class="call-to-action section">

    <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row content justify-content-center align-items-center position-relative">
        <div class="col-lg-8 mx-auto text-center">
            <h2 class="display-4 mb-4">Kenali Lebih Dekat {{ $siteSettings->site_name }}</h2>
            <p class="mb-4">Pelajari bagaimana kami membantu ribuan pelajar dan profesional meningkatkan keterampilan melalui kursus berkualitas dan mentor berpengalaman.</p>
            <a href="/about" class="btn btn-cta">Tentang Kami</a>
        </div>

        <!-- Background Shapes -->
        <div class="shape shape-1">
            <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <path d="M47.1,-57.1C59.9,-45.6,68.5,-28.9,71.4,-10.9C74.2,7.1,71.3,26.3,61.5,41.1C51.7,55.9,35,66.2,16.9,69.2C-1.3,72.2,-21,67.8,-36.9,57.9C-52.8,48,-64.9,32.6,-69.1,15.1C-73.3,-2.4,-69.5,-22,-59.4,-37.1C-49.3,-52.2,-32.8,-62.9,-15.7,-64.9C1.5,-67,34.3,-68.5,47.1,-57.1Z" transform="translate(100 100)"></path>
            </svg>
        </div>

        <div class="shape shape-2">
            <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <path d="M41.3,-49.1C54.4,-39.3,66.6,-27.2,71.1,-12.1C75.6,3,72.4,20.9,63.3,34.4C54.2,47.9,39.2,56.9,23.2,62.3C7.1,67.7,-10,69.4,-24.8,64.1C-39.7,58.8,-52.3,46.5,-60.1,31.5C-67.9,16.4,-70.9,-1.4,-66.3,-16.6C-61.8,-31.8,-49.7,-44.3,-36.3,-54C-22.9,-63.7,-8.2,-70.6,3.6,-75.1C15.4,-79.6,28.2,-58.9,41.3,-49.1Z" transform="translate(100 100)"></path>
            </svg>
        </div>

        <!-- Dot Patterns -->
        <div class="dots dots-1">
            <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <pattern id="dot-pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                <circle cx="2" cy="2" r="2" fill="currentColor"></circle>
            </pattern>
            <rect width="100" height="100" fill="url(#dot-pattern)"></rect>
            </svg>
        </div>

        <div class="dots dots-2">
            <svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
            <pattern id="dot-pattern-2" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                <circle cx="2" cy="2" r="2" fill="currentColor"></circle>
            </pattern>
            <rect width="100" height="100" fill="url(#dot-pattern-2)"></rect>
            </svg>
        </div>

        <div class="shape shape-3">
            <svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
            <path d="M43.3,-57.1C57.4,-46.5,71.1,-32.6,75.3,-16.2C79.5,0.2,74.2,19.1,65.1,35.3C56,51.5,43.1,65,27.4,71.7C11.7,78.4,-6.8,78.3,-23.9,72.4C-41,66.5,-56.7,54.8,-65.4,39.2C-74.1,23.6,-75.8,4,-71.7,-13.2C-67.6,-30.4,-57.7,-45.2,-44.3,-56.1C-30.9,-67,-15.5,-74,0.7,-74.9C16.8,-75.8,33.7,-70.7,43.3,-57.1Z" transform="translate(100 100)"></path>
            </svg>
        </div>
        </div>

    </div>

    </section><!-- /Call To Action Section -->


    <!-- Services Section -->
    <section id="services" class="services section light-background">

        <!-- Section Title -->
        <div class="container section-title" data-aos="fade-up">
          <h2>Layanan Kami</h2>
          <p>Kami siap membantu kamu dengan berbagai layanan terbaik yang bikin proses belajar dan berkembang jadi lebih mudah dan menyenangkan.</p>
        </div><!-- End Section Title -->
  
        <div class="container" data-aos="fade-up" data-aos-delay="100">
  
          <div class="row g-4">
  
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
              <div class="service-card d-flex">
                <div class="icon flex-shrink-0">
                  <i class="bi bi-activity"></i>
                </div>
                <div>
                  <h3>Pelatihan Interaktif</h3>
                  <p>Belajar jadi lebih seru dengan materi interaktif yang gampang dipahami dan bisa langsung dipraktikkan.</p>
                  <a href="service-details.html" class="read-more">Selengkapnya<i class="bi bi-arrow-right"></i></a>
                </div>
              </div>
            </div><!-- End Service Card -->
  
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="200">
              <div class="service-card d-flex">
                <div class="icon flex-shrink-0">
                  <i class="bi bi-diagram-3"></i>
                </div>
                <div>
                  <h3>Kelas Beragam</h3>
                  <p>Tersedia berbagai topik mulai dari coding, desain, sampai pengembangan diri. Pilih yang sesuai minatmu.</p>
                  <a href="service-details.html" class="read-more">Selengkapnya<i class="bi bi-arrow-right"></i></a>
                </div>
              </div>
            </div><!-- End Service Card -->
  
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="300">
              <div class="service-card d-flex">
                <div class="icon flex-shrink-0">
                  <i class="bi bi-easel"></i>
                </div>
                <div>
                  <h3>Materi Up-to-Date</h3>
                  <p>Kami selalu update materi supaya kamu belajar hal-hal terbaru dan relevan dengan industri.</p>
                  <a href="service-details.html" class="read-more">Selengkapnya<i class="bi bi-arrow-right"></i></a>
                </div>
              </div>
            </div><!-- End Service Card -->
  
            <div class="col-lg-6" data-aos="fade-up" data-aos-delay="400">
              <div class="service-card d-flex">
                <div class="icon flex-shrink-0">
                  <i class="bi bi-clipboard-data"></i>
                </div>
                <div>
                  <h3>Evaluasi & Sertifikat</h3>
                  <p>Dapatkan evaluasi pembelajaran dan sertifikat sebagai bukti kemampuanmu setelah menyelesaikan kelas.</p>
                  <a href="service-details.html" class="read-more">Read More <i class="bi bi-arrow-right"></i></a>
                </div>
              </div>
            </div><!-- End Service Card -->
  
          </div>
  
        </div>
  
      </section><!-- /Services Section -->  

<!-- Pricing Section -->
<section id="pricing" class="pricing section bg-white">
    
    <!-- Section Title -->
    <div class="container section-title" data-aos="fade-up">
        <h2>Kursus yang Sering Dibeli</h2>
        <p>Kursus-kursus ini populer dan direkomendasikan oleh banyak peserta.</p>
    </div><!-- End Section Title -->

    <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row g-4 justify-content-center">

            {{-- Kartu untuk Kursus Terpopuler ke-2 (di sisi kiri) --}}
            @if($secondMostPopularCourse)
                @php $course = $secondMostPopularCourse; @endphp
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="pricing-card">
                        <h3>{{ $course->title }}</h3>
                        <div class="price">
                            @if($course->payment_type === 'money')
                                @if($course->price > 0)
                                    <span class="currency">Rp.</span>
                                    <span class="amount">{{ number_format($course->price, 0, ',', '.') }}</span>
                                @else
                                    <span class="amount">Gratis</span>
                                @endif
                            @elseif($course->payment_type === 'diamonds')
                                <span class="amount" style="font-size: 1.5rem;"><i class="fa fa-diamond mr-2"></i> {{ number_format($course->diamond_price, 0, ',', '.') }}</span>
                            @endif
                        </div>
                        <p class="description">{{ Str::limit($course->description, 100) }}</p>
                        <h4>Dilengkapi:</h4>
                        <ul class="features-list">
                            <li><i class="bi bi-person-workspace"></i> {{ $course->lessons_count }} Materi</li>
                            <li><i class="bi bi-people"></i> {{ $course->students_count }}+ Siswa</li>
                            <li><i class="bi bi-award"></i> Sertifikat</li>
                        </ul>
                        <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-primary">
                            Detail Kursus <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            @endif

            {{-- Kartu untuk Kursus PALING Populer (di tengah) --}}
            @if($mostPopularCourse)
                @php $course = $mostPopularCourse; @endphp
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="pricing-card popular">
                        <div class="popular-badge">Paling Populer</div>
                        <h3>{{ $course->title }}</h3>
                        <div class="price">
                            @if($course->payment_type === 'money')
                                @if($course->price > 0)
                                    <span class="currency">Rp.</span>
                                    <span class="amount">{{ number_format($course->price, 0, ',', '.') }}</span>
                                @else
                                    <span class="amount">Gratis</span>
                                @endif
                            @elseif($course->payment_type === 'diamonds')
                                <span class="amount" style="font-size: 1.5rem;"><i class="fa fa-diamond mr-2"></i> {{ number_format($course->diamond_price, 0, ',', '.') }}</span>
                            @endif
                        </div>
                        <p class="description">{{ Str::limit($course->description, 100) }}</p>
                        <h4>Dilengkapi:</h4>
                        <ul class="features-list">
                            <li><i class="bi bi-person-workspace"></i> {{ $course->lessons_count }} Materi</li>
                            <li><i class="bi bi-people"></i> {{ $course->students_count }}+ Siswa</li>
                            <li><i class="bi bi-award"></i> Sertifikat</li>
                        </ul>
                        <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-light">
                            Detail Kursus <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            @endif

            {{-- Kartu untuk Kursus Terpopuler ke-3 (di sisi kanan) --}}
            @if($thirdMostPopularCourse)
                @php $course = $thirdMostPopularCourse; @endphp
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="pricing-card">
                        <h3>{{ $course->title }}</h3>
                        <div class="price">
                            @if($course->payment_type === 'money')
                                @if($course->price > 0)
                                    <span class="currency">Rp.</span>
                                    <span class="amount">{{ number_format($course->price, 0, ',', '.') }}</span>
                                @else
                                    <span class="amount">Gratis</span>
                                @endif
                            @elseif($course->payment_type === 'diamonds')
                                <span class="amount" style="font-size: 1.5rem;"><i class="fa fa-diamond mr-2"></i> {{ number_format($course->diamond_price, 0, ',', '.') }}</span>
                            @endif
                        </div>
                        <p class="description">{{ Str::limit($course->description, 100) }}</p>
                        <h4>Dilengkapi:</h4>
                        <ul class="features-list">
                            <li><i class="bi bi-person-workspace"></i> {{ $course->lessons_count }} Materi</li>
                            <li><i class="bi bi-people"></i> {{ $course->students_count }}+ Siswa</li>
                            <li><i class="bi bi-award"></i> Sertifikat</li>
                        </ul>
                        <a href="{{ route('courses.show', $course->slug) }}" class="btn btn-primary">
                            Detail Kursus <i class="bi bi-arrow-right"></i>
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </div>
</section><!-- /Pricing Section -->

      <!-- Call To Action Section - Lihat Kursus Lainnya -->
    <section id="call-to-action-2" class="call-to-action-2 section dark-background text-white py-5">

    <div class="container">
        <div class="row justify-content-center" data-aos="zoom-in" data-aos-delay="100">
        <div class="col-xl-10">
            <div class="text-center">
            <h3>Cari Kursus Lainnya?</h3>
            <p>Jelajahi berbagai pilihan kursus menarik lainnya yang sesuai dengan minat dan kebutuhan belajarmu. Tingkatkan skill dan kariermu sekarang!</p>
            <a class="cta-btn btn btn-outline-light mt-3 px-4 py-2" href="{{ route('courses') }}">Lihat Semua Kursus</a>
            </div>
        </div>
        </div>
    </div>

    </section><!-- /Call To Action Section -->


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
                        <img src="{{ asset($review->user->profile_picture_url ?? 'assets/profile-images/avatar-1.png') }}" class="testimonial-img" alt="">
                        <h3>{{ $review->user->name }}</h3>
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

<!-- Call-to-Action Section -->
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
@endsection
