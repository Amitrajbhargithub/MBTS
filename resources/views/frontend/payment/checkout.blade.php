@extends('frontend.layouts.default')
@section('title', 'MBTS : Home Plan')
@section('styles')
    <style>
        .buttn {
            width: 100%;
        }
        .row.align-items-center.checkout {
            border: 2px solid #ccc;
            border-radius: 20px;
        }
    </style>
@endsection
@section('content')
    <div class="page-section">
        <div class="container">
            <h2 class="title-section">Checkout</h2>
            <form action="{{$data['action']}}" method="post" id="checkout">
                <input type="hidden" name="key" value="{{$data['key']}}" />
                <input type="hidden" id="hash" name="hash" value=""/>
                <input type="hidden" name="txnid" value="{{$data['txnid']}}" />
                <input type="hidden" name="amount" value="{{$data['amount']}}" />
                <input type="hidden" name="productinfo" value="{{$data['productinfo']}}">
                <input type="hidden" name="surl" value="{{$data['surl']}}" />
                <input type="hidden" name="furl" value="{{$data['furl']}}" />
                <input type="hidden" name="service_provider" value="{{$data['service_provider']}}"  />
                <div class="row align-items-center checkout">
                    <div class="col-lg-6 py-3">
                        <div class="subhead">Enter your details below</div>
                            <div class="py-2">
                                <input type="text" id="firstname" name="firstname" class="form-control" placeholder="Full name" required>
                            </div>
                            <div class="py-2">
                                <input type="email" id="email" name="email" class="form-control" placeholder="Email" required>
                            </div>
                            <div class="py-2">
                                <input type="text" name="phone" class="form-control" placeholder="Mobile Number" required>
                            </div>
                            <div class="py-2">
                                <select  class="form-control" id="city" name="city"><option value="" selected="selected">Select City*</option>
                                    <option value="Bengaluru">Arunachal Pradesh</option>
                                    <option value="Bengaluru">Bengaluru</option>
                                    <option value="Bhimavaram">Bhimavaram</option>
                                    <option value="Chennai">Chennai</option><option value="Delhi">Delhi</option><option value="Hyderabad">Hyderabad</option><option value="Ahmedabad">Ahmedabad</option><option value="Coimbatore">Coimbatore</option><option value="Eluru">Eluru</option><option value="Ghaziabad">Ghaziabad</option><option value="Guntur">Guntur</option><option value="Hosur">Hosur</option><option value="Jaipur">Jaipur</option><option value="Kakinada">Kakinada</option><option value="Kanchipuram">Kanchipuram</option><option value="Lucknow">Lucknow</option><option value="Machilipatnam">Machilipatnam</option><option value="Madurai">Madurai</option><option value="Nellore">Nellore</option><option value="Ongole">Ongole</option><option value="Polachi">Polachi</option><option value="Pune">Pune</option><option value="Rajahmundry">Rajahmundry</option><option value="Tadepalligudem">Tadepalligudem</option><option value="Tenali">Tenali</option><option value="Tirupati">Tirupati</option><option value="Tiruvallur">Tiruvallur</option><option value="Trichy">Trichy</option><option value="Tumkur">Tumkur</option><option value="Vijayawada">Vijayawada</option><option value="Visakhapatnam">Visakhapatnam</option><option value="Warangal">Warangal</option><option value="Others">Others</option></select>
                            </div>
                    </div>
                    <div class="col-lg-6 py-8">
                        <div class="planbnr">
                            <p></p>
                            <h1>MBTS {{$plan->name}} {{ucfirst($plan->type)}}</h1>
                            <h2>{{$plan->speed}}</h2>
                            <h2>₹{{$plan->amount}}/month*</h2>
                            <p>OTT Apps Included</p>
                        </div>
                        <div class="mdc-form-field">
                            <div class="mdc-checkbox">
                                <input type="checkbox"
                                       class="mdc-checkbox__native-control"
                                       id="checkbox-1" required/> I Agree to Terms and Conditions
                            </div>
                            <label for="checkbox-1"></label>
                        </div>
                    </div>
                </div>
                <div class="text-right buttn">
                    <input type="submit" class="btn btn-primary rounded-pill mt-4" value="Proceed to Paymemt">
                </div>
            </form>
        </div>
    </div> <!-- .page-section -->
@endsection
@section('scripts')
    <script>
        $('#checkout').submit(function(e) {
            e.preventDefault();
            let currentForm = $(this);
            let dataString = currentForm.serialize();
            $.ajax({
                type: 'POST',
                url: "{{route('payu-hash')}}",
                data: dataString,
                dataType: 'json',
                success: function(data) {
                    console.log(data);
                    $('#hash').val(data.hash);
                    currentForm.unbind('submit').submit();
                },
                error: function() {
                    alert('error in creating payment hash');
                }
            });
        });
    </script>
@endsection
