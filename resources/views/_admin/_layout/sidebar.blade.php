    @php
        $page = Request::segment(2);
    @endphp

    <style>
        /* Menambahkan buletan di depan <li> */
        .with-bullets li {
            list-style-type: disc;
            /* Tipe buletan */
            margin-left: 30px !important;
            /* Supaya ada jarak dari tepi kiri */
        }

        .with-bullets li a {
            text-decoration: none;
            /* Hilangkan underline dari link */
            color: inherit;
            /* Sesuaikan warna dengan default */
        }

        .with-bullets li a:hover {
            color: #6610f2;
            /* Warna ketika di-hover */
        }

        .collapse.show {
            display: block;
            /* Pastikan dropdown muncul ketika class 'show' ditambahkan */
        }

        .collapse {
            padding: 2px !important
        }

        .sidebar-link.active {
            font-weight: bold;
            /* Membuat teks menjadi bold */
            color: #6610f2;
            /* Sesuaikan warna jika diperlukan */
        }

        .nav-item {
            padding-bottom: 3px !important;
        }

        .nav-item a.active {
            font-weight: 800;
            color: var(--bs-primary)
        }

        .mm-collapse:not(.mm-show) {
            display: none;
        }

        .sidebar-item {
            margin-bottom: 10px !important
        }

        .sidebar-link svg {
            font-size: 15px !important
        }
    </style>

    <style>
        aside.left-sidebar {
            background: linear-gradient(269.48deg,
                    rgba(255, 255, 255, 1) 0%,
                    rgb(240, 239, 255) 100%);
        }
    </style>

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
            {{-- <li>
                <span class="sidebar-divider lg"></span>
            </li> --}}

        </ul>
    </nav>
