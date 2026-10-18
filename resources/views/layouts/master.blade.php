<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>@yield('title', 'Dashboard | Merra Inventory')</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="{{ asset('admin/assets/img/kaiadmin/favicon.ico') }}" type="image/x-icon" />

    {{-- Web Fonts --}}
    <script src="{{ asset('admin/assets/js/plugin/webfont/webfont.min.js') }}"></script>
    <script>
        WebFont.load({
            google: {
                families: ["Public Sans:300,400,500,600,700"]
            },
            custom: {
                families: [
                    "Font Awesome 5 Solid",
                    "Font Awesome 5 Regular",
                    "Font Awesome 5 Brands",
                    "simple-line-icons",
                ],
                urls: ["{{ asset('admin/assets/css/fonts.min.css') }}"],
            },
            active: function () {
                sessionStorage.fonts = true;
            },
        });
    </script>

    {{-- Kaiadmin Core Styles (satu-satunya sumber Bootstrap CSS — tidak ada duplikat CDN) --}}
    <link rel="stylesheet" href="{{ asset('admin/assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/assets/css/plugins.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/assets/css/kaiadmin.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('admin/assets/css/demo.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    {{-- Select2 — searchable dropdown, dipakai otomatis di semua <select> lewat script global di bawah --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    @include('layouts.theme')
</head>

<body>
    <div class="wrapper">
        @include('layouts.sidebar')

        <div class="main-panel">
            {{-- Main Header --}}
            <div class="main-header">
                <div class="main-header-logo">
                    <div class="logo-header" data-background-color="dark">
                        <a href="{{ url('/') }}" class="logo">
                            <img src="{{ asset('admin/assets/img/kaiadmin/favicon.png') }}" alt="Merra Logo"
                                class="navbar-brand" height="20" />
                        </a>
                        <div class="nav-toggle">
                            <button class="btn btn-toggle toggle-sidebar">
                                <i class="fa-solid fa-burger"></i>
                            </button>
                            <button class="btn btn-toggle sidenav-toggler">
                                <i class="fa-solid fa-burger"></i>
                            </button>
                        </div>
                        <button class="topbar-toggler more">
                            <i class="gg-more-vertical-alt"></i>
                        </button>
                    </div>
                </div>
                @include('layouts.header')
            </div>

            {{-- JANGAN bungkus @yield ini dalam elemen apa pun: KaiAdmin memakai selector
                 direct-child `.main-panel > .container` (margin-top 69px untuk memberi ruang
                 header). Wrapper apa pun membuat aturan itu berhenti berlaku dan konten
                 tersembunyi di balik header. Animasi masuk halaman ditangani di theme.blade.php. --}}
            @yield('content')
            @yield('kategori')
            @yield('daftarbahan')
            @yield('BahanMasuk')
            @yield('BahanAkhir')
            @yield('BahanAkhirKitchen')
            @yield('pengguna')
            @yield('BahanKeluar')
            @yield('inventaris')
            @yield('menu')
            @yield('profile')
            @yield('laporanbahan')
            @yield('laporanbahanmasuk')
            @yield('laporan-bahan-baku')
            @yield('laporanbahanakhir')
            @yield('laporanbahankeluar')
            @yield('MenuTerjual')
            @yield('indexLaporan')
            @yield('pemantauan')
            @yield('detailInventaris')
            @yield('databahanakhir')
            @yield('bahanProcess')
        </div>
    </div>

    {{-- Core Scripts (satu sumber per library — sebelumnya jQuery & Bootstrap dimuat berkali-kali) --}}
    <script src="{{ asset('admin/assets/js/core/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/core/bootstrap.min.js') }}"></script>

    {{-- Plugins — hanya yang benar-benar dipakai aplikasi.
         Chart.js, sparkline, chart-circle, DataTables, bootstrap-notify, dan jsvectormap
         sengaja TIDAK dimuat: tidak ada satu pun view yang memakainya, dan jsvectormap
         justru melempar error karena elemen #world-map milik halaman demo template
         tidak pernah ada di aplikasi ini. --}}
    <script src="{{ asset('admin/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>
    <script src="{{ asset('admin/assets/js/plugin/sweetalert/sweetalert.min.js') }}"></script>
    <script src="https://js.pusher.com/7.0/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- Kaiadmin core. setting-demo.js & demo.js tidak dimuat: keduanya skrip halaman demo
         template (color switcher + chart demo) yang elemennya tidak ada di aplikasi ini. --}}
    <script src="{{ asset('admin/assets/js/kaiadmin.min.js') }}"></script>

    {{-- Custom Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleSidebarBtn = document.querySelector('.toggle-sidebar');
            const sidenavTogglerBtn = document.querySelector('.sidenav-toggler');
            const topbarToggler = document.querySelector('.topbar-toggler');
            const sidebar = document.querySelector('.sidebar');

            function toggleSidebar() {
                document.body.classList.toggle('nav_open');
                sidebar.classList.toggle('open');
            }

            if (toggleSidebarBtn) {
                toggleSidebarBtn.addEventListener('click', e => {
                    e.stopPropagation();
                    document.body.classList.toggle('sidebar_minimize');
                });
            }

            if (sidenavTogglerBtn) {
                sidenavTogglerBtn.addEventListener('click', e => {
                    e.stopPropagation();
                    toggleSidebar();
                });
            }

            if (topbarToggler) {
                topbarToggler.addEventListener('click', e => {
                    e.stopPropagation();
                    document.body.classList.toggle('topbar_open');
                });
            }

            document.addEventListener('click', e => {
                if (sidebar && sidebar.classList.contains('open') &&
                    !sidebar.contains(e.target) &&
                    !sidenavTogglerBtn.contains(e.target)) {
                    sidebar.classList.remove('open');
                    document.body.classList.remove('nav_open');
                }
            });

            if (sidebar) {
                sidebar.addEventListener('click', e => e.stopPropagation());
            }

            // Searchable select untuk filter bar atau select dengan class .select2
            // Sengaja TIDAK diterapkan pada select di dalam modal untuk menghindari bug HTML5 validation dan focus trap
            if (window.jQuery && jQuery.fn.select2) {
                jQuery('form:not(.modal form) select.select2, .filter-bar select:not([data-locked])').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    dropdownAutoWidth: true,
                });
            }

            // Modal DOM Hoisting:
            // Pastikan seluruh modal dipindahkan langsung sebagai child dari <body>.
            // Hal ini mutlak diperlukan untuk mencegah modal tertutup modal-backdrop hitam
            // atau terpotong oleh overflow/stacking context dari container, card, dan table-responsive.
            function hoistModalsToBody() {
                if (window.jQuery) {
                    jQuery('.modal').each(function () {
                        if (!jQuery(this).parent().is('body')) {
                            jQuery(this).appendTo('body');
                        }
                    });
                }
            }

            hoistModalsToBody();

            if (window.jQuery) {
                jQuery(document).on('show.bs.modal', '.modal', function () {
                    if (!jQuery(this).parent().is('body')) {
                        jQuery(this).appendTo('body');
                    }
                });
            }

            // Global Feedback: SweetAlert untuk flash message & validation error
            @if (session('success'))
                if (window.swal) {
                    swal("Berhasil!", "{{ session('success') }}", "success");
                }
            @endif

            @if (session('error'))
                if (window.swal) {
                    swal("Gagal!", "{{ session('error') }}", "error");
                }
            @endif

            @if ($errors->any())
                if (window.swal) {
                    swal("Validasi Gagal", "{!! addslashes(implode('\n', $errors->all())) !!}", "warning");
                }
            @endif

            // Notifikasi stok menipis realtime — pakai SweetAlert (sudah ter-load) alih-alih alert() browser polos
            @if (config('broadcasting.connections.pusher.key'))
                Pusher.logToConsole = false;
                var pusher = new Pusher("{{ config('broadcasting.connections.pusher.key') }}", {
                    cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
                    forceTLS: true
                });
                var channel = pusher.subscribe('stok-menipis');
                channel.bind('stokMenipis', function (data) {
                    if (window.swal) {
                        swal({
                            title: "Stok Menipis!",
                            text: data.bahan.nama_bahan + " tersisa " + data.bahan.sisa_stok,
                            icon: "warning",
                        });
                    }
                });
            @endif

        });
    </script>

    @stack('scripts')
</body>

</html>
