@extends('frontend.layouts.default')
@section('title', 'MBTS : Services')
@section('banner')
    <div class="page-banner home-banner">
        <img src="{{ asset('assets/img/001.jpg') }}" width="100%">
        <div class="container h-100">

        </div>
    </div>
@endsection
@section('content')
    <div class="page-section">
        <div class="container">
            <div class="text-left">
                <div class="subhead">Why Choose Us</div>
                <h2 class="title-section">Our <span class="marked">Broadband</span> Service</h2>
                <div class="divider mx-auto"></div>
                <p>A broadband service profile typically refers to the specific characteristics or features of a broadband internet service offered by a provider. These profiles can include several key aspects:
                </p>
            </div>

            <div class="row mt-5 text-left">
                <div class="col-lg-12 py-3">

                    <p>Speed: This is one of the most important aspects of a broadband service profile. It indicates how fast data can be downloaded (received) and uploaded (sent) over the internet. Speed is usually measured in Mbps (megabits per second) for consumer broadband services. Profiles can range from basic speeds suitable for browsing and email to higher speeds needed for streaming HD video or gaming.</p>
                </div>
                <div class="col-lg-12 py-3">

                    <p>Technology: Broadband services can be delivered through various technologies such as fiber optics, DSL (Digital Subscriber Line), cable, or satellite. Each technology has its own advantages and limitations in terms of speed, reliability, and availability.</p>
                </div>
                <div class="col-lg-12 py-3">

                    <p>Quality of Service (QoS): This refers to the level of service reliability and consistency offered by the provider. QoS ensures that customers receive a stable internet connection with minimal interruptions or slowdowns.</p>
                </div>

                <div class="col-lg-12 py-3">

                    <p>Additional Features: Providers may offer additional features as part of their broadband service profile, such as security features (firewalls, antivirus), bundled services (phone or TV), or customer support options.</p>
                </div>


                <div class="col-lg-12 py-3">

                    <p>Availability: The profile also includes information about where the broadband service is available. Availability can vary depending on location, with urban areas typically having more options than rural areas.</p>
                </div>

            </div>
        </div> <!-- .container -->
    </div> <!-- .page-section -->
@endsection
