    <!-- jQuery library js -->
    <script src="{{ asset('assets/js/lib/jquery-3.7.1.min.js') }}"></script>
    <!-- Bootstrap js -->
    <script src="{{ asset('assets/js/lib/bootstrap.bundle.min.js') }}"></script>
    <!-- Apex Chart js -->
    <script src="{{ asset('assets/js/lib/apexcharts.min.js') }}"></script>
    <!-- Data Table js -->
    <script src="{{ asset('assets/js/lib/dataTables.min.js') }}"></script>
    <!-- Iconify Font js -->
    <script src="{{ asset('assets/js/lib/iconify-icon.min.js') }}"></script>
    <!-- jQuery UI js -->
    <script src="{{ asset('assets/js/lib/jquery-ui.min.js') }}"></script>
    <!-- Vector Map js -->
    <script src="{{ asset('assets/js/lib/jquery-jvectormap-2.0.5.min.js') }}"></script>
    <script src="{{ asset('assets/js/lib/jquery-jvectormap-world-mill-en.js') }}"></script>
    <!-- Popup js -->
    <script src="{{ asset('assets/js/lib/magnifc-popup.min.js') }}"></script>
    <!-- Slick Slider js -->
    <script src="{{ asset('assets/js/lib/slick.min.js') }}"></script>
    <!-- prism js -->
    <script src="{{ asset('assets/js/lib/prism.js') }}"></script>
    <!-- file upload js -->
    <script src="{{ asset('assets/js/lib/file-upload.js') }}"></script>
    <!-- audioplayer -->
    <script src="{{ asset('assets/js/lib/audioplayer.js') }}"></script>

    <!-- main js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>

    <script src="{{ asset('assets/js/basecrud.js') }}?v={{ env('APP_VERSION') }}"></script>

    <script src="{{ asset('assets/js/sweetalert2.js') }}"></script> <?php echo isset($script) ? $script : ''; ?>
    <script src="{{ asset('assets/js/lib/select2/select2.bundle.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                dropdownCssClass: 'select2-dropdown-custom',
                searchCssClass: 'select2-search-custom',
            });
            $('.select2').one('select2:open', function(e) {
                $('input.select2-search__field').prop('placeholder', 'Arama yapabilirsiniz');
            });
        });
    </script>
    <script type="text/javascript">
        @if (Session::has('success'))
            Swal.fire('', '{{ session('success') }}', 'success');
        @elseif (Session::has('error'))
            Swal.fire('', '{{ session('error') }}', 'error');
        @elseif (Session::has('warning'))
            Swal.fire('', '{{ session('warning') }}', 'warning');
        @elseif (Session::has('info'))
            Swal.fire('', '{{ session('info') }}', 'info');
        @endif
    </script>
    <script>
        @if (Session::has('error'))
            toastr.error("{{ session()->get('error') }}")
        @endif
        @if (Session::has('message'))
            toastr.success("{{ session()->get('message') }}")
        @endif
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.6/jquery.inputmask.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#phone").inputmask({
                mask: "0(999)999 99 99",
                placeholder: "0(___)___ __ __",
                showMaskOnHover: false,
                showMaskOnFocus: true,
                autoUnmask: true
            });
            $("#tc").inputmask({
                mask: "99999999999",
                placeholder: "__________",
                showMaskOnHover: false,
                showMaskOnFocus: true,
                autoUnmask: true
            });
            $("#iban").inputmask({
                mask: "TR99 9999 9999 9999 9999 9999 99",
                placeholder: "TR__ ____ ____ ____ ____ ____ __",
                showMaskOnHover: false,
                showMaskOnFocus: true,
            });
        });
    </script>

    <!-- Preloader Script -->
    <script>
        // Improved preloader script
        (function() {
            // Get preloader elements
            var preloader = document.getElementById('preloader');
            var progressBar = document.querySelector('.loading-progress');

            if (!preloader || !progressBar) return;

            // Simulate progress
            var width = 0;
            var interval = setInterval(function() {
                width += 3;
                if (width <= 100) {
                    progressBar.style.width = width + '%';
                } else {
                    clearInterval(interval);
                }
            }, 50);

            // Function to hide preloader
            function hidePreloader() {
                if (preloader) {
                    preloader.classList.add('loaded');
                    // Remove preloader from DOM after transition
                    setTimeout(function() {
                        if (preloader.parentNode) {
                            preloader.parentNode.removeChild(preloader);
                        }
                    }, 1000);
                }
            }

            // Hide preloader after a short delay
            setTimeout(hidePreloader, 1500);

            // Also hide preloader when window is fully loaded
            window.addEventListener('load', hidePreloader);
        })();
    </script>
