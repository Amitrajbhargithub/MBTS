<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MobileApiController;
use App\Http\Controllers\Api\PayUApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| All routes are prefixed with /api automatically.
|
| Public Routes  : No token required
| Protected Routes: Requires Bearer token (auth:sanctum)
|--------------------------------------------------------------------------
*/

// ── Auth Routes (Public) ──────────────────────────────────────────────────
// Route::prefix('auth')->group(function () {

    // Signup: POST /api/auth/signup
    // Body: name, mobile, city
    Route::post('/signup', [MobileApiController::class, 'signup']);

    // Login: POST /api/auth/login
    // Body: mobile
    Route::post('/login', [MobileApiController::class, 'login']);

    // Verify OTP after signup: POST /api/auth/verify-otp
    // Body: mobile, otp
    Route::post('/verify-otp', [MobileApiController::class, 'verifyOtp']);

    // Verify OTP after login: POST /api/auth/login/verify-otp
    // Body: mobile, otp
    Route::post('/login/verify-otp', [MobileApiController::class, 'loginVerifyOtp']);

    // Resend OTP: POST /api/auth/resend-otp
    // Body: mobile
    Route::post('/resend-otp', [MobileApiController::class, 'resendOtp']);

    // Forgot Password – Step 1: Send OTP: POST /api/forgot-password
    // Body: mobile
    Route::post('/forgot-password', [MobileApiController::class, 'forgotPassword']);

    // Forgot Password – Step 2: Verify OTP: POST /api/forgot-password/verify-otp
    // Body: mobile, otp
    Route::post('/forgot-password/verify-otp', [MobileApiController::class, 'forgotPasswordVerifyOtp']);

    // Forgot Password – Step 3: Reset Password: POST /api/reset-password
    // Body: mobile, reset_token, password, password_confirmation
    Route::post('/reset-password', [MobileApiController::class, 'resetPassword']);
// });

// ── Public - Get Plans ────────────────────────────────────────────────────
// GET /api/plans  or  GET /api/plans?type=home|business
Route::get('/plans', [MobileApiController::class, 'plans']);

// ── Protected Routes (Bearer Token Required) ──────────────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Logout: POST /api/auth/logout
    Route::post('/auth/logout', [MobileApiController::class, 'logout']);

    // Logged-in user profile: GET /api/profile
    Route::get('/profile', [MobileApiController::class, 'profile']);

    // Submit new connection: POST /api/connection
    // Body: name, mobile, city, address, plan
    Route::post('/connection', [MobileApiController::class, 'submitConnection']);

    // Get my connections: GET /api/connection
    Route::get('/connection', [MobileApiController::class, 'myConnections']);

    // Upload KYC Document: POST /api/kyc/upload
    // Body (multipart/form-data): document_type, document (file)
    Route::post('/kyc/upload', [MobileApiController::class, 'uploadDocument']);

    // Get KYC Status: GET /api/kyc/status
    Route::get('/kyc/status', [MobileApiController::class, 'getKycStatus']);

    // ── PayU Payment Routes (Protected — Flutter SDK flow) ─────────────────

    // Step 1 – Initiate payment & get hash + all SDK params: POST /api/payment/initiate
    // Body: amount, productinfo, firstname, email, phone, udf1..udf5 (optional)
    Route::post('/payment/initiate', [PayUApiController::class, 'initiatePayment']);

    // Step 1b – Generate hash only (if Flutter app creates txnid): POST /api/payment/hash
    // Body: txnid, amount, productinfo, firstname, email
    Route::post('/payment/hash', [PayUApiController::class, 'generateHash']);

    // Step 2 – Verify payment after Flutter SDK completes: POST /api/payment/verify
    // Body: txnid, mihpayid, status, hash, amount, productinfo, firstname, email, phone
    Route::post('/payment/verify', [PayUApiController::class, 'verifyPayment']);

    // Get payment history for the logged-in user: GET /api/payment/history
    Route::get('/payment/history', [PayUApiController::class, 'paymentHistory']);

    // Get single transaction status: GET /api/payment/status/{txnid}
    Route::get('/payment/status/{txnid}', [PayUApiController::class, 'transactionStatus']);
});

// ── PayU Server-to-Server Callback (Public — PayU server hits this) ──────────
// This is a fallback: PayU posts here to ensure payment is recorded
// even if the Flutter app crashes or loses network after payment.
// POST /api/payment/callback
Route::post('/payment/callback', [PayUApiController::class, 'payuCallback'])->name('api.pay.callback');
