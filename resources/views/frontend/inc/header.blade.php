<div class="topheder">
    <ul class="topnavbar-nav">
        <li class="topnav-item active">
            <a href="index.html" class="nav-link">Blog</a>
        </li>
        <li class="topnav-item">
            <a href="about.html" class="nav-link">Offer</a>
        </li>
        <li class="topnav-item">
            <a href="services.html" class="nav-link">Help</a>
        </li>
        <li class="topnav-item">
            <a href="blog.html" class="nav-link">E-Bill</a>
        </li>
        <li class="topnav-item">
            <a href="paynow.html" class="nav-link">Pay Online</a>
        </li>
    </ul>
</div>
<div class="whatsapp-link"><img src="{{ asset('assets/img/icons8-whatsapp.svg') }}" width="50"></div>
<!-- Back to top button -->
<div class="back-to-top"></div>

<header>
    <nav class="navbar navbar-expand-lg navbar-light navbar-float">
        <div class="container">
            <a href="{{route('home')}}"><img src="{{ asset('assets/img/mbts-logo1.jpg') }}" width="60" class="1logo"></a>

            <button class="navbar-toggler" data-toggle="collapse" data-target="#navbarContent"
                    aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="navbar-collapse collapse" id="navbarContent">
                <ul class="navbar-nav ml-lg-4 pt-3 pt-lg-0">
                    <li class="nav-item {{request()->route()->getName() == 'home' ? 'active' : ''}}">
                        <a href="{{route('home')}}" class="nav-link">Home</a>
                    </li>
                    <li class="nav-item {{request()->route()->getName() == 'about' ? 'active' : ''}}">
                        <a href="{{route('about')}}" class="nav-link">About Us</a>
                    </li>
                    <li class="nav-item {{request()->route()->getName() == 'services' ? 'active' : ''}}">
                        <a href="{{route('services')}}" class="nav-link">Broadband Services</a>
                    </li>
                    <li class="nav-item {{request()->route()->getName() == 'home-plan' ? 'active' : ''}}">
                        <a href="{{route('home-plan')}}" class="nav-link">Home Plan</a>
                    </li>
                    <li class="nav-item {{request()->route()->getName() == 'business-plan' ? 'active' : ''}}">
                        <a href="{{route('business-plan')}}" class="nav-link">Business Plan</a>
                    </li>
                    <li class="nav-item {{request()->route()->getName() == 'contact' ? 'active' : ''}}">
                        <a href="{{route('contact')}}" class="nav-link">Contact</a>
                    </li>
                </ul>

                <div class="ml-auto">
                    <a href="{{route('new-connection')}}" class="btn btn-outline rounded-pill">New Connection</a>
                </div>

            </div>
        </div>
    </nav>

    @yield('banner')
</header>
