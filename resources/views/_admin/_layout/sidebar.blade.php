    @php
        $page = Request::segment(2);
    @endphp

    <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
        <ul id="sidebarnav">
            <li class="nav-small-cap mb-3 mt-4" style="color: #adadad">
                <span class="hide-menu ms-1">MENU APLIKASI</span>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link {{ $page == '/' ? 'active' : '' }}" href="{{ base_url('') }}" aria-expanded="false"
                    navigate>
                    @include('_admin._layout.icons.dashboard')
                    <span class="hide-menu">Dashboard</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link has-arrow {{ in_array($page, ['member', 'member-category']) ? 'active' : '' }}"
                    href="#" data-toggle="collapse"
                    aria-expanded="{{ in_array($page, ['member']) ? 'true' : 'false' }}">
                    @include('_admin._layout.icons.member')

                    <span class="hide-menu">Anggota</span>
                </a>
                <ul
                    class="collapse with-bullets show mm-collapse {{ in_array($page, ['member', 'member-category']) ? 'show mm-collapse mm-show' : '' }}">
                    <li class="py-2 nav-item">
                        <a href="{{ base_url('member') }}" navigate class="{{ $page == 'member' ? 'active' : '' }}">
                            <p class="mb-0">Data Angota</p>
                        </a>
                    </li>
                    <li class="py-2 nav-item {{ $page == 'member-category' ? 'active' : '' }}">
                        <a href="{{ base_url('member-category') }}" navigate class="{{ $page == 'member-category' ? 'active' : '' }}">
                            <p class="mb-0">Data Kategori Anggota</p>
                        </a>
                    </li>
                </ul>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link {{ $page == 'user' ? 'active' : '' }}" href="{{ base_url('user') }}" aria-expanded="false"
                    navigate>
                    @include('_admin._layout.icons.users')
                    <span class="hide-menu">Pengguna Aplikasi</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a class="sidebar-link {{ $page == 'setting' ? 'active' : '' }}" href="{{ base_url('setting/general') }}" aria-expanded="false"
                    navigate>
                    @include('_admin._layout.icons.setting')
                    <span class="hide-menu">Pengaturan</span>
                </a>
            </li>
            {{-- <li>
                <span class="sidebar-divider lg"></span>
            </li> --}}

        </ul>
    </nav>
