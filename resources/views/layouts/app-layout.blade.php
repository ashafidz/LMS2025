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
    <!-- themify icon -->
    <link rel="stylesheet" type="text/css" href="{{ asset('icon/themify-icons/themify-icons.css') }}" />
    <!-- Font Awesome -->
    <link rel="stylesheet" type="text/css" href="{{ asset('icon/font-awesome/css/font-awesome.min.css') }}" />
    <!-- scrollbar.css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/jquery.mCustomScrollbar.css') }}" />
    <!-- am chart export.css -->
    <link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css"
        media="all" />
    <!-- Style.css -->
    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}" />
    {{-- =============================================== --}}
</head>

<body>




    <!-- Pre-loader start -->
    <div class="theme-loader">
        <div class="loader-track">
            <div class="preloader-wrapper">
                <div class="spinner-layer spinner-blue">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div>
                    <div class="gap-patch">
                        <div class="circle"></div>
                    </div>
                    <div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>
                <div class="spinner-layer spinner-red">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div>
                    <div class="gap-patch">
                        <div class="circle"></div>
                    </div>
                    <div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>

                <div class="spinner-layer spinner-yellow">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div>
                    <div class="gap-patch">
                        <div class="circle"></div>
                    </div>
                    <div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>

                <div class="spinner-layer spinner-green">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div>
                    <div class="gap-patch">
                        <div class="circle"></div>
                    </div>
                    <div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Pre-loader end -->
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
                @else
                    {{-- Optional: Include a navbar for guests/unauthenticated users --}}
                    @include('layouts.navigations.navbars.guest-navbar')
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



















    <!-- Required Jquery -->
    <script type="text/javascript" src="{{ asset('js/jquery/jquery.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jquery-ui/jquery-ui.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/popper.js/popper.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/bootstrap/js/bootstrap.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('pages/widget/excanvas.js') }}"></script>
    <!-- waves js -->
    <script src="{{ asset('pages/waves/js/waves.min.js') }}"></script>
    <!-- jquery slimscroll js -->
    <script type="text/javascript" src="{{ asset('js/jquery-slimscroll/jquery.slimscroll.js') }} "></script>
    <!-- modernizr js -->
    <script type="text/javascript" src="{{ asset('js/modernizr/modernizr.js') }} "></script>
    <!-- slimscroll js -->
    <script type="text/javascript" src="{{ asset('js/SmoothScroll.js') }}"></script>
    <script src="{{ asset('js/jquery.mCustomScrollbar.concat.min.js') }} "></script>
    <!-- Chart js -->
    <script type="text/javascript" src="{{ asset('js/chart.js/Chart.js') }}"></script>
    <!-- amchart js -->
    <script src="https://www.amcharts.com/lib/3/amcharts.js"></script>
    <script src="{{ asset('pages/widget/amchart/gauge.js') }}"></script>
    <script src="{{ asset('pages/widget/amchart/serial.js') }}"></script>
    <script src="{{ asset('pages/widget/amchart/light.js') }}"></script>
    <script src="{{ asset('pages/widget/amchart/pie.min.js') }}"></script>
    <script src="https://www.amcharts.com/lib/3/plugins/export/export.min.js"></script>
    <!-- menu js -->
    <script src="{{ asset('js/pcoded.min.js') }}"></script>
    <script src="{{ asset('js/vertical-layout.min.js') }} "></script>
    <!-- custom js -->
    <script type="text/javascript" src="{{ asset('pages/dashboard/custom-dashboard.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/script.js') }}"></script>
</body>

</html>
