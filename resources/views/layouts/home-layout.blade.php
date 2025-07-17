<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Index - iLanding Bootstrap Template</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
<!-- CSS -->
<link href="{{ asset('home-page/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('home-page/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
<link href="{{ asset('home-page/assets/vendor/aos/aos.css') }}" rel="stylesheet">
<link href="{{ asset('home-page/assets/vendor/glightbox/css/glightbox.min.css') }}" rel="stylesheet">
<link href="{{ asset('home-page/assets/vendor/swiper/swiper-bundle.min.css') }}" rel="stylesheet">
<link href="{{ asset('home-page/assets/css/main.css') }}" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="home-page/assets/css/main.css" rel="stylesheet">
</head>

<body class="index-page" style="background-color: #f2f6fd;">

  <!-- ======= Navbar ======= -->
  <header id="header" class="header fixed-top header-transparent">
    <div class="header-container container-fluid container-xl position-relative d-flex align-items-center justify-content-between">

      <a href="{{ url('/home') }}" class="logo d-flex align-items-center me-auto me-xl-0">
        <h1 class="sitename">KURSUS IDN</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="{{ url('/home') }}">Beranda</a></li>
          <li><a href="{{ url('/about') }}">Tentang Kami</a></li>
          <li><a href="{{ url('/catalog') }}">Katalog</a></li>
          <li><a href="{{ url('/faq') }}">FAQ</a></li>
          <li><a href="{{ url('/contact') }}">Kontak</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <div class="d-flex align-items-center gap-2">
        <a href="{{ url('/cart') }}" class="btn-cart"
          onmouseover="this.firstElementChild.style.color='#0d6efd'"
          onmouseout="this.firstElementChild.style.color='black'">
          <i class="bi bi-cart" style="font-size: 1.4rem; color: black;"></i>
        </a>
        <a class="btn-getstarted ms-2" href="{{ url('/home') }}">Get Started</a>
      </div>
    </div>
  </header>
  <!-- End Navbar -->

  <!-- ======= Main Content ======= -->
  <main class="main">
    @yield('content')
  </main>

  <!-- ======= Footer ======= -->
  <footer id="footer" class="footer">

    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="{{ url('/') }}" class="logo d-flex align-items-center">
            <span class="sitename">iLanding</span>
          </a>
          <div class="footer-contact pt-3">
            <p>A108 Adam Street</p>
            <p>New York, NY 535022</p>
            <p class="mt-3"><strong>Phone:</strong> <span>+1 5589 55488 55</span></p>
            <p><strong>Email:</strong> <span>info@example.com</span></p>
          </div>
          <div class="social-links d-flex mt-4">
            <a href="#"><i class="bi bi-twitter-x"></i></a>
            <a href="#"><i class="bi bi-facebook"></i></a>
            <a href="#"><i class="bi bi-instagram"></i></a>
            <a href="#"><i class="bi bi-linkedin"></i></a>
          </div>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Useful Links</h4>
          <ul>
            <li><a href="#">Home</a></li>
            <li><a href="#">About us</a></li>
            <li><a href="#">Services</a></li>
            <li><a href="#">Terms of service</a></li>
            <li><a href="#">Privacy policy</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Our Services</h4>
          <ul>
            <li><a href="#">Web Design</a></li>
            <li><a href="#">Web Development</a></li>
            <li><a href="#">Product Management</a></li>
            <li><a href="#">Marketing</a></li>
            <li><a href="#">Graphic Design</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Hic solutasetp</h4>
          <ul>
            <li><a href="#">Molestiae accusamus iure</a></li>
            <li><a href="#">Excepturi dignissimos</a></li>
            <li><a href="#">Suscipit distinctio</a></li>
            <li><a href="#">Dilecta</a></li>
            <li><a href="#">Sit quas consectetur</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Nobis illum</h4>
          <ul>
            <li><a href="#">Ipsam</a></li>
            <li><a href="#">Laudantium dolorum</a></li>
            <li><a href="#">Dinera</a></li>
            <li><a href="#">Trodelas</a></li>
            <li><a href="#">Flexo</a></li>
          </ul>
        </div>
      </div>
    </div>

    <div class="container copyright text-center mt-4">
      <p>© <strong class="px-1 sitename">iLanding</strong> All Rights Reserved</p>
      <div class="credits">
        Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a>
      </div>
    </div>
  </footer>
  <!-- End Footer -->

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center">
    <i class="bi bi-arrow-up-short"></i>
  </a>

  <!-- Vendor JS Files -->
  <script src="{{ asset('home-page/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('home-page/assets/vendor/php-email-form/validate.js') }}"></script>
  <script src="{{ asset('home-page/assets/vendor/aos/aos.js') }}"></script>
  <script src="{{ asset('home-page/assets/vendor/glightbox/js/glightbox.min.js') }}"></script>
  <script src="{{ asset('home-page/assets/vendor/swiper/swiper-bundle.min.js') }}"></script>
  <script src="{{ asset('home-page/assets/vendor/purecounter/purecounter_vanilla.js') }}"></script>

  <!-- Main JS File -->
  <script src="{{ asset('home-page/assets/js/main.js') }}"></script>

  <!-- Tambah di sini -->
  @stack('scripts')

    <!-- Tombol WhatsApp Ikon Saja -->
    <a href="https://wa.me/6281234567890" target="_blank"
        style="
            position: fixed;
            bottom: 50px;
            right: 50px;
            z-index: 9999;
            transition: transform 0.3s ease;
        "
        onmouseover="this.style.transform='scale(1.1)'"
        onmouseout="this.style.transform='scale(1)'">
        <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg"
            alt="WhatsApp"
            style="width: 50px; height: 50px;">
    </a>
</body>

</html>
