<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Traits\ResponseFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PayUApiController extends Controller
{
    use ResponseFormat;

    // ─────────────────────────────────────────────
    //  HELPER: Build PayU Forward Hash
    //  key|txnid|amount|productinfo|firstname|email|udf1..udf10||SALT
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
    //  SALT|status|...|udf1|email|firstname|productinfo|amount|txnid|key
    // ─────────────────────────────────────────────
    private function verifyResponseHash(array $responseData): bool
    {
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
    //  1. INITIATE PAYMENT  (Flutter SDK flow)
    //     POST /api/payment/initiate  [Bearer Token]
    //
    //     Flutter app calls this endpoint with payment details.
    //     Backend generates txnid + hash and returns ALL params
    //     needed by the PayU Flutter SDK to launch the payment.
    //
    //     Body: amount, productinfo, firstname, email, phone,
    //           udf1..udf5 (optional)
    //
    //     Returns: key, txnid, hash, amount, productinfo, firstname,
    //              email, phone, surl, furl, and environment info
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
        $txnid = 'MBTS' . substr(hash('sha256', mt_rand() . microtime()), 0, 16);

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
            // surl / furl are required by PayU even for SDK flow
            // These are server-to-server callback URLs
            'surl'        => url('/api/payment/callback'),
            'furl'        => url('/api/payment/callback'),
        ];

        // Build the hash
        $payuParams['hash'] = $this->buildHash($payuParams);

        // Save a pending transaction record
        Transaction::create([
            'transaction_id' => $txnid,
            'amount'         => $payuParams['amount'],
            'plan_info'      => $payuParams['productinfo'],
            'customer_email' => $payuParams['email'],
            'customer_phone' => $payuParams['phone'],
            'customer_name'  => $payuParams['firstname'],
            'status'         => 'pending',
        ]);

        // Return all params needed by Flutter PayU SDK
        return $this->successResponse('Payment initiated successfully.', [
            'key'         => $payuParams['key'],
            'txnid'       => $payuParams['txnid'],
            'hash'        => $payuParams['hash'],
            'amount'      => $payuParams['amount'],
            'productinfo' => $payuParams['productinfo'],
            'firstname'   => $payuParams['firstname'],
            'email'       => $payuParams['email'],
            'phone'       => $payuParams['phone'],
            'surl'        => $payuParams['surl'],
            'furl'        => $payuParams['furl'],
            'udf1'        => $payuParams['udf1'],
            'udf2'        => $payuParams['udf2'],
            'udf3'        => $payuParams['udf3'],
            'udf4'        => $payuParams['udf4'],
            'udf5'        => $payuParams['udf5'],
            'environment' => config('payu.environment', 'test'), // 'test' or 'production'
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  2. GENERATE HASH ONLY (lightweight helper)
    //     POST /api/payment/hash  [Bearer Token]
    //     Body: txnid, amount, productinfo, firstname, email,
    //           udf1..udf5 (optional)
    //
    //     Use this if Flutter app generates txnid itself
    //     and only needs the hash from the server.
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
            'hash'        => $hash,
            'key'         => config('payu.merchant_key'),
            'txnid'       => $data['txnid'],
            'amount'      => $data['amount'],
            'surl'        => url('/api/payment/callback'),
            'furl'        => url('/api/payment/callback'),
            'environment' => config('payu.environment', 'test'),
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  3. VERIFY PAYMENT (Flutter SDK sends result here)
    //     POST /api/payment/verify  [Bearer Token]
    //
    //     After the PayU Flutter SDK completes payment, the app
    //     sends the SDK response to this endpoint for server-side
    //     verification and transaction recording.
    //
    //     Body: txnid, mihpayid, status, hash, amount, productinfo,
    //           firstname, email, phone (all from PayU SDK response)
    // ──────────────────────────────────────────────────────────────
    public function verifyPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'txnid'       => 'required|string',
            'mihpayid'    => 'required|string',
            'status'      => 'required|string',
            'hash'        => 'required|string',
            'amount'      => 'required|numeric',
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

        $responseData = $request->all();

        Log::info('PayU SDK Verify Request', $responseData);

        // Step 1: Verify the response hash to prevent tampering
        if (!$this->verifyResponseHash($responseData)) {
            Log::warning('PayU hash mismatch on verify', $responseData);

            // Update transaction status to failed
            Transaction::where('transaction_id', $responseData['txnid'])
                ->update(['status' => 'hash_mismatch']);

            return $this->errorResponse(
                'Payment verification failed. Hash mismatch.',
                'hash_mismatch',
                422
            );
        }

        // Step 2: Check if transaction already verified
        $transaction = Transaction::where('transaction_id', $responseData['txnid'])->first();

        if ($transaction && in_array($transaction->status, ['success', 'failure'])) {
            return $this->successResponse('Payment already verified.', [
                'transaction_id' => $transaction->transaction_id,
                'payu_id'        => $transaction->payu_id,
                'amount'         => $transaction->amount,
                'plan_info'      => $transaction->plan_info,
                'customer_name'  => $transaction->customer_name,
                'status'         => $transaction->status,
            ]);
        }

        // Step 3: Determine payment status
        $paymentStatus = strtolower($responseData['status']) === 'success' ? 'success' : 'failure';

        // Step 4: Update or create transaction record
        $transaction = Transaction::updateOrCreate(
            ['transaction_id' => $responseData['txnid']],
            [
                'payu_id'        => $responseData['mihpayid'],
                'amount'         => $responseData['amount'],
                'plan_info'      => $responseData['productinfo'],
                'customer_email' => $responseData['email'],
                'customer_phone' => $responseData['phone'] ?? '',
                'customer_name'  => $responseData['firstname'],
                'status'         => $paymentStatus,
            ]
        );

        // Step 5: Send payment confirmation SMS on success
        if ($paymentStatus === 'success') {
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
        }

        // Step 6: Return result to Flutter app
        $message = $paymentStatus === 'success'
            ? 'Payment successful.'
            : 'Payment failed or was cancelled.';

        return $this->successResponse($message, [
            'transaction_id' => $transaction->transaction_id,
            'payu_id'        => $transaction->payu_id,
            'amount'         => $transaction->amount,
            'plan_info'      => $transaction->plan_info,
            'customer_name'  => $transaction->customer_name,
            'status'         => $transaction->status,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    //  4. PAYU SERVER CALLBACK (S2S)
    //     POST /api/payment/callback  [Public — PayU server posts here]
    //
    //     PayU sends server-to-server notification here.
    //     This is a fallback to ensure transaction is recorded
    //     even if the Flutter app crashes or network drops.
    // ──────────────────────────────────────────────────────────────
    public function payuCallback(Request $request)
    {
        $responseData = $request->all();

        Log::info('PayU S2S Callback', $responseData);

        if (empty($responseData['txnid'])) {
            return response()->json(['status' => 'error', 'message' => 'Missing txnid'], 400);
        }

        // Verify hash
        if (!$this->verifyResponseHash($responseData)) {
            Log::warning('PayU S2S callback hash mismatch', $responseData);
            return response()->json(['status' => 'error', 'message' => 'Hash mismatch'], 422);
        }

        $paymentStatus = strtolower($responseData['status'] ?? '') === 'success' ? 'success' : 'failure';

        // Update or create transaction
        Transaction::updateOrCreate(
            ['transaction_id' => $responseData['txnid']],
            [
                'payu_id'        => $responseData['mihpayid'] ?? null,
                'amount'         => $responseData['amount'] ?? 0,
                'plan_info'      => $responseData['productinfo'] ?? '',
                'customer_email' => $responseData['email'] ?? '',
                'customer_phone' => $responseData['phone'] ?? '',
                'customer_name'  => $responseData['firstname'] ?? '',
                'status'         => $paymentStatus,
            ]
        );

        // Send SMS on success (if not already sent via verify endpoint)
        if ($paymentStatus === 'success') {
            try {
                $smsTemplate = config('sms.payment');
                $smsTemplate = str_replace('{customer_name}', $responseData['firstname'] ?? '', $smsTemplate);
                $smsTemplate = str_replace('{amount}', $responseData['amount'] ?? '', $smsTemplate);

                Http::timeout(10)->get(config('sms.url'), [
                    'user'     => config('sms.user'),
                    'password' => config('sms.password'),
                    'msisdn'   => $responseData['phone'] ?? '',
                    'sid'      => config('sms.sid'),
                    'msg'      => $smsTemplate,
                    'fl'       => 0,
                    'gwid'     => 2,
                ]);
            } catch (\Exception $e) {
                Log::warning('Payment SMS failed (S2S): ' . $e->getMessage());
            }
        }

        return response()->json(['status' => 'ok']);
    }

    // ──────────────────────────────────────────────────────────────
    //  5. GET PAYMENT HISTORY  [Bearer Token]
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
    //  6. GET SINGLE TRANSACTION STATUS  [Bearer Token]
    //     GET /api/payment/status/{txnid}
    //
    //     Flutter app can poll this to check payment status.
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
            'status'         => $transaction->status,
            'created_at'     => $transaction->created_at,
        ]);
    }
}
