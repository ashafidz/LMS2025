@php
    $siteName = ($siteSettings ?? null)?->site_name ?: config('app.name');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@hasSection('title')@yield('title') | @endif{{ $siteName }}</title>

    <link href="{{ asset('favicon.svg') }}" rel="icon" type="image/svg+xml">

    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Literata:ital,opsz,wght@0,7..72,400;0,7..72,500;0,7..72,700;0,7..72,800;1,7..72,400&display=swap" rel="stylesheet">

    {{-- Page content still uses Bootstrap 4 and Guru Able classes until phase D. --}}
    <link href="{{ asset('pages/waves/css/waves.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('icon/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="{{ asset('vendor/select2/select2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/ruang-studi-dashboard.css') }}" rel="stylesheet">
    @stack('styles')
</head>

<body class="rs-dash">
    @auth
        @php
            $user = Auth::user();
            $activeRole = session('active_role', 'student');
            // Same rule as the old navbars: an applicant (instructor role, any status) also gets the link;
            // RoleSwitchController sends a pending/rejected/deactive applicant to their status page.
            $switchTo = match (true) {
                $activeRole === 'student' && $user->hasRole('instructor') => 'instructor',
                $activeRole === 'instructor' && $user->hasRole('student') => 'student',
                default => null,
            };
            $navRole = match (true) {
                $activeRole === 'instructor' => 'instructor',
                $activeRole === 'student' => 'student',
                $user->hasRole('admin') => 'admin',
                $user->hasRole('superadmin') => 'superadmin',
                default => null,
            };
            $initials = collect(preg_split('/\s+/', trim($user->name)))
                ->filter()
                ->take(2)
                ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                ->implode('');
        @endphp

        <a class="rs-dash-skip" href="#main">Langsung ke isi</a>

        <div class="rs-dash-shell">
            <aside id="rs-dash-side" class="rs-dash-side" aria-label="Menu">
                <a href="{{ route('home') }}" class="rs-dash-logo">{{ $siteName }}</a>

                @if ($switchTo)
                    <div class="rs-dash-role" role="group" aria-label="Ganti peran">
                        @if ($switchTo === 'instructor')
                            <span aria-current="true">Siswa</span>
                            <a href="{{ route('role.switch', 'instructor') }}">Instruktur</a>
                        @else
                            <a href="{{ route('role.switch', 'student') }}">Siswa</a>
                            <span aria-current="true">Instruktur</span>
                        @endif
                    </div>
                @endif

                @hasSection('dash-nav')
                    @yield('dash-nav')
                @elseif ($navRole)
                    @include('layouts.dashboard.nav-' . $navRole)
                @endif

                <nav class="rs-dash-group" aria-labelledby="nav-profil">
                    <h2 id="nav-profil">Profil</h2>
                    <ul class="rs-dash-toc">
                        <x-dash.link :href="route('user.profile.index')" :active="['user.profile.*']">Profil saya</x-dash.link>
                        <x-dash.link :href="route('home')">Ke halaman depan</x-dash.link>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit">Keluar</button>
                            </form>
                        </li>
                    </ul>
                </nav>
            </aside>

            <div class="rs-dash-main">
                <header class="rs-dash-top">
                    <button type="button" class="rs-dash-menu-btn" aria-controls="rs-dash-side" aria-expanded="false">
                        <i class="bi bi-list" aria-hidden="true"></i>Menu
                    </button>
                    <span class="rs-dash-top__sp"></span>
                    @if ($navRole === 'student')
                        <a class="rs-dash-top__link" href="{{ route('student.cart.index') }}"><i class="bi bi-cart3" aria-hidden="true"></i>Keranjang</a>
                    @endif
                    <details class="rs-dash-user">
                        <summary>
                            @if ($user->profile_picture_url)
                                <img class="rs-dash-avatar" src="{{ asset($user->profile_picture_url) }}" alt="">
                            @else
                                <span class="rs-dash-avatar" aria-hidden="true">{{ $initials }}</span>
                            @endif
                            <span>{{ $user->name }}</span>
                            <i class="bi bi-chevron-down" aria-hidden="true"></i>
                        </summary>
                        <div class="rs-dash-user__menu">
                            <a href="{{ route('user.profile.index') }}">Profil saya</a>
                            <a href="{{ route('home') }}">Ke halaman depan</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit">Keluar</button>
                            </form>
                        </div>
                    </details>
                </header>

                <main id="main" class="rs-dash-content">
                    @yield('content')
                </main>
            </div>
        </div>
    @else
        <main id="main">
            @yield('content')
        </main>
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <script src="{{ asset('js/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('js/jquery-ui/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('vendor/select2/select2.min.js') }}"></script>
    <script src="{{ asset('vendor/select2/id.js') }}"></script>
    <script src="{{ asset('js/popper.js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('pages/waves/js/waves.min.js') }}"></script>
    <script src="{{ asset('js/csrf-refresh.js') }}"></script>
    <script src="{{ asset('js/SmoothScroll.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('pages/accordion/accordion.js') }}"></script>

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

        // Hentikan pemutaran video/audio saat modal Bootstrap ditutup
        function stopMediaInModal(modal) {
            if (!modal) return;

            // 1. Pause dan reset tag <video>
            modal.querySelectorAll('video').forEach(function (video) {
                try {
                    video.pause();
                    video.currentTime = 0;
                } catch (e) {}
            });

            // 2. Pause dan reset tag <audio>
            modal.querySelectorAll('audio').forEach(function (audio) {
                try {
                    audio.pause();
                    audio.currentTime = 0;
                } catch (e) {}
            });

            // 3. Hentikan pemutaran <iframe> video (YouTube, Vimeo, embed-responsive)
            modal.querySelectorAll('iframe').forEach(function (iframe) {
                try {
                    iframe.contentWindow.postMessage('{"event":"command","func":"stopVideo","args":""}', '*');
                    iframe.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
                } catch (e) {}

                var src = iframe.getAttribute('src');
                if (src && (src.includes('youtube') || src.includes('youtu.be') || src.includes('vimeo') || iframe.closest('.embed-responsive'))) {
                    iframe.setAttribute('src', '');
                    iframe.setAttribute('src', src);
                }
            });
        }

        // Event listener via jQuery jika tersedia
        if (typeof jQuery !== 'undefined') {
            jQuery(document).on('hidden.bs.modal', '.modal', function () {
                stopMediaInModal(this);
            });
        }

        // Event listener native DOM
        document.addEventListener('hidden.bs.modal', function (event) {
            stopMediaInModal(event.target);
        });
    </script>

    @if (!empty($newBadges) && $newBadges->isNotEmpty())
        <script>
            // Notifikasi badge baru (judul dan deskripsi disisipkan sebagai teks, bukan HTML)
            document.addEventListener('DOMContentLoaded', function () {
                var badges = {{ \Illuminate\Support\Js::from($newBadges) }};
                var box = document.createElement('div');
                badges.forEach(function (badge) {
                    var item = document.createElement('div');
                    item.style.margin = '12px 0';
                    var image = document.createElement('img');
                    image.src = badge.icon;
                    image.alt = '';
                    image.style.cssText = 'width:72px;height:72px;object-fit:cover;border-radius:50%;';
                    var title = document.createElement('div');
                    title.style.fontWeight = '600';
                    title.textContent = badge.title;
                    var description = document.createElement('div');
                    description.className = 'text-muted small';
                    description.textContent = badge.description;
                    item.appendChild(image);
                    item.appendChild(title);
                    item.appendChild(description);
                    box.appendChild(item);
                });
                Swal.fire({
                    title: 'Selamat! Kamu mendapat badge baru',
                    html: box,
                    confirmButtonText: 'Lihat Badge Saya'
                }).then(function (result) {
                    if (result.isConfirmed) { window.location.href = {{ \Illuminate\Support\Js::from(route('student.badges.index')) }}; }
                });
            });
        </script>
    @endif

    <script src="{{ asset('js/ruang-studi-dashboard.js') }}"></script>
    @stack('scripts')
    <script src="{{ asset('js/script.js') }}"></script>
</body>

</html>
