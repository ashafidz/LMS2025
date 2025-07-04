<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-t">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

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
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/">
                {{ config('app.name', 'Laravel') }}
            </a>
            <div class="d-flex">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn btn-outline-secondary">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary me-2">Log in</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="btn btn-dark">Register</a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </nav>



    {{-- <div class="container">

    </div> --}}
        {{-- <main class="container py-4">
            @yield('content')
        </main> --}}
        <main class="d-flex">
            @yield('content')
        </main>
        
        @stack('scripts')
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
