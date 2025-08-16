@extends('layouts.home-layout')

@section('title', 'Wahana Edukasi')

@section('content')
<!-- Contact Section -->
    <section id="contact" class="contact section light-background" style="padding-top: 120px;">

      <!-- Section Title -->
      <div class="container section-title" data-aos="fade-up">
        <h2>Hubungi Kami</h2>
        <p>Punya pertanyaan atau butuh bantuan? Tim kami siap membantu kapan saja!</p>
      </div><!-- End Section Title -->

      <div class="container" data-aos="fade-up" data-aos-delay="100">

        <div class="row g-4 g-lg-5">
          <div class="col-lg-5">
            <div class="info-box" data-aos="fade-up" data-aos-delay="200">
              <h3>Informasi Kontak</h3>
              <p>Kami siap membantu keluhan kamu. Silakan hubungi kami lewat informasi di bawah ini.</p>

              <div class="info-item" data-aos="fade-up" data-aos-delay="300">
                <div class="icon-box">
                  <i class="bi bi-geo-alt"></i>
                </div>
                <div class="content">
                  <h4>Lokasi Kami</h4>
                  <p>Jl. Merah Putih 16</p>
                  <p>Kabupaten Kediri, Jawa Timur</p>
                </div>
              </div>

              <div class="info-item" data-aos="fade-up" data-aos-delay="400">
                <div class="icon-box">
                  <i class="bi bi-telephone"></i>
                </div>
                <div class="content">
                  <h4>Nomor Telepon</h4>
                  <p>+62 1234 1234 12</p>
                  <p>+62 1234 1234 12</p>
                </div>
              </div>

              <div class="info-item" data-aos="fade-up" data-aos-delay="500">
                <div class="icon-box">
                  <i class="bi bi-envelope"></i>
                </div>
                <div class="content">
                  <h4>Alamat Email</h4>
                  <p>wahanaedukasi@example.com</p>
                  <p>wahanaedukasi@example.com</p>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-7">
            <div class="contact-form" data-aos="fade-up" data-aos-delay="300">
              <h3>Kirim Pesan</h3>
              <p>Isi form di bawah ini, dan kami akan segera menghubungi kamu kembali.</p>

              <form action="forms/contact.php" method="post" class="php-email-form" data-aos="fade-up" data-aos-delay="200">
                <div class="row gy-4">

                  <div class="col-md-6">
                    <input type="text" name="name" class="form-control" placeholder="Nama" required="">
                  </div>

                  <div class="col-md-6 ">
                    <input type="email" class="form-control" name="email" placeholder="Alamat Email" required="">
                  </div>

                  <div class="col-12">
                    <input type="text" class="form-control" name="subject" placeholder="Judul Pertanyaan" required="">
                  </div>

                  <div class="col-12">
                    <textarea class="form-control" name="message" rows="6" placeholder="Detail Pertanyaan" required=""></textarea>
                  </div>

                  <div class="col-12 text-center">
                    <div class="loading">Memuat</div>
                    <div class="error-message"></div>
                    <div class="sent-message">Pesan kamu berhasil terkirim. Terima kasih!</div>

                    <button type="submit" class="btn">Kirim Pesan</button>
                  </div>

                </div>
              </form>

            </div>
          </div>

        </div>

      </div>

    </section><!-- /Contact Section -->

    
    <!-- Call To Action 2 Section -->
    <section id="call-to-action-2" class="call-to-action-2 section dark-background">

      <div class="container">
        <div class="row justify-content-center" data-aos="zoom-in" data-aos-delay="100">
          <div class="col-xl-10">
            <div class="text-center">
              <h3>Ayo Mulai Sekarang!</h3>
              <p>Jangan tunggu nanti. Ayo, wujudkan ide dan tujuan kamu bersama kami. Tim kami siap membantu dari awal sampai sukses!</p>
              <a class="cta-btn" href="#">Call To Action</a>
            </div>
          </div>
        </div>
      </div>

    </section><!-- /Call To Action 2 Section -->

@endsection