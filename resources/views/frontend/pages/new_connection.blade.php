@extends('frontend.layouts.default')
@section('title', 'MBTS : New Connection')
@section('styles')
    <style>
        .plan {
            width: 40%;
            display: inline-block;
        }

        .planbnr {
            margin-top: 150px;
            padding: 20px;
            border: 2px solid #6c55f9;
            border-radius: 20px;
        }
    </style>
@endsection
@section('content')
    <div class="page-section">
        <div class="container">
            <br><br>
            <div class="row align-items-center">
                <div class="col-lg-6 py-3">

                    <h2 class="title-section">Let's get started</h2>
                    <div class="divider"></div>
                    <div class="subhead">Enter your details below</div>
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{route('submit-connection')}}" method="post">
                        @csrf
                        <div class="plan mb-2">
                            <strong>Select Plan:</strong><br>
                            <input type="radio" id="plan_home" name="plan" value="Home Plan" required
                                {{ old('plan') == 'Home Plan' ? 'checked' : '' }}>
                            <label for="plan_home">Home Plan</label>&nbsp;&nbsp;
                            <input type="radio" id="plan_business" name="plan" value="Business Plan"
                                {{ old('plan') == 'Business Plan' ? 'checked' : '' }}>
                            <label for="plan_business">Business Plan</label>
                        </div>
                        <div class="py-2">
                            <input type="text" name="name" class="form-control" placeholder="Full name" required value="{{ old('name') }}">
                        </div>
                        <div class="py-2">
                            <input type="text" name="mobile" class="form-control" placeholder="Mobile Number (10 digits)" required value="{{ old('mobile') }}" maxlength="10">
                        </div>

                        <div class="py-2">
                            <select class="form-control" id="edit-bo-search-city" name="city" required>
                                <option value="" {{ old('city') ? '' : 'selected' }}>Select City*</option>
                                <option value="Andhra Pradesh">Andhra Pradesh</option>
                                <option value="Andaman and Nicobar Islands">Andaman and Nicobar Islands</option>
                                <option value="Arunachal Pradesh">Arunachal Pradesh</option>
                                <option value="Assam">Assam</option>
                                <option value="Bihar">Bihar</option>
                                <option value="Chandigarh">Chandigarh</option>
                                <option value="Chhattisgarh">Chhattisgarh</option>
                                <option value="Dadar and Nagar Haveli">Dadar and Nagar Haveli</option>
                                <option value="Daman and Diu">Daman and Diu</option>
                                <option value="Delhi">Delhi</option>
                                <option value="Lakshadweep">Lakshadweep</option>
                                <option value="Puducherry">Puducherry</option>
                                <option value="Goa">Goa</option>
                                <option value="Gujarat">Gujarat</option>
                                <option value="Haryana">Haryana</option>
                                <option value="Himachal Pradesh">Himachal Pradesh</option>
                                <option value="Jammu and Kashmir">Jammu and Kashmir</option>
                                <option value="Jharkhand">Jharkhand</option>
                                <option value="Karnataka">Karnataka</option>
                                <option value="Kerala">Kerala</option>
                                <option value="Madhya Pradesh">Madhya Pradesh</option>
                                <option value="Maharashtra">Maharashtra</option>
                                <option value="Manipur">Manipur</option>
                                <option value="Meghalaya">Meghalaya</option>
                                <option value="Mizoram">Mizoram</option>
                                <option value="Nagaland">Nagaland</option>
                                <option value="Odisha">Odisha</option>
                                <option value="Punjab">Punjab</option>
                                <option value="Rajasthan">Rajasthan</option>
                                <option value="Sikkim">Sikkim</option>
                                <option value="Tamil Nadu">Tamil Nadu</option>
                                <option value="Telangana">Telangana</option>
                                <option value="Tripura">Tripura</option>
                                <option value="Uttar Pradesh">Uttar Pradesh</option>
                                <option value="Uttarakhand">Uttarakhand</option>
                                <option value="West Bengal">West Bengal</option>
                            </select>
                        </div>
                        <div class="py-2">
                            <textarea rows="6" name="address" class="form-control"
                                      placeholder="Enter Address" required>{{ old('address') }}</textarea>
                        </div>
                        <input type="submit" class="btn btn-primary rounded-pill mt-4" value="Submit">
                    </form>
                </div>
                <div class="col-lg-6 py-3">
                    <div class="planbnr">
                        <h2>15 Mbps</h2>
                        <p>Free Router Usage</p>
                        <p>Free Installation<br>
                            *Unlimited Broadband data</p>
                        <h1>MBTS Basic Home</h1>
                        <h2>₹999/month*</h2>
                        <p>OTT Apps Included</p>
                        <button type="submit" class="btn btn-primary rounded-pill mt-4">Buy Now</button>
                    </div>

                    <div class="divider"></div>

                </div>
            </div>
        </div> <!-- .container -->
    </div> <!-- .page-section -->
@endsection
