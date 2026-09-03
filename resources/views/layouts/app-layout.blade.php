<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"> --}}

    {{-- Optional: Add a custom CSS file for sidebar styling --}}
    {{-- <link href="{{ asset('css/style.css') }}" rel="stylesheet"> --}}





    {{-- ================================================ --}}
    <!-- Google font-->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,500" rel="stylesheet" />
    <!-- waves.css -->
    <link rel="stylesheet" href="{{ asset('pages/waves/css/waves.min.css') }}" type="text/css" media="all" />
    <!-- Required Fremwork -->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap/css/bootstrap.min.css') }}" />
    <!-- waves.css -->
    <link rel="stylesheet" href="{{ asset('pages/waves/css/waves.min.css') }}" type="text/css" media="all" />
    <!-- themify icon removed (Tahap 3) -->
            <link
            rel="stylesheet"
            href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css"
        />

        <link
            rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        />
    <!-- Font Awesome -->
    <link rel="stylesheet" type="text/css" href="{{ asset('icon/font-awesome/css/font-awesome.min.css') }}" />
    <!-- css scrollbar and amchart css removed (Tahap 2) -->
    <!-- ico font removed (Tahap 3) -->
    <!-- Style.css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}" />
    {{-- =============================================== --}}


            <style>
            .star-rating {
                display: inline-flex;
                gap: 8px;
                font-size: 20px;
            }

            .star-rating i {
                color: #ddd;
                transition: transform 0.2s, color 0.3s;
                cursor: pointer;
            }

            .star-rating i.hovered,
            .star-rating i.selected {
                color: #facc15; /* kuning keemasan */
                transform: scale(1.2);
            }

            /* Fix Mobile Sidebar Scrolling */
            @media only screen and (max-width: 991.98px) {
                .pcoded .pcoded-navbar {
                    position: fixed !important;
                    height: 100vh !important;
                    overflow-y: auto !important;
                    -webkit-overflow-scrolling: touch;
                }
                .pcoded .pcoded-navbar .pcoded-inner-navbar {
                    min-height: 120vh !important;
                    background-color: #fff !important;
                }
            }
        </style>
        
        <script>
            // Safely lock body scroll on mobile when the offcanvas sidebar is expanded
            document.addEventListener('DOMContentLoaded', function () {
                const pcoded = document.getElementById('pcoded');
                if (pcoded) {
                    const checkScrollLock = () => {
                        const navType = pcoded.getAttribute('vertical-nav-type');
                        const isMobile = window.innerWidth <= 991.98;
                        // On mobile, the sidebar uses 'expanded' when open
                        if (isMobile && navType === 'expanded') {
                            document.body.style.setProperty('overflow', 'hidden', 'important');
                        } else {
                            document.body.style.overflow = '';
                        }
                    };

                    // Observe attribute changes on the pcoded wrapper
                    const observer = new MutationObserver(function(mutations) {
                        mutations.forEach(function(mutation) {
                            if (mutation.attributeName === 'vertical-nav-type') {
                                checkScrollLock();
                            }
                        });
                    });
                    
                    observer.observe(pcoded, { attributes: true });
                    
                    // Also check on window resize
                    window.addEventListener('resize', checkScrollLock);
                }
            });
        </script>
        
        <style>
            /* Murni CSS Scrollbar (Tahap 2) */
            ::-webkit-scrollbar {
                width: 6px;
                height: 6px;
            }
            ::-webkit-scrollbar-track {
                background: transparent;
            }
            ::-webkit-scrollbar-thumb {
                background-color: rgba(0, 0, 0, 0.2);
                border-radius: 10px;
            }
            ::-webkit-scrollbar-thumb:hover {
                background-color: rgba(0, 0, 0, 0.4);
            }
            
            /* ========================================================
               60FPS Smooth UI & Hardware Acceleration (CSS Murni)
               ======================================================== */
            html {
                scroll-behavior: smooth !important;
            }
            
            /* Mengaktifkan GPU Acceleration untuk elemen berat ditiadakan karena merusak z-index overlay */

            /* Transisi super mulus (Material Design Cubic Bezier) untuk tombol dan tautan */
            a, button, .btn, .nav-link, .dropdown-item, .list-group-item, .pcoded-micon {
                transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
            }

            /* Efek hover dinamis (Micro-animations) */
            .card {
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                will-change: transform, box-shadow;
            }
            .card:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important;
            }

            /* Hover pada menu sidebar agar terasa hidup */
            .pcoded-navbar .pcoded-item > li > a:hover {
                background: rgba(255,255,255,0.05) !important;
                padding-left: 25px !important; /* Efek menjorok halus */
            }
            
            /* ========================================================
               60FPS Lightweight Loader (CSS Murni)
               ======================================================== */
            #smooth-loader {
                position: fixed;
                top: 0; left: 0;
                width: 100vw; height: 100vh;
                background-color: #f3f5f9; /* Sesuai warna background template umum */
                z-index: 999999;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                transition: opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.5s;
                will-change: opacity, visibility;
            }
            body.loaded #smooth-loader {
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
            }
            #smooth-loader .spinner {
                width: 45px;
                height: 45px;
                border: 4px solid rgba(68, 138, 255, 0.2); /* Warna biru muda transparan */
                border-top-color: #448aff; /* Biru utama */
                border-radius: 50%;
                animation: smooth-spin 0.8s linear infinite;
                will-change: transform;
            }
            @keyframes smooth-spin {
                0% { transform: rotate(0deg) translateZ(0); }
                100% { transform: rotate(360deg) translateZ(0); }
            }
        </style>
        
        <script>
            // Hapus loader secara sangat ringan saat halaman selesai dirender
            window.addEventListener('load', function() {
                // Memberi jeda mikrosekon agar CSS sempat merender animasi akhir
                setTimeout(() => {
                    document.body.classList.add('loaded');
                }, 100);
            });
        </script>
        
        @stack('styles')

        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body>

    <!-- 60FPS Smooth Loader -->
    <div id="smooth-loader">
        <div class="spinner"></div>
    </div>
    <!-- Pre-loader removed (Tahap 2) -->
    <div id="pcoded" class="pcoded">
        <div class="pcoded-overlay-box"></div>
        <div class="pcoded-container navbar-wrapper">
          

            @auth {{-- This whole block only runs if a user is logged in --}}

                {{-- Define state variables based on the user's session and roles --}}
                @php
                    $activeRole = session('active_role', 'student'); // Get active role, default to 'student'

                    // Check if the user is an approved instructor who can switch views
                    $canSwitch =
                        Auth::user()->hasRole('instructor') &&
                        Auth::user()->instructorProfile?->application_status === 'approved' &&
                        Auth::user()->hasRole('student');
                @endphp

                {{-- Conditionally Include the Correct Navbar --}}
                @if ($activeRole === 'instructor')
                    @include('layouts.navigations.navbars.instructor-navbar', ['canSwitch' => $canSwitch])
                @elseif ($activeRole === 'student')
                    @include('layouts.navigations.navbars.student-navbar', ['canSwitch' => $canSwitch])
                @elseif (Auth::user()->hasRole('admin'))
                    @include('layouts.navigations.navbars.admin-navbar')
                @elseif (Auth::user()->hasRole('superadmin'))
                    @include('layouts.navigations.navbars.superadmin-navbar')
                @endif
                {{-- @else --}}
                    {{-- Optional: Include a navbar for guests/unauthenticated users --}}
                    {{-- @include('layouts.navigations.navbars.guest-navbar') --}}
            @endauth



            <div class="pcoded-main-container">
                <div class="pcoded-wrapper">
                    @auth
                    @if ($activeRole === 'instructor')
                        @include('layouts.navigations.sidebars.instructor-sidebar')
                    @elseif ($activeRole === 'student')
                        @include('layouts.navigations.sidebars.student-sidebar')
                    @elseif (Auth::user()->hasRole('admin'))
                        @include('layouts.navigations.sidebars.admin-sidebar')
                    @elseif (Auth::user()->hasRole('superadmin'))
                        @include('layouts.navigations.sidebars.superadmin-sidebar')
                    @endif

                    @yield('content')

                    @else
                        @yield('content')
                    @endauth
                </div>
            </div>
        </div>
    </div>


















    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <!-- Required Jquery -->
    <script type="text/javascript" src="{{ asset('js/jquery/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jquery-ui/jquery-ui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/popper.js/popper.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/bootstrap/js/bootstrap.min.js') }}"></script>

    <!-- waves js -->
    <script src="{{ asset('pages/waves/js/waves.min.js') }}"></script>
    <!-- jquery slimscroll js removed -->
    <!-- CSRF protection -->
    <script type="text/javascript" src="{{ asset('js/csrf-refresh.js') }}"></script>
    <!-- modernizr js removed -->
    <!-- slimscroll js -->
    <script type="text/javascript" src="{{ asset('js/SmoothScroll.js') }}"></script>
    <!-- mCustomScrollbar js removed -->
    <!-- Chart js and amcharts removed (Tahap 2) -->
    <!-- menu js -->
    <script src="{{ asset('js/pcoded.min.js') }}"></script>
    <script src="{{ asset('js/vertical-layout.min.js') }} "></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="{{ asset('pages/accordion/accordion.js') }}"></script>
    


    <script>
    // Cek jika timezone belum diatur di session storage browser
    if (!sessionStorage.getItem('timezone_set')) {
        const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

        // Kirim timezone ke server menggunakan Fetch API
        fetch('/set-timezone', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}' // Penting untuk keamanan
            },
            body: JSON.stringify({ timezone: userTimezone })
        }).then(() => {
            // Tandai bahwa timezone sudah diatur agar tidak dikirim berulang kali
            sessionStorage.setItem('timezone_set', 'true');
        });
    }
</script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('form[data-lesson-submit="true"]').forEach(function (form) {
                form.addEventListener('submit', function () {
                    const button = form.querySelector('.js-lesson-submit-btn');
                    if (!button) {
                        return;
                    }
                    button.disabled = true;
                    const spinner = button.querySelector('.js-lesson-submit-spinner');
                    const text = button.querySelector('.js-lesson-submit-text');
                    if (text) {
                        text.textContent = 'Menyimpan...';
                    }
                    if (spinner) {
                        spinner.classList.remove('d-none');
                    }
                });
            });
        });
    </script>

    <!-- custom js -->
    @stack('scripts')
    {{-- <script type="text/javascript" src="{{ asset('pages/dashboard/custom-dashboard.js') }}"></script> --}}
    <script type="text/javascript" src="{{ asset('js/script.js') }}"></script>


</body>

</html>
