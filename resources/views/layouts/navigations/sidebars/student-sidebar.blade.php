                        <nav class="pcoded-navbar">
                            <div class="sidebar_toggle">
                                <a href="#"><i class="icon-close icons"></i></a>
                            </div>
                            <div class="pcoded-inner-navbar main-menu">
                                <div class="">
                                    <div class="main-menu-header">
                                        {{-- <img class="rounded-circle mr-4" style="width: 80px; height: 80px; border: 3px;"
                                            src="{{ Auth::user()->profile_picture_url 
        ? asset('storage/' . ltrim(Auth::user()->profile_picture_url, '/')) 
        : 'https://placehold.co/80x80/EBF4FF/767676?text=SA' }}"
                                            alt="{{ Auth::user()->name }}" /> --}}
                                        <div class="user-details">
                                            <span id="more-details">{{ Auth::user()->name }}<i
                                                    class="fa fa-caret-down"></i></span>
                                        </div>
                                    </div>

                                    <div class="main-menu-content">
                                        <ul>
                                            <li class="more-details">
                                                <a href="{{ route('home') }}"><i class="ti-home"></i>Home</a>
                                                <a href="{{ route('user.profile.index') }}"><i class="ti-user"></i>
                                                    Profile</a>
                                                <a href="{{ route('student.cart.index') }}"><i class="ti-shopping-cart"></i>
                                                    Keranjang</a>
                                                <a href="{{ route('student.badges.index') }}"><i class="ti-medall"></i>Badgeku</a>
                                                <a href="{{ route('logout') }}"
                                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                                                        class="ti-layout-sidebar-left"></i>Logout</a>
                                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                                    style="display: none;">
                                                    @csrf
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="pcoded-navigation-label" data-i18n="nav.category.navigation">
                                    Menu Sidebar
                                </div>
                                <ul class="pcoded-item pcoded-left-item">
                                    {{-- <li class="{{ Request::routeIs('student.dashboard') ? 'active' : '' }}">
                                        <a href="{{ route('student.dashboard') }}" class="waves-effect waves-dark">
                                            <span class="pcoded-micon"><i class="ti-home"></i><b>D</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.dash.main">Dashboard</span>
                                            <span class="pcoded-mcaret"></span>
                                        </a>
                                    </li> --}}
                                    <li class="{{ Request::routeIs('student.my_courses') ? 'active' : '' }}">
                                        <a href="{{ route('student.my_courses') }}" class="waves-effect waves-dark">
                                            <span class="pcoded-micon"><i class="ti-agenda"></i><b>D</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.dash.main">Kursus Saya</span>
                                            <span class="pcoded-mcaret"></span>
                                        </a>
                                    </li>
                                    <li class="{{ Request::routeIs('student.points.index') ? 'active' : '' }}">
                                        <a href="{{ route('student.points.index') }}" class="waves-effect waves-dark">
                                            <span class="pcoded-micon"><i class="ti-crown"></i><b>D</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.dash.main">Poin & Diamond</span>
                                            <span class="pcoded-mcaret"></span>
                                        </a>
                                    </li>
                                    {{-- <li class="{{ Request::routeIs('student.diamonds.index') ? 'active' : '' }}">
                                        <a href="{{ route('student.diamonds.index') }}" class="waves-effect waves-dark">
                                            <span class="pcoded-micon"><i class="ti-gift"></i><b>D</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.dash.main">Diamond Saya</span>
                                            <span class="pcoded-mcaret"></span>
                                        </a>
                                    </li> --}}
                                    <li class="{{ Request::routeIs('student.reviews.index') ? 'active' : '' }}">
                                        <a href="{{ route('student.reviews.index') }}" class="waves-effect waves-dark">
                                            <span class="pcoded-micon"><i class="ti-comment"></i><b>D</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.dash.main">Feedback</span>
                                            <span class="pcoded-mcaret"></span>
                                        </a>
                                    </li>
                                    <li class="{{ Request::routeIs('student.transactions.index') ? 'active' : '' }}">
                                        <a href="{{ route('student.transactions.index') }}" class="waves-effect waves-dark">
                                            <span class="pcoded-micon"><i class="ti-receipt"></i><b>D</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.dash.main">Riwayat Transaksi</span>
                                            <span class="pcoded-mcaret"></span>
                                        </a>
                                    </li>
                                    <li class="">
                                        <a href="#" class="waves-effect waves-dark">
                                            <span class="pcoded-micon"><i class="ti-layers"></i><b>FC</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.form-components.main">Fitur Selanjutnya</span>
                                            <span class="pcoded-mcaret"></span>
                                        </a>
                                    </li>
                                </ul>

                            </div>
                        </nav>
