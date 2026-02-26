<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="copyright" content="MACode ID">
<title>@yield('title')</title>
<link rel="stylesheet" href="{{ asset('assets/vendor/animate/animate.css')}}">
<link rel="stylesheet" href="{{ asset('assets/css/bootstrap.css')}}">
<link rel="stylesheet" href="{{ asset('assets/css/maicons.css')}}">
<link rel="stylesheet" href="{{ asset('assets/vendor/owl-carousel/css/owl.carousel.css')}}">
<link rel="stylesheet" href="{{ asset('assets/css/theme.css')}}">
<link rel="stylesheet" href="{{ asset('assets/style.css')}}">
<style>
    .whatsapp-link {
        position: fixed;
        right: 25px;
        bottom: 100px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: rgba(224, 223, 233, 0.7);
        cursor: pointer;
        transition: all .2s ease;
        z-index: 9999999;
    }

    .page-section1 {
        background-color: #ee3324;
        color: #fff;
    }

    .page-section1 h2 {
        color: #ffffff;
    }

    .topheder {
        background: #000B6A;
        text-align: center;
        z-index: 9999999;
        position: relative;
    }

    li.topnav-item {
        display: inline-block;
        text-align: center;
        position: relative;
        font-size: 10px;
    }

    li.topnav-item a {
        color: #fff;
    }

    .btn-outline {
        color: #ffffff;
        background: #ee3324;
    }

    .navbar-light .navbar-nav .nav-link {
        color: rgb(0 0 0);
    }

    .page-banner.home-banner {
        margin-top: 0px;
    }

    .page-banner {
        margin-top: 0px;
    }

    @media (min-width: 992px) {
        .ml-lg-4, .mx-lg-4 {
            margin: 0 auto !important;
        }

    }

    @media (max-width: 700px) {
        .col-lg-12.background-bnr.py-3 {
            background: #000;
            height: auto;
        }

        .nav-link {
            display: block;
            padding: 0.5rem 0.5rem;
        }

        ul.coment {
            display: none;
        }
    }

    .ml-auto, .mx-auto {
        margin-left: 0;
    }

    span.number {
        font-size: 55px;
        font-family: fantasy;
    }

    .counter-section p {
        margin-bottom: 6px;
        color: #343a40;
        font-weight: bolder;
        font-size: 20px;
    }

    .col-lg-12.background-bnr.py-3 {
        background-image: url(../../assets/img/Group.png);
        height: 400px;
        background-repeat: no-repeat;
        background-size: contain;
    }

    .background-bnr h1 {
        color: #ffffff;
        padding-top: 50px;
        font-weight: 900;
    }

    .img-fluid.text-center p {
        color: #9b8585;
        font-size: 23px;
    }

</style>
