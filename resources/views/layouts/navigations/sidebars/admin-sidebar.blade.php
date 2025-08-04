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
                                    <li class="{{ Request::routeIs('admin.dashboard') ? 'active' : '' }}">
                                        <a href="{{ route('admin.dashboard') }}" class="waves-effect waves-dark">
                                            <span class="pcoded-micon"><i class="ti-home"></i><b>D</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.dash.main">Dashboard</span>
                                            <span class="pcoded-mcaret"></span>
                                        </a>
                                    </li>
                                    <li class="{{ Request::routeIs('admin.publication.index') ? 'active' : '' }}">
                                        <a href="{{ route('admin.publication.index') }}" class="waves-effect waves-dark">
                                            <span class="pcoded-micon"><i class="ti-file"></i><b>FC</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.form-components.main">Publikasi</span>
                                            <span class="pcoded-mcaret"></span>
                                        </a>
                                    </li>
                                    <li class="{{ Request::routeIs('admin.coupons.index') ? 'active' : '' }}">
                                        <a href="{{ route('admin.coupons.index') }}" class="waves-effect waves-dark">
                                            <span class="pcoded-micon"><i class="ti-ticket"></i><b>FC</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.form-components.main">Kupon</span>
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
                                            {{-- end menu level manajemen user admin --}}
                                            {{-- menu level manajemen user instructor --}}
                                            <li class="pcoded-hasmenu">
                                                <a href="javascript:void(0)" class="waves-effect waves-dark">
                                                    <span class="pcoded-micon"><i class="ti-id-badge"></i></span>
                                                    <span class="pcoded-mtext"
                                                        data-i18n="nav.menu-levels.menu-level-22.main">User Instructor</span>
                                                    <span class="pcoded-mcaret"></span>
                                                </a>
                                                <ul class="pcoded-submenu">
                                                    <li class="{{ Request::routeIs('admin.instructor-application.index') ? 'active' : '' }}">
                                                        <a href="{{ route('admin.instructor-application.index') }}" class="waves-effect waves-dark">
                                                            <span class="pcoded-micon"><i
                                                                    class="ti-flag-alt"></i></span>
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
                                                        <a href="{{ route('admin.manajemen-student.index') }}" class="waves-effect waves-dark">
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
                                                        <a href="{{ route('admin.course-enrollments.index') }}" class="waves-effect waves-dark">
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
                                            {{-- menu level manajemen Skala Likert --}}
                                            <li class="pcoded-hasmenu">
                                                <a href="javascript:void(0)" class="waves-effect waves-dark">
                                                    <span class="pcoded-micon"><i class="ti-direction-alt"></i></span>
                                                    <span class="pcoded-mtext"
                                                        data-i18n="nav.menu-levels.menu-level-22.main">Skala Likert</span>
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
                                            {{-- endmenu level manajemen Skala Likert --}}

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
