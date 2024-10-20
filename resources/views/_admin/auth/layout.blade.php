<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @include('_admin._layout.favicon')

    <title>Digital Library | {{ env('LIB_NAME') }}</title>
    <link rel="stylesheet" href="{{ url('admin-ui') }}/assets/css/styles.min.css" />
</head>

<body>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">
        
        @yield('content')
    </div>
    <script src="{{ url('admin-ui') }}/assets/libs/jquery/dist/jquery.min.js"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector('form');
            const submitButton = form.querySelector('button[type="submit"]');
            const buttonText = submitButton.textContent;

            window.addEventListener("popstate", function(e) {
                // Hapus status "disabled" dari tombol sebelum berpindah halaman
                submitButton.classList.remove("disabled");
                submitButton.innerHTML = buttonText;
            });
            form.addEventListener("submit", function(e) {
                if (!form.checkValidity()) {
                    // Jika form tidak valid, mencegah pengiriman form
                    e.preventDefault();
                    // Hapus status "disabled" dari tombol
                    submitButton.classList.remove("disabled");
                    submitButton.innerHTML = buttonText;
                    form.appendChild(errorElement);
                } else {
                    // Jika form valid, aktifkan spinner pada tombol submit
                    submitButton.classList.add("disabled");
                    submitButton.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span> Memproses ...';
                }
            });
        });
    </script>
</body>

</html>
