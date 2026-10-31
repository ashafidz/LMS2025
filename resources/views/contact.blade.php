@extends('layouts.home-layout')

@section('title', 'Kontak')

@section('content')
<div class="rs-page">
    <section class="rs-pagehead">
        <div class="rs-wrap">
            <p class="rs-crumbs"><a href="{{ route('home') }}">Beranda</a> / Kontak</p>
            <h1 class="rs-h1">Hubungi Kami</h1>
            <p class="rs-lead">Punya pertanyaan atau butuh bantuan? Tim kami siap membantu kapan saja!</p>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="rs-section">
        <div class="rs-wrap">
            <div class="rs-contact">
                <div>
                    <h2 class="rs-h2">Informasi Kontak</h2>
                    <p class="rs-muted">Kami siap membantu keluhan kamu. Silakan hubungi kami lewat informasi di bawah ini.</p>

                    <dl>
                        @if ($siteSettings->address)
                            <div>
                                <dt>Alamat</dt>
                                <dd>{{ $siteSettings->address }}</dd>
                            </div>
                        @endif
                        @if ($siteSettings->phone)
                            <div>
                                <dt>Telepon</dt>
                                <dd>{{ $siteSettings->phone }}</dd>
                            </div>
                        @endif
                        @if ($siteSettings->email)
                            <div>
                                <dt>Email</dt>
                                <dd>{{ $siteSettings->email }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div>
                    <h2 class="rs-h2">Kirim Pesan</h2>
                    <p class="rs-muted">Isi form di bawah ini, dan kami akan segera menghubungi kamu kembali.</p>

                    <form action="{{ route('contact.store') }}" method="post" class="rs-form" data-rs-contact data-recaptcha-site-key="{{ config('services.recaptcha.site_key') }}">
                        @csrf
                        <div class="rs-form__row">
                            <div class="rs-field">
                                <label for="contact-name">Nama</label>
                                <input id="contact-name" type="text" name="name" class="rs-input" required="">
                            </div>
                            <div class="rs-field">
                                <label for="contact-email">Alamat email</label>
                                <input id="contact-email" type="email" class="rs-input" name="email" required="">
                            </div>
                        </div>

                        <div class="rs-field">
                            <label for="contact-subject">Judul pertanyaan</label>
                            <input id="contact-subject" type="text" class="rs-input" name="subject" required="">
                        </div>

                        <div class="rs-field">
                            <label for="contact-message">Detail pertanyaan</label>
                            <textarea id="contact-message" class="rs-input" name="message" rows="6" required="" maxlength="5000"></textarea>
                        </div>

                        <div>
                            <div class="rs-form-status rs-form-status--wait" data-rs-status="wait" role="status">Sedang mengirim pesan...</div>
                            <div class="rs-form-status rs-form-status--error" data-rs-status="error" role="alert"></div>
                            <div class="rs-form-status rs-form-status--ok" data-rs-status="ok" role="status">Pesan kamu berhasil terkirim. Terima kasih!</div>

                            <button type="submit" class="rs-btn">Kirim Pesan</button>
                        </div>
                    </form>

                    <!-- Google reCAPTCHA v3 Script -->
                    @if(config('services.recaptcha.site_key'))
                        <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
                    @endif
                </div>
            </div>
        </div>
    </section><!-- /Contact Section -->

    @guest
        <!-- Call To Action 2 Section -->
        <section class="rs-band">
            <div class="rs-wrap rs-band__inner">
                <div>
                    <h2>Ayo Mulai Sekarang!</h2>
                    <p>Jangan tunggu nanti. Ayo, wujudkan ide dan tujuan kamu bersama kami. Tim kami siap membantu dari awal sampai sukses!</p>
                </div>
                <a class="rs-btn rs-btn--light" href="{{ route('register') }}">Daftar Sekarang</a>
            </div>
        </section><!-- /Call To Action 2 Section -->
    @endguest
</div>
@endsection
