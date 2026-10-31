@extends('layouts.home-layout')

@section('title', 'FAQ')

@section('content')
<div class="rs-page">
    <section class="rs-pagehead">
        <div class="rs-wrap">
            <p class="rs-crumbs"><a href="{{ route('home') }}">Beranda</a> / FAQ</p>
            <h1 class="rs-h1">Punya pertanyaan? Cek FAQ ini dulu</h1>
            <p class="rs-lead">Di sini kamu bisa temukan jawaban dari pertanyaan yang sering diajukan. Lebih cepat, nggak perlu nunggu lama.</p>
        </div>
    </section>

    <!-- Faq Section -->
    <section id="faq" class="rs-section">
        <div class="rs-wrap">
            <div class="rs-faq">
                <details open>
                    <summary>Bagaimana cara mendaftar di platform ini?</summary>
                    <div>
                        <p>Cukup klik tombol "Daftar" di pojok kanan atas, isi data yang diminta, lalu konfirmasi email kamu. Setelah itu, kamu bisa langsung mulai belajar atau menggunakan layanan kami.</p>
                    </div>
                </details>

                <details>
                    <summary>Apakah layanan ini berbayar?</summary>
                    <div>
                        <p>Kami menyediakan versi gratis dan berbayar. Versi gratis punya akses terbatas, sementara versi berbayar memberi kamu semua fitur premium tanpa batas.</p>
                    </div>
                </details>

                <details>
                    <summary>Bagaimana cara menghubungi tim support?</summary>
                    <div>
                        <p>Kamu bisa menghubungi kami lewat menu "Kontak" di website, atau langsung kirim email ke support@domain.com. Kami biasanya balas dalam waktu 24 jam.</p>
                    </div>
                </details>
            </div>
        </div>
    </section><!-- /Faq Section -->

    <section class="rs-band">
        <div class="rs-wrap rs-band__inner">
            <div>
                <h2>Masih ada pertanyaan?</h2>
                <p>Tim kami siap membantu. Kirim pesan lewat halaman kontak.</p>
            </div>
            <a class="rs-btn rs-btn--light" href="{{ route('contact') }}">Hubungi kami</a>
        </div>
    </section>
</div>
@endsection
