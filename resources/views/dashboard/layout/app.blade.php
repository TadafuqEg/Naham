<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>@yield('title', 'home')</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicon -->
    <link href="{{asset('darkpan/img/favicon.ico')}}" rel="icon">
    <!-- Google Web Fonts -->
    <!--
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Roboto:wght@500;700&display=swap" rel="stylesheet">
    -->
    
    <!-- Icon Font Stylesheet -->
    <link href="{{asset('darkpan/all.min.css')}}" rel="stylesheet">
    <link href="{{asset('darkpan/bootstrap-icons.css')}}" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="{{asset('darkpan/lib/owlcarousel/assets/owl.carousel.min.css')}}" rel="stylesheet">
    <link href="{{asset('darkpan/lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css')}}" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="{{asset('darkpan/css/bootstrap.min.css')}}" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="{{asset('darkpan/css/style.css')}}" rel="stylesheet">
    
</head>

<body>
    <div class="container-fluid position-relative d-flex p-0">
        <!-- Spinner Start -->
        <div id="spinner" class="show bg-dark position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->


        <!-- Sidebar Start -->
        @include('dashboard.layout.side_menu')
        <!-- Sidebar End -->


        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            @include('dashboard.layout.header')
            <!-- Navbar End -->


            <!-- Blank Start -->
            @yield('content')
            <!-- Blank End -->


            <!-- Footer Start -->
            @include('dashboard.layout.footer')
            <!-- Footer End -->
        </div>
        <!-- Content End -->


        <!-- Back to Top -->
        <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>
    </div>


    <!-- JavaScript Libraries -->
    <script src="{{asset('darkpan/jquery-3.4.1.min.js')}}"></script>
    <script src="{{asset('darkpan/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('darkpan/lib/chart/chart.min.js')}}"></script>
    <script src="{{asset('darkpan/lib/easing/easing.min.js')}}"></script>
    <script src="{{asset('darkpan/lib/waypoints/waypoints.min.js')}}"></script>
    <script src="{{asset('darkpan/lib/owlcarousel/owl.carousel.min.js')}}"></script>
    <script src="{{asset('darkpan/lib/tempusdominus/js/moment.min.js')}}"></script>
    <script src="{{asset('darkpan/lib/tempusdominus/js/moment-timezone.min.js')}}"></script>
    <script src="{{asset('darkpan/lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js')}}"></script>

    <!-- Template Javascript -->
    <script src="{{asset('darkpan/js/main.js')}}"></script>
    @stack('scripts')
</body>

</html>