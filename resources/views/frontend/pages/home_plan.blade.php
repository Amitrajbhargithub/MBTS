@extends('frontend.layouts.default')
@section('title', 'MBTS : Home Plan')
@section('content')
    <div class="page-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 py-3">
                    <div class="img-fluid text-center">
                        <img src="{{asset('assets/img/bg_image_2.png')}}" alt="">
                    </div>
                </div>
                <div class="col-lg-6 py-3 pr-lg-5">
                    <h2 class="title-section"><span class="marked"></span></h2>
                    <h1>Price: Rs.999/mo.</h1>
                    <p>for 12 or 24 mo</p>

                    <h1>Speeds: 15Mbps</h1>
                    <p>(wireless speeds may vary)</p>

                    <h1>Data cap: Unlimited</h1>
                    <h1>Contract: Service Cost</h1>
                    <div class="divider"></div>

                    <a href="{{url('/checkout/home-basic-plan')}}" class="btn btn-primary">Buy Plan</a>
                    <a href="#" class="btn btn-outline border ml-2">View Plan</a>
                </div>
            </div>
        </div> <!-- .container -->
    </div> <!-- .page-section -->
    <div class="page-section border-top">
        <div class="container">
            <div class="text-center wow fadeInUp">
                <h2 class="title-section">Home Plan</h2>
                <div class="divider mx-auto"></div>
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
                            <a href="{{url('/checkout/home-basic-plan')}}" class="btn btn-outline rounded-pill">Buy Plan</a>
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
                            <a href="{{url('/checkout/home-standard-plan')}}" class="btn btn-outline rounded-pill">Choose Plan</a>
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
                            <a href="{{url('/checkout/home-premium-plan')}}" class="btn btn-outline rounded-pill">Choose Plan</a>
                        </div>
                    </div>
                </div>

            </div>
        </div> <!-- .container -->
    </div> <!-- .page-section -->
@endsection
