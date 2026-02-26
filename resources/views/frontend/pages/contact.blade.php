@extends('frontend.layouts.default')
@section('title', 'MBTS : Contact Us')
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
            <div class="row align-items-center">
                <div class="col-lg-6 py-3">
                    <h2 class="title-section">Get in Touch</h2>
                    <div class="divider"></div>
                    <p>Merchant Legal entity name: MBTS TECHNOLOGIES PRIVATE LIMITED<br><br>
                        Registered Address: 01, 01, OPPOSTE SBI, G-EXTESION, SBI BANK NAHARLGUN,
                        NAHARLAGAN, NAHARLAGUN, PAPUM PARE, ARUNACHAL PRADESH, 791110, PAPU - I,
                        Arunachal Pradesh, PIN: 791110<br><br>
                        Operational Address: 01, 01, OPPOSTE SBI, G-EXTESION, SBI BANK NAHARLGUN,
                        NAHARLAGAN, NAHARLAGUN, PAPUM PARE, ARUNACHAL PRADESH, 791110, PAPU - I,
                        Arunachal Pradesh, PIN: 791110<br><br>
                        Telephone No: 7085754774<br><br>
                        E-Mail ID: mbtstechnopvtltd@gmail.com</p>

                    <ul class="contact-list">
                        <li>
                            <div class="icon"><span class="mai-map"></span></div>
                            <div class="content">Location - Arunachal Pradesh, PIN: 791110</div>
                        </li>
                        <li>
                            <div class="icon"><span class="mai-mail"></span></div>
                            <div class="content"><a href="#">info@mbtsbroadbandservice.com</a></div>
                        </li>
                        <li>
                            <div class="icon"><span class="mai-phone-portrait"></span></div>
                            <div class="content"><a href="#">+91-8787468841</a> / 9366994261</div>
                        </li>
                    </ul>
                </div>
                <div class="col-lg-6 py-3">
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
@endsection
