function loadPage(url = "", returnedJson = false) {
    NProgress.start();

    $.ajax({
        url: url,
        type: 'GET',
        success: function (data) {
            if (returnedJson) {
                var newUrl = BASE_URL + "/" + data.redirect;
                window.history.pushState(null, '', newUrl);

                loadPage(newUrl);

                if (data.success) {
                    showToast("success", data.message)
                } else {
                    showToast("error", data.message)
                }

                NProgress.done();
            } else {
                $('#main-wrapper').removeClass('show-sidebar').addClass('mini-sidebar');
                $('#content').html(data);
                $('html, body').animate({
                    scrollTop: 0
                }, 'fast');
            }
            NProgress.done();
        },
        error: function (xhr, status, error) {
            if (xhr.status === 401) {
                window.location.href = BASE_URL + "/auth/login";
            } else {
                alert('Terjadi kesalahan saat memuat halaman.');
            }
            NProgress.done();
        }
    });
}

$(window).on('beforeunload', function () {
    if (navigator.onLine) {
        NProgress.set(0.2);
        NProgress.start();
    }
});

$(document).on('click', 'a:not([navigate]):not([navigate-api]):not([navigate-api-confirm]):not(.sidebar-link)', function (e) {
    e.preventDefault();
    var url = $(this).attr('href');

    if (navigator.onLine) {
        NProgress.set(0.2);
        NProgress.start();
        window.location.href = url;

        setTimeout(function () {
            NProgress.done();
        }, 2000);
    } else {
        lostInternet();
    }
});

$(document).on('click', '[navigate]', function (e) {
    e.preventDefault();
    var url = $(this).attr('href');

    if (navigator.onLine) {
        window.history.pushState(null, '', url);
        loadPage(url);
    } else {
        lostInternet();
    }
});

$(document).on('click', '[navigate-api]', function (e) {
    e.preventDefault();
    var url = $(this).attr('href');

    if (navigator.onLine) {
        window.history.pushState(null, '', url);
        loadPage(url, true);
    } else {
        lostInternet();
    }
});
$(document).on('click', '[navigate-api-confirm]', function (e) {
    e.preventDefault();
    var url = $(this).attr('href');

    var confirmMessage = $(this).attr('confirm-message') || 'Apakah kamu yakin?';
    var confirmation = confirm(confirmMessage);

    if (!confirmation) {
        return false;
    }

    // Jika user menekan tombol Yes, lanjutkan pengecekan koneksi
    if (navigator.onLine) {
        window.history.pushState(null, '', url);
        loadPage(url, true);
    } else {
        lostInternet();
    }
});


$(window).on('popstate', function () {
    if (navigator.onLine) {
        loadPage(window.location.pathname);
    } else {
        lostInternet();
    }
});

function showValidationErrors(errors) {
    $('.form-error').remove();

    $.each(errors, function (field, messages) {
        var input = $('[name="' + field + '"]');

        if (input.length && input.attr('type') === 'radio') {
            var parentDiv = input.closest('.mb-3');
            if (parentDiv.length) {
                parentDiv.append('<span class="form-error" style="color: red;">' + messages[0] + '</span>');
            }
        } else if (input.length) {
            input.after('<span class="form-error" style="color: red;">' + messages[0] + '</span>');
        }
    });
}

defaultSubmitBtnText = $('button[type="submit"]').text()

$(document).on('submit', 'form[navigate-form]', function (e) {
    e.preventDefault(); // Mencegah default form submit (reload halaman)
    var form = $(this);
    var url = form.attr('action'); // URL dari action form
    var formData = form.serialize(); // Serialize data form untuk dikirim via POST

    var submitButton = form.find('button[type="submit"]');
    var buttonText = submitButton.text(); // Menyimpan teks asli tombol submit


    defaultSubmitBtnText = ""
    if (defaultSubmitBtnText == "") {
        defaultSubmitBtnText = buttonText

        if (defaultSubmitBtnText == " Memproses ...") {
            defaultSubmitBtnText = "Simpan Data"
        }
    }

    console.log(buttonText);
    console.log(defaultSubmitBtnText);

    if (navigator.onLine) {
        NProgress.start();
        submitButton.addClass('disabled');
        submitButton.html(
            '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span> Memproses ...'
        );

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    console.log(response.message)
                    showToast("success", response.message);

                    var newUrl = BASE_URL + "/" + response.redirect;
                    window.history.pushState(null, '', newUrl);

                    loadPage(newUrl);
                } else {
                    showToast("error", 'Terjadi kesalahan: ' + response.message);
                    NProgress.done();
                }

                $('.modal-backdrop.fade.show').remove();
                $('body').removeAttr('style').removeClass();
                // submitButton.removeClass('disabled');
                // submitButton.html(defaultSubmitBtnText);
            },
            error: function (xhr) {
                var response = xhr.responseJSON;

                if (response && response.errors) {
                    // Jika error disebabkan oleh validasi, tampilkan pesan error
                    showValidationErrors(response.errors);
                } else {
                    alert('Terjadi kesalahan saat mengirim data.');
                    showToast("error", 'Terjadi kesalahan, Coba beberapa saat lagi atau Hubungi Tim Pengembang');
                }
                NProgress.done();

                submitButton.removeClass('disabled');
                submitButton.html(defaultSubmitBtnText);
            }
        });
    } else {
        lostInternet();
    }
});

$(document).on('submit', 'form[navigate-form-get]', function (e) {
    e.preventDefault(); // Prevent the default GET submission (full page reload)

    var form = $(this);
    var baseUrl = form.attr('action'); // Get the base URL from the form's action attribute
    var formData = form.serialize(); // Serialize form data into query string format (e.g., "param1=value1&param2=value2")

    var submitButton = form.find('button[type="submit"]');
    var originalButtonText = submitButton.text(); // Store the original button text

    // --- Improved default button text handling ---
    // Store the original text in a data attribute if it's not already set
    if (!submitButton.data('original-text')) {
        submitButton.data('original-text', originalButtonText);
    }
    var defaultSubmitBtnText = submitButton.data('original-text');
    // --- End improved button text handling ---

    // Construct the final URL with query parameters
    var newUrl = baseUrl;
    if (formData) { // Only add query string if formData is not empty
        // Check if the baseUrl already contains a query string
        newUrl += (baseUrl.indexOf('?') === -1 ? '?' : '&') + formData;
    }

    console.log("Navigating to (GET):", newUrl); // Log the target URL

    if (navigator.onLine) {
        NProgress.start(); // Start the progress indicator
        submitButton.prop('disabled', true); // Disable button using prop for better compatibility
        submitButton.html(
            '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span> Memproses ...' // Show loading state
        );

        try {
            // Update the browser's history without reloading the page
            // Pass the newUrl in the state object for potential use with popstate
            window.history.pushState({ path: newUrl }, '', newUrl);

            // Assume loadPage function exists to fetch and inject content for the newUrl
            // loadPage should ideally handle NProgress.done() upon its completion or error.
            loadPage(newUrl);

            // --- Cleanup and Button Reset ---
            // It's often better if loadPage signals completion (success/error)
            // to handle NProgress.done() and button reset.
            // But for simplicity mirroring the original structure, we reset here.
            // Consider moving this logic into loadPage's success/error handling if possible.

            // Remove modal backdrops if necessary (might be unrelated, keep from original)
            $('.modal-backdrop.fade.show').remove();
            $('body').removeAttr('style').removeClass(); // Reset body styles if needed

            // Re-enable the button and restore its original text
            submitButton.prop('disabled', false);
            submitButton.html(defaultSubmitBtnText);

            // If loadPage doesn't handle NProgress.done(), call it here.
            // NProgress.done(); // Uncomment if loadPage doesn't handle it.

            // --- End Cleanup and Button Reset ---

        } catch (error) {
            console.error("Error during GET navigation:", error);
            // Use your existing notification system
            showToast("error", 'Terjadi kesalahan saat mencoba navigasi.');
            NProgress.done(); // Ensure progress bar stops on error
            // Restore button state on error
            submitButton.prop('disabled', false);
            submitButton.html(defaultSubmitBtnText);
        }

    } else {
        // Handle the case where the browser is offline
        lostInternet(); // Call your existing offline handler function
        // Optionally, restore button state if offline
        submitButton.prop('disabled', false);
        submitButton.html(defaultSubmitBtnText);
    }
});

function lostInternet() {
    alert("Koneksi internet terputus. Silakan periksa koneksi Anda dan coba lagi.")
}

