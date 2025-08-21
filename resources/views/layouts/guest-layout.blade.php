<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-t">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $SiteSetting->site_name }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .auth-card {
            max-width: 450px;
            margin-top: 50px;
        }

        .bg-default-blue {
        background-color: #448aff;
        }

        .bg-default-white {
            background-color: #eff5f7;
        }

        .bg-secondary-dark-blue {
            background-color: #3f629d;
        }

            .custom-btn-hover:hover {
  color: #000000 !important; /* Change text to black on hover */
  /* You can also change the background or border here */
  background-color: #FFFFFF;
  border-color: #448aff;}
    </style>


    <style>
        html, body {
            height: 100%;
        }
        body {
            /* Set up the body as a vertical flex container */
            display: flex;
            flex-direction: column;
            background-color: #f8f9fa;
        }
        main {
            /* This is the key: it makes the main element grow to fill remaining space */
            flex-grow: 1;
        }
    </style>
</head>
<body class="font-sans text-gray-900 antialiased">




    {{-- <div class="container">

    </div> --}}
        {{-- <main class="container py-4">
            @yield('content')
        </main> --}}
        @include('layouts.navigations.navbars.guest-navbar')
        
        <main class="d-flex">
            @yield('content')
        </main>
        
        @stack('scripts')
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

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
</body>
</html>
