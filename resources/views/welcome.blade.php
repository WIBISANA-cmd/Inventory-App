<!DOCTYPE html>
<html lang="en">

<head>
    <title>Merra Coffee and Talk</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link href="https://fonts.googleapis.com/css?family=Noto+Sans:400,400i,700,700i&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Noto+Serif:400,400i,700,700i&display=swap" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css?family=Abril+Fatface&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('landingpage/css/open-iconic-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('landingpage/css/animate.css') }}">
    <link rel="shortcut icon" href="{{ asset('landingpage/images/favicon.ico')}}" type="image/x-icon">

    <link rel="stylesheet" href="{{ asset('landingpage/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('landingpage/css/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset('landingpage/css/magnific-popup.css') }}">

    <link rel="stylesheet" href="{{ asset('landingpage/css/aos.css') }}">

    <link rel="stylesheet" href="{{ asset('landingpage/css/ionicons.min.css') }}">

    <link rel="stylesheet" href="{{ asset('landingpage/css/bootstrap-datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('landingpage/css/jquery.timepicker.css') }}">


    <link rel="stylesheet" href="{{ asset('landingpage/css/flaticon.css') }}">
    <link rel="stylesheet" href="{{ asset('landingpage/css/icomoon.css') }}">
    <link rel="stylesheet" href="{{ asset('landingpage/css/style.css') }}">
    <style>
        .btn-gray-terra {
            background-color: #3b643c; /* abu-abu muda */
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            font-weight: 600;
            transition: background-color 0.3s ease;
            border-radius: 4px;
        }
        
        .btn-gray-terra:hover {
            background-color: #e87761; /* terracotta */
            color: #242424;
        }
        </style>
</head>

<body>


    <nav id="navbar" class="navbar">
        <ul class="nav-menu">
            <li>
                <a data-scroll="home" href="#home" class="dot active">
                    <span>Home</span>
                </a>
            </li>
        </ul>
    </nav>
    <!-- End Nav Section -->

    <!-- Start Home Section -->
    <section id="home" class="hero-wrap js-fullheight"
    style="background-image: url('{{ asset('landingpage/images/homebg.jpg') }}'); background-size: cover; background-position: center;"
    data-stellar-background-ratio="0.5">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-center">
            <div class="col-lg-8 col-md-10 text-center ftco-animate d-flex align-items-center">
                <div class="text w-100">
                    <a href="index.html">
                        <img src="{{ asset('landingpage/images/logomerra.png') }}" 
                             alt="Logo Merra" 
                             class="img-fluid mb-5" 
                             style="max-width: 80%; height: auto;">
                    </a>
                    <h1 class="mb-4">INVENTORY SYSTEM</h1>
                    <p class="mt-5">
                        <a href="{{ route('login') }}" class="btn btn-gray-terra">
                          Staff's Login
                        </a>
                      </p>
                                        
                </div>
            </div>
        </div>
    </div>
</section>

    <!-- End Home Section -->
    <!-- Start Footer Section -->
    <footer class="ftco-footer py-5">
        <div class="container text-center">
            <div class="row">
                <div class="col-md-12 text-center">

                    <p><!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
                        Copyright &copy;
                        <script>
                            document.write(new Date().getFullYear());
                        </script> by: Merracota
                        <!-- Link back to Colorlib can't be removed. Template is licensed under CC BY 3.0. -->
                    </p>

                    <ul class="ftco-footer-social p-0">
                        <li class="ftco-animate"><a href="#"><span class="icon-twitter"></span></a></li>
                        <li class="ftco-animate"><a href="#"><span class="icon-facebook"></span></a></li>
                        <li class="ftco-animate"><a href="#"><span class="icon-instagram"></span></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
    <!-- End Footer Section -->
    <!-- loader -->
    <div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px">
            <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4"
                stroke="#eeeeee" />
            <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4"
                stroke-miterlimit="10" stroke="#F96D00" />
        </svg></div>
    <script src="{{ asset('landingpage/js/jquery.min.js') }}"></script>
    <script src="{{ asset('landingpage/js/jquery-migrate-3.0.1.min.js') }}"></script>
    <script src="{{ asset('landingpage/js/popper.min.js') }}"></script>
    <script src="{{ asset('landingpage/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('landingpage/js/jquery.easing.1.3.js') }}"></script>
    <script src="{{ asset('landingpage/js/jquery.waypoints.min.js') }}"></script>
    <script src="{{ asset('landingpage/js/jquery.stellar.min.js') }}"></script>
    <script src="{{ asset('landingpage/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('landingpage/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('landingpage/js/aos.js') }}"></script>
    <script src="{{ asset('landingpage/js/jquery.animateNumber.min.js') }}"></script>
    <script src="{{ asset('landingpage/js/scrollax.min.js') }}"></script>
    {{-- Skrip Google Maps & google-map.js dihapus: URL absolut sempat dibungkus asset()
         sehingga selalu 404 (/landingpage/https://...), dan google-map.js menargetkan
         elemen #map yang tidak pernah ada di halaman ini. Sekaligus menghentikan API key
         Maps ikut terekspos di halaman publik. --}}
    <script src="{{ asset('landingpage/js/main.js') }}"></script>
</body>

</html>
