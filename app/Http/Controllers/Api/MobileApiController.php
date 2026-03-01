<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Connection;
use App\Models\CustomerKyc;
use Illuminate\Support\Facades\Storage;
use App\Models\Plan;
use App\Models\User;
use App\Traits\ResponseFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MobileApiController extends Controller
{
    use ResponseFormat;

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
    //  1. SIGNUP
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
            return $this->error($validator->errors()->first(), 422, $validator->errors());
        }

        $existingUser = User::where('mobile', $request->mobile)->first();
        if ($existingUser) {
            return $this->errorResponse('Mobile number already registered. Please login.', 'already_registered', 409);
        }

        $otp  = '123456';

        $user = User::create([
            'name'           => $request->name,
            'mobile'         => $request->mobile,
            'email'          => $request->mobile . '@mbts.local',
            'city'           => $request->city,
            'password'       => Hash::make('Test@123'),
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $this->sendOtp($request->mobile, $otp);
        return $this->successResponse('User registered successfully.', $user);
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
            return $this->errorResponse($validator->errors()->first(), 'validation_failed', 422, $validator->errors());
        }

        $user = User::where('mobile', $request->mobile)->first();

        if (!$user) {
            return $this->errorResponse('Mobile number not found.', 'mobile_not_found', 404);
        }

        if ($user->otp !== $request->otp) {
            return $this->errorResponse('Invalid OTP. Please try again.', 'invalid_otp', 401);
        }

        if ($user->otp_expires_at && now()->isAfter($user->otp_expires_at)) {
            return $this->errorResponse('OTP has expired. Please request a new one.', 'otp_expired', 401);
        }

        $user->update(['otp' => null, 'otp_expires_at' => null]);
        $token = $user->createToken('mobile-app')->plainTextToken;

        $user->token = $token; 
        return $this->successResponse('OTP verified successfully.', $user);
    }

    // ─────────────────────────────────────────────
    //  3. LOGIN
    //     POST /api/auth/login
    //     Body: mobile, password
    // ─────────────────────────────────────────────
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile'   => 'required|digits:10',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 'validation_failed', 422, $validator->errors());
        }

        $user = User::where('mobile', $request->mobile)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->errorResponse('Invalid mobile number or password.', 'invalid_credentials', 401);
        }

        $otp = '123456'; // Fixed OTP as per requirement

        $user->update([
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $this->sendOtp($request->mobile, $otp);

        $data =  ['mobile' => $request->mobile];
        return $this->successResponse('OTP sent to your registered mobile number.', $data);
    }

    // ─────────────────────────────────────────────
    //  4. VERIFY LOGIN OTP
    //     POST /api/auth/login/verify-otp
    //     Body: mobile, otp
    // ─────────────────────────────────────────────
    public function loginVerifyOtp(Request $request)
    {
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
            return $this->errorResponse($validator->errors()->first(), 'validation_failed', 422, $validator->errors());
        }

        $user = User::where('mobile', $request->mobile)->first();

        if (!$user) {
            return $this->errorResponse('Mobile number not found.', 'mobile_not_found', 404);
        }

        $otp = '123456'; // Fixed OTP as per requirement

        $user->update([
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $this->sendOtp($request->mobile, $otp);

        return $this->successResponse('OTP resent successfully.', ['mobile' => $request->mobile]);
    }

    // ─────────────────────────────────────────────
    //  6. LOGOUT
    //     POST /api/auth/logout  [Bearer Token]
    // ─────────────────────────────────────────────
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(null, 'Logged out successfully.');
    }

    // ─────────────────────────────────────────────
    //  7. USER PROFILE
    //     GET /api/profile  [Bearer Token]
    // ─────────────────────────────────────────────
    public function profile(Request $request)
    {
        $user = $request->user();

        return $this->successResponse("User profile retrieved successfully.", [
            'id'         => $user->id,
            'name'       => $user->name,
            'mobile'     => $user->mobile,
            'email'      => $user->email,
            'city'       => $user->city,
            'is_active'  => $user->is_active,
            'created_at' => $user->created_at,
        ]);
    }

    // ─────────────────────────────────────────────
    //  8. GET PLANS
    //     GET /api/plans?type=home|business
    // ─────────────────────────────────────────────
    public function plans(Request $request)
    {
        $query = Plan::query();

        if ($request->has('type') && in_array($request->type, ['home', 'business'])) {
            $query->where('type', $request->type);
        }

        return $this->successResponse("Plan fetch successfully",$query->get());
    }

    // ─────────────────────────────────────────────
    //  9. SUBMIT NEW CONNECTION
    //     POST /api/connection  [Bearer Token]
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
           return $this->errorResponse($validator->errors()->first(), 'validation_failed', 422, $validator->errors());
        }

        $existing = Connection::where('mobile', $request->mobile)->first();
        if ($existing) {
            return $this->errorResponse(
                'Connection request already exists with Request ID# ' . $existing->request_number,'already_exist',
                409
            );
            
        }

        $requestNumber = 'MBTS' . rand(10000000000, 99999999999);

        $connection = Connection::create([
            'user_id'        => $request->user()->id,
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

        return $this->successResponse('Connection request submitted successfully.',
            ['request_number' => $requestNumber, 'connection' => $connection]
        );
    }

    // ─────────────────────────────────────────────
    //  10. GET MY CONNECTIONS
    //      GET /api/connection  [Bearer Token]
    // ─────────────────────────────────────────────
    public function myConnections(Request $request)
    {
        $connections = Connection::where('user_id', $request->user()->id)->latest()->get();

        return $this->successResponse('Connection retrive successfully.',$connections);
    }

    // ─────────────────────────────────────────────
    //  11. FORGOT PASSWORD – STEP 1: SEND OTP
    //      POST /api/forgot-password
    //      Body: mobile
    // ─────────────────────────────────────────────
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|digits:10',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 'validation_failed', 422, $validator->errors());
        }

        $user = User::where('mobile', $request->mobile)->first();

        if (!$user) {
            return $this->errorResponse('Mobile number not registered.', 'mobile_not_found', 404);
        }

        $otp = '123456'; // Fixed OTP as per project requirement

        $user->update([
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        $this->sendOtp($request->mobile, $otp);

        return $this->successResponse('OTP sent to your registered mobile number for password reset.', [
            'mobile' => $request->mobile,
        ]);
    }

    // ─────────────────────────────────────────────
    //  12. FORGOT PASSWORD – STEP 2: VERIFY OTP
    //      POST /api/forgot-password/verify-otp
    //      Body: mobile, otp
    //      Returns: reset_token (valid 15 min)
    // ─────────────────────────────────────────────
    public function forgotPasswordVerifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile' => 'required|digits:10',
            'otp'    => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 'validation_failed', 422, $validator->errors());
        }

        $user = User::where('mobile', $request->mobile)->first();

        if (!$user) {
            return $this->errorResponse('Mobile number not found.', 'mobile_not_found', 404);
        }

        if ($user->otp !== $request->otp) {
            return $this->errorResponse('Invalid OTP. Please try again.', 'invalid_otp', 401);
        }

        if ($user->otp_expires_at && now()->isAfter($user->otp_expires_at)) {
            return $this->errorResponse('OTP has expired. Please request a new one.', 'otp_expired', 401);
        }

        // Generate a secure one-time reset token and store it temporarily
        $resetToken = bin2hex(random_bytes(32));

        $user->update([
            'otp'            => $resetToken,          // reuse otp column to store reset token
            'otp_expires_at' => now()->addMinutes(15),
        ]);

        return $this->successResponse('OTP verified. Use the reset_token to set a new password.', [
            'mobile'      => $request->mobile,
            'reset_token' => $resetToken,
        ]);
    }

    // ─────────────────────────────────────────────
    //  13. FORGOT PASSWORD – STEP 3: RESET PASSWORD
    //      POST /api/reset-password
    //      Body: mobile, reset_token, password, password_confirmation
    // ─────────────────────────────────────────────
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile'                => 'required|digits:10',
            'reset_token'           => 'required|string',
            'password'              => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 'validation_failed', 422, $validator->errors());
        }

        $user = User::where('mobile', $request->mobile)->first();

        if (!$user) {
            return $this->errorResponse('Mobile number not found.', 'mobile_not_found', 404);
        }

        if ($user->otp !== $request->reset_token) {
            return $this->errorResponse('Invalid or expired reset token. Please start again.', 'invalid_reset_token', 401);
        }

        if ($user->otp_expires_at && now()->isAfter($user->otp_expires_at)) {
            return $this->errorResponse('Reset token has expired. Please request a new OTP.', 'reset_token_expired', 401);
        }

        // Update password and clear the token
        $user->update([
            'password'       => Hash::make($request->password),
            'otp'            => null,
            'otp_expires_at' => null,
        ]);

        return $this->successResponse('Password reset successfully. Please login with your new password.');
    }

    // ─────────────────────────────────────────────
    //  14. UPLOAD / VERIFY DOCUMENT (KYC)
    //      POST /api/kyc/upload  [Bearer Token]
    //      Body (multipart/form-data):
    //        document_type : aadhar_front | aadhar_back | pancard_front | selfie
    //        document      : file (jpg|jpeg|png|pdf) max 5MB
    // ─────────────────────────────────────────────
    public function uploadDocument(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document_type' => 'required|string|in:' . implode(',', CustomerKyc::DOCUMENT_TYPES),
            'document'      => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors()->first(), 'validation_failed', 422, $validator->errors());
        }

        $user         = $request->user();
        $documentType = $request->document_type;
        $file         = $request->file('document');

        // Delete old file for this document type if it exists
        $existing = CustomerKyc::where('customer_id', $user->id)
                                ->where('document_type', $documentType)
                                ->first();

        if ($existing && Storage::disk('public')->exists($existing->file_path)) {
            Storage::disk('public')->delete($existing->file_path);
        }

        // Store the new file: storage/app/public/kyc/{user_id}/{type}_{timestamp}.{ext}
        $extension = $file->getClientOriginalExtension();
        $filename  = $documentType . '_' . time() . '.' . $extension;
        $filePath  = $file->storeAs('kyc/' . $user->id, $filename, 'public');

        // Upsert the kyc record (insert or update)
        $kyc = CustomerKyc::updateOrCreate(
            [
                'customer_id'   => $user->id,
                'document_type' => $documentType,
            ],
            [
                'file_path'     => $filePath,
                'original_name' => $file->getClientOriginalName(),
                'status'        => 'pending',
            ]
        );

        return $this->successResponse('Document uploaded successfully.', [
            'id'            => $kyc->id,
            'document_type' => $kyc->document_type,
            'status'        => $kyc->status,
            'file_url'      => Storage::disk('public')->url($filePath),
        ]);
    }

    // ─────────────────────────────────────────────
    //  15. GET KYC STATUS
    //      GET /api/kyc/status  [Bearer Token]
    // ─────────────────────────────────────────────
    public function getKycStatus(Request $request)
    {
        $user      = $request->user();
        $documents = CustomerKyc::where('customer_id', $user->id)->get();

        // Build a map of all required document types with their current status
        $kycMap = [];
        foreach (CustomerKyc::DOCUMENT_TYPES as $type) {
            $doc = $documents->firstWhere('document_type', $type);
            $kycMap[$type] = $doc ? [
                'id'       => $doc->id,
                'status'   => $doc->status,
                'file_url' => Storage::disk('public')->url($doc->file_path),
            ] : null;
        }

        $uploadedCount  = $documents->count();
        $totalRequired  = count(CustomerKyc::DOCUMENT_TYPES);
        $allUploaded    = $uploadedCount >= $totalRequired;
        $allVerified    = $documents->where('status', 'verified')->count() === $totalRequired;

        return $this->successResponse('KYC status retrieved successfully.', [
            'all_uploaded' => $allUploaded,
            'all_verified' => $allVerified,
            'documents'    => $kycMap,
        ]);
    }
}
