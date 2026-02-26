<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayUMoneyController extends Controller
{
    public function payUMoneyView()
    {
        $MERCHANT_KEY = config('payu.merchant_key'); // TEST MERCHANT KEY
        $SALT = config('payu.salt'); // TEST SALT

        $PAYU_BASE_URL = config('payu.base_url');

        //$PAYU_BASE_URL = "https://secure.payu.in"; // PRODUCTION
        $name = 'Haresh Chauhan';
        $successURL = route('pay.u.response');
        $failURL = route('pay.u.cancel');
        $email = 'example@gmail.com';
        $amount = 1000;

        $action = '';
        $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
        $posted = [
            'key' => $MERCHANT_KEY,
            'txnid' => $txnid,
            'amount' => $amount,
            'firstname' => $name,
            'email' => $email,
            'productinfo' => 'Webappfix',
            'surl' => $successURL,
            'furl' => $failURL,
            'service_provider' => 'payu_paisa',
        ];

        if(empty($posted['txnid'])) {
            $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
        }
        else{
            $txnid = $posted['txnid'];
        }

        $hash = '';
        $hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";

        if(empty($posted['hash']) && sizeof($posted) > 0) {
            $hashVarsSeq = explode('|', $hashSequence);
            $hash_string = '';
            foreach($hashVarsSeq as $hash_var) {
                $hash_string .= isset($posted[$hash_var]) ? $posted[$hash_var] : '';
                $hash_string .= '|';
            }
            $hash_string .= $SALT;

            $hash = strtolower(hash('sha512', $hash_string));
            $action = $PAYU_BASE_URL . '/_payment';
        }
        elseif(!empty($posted['hash']))
        {
            $hash = $posted['hash'];
            $action = $PAYU_BASE_URL . '/_payment';
        }

        return view('frontend.payment.payu',compact('action','hash','MERCHANT_KEY','txnid','successURL','failURL','name','email','amount'));
    }

    public function checkout($slug)
    {
        $plan = Plan::where('slug', $slug)->first();
        if (is_null($plan)) {
            return redirect()->back()->withErrors(['Requested plan doesn\'t exists']);
        }
        $data = [
            'key' => config('payu.merchant_key'),
            'txnid' => substr(hash('sha256', mt_rand() . microtime()), 0, 20),
            'amount' => $plan->amount,
            'productinfo' => $plan->type . ' ' . $plan->name,
            'surl' => route('pay.u.response'),
            'furl' => route('pay.u.cancel'),
            'service_provider' => '',
            'action' => config('payu.base_url') . '/_payment',
            'salt' => config('payu.salt')
        ];
        return view('frontend.payment.checkout', compact('data', 'plan'));
    }

    public function payUResponse(Request $request)
    {
        $responseData = $request->all();
        $data = [
            'transaction_id' => $responseData['txnid'],
            'payu_id' => $responseData['mihpayid'],
            'amount' => $responseData['amount'],
            'plan_info' => $responseData['productinfo'],
            'customer_email' => $responseData['email'],
            'customer_phone' => $responseData['phone'],
            'customer_name' => $responseData['firstname'],
            'status' => $responseData['status']
        ];
        Transaction::create($data);
        $smsUrl = config('sms.url');
        $smsTemplate = config('sms.payment');
        $smsTemplate = str_replace('{customer_name}', $data['customer_name'], $smsTemplate);
        $smsTemplate = str_replace('{amount}', $data['amount'], $smsTemplate);
        $response = Http::get($smsUrl, [
            'user' => config('sms.user'),
            'password' => config('sms.password'),
            'msisdn' => $data['customer_phone'],
            'sid' => config('sms.sid'),
            'msg' => $smsTemplate,
            'fl' => 0,
            'gwid' => 2
        ]);
        Log::info($response->body());
        if ($response->successful()) {
            return view('frontend.payment.thanks', compact('data'));
        } else {
            return view('frontend.payment.thanks', compact('data'))->withErrors(['Error in SMS API Response']);
        }
    }

    public function payUCancel(Request $request)
    {
        return redirect('/checkout/home-basic-plan')->withErrors(['Payment Failed']);
    }

    public function payuHash(Request $request)
    {
        $data = $request->all();
        $hashString = '';
        $hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
        $hashVars = explode('|', $hashSequence);
        foreach ($hashVars as $var) {
            $hashString .= !empty($data[$var]) ? $data[$var] : '';
            $hashString .= '|';
        }
        $hashString .= config('payu.salt');
        $hash = hash('sha512', $hashString);
        return response()->json(['message' => __('success'), 'hash' => $hash]);
    }
}
