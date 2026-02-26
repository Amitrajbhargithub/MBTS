@extends('frontend.layouts.default')
@section('title', 'Blazing Fast Internet for Your Digital Lifestyle - MBTS Broadband')
@section('banner')
    <div class="page-banner home-banner">
        <div class="carousel" role="group" aria-label="infinite loop carousel demo" aria-roledescription="carousel">
            <div class="carousel-slider" id="slides" aria-atomic="false" aria-live="off">
                <div class="carousel-slide" id="slide1" role="group" aria-label="1 of 6" aria-roledescription="slide">
                    <img src="{{ asset('assets/img/001.jpg') }}" alt="Image 1"></div>
                <div class="carousel-slide" id="slide2" role="group" aria-label="2 of 6" aria-roledescription="slide">
                    <img src="{{ asset('assets/img/Frame1.png') }}" alt="Image 2"></div>
                <div class="carousel-slide" id="slide3" role="group" aria-label="3 of 6" aria-roledescription="slide">
                    <img src="{{ asset('assets/img/Frame3.png') }}" alt="Image 3"></div>
                <div class="carousel-slide" id="slide4" role="group" aria-label="4 of 6" aria-roledescription="slide">
                    <img src="{{ asset('assets/img/Frame2.png') }}" alt="Image 4"></div>
            </div>
        </div>
        <div class="container h-100">
            <div class="row align-items-center h-100">
                <div class="col-lg-6 py-3 wow fadeInUp">
                    <h1 class="mb-4">High-Speed Internet</h1>
                    <p class="text-lg mb-5">Lightning-Fast Speeds: Enjoy download speeds up to 15 Mbps, ensuring that
                        your online experience is smooth and uninterrupted.</p>

                    <p>Unlimited Data: No data caps. Stream, download, and browse without limits.</p>
                    <p>Flexible Plans: Choose from a variety of plans that suit your needs and budget.</p>
                </div>
                <div class="col-lg-6 py-3 wow zoomIn">
                    <div class="img-place">
                        <img src="{{ asset('assets/img/bg_image_1.png') }}" alt="">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('content')
    <div class="page-section features">
        <div class="container">
            <H2 style="text-align:center;">We are Internet Service Provider</H2>
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-4 py-3 wow fadeInUp">
                    <div class="d-flex flex-row">
                        <div class="img-fluid mr-3">
                            <img src="{{ asset('assets/img/icon_pattern.svg') }}" alt="">
                        </div>
                        <div>
                            <h5>Broadband Services</h5>
                            <p>We offer a range of flexible plans to suit every budget. Whether you're a light user or a
                                heavy streamer, we have the perfect plan for you.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 py-3 wow fadeInUp">
                    <div class="d-flex flex-row">
                        <div class="img-fluid mr-3">
                            <img src="{{ asset('assets/img/icon_pattern.svg') }}" alt="">
                        </div>
                        <div>
                            <h5>Internet Security</h5>
                            <p> At MBTS Broadband Services, we understand the importance of internet security and offer
                                comprehensive solutions to keep you and your data safe.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 py-3 wow fadeInUp">
                    <div class="d-flex flex-row">
                        <div class="img-fluid mr-3">
                            <img src="{{ asset('assets/img/icon_pattern.svg') }}" alt="">
                        </div>
                        <div>
                            <h5>Wifi + OTT + Hotstar </h5>
                            <p>Experience ultra-fast, reliable Wi-Fi coverage throughout your home. Stream, game, and
                                work with confidence, knowing that your connection is stable and robust.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!-- .container -->
    </div> <!-- .page-section -->

    <div class="page-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 py-3 wow zoomIn">
                    <div class="img-place text-center">
                        <img src="{{ asset('assets/img/bnr.png') }}" alt="">
                    </div>
                </div>
                <div class="col-lg-6 py-3 wow fadeInRight">
                    <h2 class="title-section">About <span class="marked">MBTS</span> Broadband Services</h2>
                    <div class="divider"></div>
                    <p>MBTS Broadband Services is one of Arunachal Pradesh leading companies with interest in&nbsp;
                        Internet Service provider, Network Solution, CCTV &amp; Biometrics Solutions. WEFE has been a
                        pioneer force in the networking sector with many firsts and innovations to its credit.</p>
                    <div class="img-place mb-3">
                        <img src="{{ asset('assets/img/testi_image.png') }}" alt="">
                    </div>
                    <a href="#" class="btn btn-primary">More Details</a>
                    <a href="#" class="btn btn-outline border ml-2">Success Stories</a>
                </div>
            </div>
        </div> <!-- .container -->
    </div> <!-- .page-section -->

    <div class="page-section counter-section">
        <div class="container">
            <div class="row align-items-center text-center">
                <div class="col-lg-4">
                    <p>Happy Customer</p>
                    <h2><span class="number" data-number="454545"></span></h2>
                </div>
                <div class="col-lg-4">
                    <p>Home Plan Customer</p>
                    <h2><span class="number" data-number="3434"></span></h2>
                </div>
                <div class="col-lg-4">
                    <p>Business Plan Customer</p>
                    <h2><span class="number" data-number="7343"></span></h2>
                </div>
            </div>
        </div> <!-- .container -->
    </div> <!-- .page-section -->

    <div class="page-section page-section1">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 py-3 wow fadeInLeft">
                    <h2 class="title-section">Check Availabality Check Ability To Connect Our Services In Your
                        Area.</h2>
                    <div class="divider"></div>
                    <h5>My Location</h5>
                </div>
                <div class="col-lg-6 py-3 wow zoomIn">
                    <div class="img-place text-center">
                        <form action="#">
                            <div class="py-2">
                                <input type="text" class="form-control" placeholder="Full name">
                            </div>
                            <div class="py-2">
                                <input type="text" class="form-control" placeholder="Mobile No.">
                            </div>

                            <div class="py-2">
                                <input type="text" class="form-control" placeholder="Type City">
                            </div>
                            <button type="submit" class="btn btn-primary rounded-pill mt-4">Check</button>
                        </form>
                    </div>
                </div>
            </div>
        </div> <!-- .container -->
    </div> <!-- .page-section -->

    <div class="page-section border-top">
        <div class="container">
            <div class="text-center wow fadeInUp">
                <h2 class="title-section">Home Plan</h2>

            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-lg-auto py-3 wow fadeInLeft">
                    <div class="card-pricing">
                        <div class="header">
                            <div class="price-icon"></div>
                            <div class="price-title">BASIC PLAN</div>
                        </div>
                        <div class="body py-3">
                            <div class="price-tag">
                                <span class="currency">Rs</span>
                                <h2 class="display-4">999</h2>
                                <span class="period">/monthly</span>
                            </div>
                            <div class="price-info">
                                <p>15 MBPS</p>
                                <p>UNLIMITED DATA</p>
                            </div>
                        </div>
                        <div class="footer">
                            <a href="/checkout/home-basic-plan" class="btn btn-outline rounded-pill">Choose Plan</a>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-auto py-3 wow fadeInUp">
                    <div class="card-pricing active">
                        <div class="header">
                            <div class="price-labled">Best</div>
                            <div class="price-icon"></div>
                            <div class="price-title">STANDARD PLAN</div>
                        </div>
                        <div class="body py-3">
                            <div class="price-tag">
                                <span class="currency">Rs.</span>
                                <h2 class="display-4">1599</h2>
                                <span class="period">/monthly</span>
                            </div>
                            <div class="price-info">
                                <p>20 MBPS</p>
                                <p>UNLIMITED DATA</p>
                            </div>
                        </div>
                        <div class="footer">
                            <a href="checkout.php" class="btn btn-outline rounded-pill">Choose Plan</a>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-auto py-3 wow fadeInRight">
                    <div class="card-pricing">
                        <div class="header">
                            <div class="price-icon"></div>
                            <div class="price-title">PREMIUM PLAN</div>
                        </div>
                        <div class="body py-3">
                            <div class="price-tag">
                                <span class="currency">Rs.</span>
                                <h2 class="display-4">2500</h2>
                                <span class="period">/monthly</span>
                            </div>
                            <div class="price-info">
                                <p>25 MBPS</p>
                                <p>UNLIMITED DATA</p>
                            </div>
                        </div>
                        <div class="footer">
                            <a href="checkout.php" class="btn btn-outline rounded-pill">Choose Plan</a>
                        </div>
                    </div>
                </div>

            </div>
        </div> <!-- .container -->
    </div> <!-- .page-section -->

    <div class="page-section border-top">
        <div class="container">
            <div class="text-center wow fadeInUp">
                <h2 class="title-section">Business Plan</h2>

            </div>

            <div class="row justify-content-center">
                <div class="col-12 col-lg-auto py-3 wow fadeInLeft">
                    <div class="card-pricing">
                        <div class="header">
                            <div class="price-icon"></div>
                            <div class="price-title">BASIC PLAN</div>
                        </div>
                        <div class="body py-3">
                            <div class="price-tag">
                                <span class="currency">Rs</span>
                                <h2 class="display-4">3500</h2>
                                <span class="period">/monthly</span>
                            </div>
                            <div class="price-info">
                                <p>10 MBPS</p>
                                <p>UNLIMITED DATA</p>
                            </div>
                        </div>
                        <div class="footer">
                            <a href="checkout.php" class="btn btn-outline rounded-pill">Choose Plan</a>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-auto py-3 wow fadeInUp">
                    <div class="card-pricing active">
                        <div class="header">
                            <div class="price-labled">Best</div>
                            <div class="price-icon"></div>
                            <div class="price-title">STANDARD PLAN</div>
                        </div>
                        <div class="body py-3">
                            <div class="price-tag">
                                <span class="currency">Rs.</span>
                                <h2 class="display-4">5200</h2>
                                <span class="period">/monthly</span>
                            </div>
                            <div class="price-info">
                                <p>20 MBPS</p>
                                <p>UNLIMITED DATA</p>
                            </div>
                        </div>
                        <div class="footer">
                            <a href="checkout.php" class="btn btn-outline rounded-pill">Choose Plan</a>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-auto py-3 wow fadeInRight">
                    <div class="card-pricing">
                        <div class="header">
                            <div class="price-icon"></div>
                            <div class="price-title">PREMIUM PLAN</div>
                        </div>
                        <div class="body py-3">
                            <div class="price-tag">
                                <span class="currency">Rs.</span>
                                <h2 class="display-4">7500</h2>
                                <span class="period">/monthly</span>
                            </div>
                            <div class="price-info">
                                <p>30 MBPS</p>
                                <p>UNLIMITED DATA</p>
                            </div>
                        </div>
                        <div class="footer">
                            <a href="checkout.php" class="btn btn-outline rounded-pill">Choose Plan</a>
                        </div>
                    </div>
                </div>

            </div>
        </div> <!-- .container -->
    </div> <!-- .page-section -->

    <div class="page-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 py-3 wow fadeInUp">
                    <h2 class="title-section">Get in Touch</h2>
                    <div class="divider"></div>
                    <p>MBTS Broadband Services is one of Arunachal Pradesh leading companies with interest in Internet
                        Service provider, Network Solution, CCTV & Biometrics Solutions.</p>

                    <ul class="contact-list">
                        <li>
                            <div class="icon"><span class="mai-map"></span></div>
                            <div class="content">Address : Arunachal Pradesh</div>
                        </li>
                        <li>
                            <div class="icon"><span class="mai-mail"></span></div>
                            <div class="content"><a href="#">info@mbtsbroadbandservice.com</a></div>
                        </li>
                        <li>
                            <div class="icon"><span class="mai-phone-portrait"></span></div>
                            <div class="content"><a href="#">+91-7085754774</a></div>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-6 py-3 wow fadeInUp">
                    <div class="subhead">Contact Us</div>
                    <h2 class="title-section">Drop Us a Line</h2>
                    <div class="divider"></div>

                    <form action="#">
                        <div class="py-2">
                            <input type="text" class="form-control" placeholder="Full name">
                        </div>
                        <div class="py-2">
                            <input type="text" class="form-control" placeholder="Email">
                        </div>
                        <div class="py-2">
                            <textarea rows="6" class="form-control" placeholder="Enter message"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary rounded-pill mt-4">Send Message</button>
                    </form>
                </div>
            </div>
        </div> <!-- .container -->
    </div> <!-- .page-section -->

    <div class="page-section border-top">
        <div class="container">
            <div class="text-center wow fadeInUp">
                <h2 class="title-section">Gallery</h2>

                <div class="row align-items-center">
                    <div class="col-lg-4 py-3">
                        <div class="img-fluid text-center">
                            <img src="{{ asset('assets/img/img1.jpeg') }}" width="100%">
                        </div>
                    </div>
                    <div class="col-lg-4 py-3">
                        <div class="img-fluid text-center">
                            <img src="{{ asset('assets/img/img2.jpeg') }}" width="100%">
                        </div>
                    </div>
                    <div class="col-lg-4 py-3">
                        <div class="img-fluid text-center">
                            <img src="{{ asset('assets/img/img3.jpeg') }}" width="100%">
                        </div>
                    </div>


                </div>
            </div> <!-- .container -->
        </div> <!-- .page-section -->
    </div> <!-- .page-section -->
    <div class="page-section border-top">
        <div class="container">
            <div class="text-center wow fadeInUp">
                <div class="row align-items-center">
                    <div class="col-lg-12 background-bnr py-3">
                        <div class="img-fluid text-center">
                            <h1>Looking for fast and reliable internet?</h1>
                            <p>We've got you covered with our latest broadband offers:</p>
                        </div>
                        <a href="new-connection.php" class="btn btn-primary rounded-pill mt-4">Apply New Connection</a>
                    </div>


                </div>
            </div> <!-- .container -->
        </div> <!-- .page-section -->
    </div>
@endsection
