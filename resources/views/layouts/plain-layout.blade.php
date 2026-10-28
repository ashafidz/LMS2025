@php
    $siteName = ($siteSettings ?? null)?->site_name ?: config('app.name');
@endphp
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex">
    <title>@hasSection('title')@yield('title') | @endif{{ $siteName }}</title>

    <link href="{{ asset('favicon.svg') }}" rel="icon" type="image/svg+xml">

    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Literata:ital,opsz,wght@0,7..72,400;0,7..72,500;0,7..72,700;0,7..72,800;1,7..72,400&display=swap" rel="stylesheet">

    <link href="{{ asset('vendor/bootstrap-icons/bootstrap-icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/ruang-studi.css') }}" rel="stylesheet">
    @stack('styles')
</head>

<body class="rs-body rs-plain">
    <a class="rs-skip" href="#main">Langsung ke isi</a>

    <header class="rs-plain__head">
        <div class="rs-wrap">
            <a href="{{ route('home') }}" class="rs-logo">{{ $siteName }}</a>
            <a href="{{ route('home') }}" class="rs-link">Kembali ke beranda</a>
        </div>
    </header>

    <main id="main" class="rs-plain__main">
        <div class="rs-wrap">
            @yield('content')
        </div>
    </main>

    <footer class="rs-plain__foot">
        <div class="rs-wrap">
            <p>&copy; {{ date('Y') }} {{ $siteName }}</p>
        </div>
    </footer>

    <script src="{{ asset('js/ruang-studi.js') }}"></script>
    @stack('scripts')
</body>

</html>
