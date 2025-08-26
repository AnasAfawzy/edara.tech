<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="layout-navbar-fixed layout-menu-fixed layout-compact"
    dir="ltr" data-skin="default" data-assets-path="{{ asset('assets') }}/" data-template="vertical-menu-template"
    data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    <!-- Fonts -->
    {{-- <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" /> --}}

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    {{-- <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
        rel="stylesheet" /> --}}

    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/iconify-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/fontawesome.css') }}" />

    <!-- Core CSS -->
    <!-- build:css assets/vendor/css/theme.css  -->
    {{-- <link rel="preload" href="{{ asset('assets/css/fonts/tabler-icons.woff2') }}" as="font" type="font/woff2"
        crossorigin>

    <link rel="preload" href="{{ asset('assets/fonts/tabler-icons.woff') }}" as="font" type="font/woff"
        crossorigin> --}}

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/node-waves/node-waves.css') }}" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/pickr/pickr-themes.css') }}" />

    <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />

    {{-- <link rel="stylesheet" href="{{ asset('assets/css/tabler-icons.min.css') }}" /> --}}

    <!-- Vendors CSS -->

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />

    <!-- endbuild -->

    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/swiper/swiper.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet"
        href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendor/fonts/flag-icons.css') }}" />


    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/css/pages/cards-advance.css') }}" />
    {{-- tree_view --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/jstree/jstree.css') }}" />
    <!-- Helpers -->
    <script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->

    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    <script src="{{ asset('assets/vendor/js/template-customizer.js') }}"></script>

    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->

    <script src="{{ asset('assets/js/config.js') }}"></script>
    <script>
        window.appLocale = "{{ app()->getLocale() }}";
    </script>

    @stack('css')
    @yield('css')
</head>

<body class="font-sans antialiased">
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: "{{ session('swal_title') ?? 'تمت العملية بنجاح' }}",
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 1800,
                customClass: {
                    title: 'swal-title-custom',
                    popup: 'swal-popup-custom',
                    content: 'swal-content-custom'
                }
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: "{{ session('swal_title') ?? 'خطأ!' }}",
                text: "{{ session('error') }}",
                showConfirmButton: false,
                timer: 1800,
                customClass: {
                    title: 'swal-title-custom',
                    popup: 'swal-popup-custom',
                    content: 'swal-content-custom'
                }
            });
        </script>
    @endif
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">

            @include('layouts.sidebar')


            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->

                @include('layouts.navigation')

                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @yield('content')
                    </div>
                    <!-- / Content -->

                    @include('layouts.Footer')

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>
    </div>
    @yield('scripts')
    @stack('scripts')

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/theme.js -->

    <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/node-waves/node-waves.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/@algolia/autocomplete-js.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/pickr/pickr.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/hammer/hammer.js') }}"></script>

    <script src="{{ asset('assets/vendor/libs/i18n/i18n.js') }}"></script>

    <script src="{{ asset('assets/vendor/js/menu.js') }}"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/swiper/swiper.js') }}"></script>
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>

    <!-- Main JS -->

    <script src="{{ asset('assets/js/main.js') }}"></script>

    <!-- Page JS -->
    <script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>
    <script>
        window.currentLang = "{{ app()->getLocale() }}";
        window.currentDir = "{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}";
    </script>
    <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>

    {{-- Optimize modal in livewire to open and close --}}
    <script>
        window.addEventListener('createModalToggle', event => {
            $('#createModal').modal('toggle');
        })

        window.addEventListener('updateModalToggle', event => {
            $('#updateModal').modal('toggle');
        })

        // window.addEventListener('deleteModalToggle', event => {
        //     $('#deleteModal').modal('toggle');
        // })

        // window.addEventListener('showModalToggle', event => {
        //     $('#showModal').modal('toggle');
        // })

        // window.addEventListener('wizardModalToggle', event => {
        //     $('#wizardModal').modal('toggle');
        // })

        // window.addEventListener('wizardUpdateModalToggle', event => {
        //     $('#wizardUpdateModal').modal('toggle');
        // })

        // window.addEventListener('wizardCompleteModalToggle', event => {
        //     $('#wizardCompleteModal').modal('toggle');
        // })

        // window.addEventListener('updateSaleryModalToggle', event => {
        //     $('#UpdateSalery').modal('toggle');
        // })
    </script>
    {{-- End optimize modal in livewire to open and close --}}


</body>

</html>
