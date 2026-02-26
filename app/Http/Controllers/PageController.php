<?php

namespace App\Http\Controllers;

use App\Models\Connection;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PageController extends Controller
{
    public function index()
    {
        return view('frontend.pages.index');
    }

    public function about()
    {
        return view('frontend.pages.about');
    }

    public function services()
    {
        return view('frontend.pages.services');
    }

    public function contact()
    {
        return view('frontend.pages.contact');
    }

 public function terms()
    {
        return view('frontend.pages.terms');
    }

 public function career()
    {
        return view('frontend.pages.career');
    }
    
    
     public function news()
    {
        return view('frontend.pages.news');
    }


 public function refund()
    {
        return view('frontend.pages.refund');
    }
 public function privacy()
    {
        return view('frontend.pages.privacy');
    }
    public function homePlan()
    {
        $plans = Plan::where('type', 'home')->get()->toArray();
        return view('frontend.pages.home_plan', compact('plans'));
    }

    public function businessPlan()
    {
        $plans = Plan::where('type', 'business')->get()->toArray();
        return view('frontend.pages.business_plan', compact('plans'));
    }

    public function thank()
    {
        return view('frontend.pages.thanks');
    }

    public function connection()
    {
        return view('frontend.pages.new_connection');
    }

    public function submitConnection(Request $request)
    {
        // Validate inputs before hitting the database
        $request->validate([
            'name'    => 'required|string|max:255',
            'mobile'  => 'required|digits:10',
            'city'    => 'required|string|max:255',
            'address' => 'required|string',
            'plan'    => 'required|string',
        ], [
            'mobile.digits' => 'Mobile number must be exactly 10 digits.',
            'plan.required' => 'Please select a plan (Home Plan or Business Plan).',
        ]);

        try {
            // Check for duplicate mobile
            $row = Connection::where('mobile', $request->mobile)->first();
            if (!is_null($row)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => 'Request already registered with this mobile number with Request ID# ' . $row->request_number]);
            }

            $data = $request->only(['name', 'mobile', 'city', 'address', 'plan']);
            $data['request_number'] = 'MBTS' . rand(10000000000, 99999999999);
            Connection::create($data);

            // Send SMS (non-blocking - failure won't stop the redirect)
            try {
                $smsUrl = config('sms.url');
                $response = Http::timeout(10)->get($smsUrl, [
                    'user'     => config('sms.user'),
                    'password' => config('sms.password'),
                    'msisdn'   => $request->mobile,
                    'sid'      => config('sms.sid'),
                    'msg'      => config('sms.lead'),
                    'fl'       => 0,
                    'gwid'     => 2,
                ]);
                Log::info('SMS Response: ' . $response->body());
            } catch (\Exception $smsException) {
                Log::warning('SMS sending failed: ' . $smsException->getMessage());
            }

            return redirect()->route('thank-you');

        } catch (\Exception $exception) {
            Log::error('submitConnection error: ' . $exception->getMessage() . ' | Line: ' . $exception->getLine() . ' | File: ' . $exception->getFile());
            $errorMessage = config('app.debug')
                ? 'Error: ' . $exception->getMessage()
                : 'An unknown error occurred. Please try again later.';
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => $errorMessage]);
        }
    }
}
