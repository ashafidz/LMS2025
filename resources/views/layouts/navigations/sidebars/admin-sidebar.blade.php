                        <nav class="pcoded-navbar">
                            <div class="sidebar_toggle">
                                <a href="#"><i class="icon-close icons"></i></a>
                            </div>
                            <div class="pcoded-inner-navbar main-menu">
                                <div class="">
                                    <div class="main-menu-header">
                                        <img class="rounded-circle mr-4" style="width: 80px; height: 80px; border: 3px;"
                                            src="{{ asset(Auth::user()->profile_picture_url ?? 'https://placehold.co/32x32/EBF4FF/767676?text=SA') }}"
                                            alt="{{ Auth::user()->name }}" />
                                        <div class="user-details">
                                            <span id="more-details">{{ Auth::user()->name }}<i
                                                    class="fa fa-caret-down"></i></span>
                                        </div>
                                    </div>

                                    <div class="main-menu-content">
                                        <ul>
                                            <li class="more-details">
                                                <a href="{{ route('user.profile.index') }}"><i class="ti-user"></i>View
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
                                            <span class="pcoded-micon"><i class="ti-layers"></i><b>FC</b></span>
                                            <span class="pcoded-mtext" data-i18n="nav.form-components.main">Publikasi</span>
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
                                                    <span class="pcoded-micon"><i class="ti-direction-alt"></i></span>
                                                    <span class="pcoded-mtext"
                                                        data-i18n="nav.menu-levels.menu-level-22.main">User Instructor</span>
                                                    <span class="pcoded-mcaret"></span>
                                                </a>
                                                <ul class="pcoded-submenu">
                                                    <li class="{{ Request::routeIs('admin.instructor-application.index') ? 'active' : '' }}">
                                                        <a href="{{ route('admin.instructor-application.index') }}" class="waves-effect waves-dark">
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
                                                        <a href="{{ route('admin.manajemen-student.index') }}" class="waves-effect waves-dark">
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
                                {{-- end menu level managemen user --}}

                            </div>
                        </nav>
