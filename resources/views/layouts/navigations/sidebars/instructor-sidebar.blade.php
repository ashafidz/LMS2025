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
                                                <a href="#!"><i class="ti-settings"></i>Settings</a>
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
                                    {{-- sidebar dashboard --}}
                                    <li class="{{ Request::routeIs('instructor.dashboard') ? 'active' : '' }}">
                                        <a href="{{ route('instructor.dashboard') }}" class="waves-effect waves-dark">
                                            <span class="pcoded-micon"><i class="ti-home"></i><b>D</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.dash.main">Dashboard</span>
                                            <span class="pcoded-mcaret"></span>
                                        </a>
                                    </li>
                                    {{-- end sidebar dashboard --}}

                                    {{-- sidebar manajemen course --}}
                                    <li class="{{ Request::routeIs('instructor.courses.index') ? 'active' : '' }}">
                                        <a href="{{ route('instructor.courses.index') }}" class="waves-effect waves-dark">
                                            <span class="pcoded-micon"><i class="ti-desktop"></i><b>D</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.dash.main">Manajemen Course</span>
                                            <span class="pcoded-mcaret"></span>
                                        </a>
                                    </li>
                                    {{-- end sidebar manajemen course --}}

                                    {{-- sidebar Bank Soal --}}
                                    <li class="{{ Request::routeIs('instructor.question-bank.topics.index') ? 'active' : '' }}">
                                        <a href="{{ route('instructor.question-bank.topics.index') }}" class="waves-effect waves-dark">
                                            <span class="pcoded-micon"><i class="fa fa-university"></i><b>FC</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.form-components.main">Bank Soal</span>
                                            <span class="pcoded-mcaret"></span>
                                        </a>
                                    </li>
                                    {{-- end sidebar banksoal --}}

                                    {{-- template sidebar menu 1 --}}
                                    <li class="">
                                        <a href="#" class="waves-effect waves-dark">
                                            <span class="pcoded-micon"><i class="ti-layers"></i><b>FC</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.form-components.main">Fitur Selanjutnya</span>
                                            <span class="pcoded-mcaret"></span>
                                        </a>
                                    </li>
                                    {{-- endtemplate sidebar menu 1 --}}
                                </ul>
                                {{-- start menu levels question bank --}}
                                <ul class="pcoded-item pcoded-left-item">
                                    <li class="pcoded-hasmenu">
                                        {{-- label menu level question bank --}}
                                        <a
                                            href="javascript:void(0)"
                                            class="waves-effect waves-dark"
                                        >
                                            <span class="pcoded-micon"
                                                ><i class="ti-direction-alt"></i
                                                ><b>M</b></span
                                            >
                                            <span
                                                class="pcoded-mtext"
                                                data-i18n="nav.menu-levels.main"
                                                >menu level</span
                                            >
                                            <span class="pcoded-mcaret"></span>
                                        </a>
                                        {{-- end label menu level question bank --}}
                                        <ul class="pcoded-submenu">
                                            <li class="">
                                                <a
                                                    href="javascript:void(0)"
                                                    class="waves-effect waves-dark"
                                                >
                                                    <span class="pcoded-micon"
                                                        ><i
                                                            class="ti-angle-right"
                                                        ></i
                                                    ></span>
                                                    <span
                                                        class="pcoded-mtext"
                                                        data-i18n="nav.menu-levels.menu-level-21"
                                                        >Materi</span
                                                    >
                                                    <span
                                                        class="pcoded-mcaret"
                                                    ></span>
                                                </a>
                                            </li>
                                            <li class="">
                                                <a
                                                    href="javascript:void(0)"
                                                    class="waves-effect waves-dark"
                                                >
                                                    <span class="pcoded-micon"
                                                        ><i
                                                            class="ti-angle-right"
                                                        ></i
                                                    ></span>
                                                    <span
                                                        class="pcoded-mtext"
                                                        data-i18n="nav.menu-levels.menu-level-21"
                                                        >Materi</span
                                                    >
                                                    <span
                                                        class="pcoded-mcaret"
                                                    ></span>
                                                </a>
                                            </li>
                                            {{-- <li class="pcoded-hasmenu">
                                                <a
                                                    href="javascript:void(0)"
                                                    class="waves-effect waves-dark"
                                                >
                                                    <span class="pcoded-micon"
                                                        ><i
                                                            class="ti-direction-alt"
                                                        ></i
                                                    ></span>
                                                    <span
                                                        class="pcoded-mtext"
                                                        data-i18n="nav.menu-levels.menu-level-22.main"
                                                        >Menu Level 2.2</span
                                                    >
                                                    <span
                                                        class="pcoded-mcaret"
                                                    ></span>
                                                </a>
                                                <ul class="pcoded-submenu">
                                                    <li class="">
                                                        <a
                                                            href="javascript:void(0)"
                                                            class="waves-effect waves-dark"
                                                        >
                                                            <span
                                                                class="pcoded-micon"
                                                                ><i
                                                                    class="ti-angle-right"
                                                                ></i
                                                            ></span>
                                                            <span
                                                                class="pcoded-mtext"
                                                                data-i18n="nav.menu-levels.menu-level-22.menu-level-31"
                                                                >Menu Level
                                                                3.1</span
                                                            >
                                                            <span
                                                                class="pcoded-mcaret"
                                                            ></span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </li>
                                            <li class="">
                                                <a
                                                    href="javascript:void(0)"
                                                    class="waves-effect waves-dark"
                                                >
                                                    <span class="pcoded-micon"
                                                        ><i
                                                            class="ti-angle-right"
                                                        ></i
                                                    ></span>
                                                    <span
                                                        class="pcoded-mtext"
                                                        data-i18n="nav.menu-levels.menu-level-23"
                                                        >Menu Level 2.3</span
                                                    >
                                                    <span
                                                        class="pcoded-mcaret"
                                                    ></span>
                                                </a>
                                            </li> --}}
                                        </ul>
                                    </li>
                                </ul>
                                {{-- end menu levels question bank --}}

                            </div>
                        </nav>
