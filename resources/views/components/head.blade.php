<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ env('APP_NAME') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}" />
    <!-- Preloader CSS - loaded first -->
    <link rel="stylesheet" href="{{ asset('assets/css/preloader.css') }}">
    <!-- remix icon font css  -->
    <link rel="stylesheet" href="{{ asset('assets/css/remixicon.css') }}">
    <!-- BootStrap css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/bootstrap.min.css') }}">
    <!-- Apex Chart css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/apexcharts.css') }}">
    <!-- Data Table css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/dataTables.min.css') }}">
    <!-- Text Editor css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/editor-katex.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/editor.atom-one-dark.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/editor.quill.snow.css') }}">
    <!-- Date picker css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/flatpickr.min.css') }}">
    <!-- Calendar css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/full-calendar.css') }}">
    <!-- Vector Map css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/jquery-jvectormap-2.0.5.css') }}">
    <!-- Popup css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/magnific-popup.css') }}">
    <!-- Slick Slider css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/slick.css') }}">
    <!-- prism css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/prism.css') }}">
    <!-- file upload css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/file-upload.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/lib/audioplayer.css') }}">
    <!-- main css -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <!-- DataTable sort icons fix -->
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" />

    <style>
        .sidebar-logo {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .light-logo {
            display: block;
            margin: 0 auto;
        }

        .select2-container {
            width: 100% !important;
            display: grid !important;
        }

        .select2-container--default .select2-selection--single {
            border: 1px solid #ced4da !important;
            border-radius: 0.25rem !important;
            height: 44px !important;
            display: flex !important;
            align-items: center !important;
            background-color: var(--white) !important;
        }

        .select2-search--dropdown:before {
            content: '' !important;
            display: none !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: auto !important;
            position: absolute !important;
            top: 0 !important;
            right: 9px !important;
            width: 20px !important;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field {
            padding-left: 15px !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            margin-top: 20px !important;
        }

        .table td,
        .table th,
        .table td span {
            max-width: 200px !important;
            white-space: normal !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
        }

        /* Not sütunları için ek stiller */
        .text-wrap {
            white-space: normal !important;
            word-break: break-word !important;
        }

        .note-text {
            display: inline-block;
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            vertical-align: middle;
        }

        /* Tooltip stili */
        .tooltip-inner {
            max-width: 300px;
            text-align: left;
            word-wrap: break-word;
        }

        /* DataTable özel stilleri */
        table.dataTable td,
        table.dataTable th {
            vertical-align: middle !important;
        }

        table.dataTable td.dataTables_empty {
            text-align: center !important;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            text-align: center !important;
            float: none !important;
            margin-top: 10px !important;
        }
    </style>
    @yield('css')

    <!-- Inline script to ensure preloader is shown immediately -->
    <script>
        // Make sure preloader is visible immediately
        document.addEventListener('DOMContentLoaded', function() {
            var preloader = document.getElementById('preloader');
            if (preloader) {
                preloader.style.display = 'flex';
            }
        });
    </script>
</head>
