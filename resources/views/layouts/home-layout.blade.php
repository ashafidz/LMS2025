<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') | @endif{{ ($siteSettings->site_name ?? null) ?: config('app.name') }}</title>
    @php
        $seoSite = ($siteSettings->site_name ?? null) ?: config('app.name');
        // Inline @section('x', $value) already escapes $value, so decode once here and let {{ }} escape it again.
        $seoSection = fn (string $name): string => trim(html_entity_decode($__env->yieldContent($name), ENT_QUOTES | ENT_HTML5));
        $seoTitle = $seoSection('title') ?: $seoSite;
        $seoDescription = $seoSection('meta_description')
            ?: 'Ruang Studi adalah platform belajar online: kursus dari instruktur, kuis, tugas, dan sertifikat yang bisa dicek keasliannya.';
        $seoCustomImage = $seoSection('og_image');
        $seoImage = $seoCustomImage ?: asset('images/og/ruang-studi-share.png');
        $seoUrl = url()->current();
    @endphp
    <meta name="description" content="{{ $seoDescription }}">
    <link rel="canonical" href="{{ $seoUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $seoSite }}">
    <meta property="og:locale" content="id_ID">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $seoUrl }}">
    <meta property="og:image" content="{{ $seoImage }}">
    @unless ($seoCustomImage)
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    @endunless
    <meta property="og:image:alt" content="{{ $seoTitle }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $seoImage }}">

    <!-- Favicons -->
    <link href="{{ asset('favicon.svg') }}" rel="icon" type="image/svg+xml">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,400;0,6..12,500;0,6..12,600;0,6..12,700;0,6..12,800;1,6..12,400&family=Poppins:wght@500;600;700;800&family=JetBrains+Mono:wght@400;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="{{ asset('css/ruang-studi.css') }}" rel="stylesheet">
    @stack('styles')
</head>

<body class="rs-body">
    @yield('intro')
    <a class="rs-skip" href="#main">Langsung ke isi</a>

    <!-- ======= Navbar ======= -->
@php
    $navItems = [
        ['label' => 'Beranda', 'url' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => 'Tentang Kami', 'url' => route('about'), 'active' => request()->routeIs('about')],
        ['label' => 'Katalog', 'url' => route('courses'), 'active' => request()->routeIs('courses', 'courses.show')],
        ['label' => 'FAQ', 'url' => route('faqs'), 'active' => request()->routeIs('faqs')],
        ['label' => 'Kontak', 'url' => route('contact'), 'active' => request()->routeIs('contact')],
    ];
    $activeRole = session('active_role');
    $dashboardUrl = null;
    if (Auth::check()) {
        if ($activeRole === 'superadmin') {
            $dashboardUrl = route('superadmin.dashboard');
        } elseif ($activeRole === 'admin') {
            $dashboardUrl = route('admin.dashboard');
        } elseif ($activeRole === 'instructor' && Auth::user()->instructorProfile?->application_status === 'approved') {
            $dashboardUrl = route('instructor.dashboard');
        } elseif ($activeRole === 'student') {
            $dashboardUrl = route('student.dashboard');
        }
    }
@endphp
<header class="rs-header">
    <div class="rs-wrap">
        <div class="rs-header__bar">
            <a href="{{ route('home') }}" class="rs-logo">{{ ($siteSettings->site_name ?? null) ?: config('app.name') }}</a>
            <nav class="rs-nav" aria-label="Menu utama">
                @foreach ($navItems as $item)
                    <a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
            </nav>
            <div class="rs-account">
                @auth
                    @if ($activeRole === 'student')
                        <span class="rs-diamond" title="Saldo diamond"><i class="bi bi-gem" aria-hidden="true"></i>{{ number_format(Auth::user()->diamond_balance, 0, ',', '.') }} Diamond</span>
                        <a href="{{ url('/cart') }}" class="rs-iconlink"><i class="bi bi-cart" aria-hidden="true"></i>Keranjang</a>
                    @endif
                    @if ($dashboardUrl)
                        <a class="rs-btn" href="{{ $dashboardUrl }}">Dashboard</a>
                    @endif
                @endauth
                @guest
                    <a class="rs-btn rs-btn--outline" href="{{ url('/login') }}">Masuk</a>
                    <a class="rs-btn" href="{{ route('register') }}">Daftar</a>
                @endguest
            </div>
            <button type="button" class="rs-menu-btn" aria-expanded="false" aria-controls="rs-mobile-menu">
                <i class="bi bi-list" aria-hidden="true"></i>Menu
            </button>
        </div>
        <div class="rs-mobile" id="rs-mobile-menu">
            <ul>
                @foreach ($navItems as $item)
                    <li><a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif>{{ $item['label'] }}</a></li>
                @endforeach
            </ul>
            <div class="rs-mobile__account">
                @auth
                    @if ($activeRole === 'student')
                        <span class="rs-diamond"><i class="bi bi-gem" aria-hidden="true"></i>{{ number_format(Auth::user()->diamond_balance, 0, ',', '.') }} Diamond</span>
                        <a href="{{ url('/cart') }}" class="rs-btn rs-btn--outline rs-btn--block"><i class="bi bi-cart" aria-hidden="true"></i>Keranjang</a>
                    @endif
                    @if ($dashboardUrl)
                        <a class="rs-btn rs-btn--block" href="{{ $dashboardUrl }}">Dashboard</a>
                    @endif
                @endauth
                @guest
                    <a class="rs-btn rs-btn--outline rs-btn--block" href="{{ url('/login') }}">Masuk</a>
                    <a class="rs-btn rs-btn--block" href="{{ route('register') }}">Daftar</a>
                @endguest
            </div>
        </div>
    </div>
</header>
    <!-- End Navbar -->

    <!-- ======= Main Content ======= -->
    <main id="main">
        @yield('content')
    </main>

    <!-- ======= Footer ======= -->
<footer class="rs-footer">
    <div class="rs-wrap">
        <div class="rs-footer__grid">
            <div>
                <a href="{{ url('/') }}" class="rs-footer__brand">{{ ($siteSettings->site_name ?? null) ?: config('app.name') }}</a>
                @if ($siteSettings->company_name)<p>{{ $siteSettings->company_name }}</p>@endif
                @if ($siteSettings->address)<p>{{ $siteSettings->address }}</p>@endif
                @if ($siteSettings->phone)<p>Telepon: {{ $siteSettings->phone }}</p>@endif
                @if ($siteSettings->email)<p>Email: {{ $siteSettings->email }}</p>@endif
                <div class="rs-social">
                    {{-- Tampilkan ikon hanya jika link-nya diisi --}}
                    @if ($siteSettings->twitter_url && $siteSettings->twitter_url !== '#')
                        <a href="{{ $siteSettings->twitter_url }}" target="_blank" rel="noopener" aria-label="Twitter"><i class="bi bi-twitter-x" aria-hidden="true"></i></a>
                    @endif
                    @if ($siteSettings->facebook_url && $siteSettings->facebook_url !== '#')
                        <a href="{{ $siteSettings->facebook_url }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="bi bi-facebook" aria-hidden="true"></i></a>
                    @endif
                    @if ($siteSettings->instagram_url && $siteSettings->instagram_url !== '#')
                        <a href="{{ $siteSettings->instagram_url }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="bi bi-instagram" aria-hidden="true"></i></a>
                    @endif
                    @if ($siteSettings->youtube_url && $siteSettings->youtube_url !== '#')
                        <a href="{{ $siteSettings->youtube_url }}" target="_blank" rel="noopener" aria-label="YouTube"><i class="bi bi-youtube" aria-hidden="true"></i></a>
                    @endif
                </div>
            </div>
            <div>
                <h2>Beranda</h2>
                <ul>
                    <li><a href="{{ url('/#services') }}">Layanan Kami</a></li>
                    <li><a href="{{ url('/#pricing') }}">Kursus yang Sering Dibeli</a></li>
                </ul>
            </div>
            <div>
                <h2>Tentang Kami</h2>
                <ul>
                    <li><a href="{{ url('/about/#features') }}">Fitur Unggulan</a></li>
                    <li><a href="{{ url('/#testimonials') }}">Testimoni Peserta</a></li>
                    <li><a href="{{ route('certificates.verify') }}">Cek Keaslian Sertifikat</a></li>
                </ul>
            </div>
            <div>
                <h2>Katalog</h2>
                <ul>
                    {{-- Loop untuk menampilkan kategori dari View Composer --}}
                    @if (isset($footerCategories))
                        @foreach ($footerCategories as $category)
                            {{-- Tautan ini akan mengarah ke halaman katalog dengan filter kategori yang sudah diterapkan --}}
                            <li><a href="{{ route('courses', ['categories[]' => $category->id]) }}">{{ $category->name }}</a></li>
                        @endforeach
                    @endif
                </ul>
            </div>
        </div>
        <div class="rs-footer__bottom">
            <span>&copy; {{ date('Y') }} {{ ($siteSettings->site_name ?? null) ?: config('app.name') }}</span>
        </div>
    </div>
</footer>
    <!-- End Footer -->

    <a href="https://wa.me/6281234567890" target="_blank" rel="noopener" class="rs-wa"><i class="bi bi-whatsapp" aria-hidden="true"></i><span>WhatsApp</span></a>

    <script>
        // Cek jika timezone belum diatur di session storage browser
        if (!sessionStorage.getItem('timezone_set')) {
            const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            const token = document.querySelector('meta[name="csrf-token"]').content;

            // Kirim timezone ke server menggunakan Fetch API
            fetch('/set-timezone', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token // Gunakan token dari meta tag
                },
                body: JSON.stringify({
                    timezone: userTimezone
                })
            }).then(response => {
                if (response.ok) {
                    // Tandai bahwa timezone sudah diatur agar tidak dikirim berulang kali
                    sessionStorage.setItem('timezone_set', 'true');
                } else if (response.status === 419) {
                    // CSRF token mismatch, refresh token and try again
                    refreshCsrfToken().then(newToken => {
                        fetch('/set-timezone', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': newToken
                            },
                            body: JSON.stringify({
                                timezone: userTimezone
                            })
                        }).then(() => {
                            sessionStorage.setItem('timezone_set', 'true');
                        });
                    });
                }
            }).catch(error => {
                console.error('Error setting timezone:', error);
            });
        }
    </script>

    <!-- CSRF protection -->
    <script type="text/javascript" src="{{ asset('js/csrf-refresh.js') }}"></script>
    <script src="{{ asset('js/ruang-studi.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <script>window.rsDialogImages = {{ \Illuminate\Support\Js::from(['ok' => asset('images/illustrations/rs-16-bayar-berhasil.webp'), 'fail' => asset('images/illustrations/rs-18-bayar-gagal.webp')]) }};</script>
    <script src="{{ asset('js/rs-dialog.js') }}"></script>
    <script src="{{ asset('js/rs-forms.js') }}"></script>
    <script src="{{ asset('js/rs-motion.js') }}"></script>
    @stack('scripts')
    <x-rs.flash-toast />
</body>

</html>
