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
                                                @if (Auth::user()->hasRole('student') && session('active_role') == 'instructor')
                                                    <a href="{{ route('role.switch', 'student') }}">
                                                        👨‍🎓 Switch Role Siswa
                                                    </a>
                                                @endif
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
                                            <span class="pcoded-micon"><i
                                                    class="fa fa-tachometer-alt"></i><b>D</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.dash.main">Dashboard</span>
                                            <span class="pcoded-mcaret"></span>
                                        </a>
                                    </li>
                                    {{-- end sidebar dashboard --}}

                                    {{-- sidebar manajemen course --}}
                                    <li class="{{ Request::routeIs('instructor.courses.index') ? 'active' : '' }}">
                                        <a href="{{ route('instructor.courses.index') }}"
                                            class="waves-effect waves-dark">
                                            <span class="pcoded-micon"><i class="ti-desktop"></i><b>D</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.dash.main">Manajemen Course</span>
                                            <span class="pcoded-mcaret"></span>
                                        </a>
                                    </li>
                                    {{-- end sidebar manajemen course --}}

                                    {{-- sidebar Bank Soal --}}
                                    <li
                                        class="{{ Request::routeIs('instructor.question-bank.topics.index') ? 'active' : '' }}">
                                        <a href="{{ route('instructor.question-bank.topics.index') }}"
                                            class="waves-effect waves-dark">
                                            <span class="pcoded-micon"><i class="fa fa-university"></i><b>FC</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.form-components.main">Bank
                                                Soal</span>
                                            <span class="pcoded-mcaret"></span>
                                        </a>
                                    </li>
                                    {{-- end sidebar banksoal --}}
                                </ul>

                                </ul>
                                </li>
                                </ul>
                                {{-- end menu levels question bank --}}

                            </div>
                        </nav>
