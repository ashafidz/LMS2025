                        <nav class="pcoded-navbar">
                            <div class="sidebar_toggle">
                                <a href="#"><i class="icon-close icons"></i></a>
                            </div>
                            <div class="pcoded-inner-navbar main-menu">
                                <div class="">
                                    <div class="main-menu-header">
                                        <img class="rounded-circle mr-4" style="width: 80px; height: 80px; border: 3px;"
                                            src="{{ Auth::user()->profile_picture_url 
        ? asset('storage/' . ltrim(Auth::user()->profile_picture_url, '/')) 
        : 'https://placehold.co/80x80/EBF4FF/767676?text=SA' }}"
                                            alt="{{ Auth::user()->name }}" />
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
                                    <li class="{{ Request::routeIs('superadmin.dashboard') ? 'active' : '' }}">
                                        <a href="{{ route('superadmin.dashboard') }}" class="waves-effect waves-dark">
                                            <span class="pcoded-micon"><i class="ti-home"></i><b>D</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.dash.main">Dashboard</span>
                                            <span class="pcoded-mcaret"></span>
                                        </a>
                                    </li>
                                    <li class="{{ Request::routeIs('superadmin.publication.index') ? 'active' : '' }}">
                                        <a href="{{ route('superadmin.publication.index') }}" class="waves-effect waves-dark">
                                            <span class="pcoded-micon"><i class="ti-home"></i><b>D</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.dash.main">Publikasi</span>
                                            <span class="pcoded-mcaret"></span>
                                        </a>
                                    </li>
                                    <li class="{{ Request::routeIs('superadmin.coupons.index') ? 'active' : '' }}">
                                        <a href="{{ route('superadmin.coupons.index') }}" class="waves-effect waves-dark">
                                            <span class="pcoded-micon"><i class="ti-home"></i><b>D</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.dash.main">Kupon</span>
                                            <span class="pcoded-mcaret"></span>
                                        </a>
                                    </li>
                                </ul>

                                {{-- menu level managemen user --}}
                                <ul class="pcoded-item pcoded-left-item">
                                    <li class="pcoded-hasmenu">
                                        <a href="javascript:void(0)" class="waves-effect waves-dark">
                                            <span class="pcoded-micon"><i class="ti-direction-alt"></i><b>M</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.menu-levels.main">Manajemen User</span>
                                            <span class="pcoded-mcaret"></span>
                                        </a>
                                        <ul class="pcoded-submenu">

                                            {{-- menu level manajemen user admin --}}
                                            <li class="pcoded-hasmenu">
                                                <a href="javascript:void(0)" class="waves-effect waves-dark">
                                                    <span class="pcoded-micon"><i class="ti-direction-alt"></i></span>
                                                    <span class="pcoded-mtext"
                                                        data-i18n="nav.menu-levels.menu-level-22.main">User Admin</span>
                                                    <span class="pcoded-mcaret"></span>
                                                </a>
                                                <ul class="pcoded-submenu">
                                                    <li class="">
                                                        <a href="javascript:void(0)" class="waves-effect waves-dark">
                                                            <span class="pcoded-micon"><i
                                                                    class="ti-angle-right"></i></span>
                                                            <span class="pcoded-mtext"
                                                                data-i18n="nav.menu-levels.menu-level-22.menu-level-31">Manajemen User admin 1</span>
                                                            <span class="pcoded-mcaret"></span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </li>
                                            {{-- end menu level manajemen user admin --}}
                                            {{-- menu level manajemen user instructor --}}
                                            <li class="pcoded-hasmenu">
                                                <a href="javascript:void(0)" class="waves-effect waves-dark">
                                                    <span class="pcoded-micon"><i class="ti-direction-alt"></i></span>
                                                    <span class="pcoded-mtext"
                                                        data-i18n="nav.menu-levels.menu-level-22.main">User Instructor</span>
                                                    <span class="pcoded-mcaret"></span>
                                                </a>
                                                <ul class="pcoded-submenu">
                                                    <li class="{{ Request::routeIs('superadmin.instructor-application.index') ? 'active' : '' }}">
                                                        <a href="{{ route('superadmin.instructor-application.index') }}" class="waves-effect waves-dark">
                                                            <span class="pcoded-micon"><i
                                                                    class="ti-angle-right"></i></span>
                                                            <span class="pcoded-mtext"
                                                                data-i18n="nav.menu-levels.menu-level-22.menu-level-31">Status Instructor</span>
                                                            <span class="pcoded-mcaret"></span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </li>
                                            {{-- end menu level manajemen user instructor --}}

                                            {{-- menu level manajemen user student --}}
                                            <li class="pcoded-hasmenu">
                                                <a href="javascript:void(0)" class="waves-effect waves-dark">
                                                    <span class="pcoded-micon"><i class="ti-direction-alt"></i></span>
                                                    <span class="pcoded-mtext"
                                                        data-i18n="nav.menu-levels.menu-level-22.main">User Student</span>
                                                    <span class="pcoded-mcaret"></span>
                                                </a>
                                                <ul class="pcoded-submenu">
                                                    <li class="">
                                                        <a href="{{ route('superadmin.manajemen-student.index') }}" class="waves-effect waves-dark">
                                                            <span class="pcoded-micon"><i
                                                                    class="ti-angle-right"></i></span>
                                                            <span class="pcoded-mtext"
                                                                data-i18n="nav.menu-levels.menu-level-22.menu-level-31">Status Student</span>
                                                            <span class="pcoded-mcaret"></span>
                                                        </a>
                                                    </li>
                                                </ul>
                                                <ul class="pcoded-submenu">
                                                    <li class="">
                                                        <a href="{{ route('superadmin.course-enrollments.index') }}" class="waves-effect waves-dark">
                                                            <span class="pcoded-micon"><i
                                                                    class="ti-angle-right"></i></span>
                                                            <span class="pcoded-mtext"
                                                                data-i18n="nav.menu-levels.menu-level-22.menu-level-31">Enrollments</span>
                                                            <span class="pcoded-mcaret"></span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </li>
                                            {{-- end menu level manajemen user student --}}
                                            
                                            <li class="">
                                                <a href="javascript:void(0)" class="waves-effect waves-dark">
                                                    <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                    <span class="pcoded-mtext"
                                                        data-i18n="nav.menu-levels.menu-level-23">Menu Level 2.3</span>
                                                    <span class="pcoded-mcaret"></span>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                                {{-- end menu level managemen user --}}

                                {{-- menu level managemen site --}}
                                <ul class="pcoded-item pcoded-left-item">
                                    <li class="pcoded-hasmenu">
                                        <a href="javascript:void(0)" class="waves-effect waves-dark">
                                            <span class="pcoded-micon"><i class="ti-direction-alt"></i><b>M</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.menu-levels.main">Manajemen Site</span>
                                            <span class="pcoded-mcaret"></span>
                                        </a>
                                        <ul class="pcoded-submenu">
                                            {{-- menu level manajemen general --}}
                                            <li class="pcoded-hasmenu">
                                                <a href="javascript:void(0)" class="waves-effect waves-dark">
                                                    <span class="pcoded-micon"><i class="ti-direction-alt"></i></span>
                                                    <span class="pcoded-mtext"
                                                        data-i18n="nav.menu-levels.menu-level-22.main">General</span>
                                                    <span class="pcoded-mcaret"></span>
                                                </a>
                                                <ul class="pcoded-submenu">
                                                    <li class="{{ Request::routeIs('superadmin.settings.edit') ? 'active' : '' }}">
                                                        <a href="{{ route('superadmin.settings.edit') }}" class="waves-effect waves-dark">
                                                            <span class="pcoded-micon"><i
                                                                    class="ti-angle-right"></i></span>
                                                            <span class="pcoded-mtext"
                                                                data-i18n="nav.menu-levels.menu-level-22.menu-level-31">Setting</span>
                                                            <span class="pcoded-mcaret"></span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </li>
                                            {{-- endmenu level manajemen general --}}

                                            {{-- menu level manajemen general --}}
                                            <li class="pcoded-hasmenu">
                                                <a href="javascript:void(0)" class="waves-effect waves-dark">
                                                    <span class="pcoded-micon"><i class="ti-direction-alt"></i></span>
                                                    <span class="pcoded-mtext"
                                                        data-i18n="nav.menu-levels.menu-level-22.main">Skala Likert</span>
                                                    <span class="pcoded-mcaret"></span>
                                                </a>
                                                <ul class="pcoded-submenu">
                                                    <li class="{{ Request::routeIs('superadmin.likert-questions.index') ? 'active' : '' }}">
                                                        <a href="{{ route('superadmin.likert-questions.index') }}" class="waves-effect waves-dark">
                                                            <span class="pcoded-micon"><i
                                                                    class="ti-angle-right"></i></span>
                                                            <span class="pcoded-mtext"
                                                                data-i18n="nav.menu-levels.menu-level-22.menu-level-31">Setting</span>
                                                            <span class="pcoded-mcaret"></span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </li>
                                            {{-- endmenu level manajemen general --}}

                                            {{-- menu level manajemen user student --}}
                                            <li class="pcoded-hasmenu">
                                                <a href="javascript:void(0)" class="waves-effect waves-dark">
                                                    <span class="pcoded-micon"><i class="ti-direction-alt"></i></span>
                                                    <span class="pcoded-mtext"
                                                        data-i18n="nav.menu-levels.menu-level-22.main">Badge</span>
                                                    <span class="pcoded-mcaret"></span>
                                                </a>
                                                <ul class="pcoded-submenu">
                                                    <li class="">
                                                        <a href="{{ route('superadmin.badges.index') }}" class="waves-effect waves-dark">
                                                            <span class="pcoded-micon"><i
                                                                    class="ti-angle-right"></i></span>
                                                            <span class="pcoded-mtext"
                                                                data-i18n="nav.menu-levels.menu-level-22.menu-level-31">Setting</span>
                                                            <span class="pcoded-mcaret"></span>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </li>
                                            {{-- end menu level manajemen user student --}}
                                            
                                            <li class="">
                                                <a href="javascript:void(0)" class="waves-effect waves-dark">
                                                    <span class="pcoded-micon"><i class="ti-angle-right"></i></span>
                                                    <span class="pcoded-mtext"
                                                        data-i18n="nav.menu-levels.menu-level-23">Menu Level 2.3</span>
                                                    <span class="pcoded-mcaret"></span>
                                                </a>
                                            </li>
                                        </ul>
                                    </li>
                                </ul>
                                {{-- end menu level managemen site --}}

                            </div>
                        </nav>
