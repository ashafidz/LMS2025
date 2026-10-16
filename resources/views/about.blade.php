@extends('layouts.home-layout')

@section('title', 'Tentang Kami')

@section('content')
<div class="rs-page">
    <section class="rs-pagehead">
        <div class="rs-wrap">
            <p class="rs-crumbs"><a href="{{ route('home') }}">Beranda</a> / Tentang Kami</p>
            <h1 class="rs-h1">Tentang Kami</h1>
            <p class="rs-lead">Temukan keunggulan platform {{ $siteSettings->site_name }} yang dirancang khusus untuk mendukung pembelajaran digital di Indonesia.</p>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="rs-section">
        <div class="rs-wrap">
            <h2 class="rs-h2">Fitur Unggulan</h2>

            <div class="rs-split">
                <div>
                    <p class="rs-eyebrow">Belajar Fleksibel</p>
                    <h3 class="rs-h3">Akses Materi Kapan Saja</h3>
                    <p>
                        Belajar tidak lagi terikat waktu dan tempat. Kamu bisa mengakses seluruh materi melalui perangkat apa pun.
                    </p>
                    <ul class="rs-checks rs-checks--1">
                        <li><i class="bi bi-check2" aria-hidden="true"></i> <span>Video pembelajaran yang dapat diulang kapan saja.</span></li>
                        <li><i class="bi bi-check2" aria-hidden="true"></i> <span>Tersedia aplikasi mobile dan desktop.</span></li>
                        <li><i class="bi bi-check2" aria-hidden="true"></i> <span>Cocok untuk pelajar, mahasiswa, hingga profesional.</span></li>
                    </ul>
                </div>
                <div>
                    <img src="{{ asset('home-page/assets/img/features-illustration-1.webp') }}" alt="">
                </div>
            </div>

            <div class="rs-split rs-split--flip">
                <div>
                    <p class="rs-eyebrow">Kursus Berkualitas</p>
                    <h3 class="rs-h3">Instruktur Berpengalaman</h3>
                    <p>
                        Semua kursus dikurasi dan diajarkan oleh praktisi industri dan akademisi terpercaya.
                    </p>
                    <ul class="rs-checks rs-checks--1">
                        <li><i class="bi bi-check2" aria-hidden="true"></i> <span>Sertifikat resmi setelah menyelesaikan kursus.</span></li>
                        <li><i class="bi bi-check2" aria-hidden="true"></i> <span>Konten interaktif dengan studi kasus nyata.</span></li>
                        <li><i class="bi bi-check2" aria-hidden="true"></i> <span>Update materi berkala sesuai tren industri.</span></li>
                        <li><i class="bi bi-check2" aria-hidden="true"></i> <span>Forum diskusi dan komunitas pembelajar.</span></li>
                    </ul>
                </div>
                <div>
                    <img src="{{ asset('home-page/assets/img/features-illustration-2.webp') }}" alt="">
                </div>
            </div>

            <div class="rs-split">
                <div>
                    <p class="rs-eyebrow">Dukungan Karier</p>
                    <h3 class="rs-h3">Siap Menghadapi Dunia Kerja</h3>
                    <ul class="rs-checks rs-checks--1">
                        <li><i class="bi bi-check2" aria-hidden="true"></i> <span>Konsultasi karier dan pembuatan CV profesional.</span></li>
                        <li><i class="bi bi-check2" aria-hidden="true"></i> <span>Akses ke lowongan kerja eksklusif mitra {{ $siteSettings->site_name }}.</span></li>
                        <li><i class="bi bi-check2" aria-hidden="true"></i> <span>Pelatihan interview dan pengembangan soft skill.</span></li>
                    </ul>
                    <p class="mt-3">
                        {{ $siteSettings->site_name }} mendukung kariermu sejak proses belajar hingga siap terjun ke industri.
                    </p>
                </div>
                <div>
                    <img src="{{ asset('home-page/assets/img/features-illustration-3.webp') }}" alt="">
                </div>
            </div>
        </div>
    </section><!-- /Features Section -->

    <!-- Features Cards Section -->
    <section id="features-cards" class="rs-section rs-section--paper">
        <div class="rs-wrap">
            <ul class="rs-toc">
                <li>
                    <h3 class="rs-h3">Sertifikat Resmi</h3>
                    <p>Dapatkan sertifikat setelah menyelesaikan kursus untuk menunjang CV dan portofolio profesionalmu.</p>
                </li>
                <li>
                    <h3 class="rs-h3">Instruktur Terpercaya</h3>
                    <p>Belajar langsung dari praktisi industri dan akademisi berpengalaman di bidangnya.</p>
                </li>
                <li>
                    <h3 class="rs-h3">Akses Seumur Hidup</h3>
                    <p>Setelah membeli kursus, kamu bisa mengakses materi kapan saja dan selamanya tanpa batas.</p>
                </li>
                <li>
                    <h3 class="rs-h3">Komunitas Belajar</h3>
                    <p>Gabung bersama ribuan pelajar lainnya untuk saling diskusi dan belajar bersama dalam forum komunitas.</p>
                </li>
            </ul>
        </div>
    </section><!-- /Features Cards Section -->

    <!-- Features 2 Section -->
    <!-- /Features 2 Section -->

    <!-- Testimonials Section -->
    <section id="testimonials" class="rs-section">
        <div class="rs-wrap">
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

    @guest
        <!-- Call To Action 2 Section -->
        <section class="rs-band">
            <div class="rs-wrap rs-band__inner">
                <div>
                    <h2>Sudah Siap Jadi Bagian dari Mereka?</h2>
                    <p>Bergabunglah bersama ribuan peserta lainnya yang telah merasakan manfaat kursus kami. Mulailah langkah barumu hari ini!</p>
                </div>
                <a class="rs-btn rs-btn--light" href="{{ route('register') }}">Registrasi</a>
            </div>
        </section><!-- /Call To Action 2 Section -->
    @endguest
</div>
@endsection
