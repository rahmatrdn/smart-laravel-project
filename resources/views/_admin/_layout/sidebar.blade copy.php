@php
    $page = Request::segment(2);
    $subPage = Request::segment(3);
@endphp

<nav class="sidebar-nav scroll-sidebar" data-simplebar="">
    <ul id="sidebarnav">

        <li class="nav-small-cap" style="color: #adadad">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.4rem" height="1.4rem" viewBox="0 0 24 24">
                <g fill="none" stroke="currentColor" stroke-width="0.95">
                    <path
                        d="M2 12.204c0-2.289 0-3.433.52-4.381c.518-.949 1.467-1.537 3.364-2.715l2-1.241C9.889 2.622 10.892 2 12 2s2.11.622 4.116 1.867l2 1.241c1.897 1.178 2.846 1.766 3.365 2.715S22 9.915 22 12.203v1.522c0 3.9 0 5.851-1.172 7.063S17.771 22 14 22h-4c-3.771 0-5.657 0-6.828-1.212S2 17.626 2 13.725z"
                        opacity="0.5" />
                    <path stroke-linecap="round" d="M12 15v3" />
                </g>
            </svg>
            <span class="hide-menu ms-1">BERANDA</span>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link {{ $page == '/' ? 'active' : '' }}" href="{{ base_url('') }}" aria-expanded="false"
                navigate>
                <span class="hide-menu">Dashboard</span>
            </a>
        </li>
        <li>
            <span class="sidebar-divider lg"></span>
        </li>
        <li class="nav-small-cap" style="color: #adadad">
            <svg xmlns="http://www.w3.org/2000/svg" width="1.4rem" height="1.4rem" viewBox="0 0 24 24">
                <g fill="none" stroke="currentColor" stroke-width="0.95">
                    <circle cx="9" cy="6" r="4" />
                    <path d="M12.5 4.341a3 3 0 1 1 0 3.318" opacity="0.5" />
                    <ellipse cx="9" cy="17" rx="7" ry="4" />
                    <path stroke-linecap="round" d="M18 14c1.754.385 3 1.359 3 2.5c0 1.03-1.014 1.923-2.5 2.37"
                        opacity="0.5" />
                </g>
            </svg>
            <span class="hide-menu ms-1">ANGGOTA</span>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link {{ $page == 'member' ? 'active' : '' }}" href="{{ base_url('member') }}"
                aria-expanded="false" navigate>
                <span class="hide-menu">Data Anggota</span>
            </a>
            <a class="sidebar-link {{ $page == 'member-category' ? 'active' : '' }}"
                href="{{ base_url('member-category') }}" aria-expanded="false" navigate>
                <span class="hide-menu">Kategori Anggota</span>
            </a>
        </li>
        <li class="sidebar-item">
            <a class="sidebar-link has-arrow" href="#" data-toggle="collapse" aria-expanded="false">
                <span class="hide-menu">Data Produk</span>
            </a>
            <ul class="collapse px-4 with-bullets">
                <li class="py-2"><a href="{{ base_url('product') }}"
                        class="{{ $page == 'product' ? 'active' : '' }}">Data Produk</a></li>
                <li class="py-2"><a href="{{ base_url('product_category') }}"
                        class="{{ $page == 'product_category' ? 'active' : '' }}">Data Kategori Produk</a></li>
            </ul>
        </li>
    </ul>
    {{-- <div
        class="unlimited-access d-flex align-items-center hide-menu bg-primary-subtle position-relative mb-0 mt-4 p-3 rounded">
        <div>
            <p class="text-dark mb-0"><b>Digilib {{ env('APP_VERSION') }}</b></p>
            <h6 class="fw-semibold fs-4 mb-0 text-primary">by Jinggolabs</h6>
        </div>
    </div> --}}
</nav>
<!-- End Sidebar navigation -->
