<!doctype html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @include('_admin._layout.favicon')

    <title>{{ env('APP_NAME') }}</title>

    <link rel="stylesheet" href="{{ url('admin-ui') }}/assets/css/style.css?v={{ env('APP_VERSION') }}" />
    <link rel="stylesheet" href="{{ url('admin-ui') }}/assets/css/custom.css?v={{ env('APP_VERSION') }}" />
    <link rel="stylesheet" href="{{ url('admin-ui') }}/assets/css/sidebar.css?v={{ env('APP_VERSION') }}" />
    <link rel="stylesheet" href="{{ url('admin-ui') }}/assets/css/dark.css?v={{ env('APP_VERSION') }}" />

    {{-- External CSS Libraries --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/metisMenu/3.0.7/metisMenu.min.css" />

    @yield('css')
</head>

<body>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        <!-- Sidebar Start -->
        <aside class="left-sidebar">
            <!-- Sidebar scroll-->
            <div>
                <div class="brand-logo d-flex align-items-center justify-content-between">
                    <a class="text-nowrap logo-img">
                        <img src="{{ url('admin-ui') }}/assets/images/logos/logo-2.png" alt="" width="200" />
                    </a>
                    <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
                        <svg xmlns="http://www.w3.org/2000/svg" width="2em" height="2em" viewBox="0 0 12 12">
                            <path fill="currentColor" fill-rule="evenodd"
                                d="M4.28 3.22a.75.75 0 0 0-1.06 1.06L4.94 6L3.22 7.72a.75.75 0 0 0 1.06 1.06L6 7.06l1.72 1.72a.75.75 0 0 0 1.06-1.06L7.06 6l1.72-1.72a.75.75 0 0 0-1.06-1.06L6 4.94z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>
                <!-- Sidebar navigation-->
                @include('_admin/_layout/sidebar')
            </div>
            <!-- End Sidebar scroll-->
        </aside>
        <!--  Sidebar End -->
        <!--  Main wrapper -->
        <div class="body-wrapper">
            <!--  Header Start -->
            <header class="app-header">
                <nav class="navbar navbar-expand-lg navbar-light">
                    <ul class="navbar-nav">
                        <li class="nav-item d-block d-xl-none">
                            <a class="nav-link sidebartoggler" id="headerCollapse" href="javascript:void(0)">
                                <svg xmlns="http://www.w3.org/2000/svg" width="2em" height="2em"
                                    viewBox="0 0 24 24">
                                    <g>
                                        <path fill="currentColor"
                                            d="M12 22c-4.714 0-7.071 0-8.536-1.465C2 19.072 2 16.714 2 12s0-7.071 1.464-8.536C4.93 2 7.286 2 12 2s7.071 0 8.535 1.464C22 4.93 22 7.286 22 12s0 7.071-1.465 8.535C19.072 22 16.714 22 12 22"
                                            opacity="0.5" />
                                        <path fill="white"
                                            d="M18.75 8a.75.75 0 0 1-.75.75H6a.75.75 0 0 1 0-1.5h12a.75.75 0 0 1 .75.75m0 4a.75.75 0 0 1-.75.75H6a.75.75 0 0 1 0-1.5h12a.75.75 0 0 1 .75.75m0 4a.75.75 0 0 1-.75.75H6a.75.75 0 0 1 0-1.5h12a.75.75 0 0 1 .75.75" />
                                    </g>
                                </svg>

                            </a>
                        </li>
                    </ul>
                    <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
                        <div class="d-none d-md-block">
                            <p class="mb-0 text-gray-2 badge bg-light text-dark" id="date">
                            </p>
                            <p class="mb-0 text-gray-2 badge bg-primary-subtle text-primary" id="time">
                            </p>
                        </div>
                        <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
                            <li class="nav-item dropdown">
                                <a class="nav-link " href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <img src="{{ url('admin-ui') }}/assets/images/user.png" alt=""
                                        width="25" height="25" class="">
                                </a>
                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up"
                                    aria-labelledby="drop2">
                                    <div class="message-body">
                                        <button
                                            class="d-flex align-items-center gap-2 dropdown-item" id="theme-toggle">

                                            <p class="mb-0 fs-3">Tampilan Gelap</p>
                                        </button>
                                        <a href="{{ base_url("user/change-password") }}"
                                            class="d-flex align-items-center gap-2 dropdown-item" navigate>
                                            <p class="mb-0 fs-3">Ubah Password</p>
                                        </a>
                                        <hr>
                                        <a href="{{ base_url('auth/logout') }}"
                                            class="btn btn-outline-danger mx-3 mt-2 d-block">Logout</a>
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
            </header>
            <!--  Header End -->
            <div class="body-wrapper-inner">
                <div class="container-fluid">
                    <div class="mb-3 mb-md-0"></div>

                    <div id="content">
                        <!-- Jika ada konten dinamis (untuk refresh), tampilkan -->
                        {!! isset($content) ? $content : '' !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const BASE_URL = "<?= base_url('') ?>";
    </script>

    <script src="{{ url('admin-ui') }}/assets/libs/jquery/dist/jquery.min.js"></script>
    <script src="{{ url('admin-ui') }}/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ url('admin-ui') }}/assets/js/sidebarmenu.js"></script>
    <script src="{{ url('admin-ui') }}/assets/js/app.min.js"></script>

    {{-- External JS Libraries --}}
    <script src="{{ url('admin-ui') }}/assets/libs/simplebar/dist/simplebar.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/nprogress/0.2.0/nprogress.min.js"></script>
    <!-- solar icons -->

    <script src="{{ url('assets/js/navigate.js') }}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/metisMenu/3.0.7/metisMenu.min.js"></script>

    {{-- <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script> --}}

    @yield('js')

    @include('_admin._layout.components.toast')

    @if (Session::has('success') || Session::has('error'))
        @php
            $type = Session::has('success') ? 'success' : 'error';
            $message = $type == 'success' ? session('success') : session('error');
        @endphp

        <script>
            showToast('<?= $type ?>', '<?= $message ?>');
        </script>

        @php
            Session::forget('success');
            Session::forget('error');
        @endphp
    @endif

    <script>
        $(document).ready(function() {
            const form2 = $('form');
            const submitButton2 = form2.find('button[type="submit"]');
            const buttonText = submitButton2.text();

            // $(window).on('popstate', function(e) {
            //     // Hapus status "disabled" dari tombol sebelum berpindah halaman
            //     submitButton2.removeClass('disabled');
            //     submitButton2.html(buttonText);
            // });

            form2.on('submit', function(e) {
                console.log("sip")
                if (!this.checkValidity()) {
                    // Jika form tidak valid, mencegah pengiriman form
                    e.preventDefault();
                    // Hapus status "disabled" dari tombol
                    submitButton2.removeClass('disabled');
                    submitButton2.html(defaultSubmitBtnText);
                    form.append(errorElement);

                    console.log("okok")
                } else {
                    console.log("xxxx")
                    // Jika form valid, aktifkan spinner pada tombol submit
                    submitButton2.addClass('disabled');
                    submitButton2.html(
                        '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span> Memproses ...'
                    );
                }
            });
        });
    </script>


    <script>
        updateTime();

        function updateTime() {
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const now = new Date();
            const localTime = new Date(now.getTime()); // UTC +7

            const dateStr = localTime.toLocaleDateString('id-ID', options);
            const timeStr = localTime.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            });

            document.getElementById('date').innerHTML = `<b>${dateStr}</b>`;
            document.getElementById('time').innerHTML = `<b>${timeStr}</b>`;
        }

        setInterval(updateTime, 1000); // Update every second
    </script>


    <script></script>

    <script>
        $(document).ready(function() {
            $("#sidebarnav").metisMenu(); // Pastikan ID sesuai
            // $('.xx').css('display', 'block');
        });

        $(function() {

            $('#menu li a').click(function(event) {
                var elem = $(this).next();
                if (elem.is('ul')) {
                    event.preventDefault();
                    $('#menu ul:visible').not(elem).slideUp();
                    elem.slideToggle();
                }
                return false;
            });
        });
    </script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const themeToggle = document.getElementById('theme-toggle');
        const html = document.documentElement;

        // Cek preferensi tema dari localStorage
        if (localStorage.getItem('theme') === 'dark') {
            html.setAttribute('data-bs-theme', 'dark');
            themeToggle.textContent = 'Tampilan Terang';
        }

        themeToggle.addEventListener('click', () => {
            if (html.getAttribute('data-bs-theme') === 'dark') {
                html.setAttribute('data-bs-theme', 'light');
                themeToggle.textContent = 'Tampilan Gelap';
                localStorage.setItem('theme', 'light');
            } else {
                html.setAttribute('data-bs-theme', 'dark');
                themeToggle.textContent = 'Tampilan Terang';
                localStorage.setItem('theme', 'dark');
            }
        });
    });
</script>
</body>

</html>
