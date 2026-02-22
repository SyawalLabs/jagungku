</div> <!-- Penutup main-content -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Modern Dashboard JavaScript -->
<script>
    $(document).ready(function() {
        // ====================================
        // HAMBURGER MENU
        // ====================================
        const $hamburger = $('#hamburgerBtn');
        const $sidebar = $('#sidebar');
        const $overlay = $('#sidebarOverlay');

        function openSidebar() {
            $sidebar.addClass('active');
            $overlay.addClass('active');
            $hamburger.addClass('active');
            $('body').css('overflow', 'hidden');
        }

        function closeSidebar() {
            $sidebar.removeClass('active');
            $overlay.removeClass('active');
            $hamburger.removeClass('active');
            $('body').css('overflow', '');
        }

        $hamburger.on('click', function(e) {
            e.stopPropagation();
            $sidebar.hasClass('active') ? closeSidebar() : openSidebar();
        });

        $overlay.on('click', closeSidebar);

        $('.sidebar .nav-link').on('click', function() {
            if ($(window).width() < 992) {
                setTimeout(closeSidebar, 300);
            }
        });

        $(window).on('resize', function() {
            if ($(window).width() >= 992) {
                $sidebar.removeClass('active').css('left', '0');
                $overlay.removeClass('active');
                $hamburger.removeClass('active');
                $('body').css('overflow', '');
            } else {
                $sidebar.removeClass('active').css('left', '');
                closeSidebar();
            }
        });

        // ====================================
        // TOOLTIP INIT
        // ====================================
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // ====================================
        // AUTO HIDE ALERT AFTER 5 SECONDS
        // ====================================
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        // ====================================
        // ADD LOADING STATE TO BUTTONS
        // ====================================
        $('.btn-submit').on('click', function() {
            var $btn = $(this);
            $btn.html('<span class="loading me-2"></span> Loading...').prop('disabled', true);
            $btn.closest('form').submit();
        });

        // ====================================
        // FORMAT NUMBER WITH THOUSAND SEPARATOR
        // ====================================
        $('.format-number').on('input', function() {
            var value = $(this).val().replace(/\D/g, '');
            if (value) {
                $(this).val(parseInt(value).toLocaleString('id-ID'));
            }
        });

        // ====================================
        // SMOOTH SCROLL TO TOP
        // ====================================
        $(window).scroll(function() {
            if ($(this).scrollTop() > 100) {
                $('#scrollTop').fadeIn();
            } else {
                $('#scrollTop').fadeOut();
            }
        });

        $('#scrollTop').on('click', function() {
            $('html, body').animate({
                scrollTop: 0
            }, 600);
            return false;
        });

        // Add scroll top button if not exists
        if ($('#scrollTop').length === 0) {
            $('body').append('<div id="scrollTop" class="btn btn-success rounded-circle position-fixed" style="bottom: 20px; right: 20px; width: 50px; height: 50px; display: none; align-items: center; justify-content: center; z-index: 99;"><i class="fas fa-arrow-up"></i></div>');
        }
    });
</script>

</body>

</html>