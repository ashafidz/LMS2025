<style>
        .custom-btn-hover:hover {
  color: #000000 !important; /* Change text to black on hover */
  /* You can also change the background or border here */
  background-color: #FFFFFF;
  border-color: #448aff;
  
  }
</style>


    <nav id="mainNavbar" class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                {{ config('app.name', 'Laravel') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav me-auto">
                    <!-- You can add other student navigation links here, e.g., My Courses -->
                </ul>
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('courses') }}">Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('about') }}">About</a>
                    </li>

                    @guest
                    <div class="d-block d-lg-none">
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary me-2">Log in</a>
                        <a href="{{ route('register') }}" class="btn bg-default bg-default-blue text-white custom-btn-hover">Register</a>
                    </div>
                    @endguest

                    <div class="d-block d-lg-none">
                    @auth
                        @if (Auth::user()->hasRole('superadmin'))
                            <a href="{{ route('superadmin.dashboard') }}" class="btn btn-primary btn-lg px-4 gap-3">Dashboard</a>
                        @elseif (Auth::user()->hasRole('admin'))
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-lg px-4 gap-3">Dashboard</a>
                        @elseif (Auth::user()->hasRole('instructor') && Auth::user()->instructorProfile?->application_status === 'approved')
                            <a href="{{ route('instructor.dashboard') }}" class="btn btn-primary btn-lg px-4 gap-3">Dashboard</a>
                        @elseif (Auth::user()->hasRole('student'))
                            <a href="{{ route('student.dashboard') }}" class="btn btn-primary btn-lg px-4 gap-3">Dashboard</a>
                        @endif
                    @endauth
                    </div>
                    

                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="d-none d-lg-block navbar-nav ms-auto mb-2 mb-lg-0 align-items-center">
                    @guest
                        <a href="{{ route('login') }}" class="btn btn-outline-secondary me-2">Log in</a>
                        <a href="{{ route('register') }}" class="btn bg-default bg-default-blue text-white custom-btn-hover">Register</a>
                    @endguest

                    @auth
                        @if (Auth::user()->hasRole('superadmin'))
                            <a href="{{ route('superadmin.dashboard') }}" class="btn btn-primary btn-lg px-4 gap-3">Dashboard</a>
                        @elseif (Auth::user()->hasRole('admin'))
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-primary btn-lg px-4 gap-3">Dashboard</a>
                        @elseif (Auth::user()->hasRole('instructor') && Auth::user()->instructorProfile?->application_status === 'approved')
                            <a href="{{ route('instructor.dashboard') }}" class="btn btn-primary btn-lg px-4 gap-3">Dashboard</a>
                        @elseif (Auth::user()->hasRole('student'))
                            <a href="{{ route('student.dashboard') }}" class="btn btn-primary btn-lg px-4 gap-3">Dashboard</a>
                        @endif
                    @endauth
                </ul>
            </div>
        </div>
    </nav>


<script>
    window.addEventListener('DOMContentLoaded', () => {
        const navbar = document.getElementById('mainNavbar');
        const navbarOffsetTop = navbar.offsetTop;

        window.addEventListener('scroll', () => {
            if (window.scrollY > navbarOffsetTop) {
                navbar.classList.add('fixed-top');
                document.body.style.paddingTop = navbar.offsetHeight + 'px'; // prevent jump
            } else {
                navbar.classList.remove('fixed-top');
                document.body.style.paddingTop = '0';
            }
        });
    });
</script>