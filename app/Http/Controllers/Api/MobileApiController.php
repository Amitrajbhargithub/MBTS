<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Connection;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MobileApiController extends Controller
{
    // ─────────────────────────────────────────────
    //  HELPER: Send SMS OTP
    // ─────────────────────────────────────────────
    private function sendOtp(string $mobile, string $otp): void
    {
        try {
            $message = "Your OTP for MBTS Broadband login is: {$otp}. Valid for 10 minutes. Regards MBTS Broadband Pvt. Ltd";
            Http::timeout(10)->get(config('sms.url'), [
                'user'     => config('sms.user'),
                'password' => config('sms.password'),
                'msisdn'   => $mobile,
                'sid'      => config('sms.sid'),
                'msg'      => $message,
                'fl'       => 0,
                'gwid'     => 2,
            ]);
        } catch (\Exception $e) {
            Log::warning('OTP SMS failed: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────
    //  1. SIGNUP - Step 1: Register user
    //     POST /api/auth/signup
    //     Body: name, mobile, city
    // ─────────────────────────────────────────────
    public function signup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255',
            'mobile' => 'required|digits:10',
            'city'   => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Check if mobile already registered
        $existingUser = User::where('mobile', $request->mobile)->first();
        if ($existingUser) {
            return response()->json([
                'success' => false,
                'message' => 'Mobile number already registered. Please login.',
            ], 409);
        }

        $otp = '123456'; // Fixed OTP as per requirement

        // Create / update user
        $user = User::create([
            'name'           => $request->name,
            'mobile'         => $request->mobile,
            'email'          => $request->mobile . '@mbts.local', // placeholder email
            'city'           => $request->city,
            'password'       => Hash::make('Test@123'),           // Fixed password
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        // Send OTP via SMS
        $this->sendOtp($request->mobile, $otp);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to ' . $request->mobile . '. Please verify.',
            'data'    => [
                'mobile'  => $request->mobile,
                'user_id' => $user->id,
            ],
        ], 201);
    }

    // ─────────────────────────────────────────────
    //  2. VERIFY OTP (Signup)
    //     POST /api/auth/verify-otp
    //     Body: mobile, otp
    // ─────────────────────────────────────────────
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|digits:10',
            'otp'    => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::where('mobile', $request->mobile)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Mobile number not found.',
            ], 404);
        }

        if ($user->otp !== $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP. Please try again.',
            ], 401);
        }

        if ($user->otp_expires_at && now()->isAfter($user->otp_expires_at)) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.',
            ], 401);
        }

        // Clear OTP & issue token
        $user->update(['otp' => null, 'otp_expires_at' => null]);
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully.',
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'user'       => [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'mobile' => $user->mobile,
                    'city'   => $user->city,
                ],
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    //  3. LOGIN - Step 1: Send OTP
    //     POST /api/auth/login
    //     Body: mobile (or email), password
    // ─────────────────────────────────────────────
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile'   => 'required|digits:10',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::where('mobile', $request->mobile)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid mobile number or password.',
            ], 401);
        }

        $otp = '123456'; // Fixed OTP as per requirement

        $user->update([
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        // Send OTP via SMS
        $this->sendOtp($request->mobile, $otp);

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to your registered mobile number.',
            'data'    => [
                'mobile' => $request->mobile,
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    //  4. VERIFY LOGIN OTP
    //     POST /api/auth/login/verify-otp
    //     Body: mobile, otp
    // ─────────────────────────────────────────────
    public function loginVerifyOtp(Request $request)
    {
        // Same logic as signup OTP verify
        return $this->verifyOtp($request);
    }

    // ─────────────────────────────────────────────
    //  5. RESEND OTP
    //     POST /api/auth/resend-otp
    //     Body: mobile
    // ─────────────────────────────────────────────
    public function resendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|digits:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::where('mobile', $request->mobile)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Mobile number not found.',
            ], 404);
        }

        $otp = '123456'; // Fixed OTP as per requirement

        $user->update([
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $this->sendOtp($request->mobile, $otp);

        return response()->json([
            'success' => true,
            'message' => 'OTP resent successfully.',
        ]);
    }

    // ─────────────────────────────────────────────
    //  6. LOGOUT
    //     POST /api/auth/logout  [Requires Bearer Token]
    // ─────────────────────────────────────────────
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    // ─────────────────────────────────────────────
    //  7. GET AUTHENTICATED USER PROFILE
    //     GET /api/user  [Requires Bearer Token]
    // ─────────────────────────────────────────────
    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data'    => [
                'id'         => $user->id,
                'name'       => $user->name,
                'mobile'     => $user->mobile,
                'email'      => $user->email,
                'city'       => $user->city,
                'is_active'  => $user->is_active,
                'created_at' => $user->created_at,
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    //  8. GET PLANS
    //     GET /api/plans
    //     Query: ?type=home|business
    // ─────────────────────────────────────────────
    public function plans(Request $request)
    {
        $query = Plan::query();

        if ($request->has('type') && in_array($request->type, ['home', 'business'])) {
            $query->where('type', $request->type);
        }

        $plans = $query->get();

        return response()->json([
            'success' => true,
            'data'    => $plans,
        ]);
    }

    // ─────────────────────────────────────────────
    //  9. SUBMIT NEW CONNECTION
    //     POST /api/connection  [Requires Bearer Token]
    //     Body: name, mobile, city, address, plan
    // ─────────────────────────────────────────────
    public function submitConnection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'    => 'required|string|max:255',
            'mobile'  => 'required|digits:10',
            'city'    => 'required|string|max:255',
            'address' => 'required|string',
            'plan'    => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        // Check for duplicate connection request
        $existing = Connection::where('mobile', $request->mobile)->first();
        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Connection request already exists with Request ID# ' . $existing->request_number,
            ], 409);
        }

        $requestNumber = 'MBTS' . rand(10000000000, 99999999999);

        $connection = Connection::create([
            'user_id'        => $user->id,
            'name'           => $request->name,
            'mobile'         => $request->mobile,
            'city'           => $request->city,
            'address'        => $request->address,
            'plan'           => $request->plan,
            'request_number' => $requestNumber,
            'status'         => 'pending',
        ]);

        // Send confirmation SMS (non-blocking)
        try {
            Http::timeout(10)->get(config('sms.url'), [
                'user'     => config('sms.user'),
                'password' => config('sms.password'),
                'msisdn'   => $request->mobile,
                'sid'      => config('sms.sid'),
                'msg'      => config('sms.lead'),
                'fl'       => 0,
                'gwid'     => 2,
            ]);
        } catch (\Exception $e) {
            Log::warning('Connection SMS failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Connection request submitted successfully.',
            'data'    => [
                'request_number' => $requestNumber,
                'connection'     => $connection,
            ],
        ], 201);
    }

    // ─────────────────────────────────────────────
    //  10. GET MY CONNECTIONS
    //      GET /api/connection  [Requires Bearer Token]
    // ─────────────────────────────────────────────
    public function myConnections(Request $request)
    {
        $connections = Connection::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $connections,
        ]);
    }
}
