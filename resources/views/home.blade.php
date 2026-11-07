@extends('layouts.home-layout')

@section('intro')
    <x-rs.intro />
@endsection

@section('content')
<div class="rs-page">
    @php $shelf = collect([$mostPopularCourse, $secondMostPopularCourse, $thirdMostPopularCourse])->filter(); @endphp

    <!-- Hero Section -->
    <section id="hero" class="rs-hero">
        <div class="rs-wrap">
            <div class="rs-hero__grid">
                <div class="rs-hero__text">
                    <span class="rs-pill">Platform belajar online</span>
                    <h1 class="rs-h1">
                        Raih <em>Impian</em> <br>
                        Kembangkan Potensi <br>
                        Bersama Kami
                    </h1>
                    <p class="rs-lead">
                        Kami hadir untuk membantu kamu belajar dengan cara yang mudah dan menyenangkan.
                        Materi lengkap, pembimbing berpengalaman, dan dukungan penuh untuk membuat kamu semakin percaya diri.
                    </p>
                    <div class="rs-actions">
                        <a class="rs-btn" href="{{ route('student.dashboard') }}">Mulai</a>
                        <a class="rs-btn rs-btn--outline" href="{{ route('courses') }}">Lihat katalog</a>
                        @if (!empty($siteSettings->hero_video_url))
                            <a class="rs-link" href="{{ $siteSettings->hero_video_url }}" target="_blank" rel="noopener">Lihat video</a>
                        @endif
                    </div>
                    <ul class="rs-ticks">
                        <li>Sertifikat resmi</li>
                        <li>Akses seumur hidup</li>
                        <li>Studi kasus nyata</li>
                    </ul>
                </div>

                <div class="rs-hero__art" aria-hidden="true">
                    <span class="rs-blob rs-blob--blue"></span>
                    <span class="rs-blob rs-blob--lav"></span>
                    <span class="rs-blob rs-blob--sun"></span>
                    <x-rs.illustration name="rs-01-hero-belajar" size="lg" class="rs-hero__illu" loading="eager" fetchpriority="high" />
                    <div class="rs-float rs-float--1"><i class="bi bi-play-circle"></i><div><b>Materi video</b><span>dan bacaan lengkap</span></div></div>
                    <div class="rs-float rs-float--2"><i class="bi bi-award"></i><div><b>Sertifikat resmi</b><span>setelah kursus selesai</span></div></div>
                </div>
            </div>
        </div>
    </section><!-- /Hero Section -->

    <!-- About Section -->
    <section id="about" class="rs-section">
        <div data-rs-reveal class="rs-wrap">
            <div class="rs-split">
                <div>
                    <p class="rs-eyebrow">Tentang Kami</p>
                    <h2 class="rs-h2">Belajar Tanpa Batas untuk Masa Depan Lebih Baik</h2>
                    <p class="rs-muted">{{ $siteSettings->site_name }} hadir sebagai platform pembelajaran digital untuk membantu kamu menguasai keterampilan yang dibutuhkan di era digital. Dengan mentor berpengalaman dan materi berkualitas, kami berkomitmen mendukung setiap langkah belajarmu.</p>

                    <ul class="rs-checks">
                        <li><i class="bi bi-check2" aria-hidden="true"></i><span>Materi mudah dipahami</span></li>
                        <li><i class="bi bi-check2" aria-hidden="true"></i><span>Mentor profesional</span></li>
                        <li><i class="bi bi-check2" aria-hidden="true"></i><span>Sertifikat resmi</span></li>
                        <li><i class="bi bi-check2" aria-hidden="true"></i><span>Akses seumur hidup</span></li>
                        <li><i class="bi bi-check2" aria-hidden="true"></i><span>Studi kasus nyata</span></li>
                        <li><i class="bi bi-check2" aria-hidden="true"></i><span>Komunitas diskusi</span></li>
                    </ul>

                    <p class="rs-mt-4"><a class="rs-link" href="{{ route('contact') }}">Hubungi kami</a></p>
                </div>
                <div>
                    <x-rs.illustration name="rs-02-tentang-tim" size="lg" />
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

    <!-- Services Section -->
    <section id="services" class="rs-section rs-section--paper">
        <div data-rs-reveal class="rs-wrap">
            <div class="rs-section-head">
                <div>
                    <h2 class="rs-h2">Layanan Kami</h2>
                    <p class="rs-sub">Kami siap membantu kamu dengan berbagai layanan terbaik yang bikin proses belajar dan berkembang jadi lebih mudah dan menyenangkan.</p>
                </div>
            </div>

            <ul class="rs-toc">
                <li>
                    <h3 class="rs-h3">Pelatihan Interaktif</h3>
                    <p>Belajar jadi lebih seru dengan materi interaktif yang gampang dipahami dan bisa langsung dipraktikkan.</p>
                    <a class="rs-link" href="{{ route('services.detail') }}#pelatihan-interaktif">Selengkapnya</a>
                </li>
                <li>
                    <h3 class="rs-h3">Kelas Beragam</h3>
                    <p>Tersedia berbagai topik mulai dari coding, desain, sampai pengembangan diri. Pilih yang sesuai minatmu.</p>
                    <a class="rs-link" href="{{ route('services.detail') }}#kelas-beragam">Selengkapnya</a>
                </li>
                <li>
                    <h3 class="rs-h3">Materi Up-to-Date</h3>
                    <p>Kami selalu update materi supaya kamu belajar hal-hal terbaru dan relevan dengan industri.</p>
                    <a class="rs-link" href="{{ route('services.detail') }}#materi-up-to-date">Selengkapnya</a>
                </li>
                <li>
                    <h3 class="rs-h3">Evaluasi &amp; Sertifikat</h3>
                    <p>Dapatkan evaluasi pembelajaran dan sertifikat sebagai bukti kemampuanmu setelah menyelesaikan kelas.</p>
                    <a class="rs-link" href="{{ route('services.detail') }}#evaluasi-sertifikat">Selengkapnya</a>
                </li>
            </ul>
        </div>
    </section><!-- /Services Section -->

    <!-- About CTA Section -->
    <section class="rs-section">
        <div data-rs-reveal class="rs-wrap">
            <div class="rs-cta">
                <h2>Kenali Lebih Dekat {{ $siteSettings->site_name }}</h2>
                <p>Pelajari bagaimana kami membantu pelajar dan profesional meningkatkan keterampilan melalui kursus berkualitas dan mentor berpengalaman.</p>
                <a class="rs-btn rs-btn--light" href="{{ route('about') }}">Tentang Kami</a>
            </div>
        </div>
    </section><!-- /About CTA Section -->

    <!-- Pricing Section -->
    <section id="pricing" class="rs-section">
        <div data-rs-reveal class="rs-wrap">
            <div class="rs-section-head">
                <div>
                    <h2 class="rs-h2">Kursus yang Sering Dibeli</h2>
                    <p class="rs-sub">Kursus-kursus ini populer dan direkomendasikan oleh banyak peserta.</p>
                </div>
                <a class="rs-link" href="{{ route('courses') }}">Lihat semua kursus</a>
            </div>

            @if ($shelf->isNotEmpty())
                <div class="rs-books">
                    {{-- Urutan: paling populer, kedua, ketiga --}}
                    @foreach ($shelf as $c)
                        <x-rs.course-book :course="$c" />
                    @endforeach
                </div>
            @else
                <div class="rs-empty">
                    <h3 class="rs-h3">Belum ada kursus</h3>
                    <p>Kursus yang sudah terbit akan tampil di sini.</p>
                </div>
            @endif
        </div>
    </section><!-- /Pricing Section -->

    <!-- Call To Action Section -->
    <section class="rs-band">
        <div data-rs-reveal class="rs-wrap rs-band__inner">
            <div>
                <h2>Cari Kursus Lainnya?</h2>
                <p>Jelajahi berbagai pilihan kursus lainnya yang sesuai dengan minat dan kebutuhan belajarmu. Tingkatkan keterampilan dan kariermu sekarang.</p>
            </div>
            <a class="rs-btn rs-btn--light" href="{{ route('courses') }}">Lihat Semua Kursus</a>
        </div>
    </section><!-- /Call To Action Section -->

    <!-- Testimonials Section -->
    <section id="testimonials" class="rs-section rs-section--paper">
        <div data-rs-reveal class="rs-wrap">
            <div class="rs-section-head">
                <div>
                    <h2 class="rs-h2">Testimoni Peserta</h2>
                    <p class="rs-sub">Berbagai pengalaman nyata dari peserta yang telah menggunakan platform kami.</p>
                </div>
                <a class="rs-link" href="{{ route('testimonials') }}">Lihat semua testimoni</a>
            </div>

            @if (isset($platformReviews) && $platformReviews->isNotEmpty())
                <div class="rs-quotes">
                    @foreach ($platformReviews as $review)
                        <x-rs.testimonial :review="$review" />
                    @endforeach
                </div>
            @else
                <div class="rs-empty">
                    <h3 class="rs-h3">Belum ada testimoni</h3>
                    <p>Jadilah yang pertama memberikan testimoni untuk platform ini!</p>
                </div>
            @endif
        </div>
    </section><!-- /Testimonials Section -->
</div>
@endsection
