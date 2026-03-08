<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Traits\ResponseFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PayUApiController extends Controller
{
    use ResponseFormat;

    // ─────────────────────────────────────────────
    //  HELPER: Build PayU Forward Hash
    // ─────────────────────────────────────────────
    private function buildHash(array $data): string
    {
        $hashSequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
        $hashVars     = explode('|', $hashSequence);
        $hashString   = '';

        foreach ($hashVars as $var) {
            $hashString .= !empty($data[$var]) ? $data[$var] : '';
            $hashString .= '|';
        }
        $hashString .= config('payu.salt');

        return strtolower(hash('sha512', $hashString));
    }

    // ─────────────────────────────────────────────
    //  HELPER: Verify PayU Reverse Response Hash
    // ─────────────────────────────────────────────
    private function verifyResponseHash(array $responseData): bool
    {
        // PayU reverse hash format:
        // sha512( SALT | status | udf10 | udf9 | ... | udf1 | email | firstname | productinfo | amount | txnid | key )
        // IMPORTANT: SALT is FIRST and merchant key is LAST.
        $reverseFields = [
            'status','udf10','udf9','udf8','udf7','udf6',
            'udf5','udf4','udf3','udf2','udf1',
            'email','firstname','productinfo','amount','txnid',
        ];

        $hashStr = config('payu.salt') . '|';
        foreach ($reverseFields as $var) {
            $hashStr .= ($responseData[$var] ?? '') . '|';
        }
        $hashStr .= config('payu.merchant_key');

        $computedHash = strtolower(hash('sha512', $hashStr));
        $receivedHash = strtolower($responseData['hash'] ?? '');

        return hash_equals($computedHash, $receivedHash);
    }

    // ──────────────────────────────────────────────────────────────
    //  1. INITIATE PAYMENT
    //     POST /api/payment/initiate  [Bearer Token]
    //
    //     Flow:
    //       a) App calls this endpoint with payment details
    //       b) We generate txnid + hash, cache the params for 30 min
    //       c) Return { payment_url } — app opens this in a WebView
    //       d) Our server serves an auto-submit HTML form → POSTs to PayU
    //       e) PayU → https://apitest.payu.in/public/#/{hash}/paymentoptions
    //       f) User pays → PayU POSTs to surl / furl
    //       g) App monitors WebView URL for surl/furl to detect result
    //
    //     Body: amount, productinfo, firstname, email, phone,
    //           udf1..udf5 (optional)
    // ──────────────────────────────────────────────────────────────
    public function initiatePayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount'      => 'required|numeric|min:1',
            'productinfo' => 'required|string|max:255',
            'firstname'   => 'required|string|max:100',
            'email'       => 'required|email|max:255',
            'phone'       => 'required|digits:10',
            'udf1'        => 'nullable|string|max:255',
            'udf2'        => 'nullable|string|max:255',
            'udf3'        => 'nullable|string|max:255',
            'udf4'        => 'nullable|string|max:255',
            'udf5'        => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                $validator->errors()->first(),
                'validation_failed',
                422,
                $validator->errors()
            );
        }

        // Generate unique transaction ID
        $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);

        $payuParams = [
            'key'         => config('payu.merchant_key'),
            'txnid'       => $txnid,
            'amount'      => number_format((float) $request->amount, 2, '.', ''),
            'productinfo' => $request->productinfo,
            'firstname'   => $request->firstname,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'udf1'        => $request->udf1 ?? '',
            'udf2'        => $request->udf2 ?? '',
            'udf3'        => $request->udf3 ?? '',
            'udf4'        => $request->udf4 ?? '',
            'udf5'        => $request->udf5 ?? '',
            // PayU POSTs back to these URLs after payment.
            // Must be web routes (browser-visible HTML page), NOT JSON API routes.
            'surl'        => url('/payment/payu/success'),
            'furl'        => url('/payment/payu/failure'),
        ];

        // Build the hash
        $payuParams['hash']   = $this->buildHash($payuParams);
        $payuParams['action'] = config('payu.base_url') . '/_payment';

        // Cache payment params for 30 minutes (used by redirectToPayU)
        Cache::put('payu_txn_' . $txnid, $payuParams, now()->addMinutes(30));

        // The payment_url is what the mobile app opens in a WebView.
        // It auto-submits an HTML form to PayU → PayU redirects to their Bolt page.
        $paymentUrl = url('/api/payment/redirect/' . $txnid);

        return $this->successResponse('Payment initiated successfully.', [
            'txnid'       => $txnid,
            'amount'      => $payuParams['amount'],
            'productinfo' => $payuParams['productinfo'],
            // ← Open this URL in a WebView
            'payment_url' => $paymentUrl,
            // The app should watch the WebView URL for these patterns to detect result
            'success_url' => url('/payment/payu/success'),
            'failure_url' => url('/payment/payu/failure'),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  2. REDIRECT TO PAYU (WebView opens this)
    //     GET /api/payment/redirect/{txnid}  [Public — browser only]
    //
    //     Serves an HTML page with a hidden form that auto-submits
    //     to PayU's _payment endpoint.
    //     PayU then redirects browser to:
    //       https://apitest.payu.in/public/#/{hash}/paymentoptions
    // ──────────────────────────────────────────────────────────────
    public function redirectToPayU(string $txnid)
    {
        // Retrieve cached payment params
        $payuParams = Cache::get('payu_txn_' . $txnid);

        if (!$payuParams) {
            return response('<html><body>
                <h3 style="font-family:sans-serif;text-align:center;color:red;margin-top:80px;">
                  Payment session expired or invalid.<br>Please try again.
                </h3>
            </body></html>', 410)->header('Content-Type', 'text/html');
        }

        $action = $payuParams['action'];
        unset($payuParams['action']); // Don't submit 'action' as a form field

        // Build hidden form fields
        $fields = '';
        foreach ($payuParams as $key => $value) {
            $escapedValue = htmlspecialchars((string) $value, ENT_QUOTES);
            $escapedKey   = htmlspecialchars((string) $key, ENT_QUOTES);
            $fields .= "<input type=\"hidden\" name=\"{$escapedKey}\" value=\"{$escapedValue}\">\n";
        }

        // Return HTML that auto-submits to PayU
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting to Payment...</title>
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
            max-width: 340px;
            width: 90%;
        }
        .logo { font-size: 40px; margin-bottom: 16px; }
        h2 { color: #1a1a2e; font-size: 18px; margin-bottom: 8px; }
        p  { color: #666; font-size: 14px; margin-bottom: 24px; }
        .spinner {
            width: 40px; height: 40px;
            border: 4px solid #e2e8f0;
            border-top-color: #4361ee;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .amount {
            background: #f0f4f8;
            border-radius: 8px;
            padding: 10px 20px;
            display: inline-block;
            font-size: 22px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">💳</div>
        <h2>Redirecting to PayU</h2>
        <p>Please wait while we redirect you to the secure payment page.</p>
        <div class="spinner"></div>
        <div class="amount">₹{$payuParams['amount']}</div>
        <p style="font-size:12px;color:#999;">Secured by PayU &nbsp;🔒</p>
    </div>

    <form id="payuForm" action="{$action}" method="POST" style="display:none;">
        {$fields}
    </form>

    <script>
        // Auto-submit after a short delay (so user sees the loading screen)
        setTimeout(function () {
            document.getElementById('payuForm').submit();
        }, 1200);
    </script>
</body>
</html>
HTML;

        return response($html)->header('Content-Type', 'text/html');
    }

    // ──────────────────────────────────────────────────────────────
    //  3. GENERATE HASH ONLY (optional helper)
    //     POST /api/payment/hash  [Bearer Token]
    //     Body: txnid, amount, productinfo, firstname, email
    // ──────────────────────────────────────────────────────────────
    public function generateHash(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'txnid'       => 'required|string',
            'amount'      => 'required|numeric|min:1',
            'productinfo' => 'required|string',
            'firstname'   => 'required|string',
            'email'       => 'required|email',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                $validator->errors()->first(),
                'validation_failed',
                422,
                $validator->errors()
            );
        }

        $data = array_merge(
            ['key' => config('payu.merchant_key')],
            $request->only([
                'txnid', 'amount', 'productinfo', 'firstname', 'email',
                'udf1', 'udf2', 'udf3', 'udf4', 'udf5',
                'udf6', 'udf7', 'udf8', 'udf9', 'udf10',
            ])
        );

        $hash = $this->buildHash($data);

        return $this->successResponse('Hash generated successfully.', [
            'hash'            => $hash,
            'key'             => config('payu.merchant_key'),
            'txnid'           => $data['txnid'],
            'amount'          => $data['amount'],
            'payu_action_url' => config('payu.base_url') . '/_payment',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  4. PAYMENT SUCCESS CALLBACK
    //     POST /api/payment/success  [Public — PayU posts here]
    //
    //     PayU posts form data here after successful payment.
    //     App's WebView should detect this URL as the final URL,
    //     then call GET /api/payment/status/{txnid} to get the result.
    // ──────────────────────────────────────────────────────────────
    public function paymentSuccess(Request $request)
    {
        $responseData = $request->all();

        Log::info('PayU Success Callback', $responseData);

        // Verify hash to prevent tampered responses
        if (!$this->verifyResponseHash($responseData)) {
            Log::warning('PayU hash mismatch on success callback', $responseData);

            return response()->json([
                'status'  => 'failed',
                'message' => 'Payment verification failed. Hash mismatch.',
                'data'    => [],
            ], 422);
        }

        // Avoid duplicate transaction records
        $existing = Transaction::where('transaction_id', $responseData['txnid'])->first();
        if ($existing) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Payment already recorded.',
                'data'    => [
                    'transaction_id' => $existing->transaction_id,
                    'payu_id'        => $existing->payu_id,
                    'amount'         => $existing->amount,
                    'plan_info'      => $existing->plan_info,
                    'customer_name'  => $existing->customer_name,
                    'status'         => $existing->status,
                ],
            ]);
        }

        // Save transaction
        $transaction = Transaction::create([
            'transaction_id' => $responseData['txnid'],
            'payu_id'        => $responseData['mihpayid'],
            'amount'         => $responseData['amount'],
            'plan_info'      => $responseData['productinfo'],
            'customer_email' => $responseData['email'],
            'customer_phone' => $responseData['phone'],
            'customer_name'  => $responseData['firstname'],
            'status'         => $responseData['status'],
        ]);

        // Clear the cached payment params
        Cache::forget('payu_txn_' . $responseData['txnid']);

        // Send payment confirmation SMS (non-blocking)
        try {
            $smsTemplate = config('sms.payment');
            $smsTemplate = str_replace('{customer_name}', $transaction->customer_name, $smsTemplate);
            $smsTemplate = str_replace('{amount}', $transaction->amount, $smsTemplate);

            Http::timeout(10)->get(config('sms.url'), [
                'user'     => config('sms.user'),
                'password' => config('sms.password'),
                'msisdn'   => $transaction->customer_phone,
                'sid'      => config('sms.sid'),
                'msg'      => $smsTemplate,
                'fl'       => 0,
                'gwid'     => 2,
            ]);
        } catch (\Exception $e) {
            Log::warning('Payment SMS failed: ' . $e->getMessage());
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Payment successful.',
            'data'    => [
                'transaction_id' => $transaction->transaction_id,
                'payu_id'        => $transaction->payu_id,
                'amount'         => $transaction->amount,
                'plan_info'      => $transaction->plan_info,
                'customer_name'  => $transaction->customer_name,
                'status'         => $transaction->status,
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  5. PAYMENT FAILURE CALLBACK
    //     POST /api/payment/failure  [Public — PayU posts here]
    // ──────────────────────────────────────────────────────────────
    public function paymentFailure(Request $request)
    {
        $responseData = $request->all();

        Log::warning('PayU Failure Callback', $responseData);

        // Record failed transaction for audit
        $existing = Transaction::where('transaction_id', $responseData['txnid'] ?? '')->first();
        if (!$existing && !empty($responseData['txnid'])) {
            Transaction::create([
                'transaction_id' => $responseData['txnid'],
                'payu_id'        => $responseData['mihpayid'] ?? null,
                'amount'         => $responseData['amount']    ?? 0,
                'plan_info'      => $responseData['productinfo'] ?? '',
                'customer_email' => $responseData['email']       ?? '',
                'customer_phone' => $responseData['phone']       ?? '',
                'customer_name'  => $responseData['firstname']   ?? '',
                'status'         => 'failure',
            ]);
        }

        // Clear cache
        if (!empty($responseData['txnid'])) {
            Cache::forget('payu_txn_' . $responseData['txnid']);
        }

        return response()->json([
            'status'  => 'failed',
            'message' => 'Payment failed or was cancelled.',
            'data'    => [
                'txnid'  => $responseData['txnid'] ?? null,
                'reason' => $responseData['field9'] ?? $responseData['error_Message'] ?? 'Payment was not completed.',
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  6. GET PAYMENT HISTORY  [Bearer Token]
    //     GET /api/payment/history
    // ──────────────────────────────────────────────────────────────
    public function paymentHistory(Request $request)
    {
        $user = $request->user();

        $transactions = Transaction::where('customer_email', $user->email)
            ->orWhere('customer_phone', $user->mobile)
            ->latest()
            ->get();

        return $this->successResponse('Payment history retrieved successfully.', $transactions);
    }

    // ──────────────────────────────────────────────────────────────
    //  7. GET SINGLE TRANSACTION STATUS  [Bearer Token]
    //     GET /api/payment/status/{txnid}
    //
    //     App calls this after WebView detects surl/furl redirect
    //     to get the final payment result.
    // ──────────────────────────────────────────────────────────────
    public function transactionStatus(Request $request, string $txnid)
    {
        $transaction = Transaction::where('transaction_id', $txnid)->first();

        if (!$transaction) {
            return $this->errorResponse(
                'Transaction not found. Payment may still be processing.',
                'not_found',
                404
            );
        }

        return $this->successResponse('Transaction status retrieved.', [
            'transaction_id' => $transaction->transaction_id,
            'payu_id'        => $transaction->payu_id,
            'amount'         => $transaction->amount,
            'plan_info'      => $transaction->plan_info,
            'customer_name'  => $transaction->customer_name,
            'customer_email' => $transaction->customer_email,
            'status'         => $transaction->status,   // success | failure | pending
            'created_at'     => $transaction->created_at,
        ]);
    }
}
