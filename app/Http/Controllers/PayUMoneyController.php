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

    // ──────────────────────────────────────────────────────────────
    //  Mobile App PayU Callbacks  (PayU POSTs here from browser)
    //  Returns HTML pages — WebView detects URL change to know result
    // ──────────────────────────────────────────────────────────────

    public function payUApiSuccess(Request $request)
    {
        $responseData = $request->all();

        Log::info('PayU API Success Callback', $responseData);

        // Verify PayU response hash.
        // Correct PayU reverse hash: sha512(SALT|status|udf10|...|udf1|email|firstname|productinfo|amount|txnid|key)
        // IMPORTANT: SALT is FIRST and key is LAST — opposite of forward hash.
        $salt = config('payu.salt');
        $reverseFields = ['status','udf10','udf9','udf8','udf7','udf6','udf5','udf4','udf3','udf2','udf1','email','firstname','productinfo','amount','txnid'];
        // Start with salt, then each reversed field, then key at end
        $hashStr = $salt . '|';
        foreach ($reverseFields as $var) {
            $hashStr .= ($responseData[$var] ?? '') . '|';
        }
        $hashStr .= config('payu.merchant_key');
        $computedHash = strtolower(hash('sha512', $hashStr));
        $receivedHash = strtolower($responseData['hash'] ?? '');

        if (!hash_equals($computedHash, $receivedHash)) {
            Log::warning('PayU API: Hash mismatch on success callback', $responseData);
            return response($this->paymentResultHtml('failed', 'Payment verification failed. Please contact support.', $responseData['txnid'] ?? ''))
                ->header('Content-Type', 'text/html');
        }

        // Save transaction (avoid duplicates)
        $existing = Transaction::where('transaction_id', $responseData['txnid'] ?? '')->first();
        if (!$existing) {
            Transaction::create([
                'transaction_id' => $responseData['txnid'],
                'payu_id'        => $responseData['mihpayid'] ?? null,
                'amount'         => $responseData['amount'],
                'plan_info'      => $responseData['productinfo'],
                'customer_email' => $responseData['email'],
                'customer_phone' => $responseData['phone'],
                'customer_name'  => $responseData['firstname'],
                'status'         => $responseData['status'],
            ]);

            // Send SMS (non-blocking)
            try {
                $smsTemplate = config('sms.payment');
                $smsTemplate = str_replace('{customer_name}', $responseData['firstname'], $smsTemplate);
                $smsTemplate = str_replace('{amount}', $responseData['amount'], $smsTemplate);
                Http::timeout(10)->get(config('sms.url'), [
                    'user'     => config('sms.user'),
                    'password' => config('sms.password'),
                    'msisdn'   => $responseData['phone'],
                    'sid'      => config('sms.sid'),
                    'msg'      => $smsTemplate,
                    'fl'       => 0,
                    'gwid'     => 2,
                ]);
            } catch (\Exception $e) {
                Log::warning('PayU API: SMS send failed: ' . $e->getMessage());
            }
        }

        return response($this->paymentResultHtml(
            'success',
            'Payment of ₹' . ($responseData['amount'] ?? '') . ' was successful! Thank you.',
            $responseData['txnid'] ?? ''
        ))->header('Content-Type', 'text/html');
    }

    public function payUApiFailure(Request $request)
    {
        $responseData = $request->all();

        Log::warning('PayU API Failure Callback', $responseData);

        // Save failed transaction for audit
        $existing = Transaction::where('transaction_id', $responseData['txnid'] ?? '')->first();
        if (!$existing && !empty($responseData['txnid'])) {
            Transaction::create([
                'transaction_id' => $responseData['txnid'],
                'payu_id'        => $responseData['mihpayid'] ?? null,
                'amount'         => $responseData['amount']      ?? 0,
                'plan_info'      => $responseData['productinfo'] ?? '',
                'customer_email' => $responseData['email']       ?? '',
                'customer_phone' => $responseData['phone']       ?? '',
                'customer_name'  => $responseData['firstname']   ?? '',
                'status'         => 'failure',
            ]);
        }

        $reason = $responseData['field9'] ?? $responseData['error_Message'] ?? 'Payment was not completed.';

        return response($this->paymentResultHtml(
            'failed',
            'Payment failed or was cancelled. ' . $reason,
            $responseData['txnid'] ?? ''
        ))->header('Content-Type', 'text/html');
    }

    /**
     * Generate a simple, standalone HTML page for payment result.
     * The mobile app WebView detects the URL change and reads data-status attribute.
     */
    private function paymentResultHtml(string $status, string $message, string $txnid): string
    {
        $isSuccess  = $status === 'success';
        $icon       = $isSuccess ? '✅' : '❌';
        $title      = $isSuccess ? 'Payment Successful' : 'Payment Failed';
        $color      = $isSuccess ? '#16a34a' : '#dc2626';
        $bgColor    = $isSuccess ? '#f0fdf4' : '#fef2f2';
        $borderColor= $isSuccess ? '#bbf7d0' : '#fecaca';

        return <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>{$title}</title>
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body {
                        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                        background: #f0f4f8;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        min-height: 100vh;
                    }
                    .card {
                        background: #fff;
                        border-radius: 16px;
                        padding: 40px 32px;
                        text-align: center;
                        box-shadow: 0 4px 24px rgba(0,0,0,0.10);
                        max-width: 360px;
                        width: 90%;
                        border: 1px solid {$borderColor};
                        background: {$bgColor};
                    }
                    .icon { font-size: 56px; margin-bottom: 16px; }
                    h2 { color: {$color}; font-size: 22px; margin-bottom: 12px; font-weight: 700; }
                    p  { color: #555; font-size: 15px; margin-bottom: 10px; line-height: 1.5; }
                    .txnid { font-size: 12px; color: #999; word-break: break-all; margin-top: 12px; }
                    .badge {
                        display: inline-block;
                        background: {$color};
                        color: #fff;
                        border-radius: 20px;
                        padding: 4px 16px;
                        font-size: 13px;
                        font-weight: 600;
                        margin-bottom: 16px;
                    }
                </style>
            </head>
            <body>
                <div class="card" data-status="{$status}" data-txnid="{$txnid}">
                    <div class="icon">{$icon}</div>
                    <div class="badge">{$title}</div>
                    <h2>MBTS Broadband</h2>
                    <p>{$message}</p>
                    <p class="txnid">Transaction ID: {$txnid}</p>
                </div>
            </body>
            </html>
            HTML;
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
