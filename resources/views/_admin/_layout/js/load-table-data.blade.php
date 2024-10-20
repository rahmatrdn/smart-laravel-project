<script type="text/javascript">
    $(document).ready(function() {
        var currentPage = getCurrentPage();
        fetch_data(currentPage);

        $(document).on('click', '.pagination a', function(event) {
            event.preventDefault();
            var page = $(this).attr('href').split('page=')[1];
            fetch_data(page);
        });

        function fetch_data(page) {
            var currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('page', page);

            $('#loading-spinner').show();
            $('#table_data').css('opacity', '0');

            $.ajax({
                url: currentUrl.href,
                success: function(data) {
                    $('#table_data').html(data);

                    // Animasi fade in dengan durasi 500ms
                    $('#table_data').animate({
                        opacity: 1
                    }, 500);

                    // Update URL tanpa reload
                    window.history.pushState({}, '', currentUrl.href);

                    // Scroll halaman ke atas
                    $('html, body').animate({ scrollTop: 0 }, 'fast');
                },
                complete: function() {
                    $('#loading-spinner').hide();
                }
            });
        }

        function getCurrentPage() {
            var urlParams = new URLSearchParams(window.location.search);
            return urlParams.has('page') ? urlParams.get('page') : 1;
        }
    });
</script>
