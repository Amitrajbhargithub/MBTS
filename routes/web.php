<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\PayUMoneyController;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CashfreePaymentController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Route::get('/', [PageController::class, 'index'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/services', [PageController::class, 'services'])->name('services');

Route::get('/services', [PageController::class, 'services'])->name('services');

Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/refund', [PageController::class, 'refund'])->name('refund');
Route::get('/career', [PageController::class, 'career'])->name('career');
Route::get('/news', [PageController::class, 'news'])->name('news');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');

Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/home-plan', [PageController::class, 'homePlan'])->name('home-plan');
Route::get('/business-plan', [PageController::class, 'businessPlan'])->name('business-plan');
Route::get('/connection', [PageController::class, 'connection'])->name('new-connection');
Route::post('/connection', [PageController::class, 'submitConnection'])->name('submit-connection');
Route::get('/thanks', [PageController::class, 'thank'])->name('thank-you');
Route::get('/checkout/{slug}', [PayUMoneyController::class, 'checkout']);
Route::get('pay-u-money-view',[PayUMoneyController::class,'payUMoneyView']);
Route::post('payu-hash',[PayUMoneyController::class,'payuHash'])->name('payu-hash')->withoutMiddleware([VerifyCsrfToken::class]);
Route::post('checkout/success',[PayUMoneyController::class,'payUResponse'])->name('pay.u.response')->withoutMiddleware([VerifyCsrfToken::class]);
Route::post('pay-u-cancel',[PayUMoneyController::class,'payUCancel'])->name('pay.u.cancel')->withoutMiddleware([VerifyCsrfToken::class]);

// ── PayU API (Mobile App) Callback Routes ─────────────────────────────────
// PayU POSTs to these after payment. Must be web routes (HTML response).
// CSRF exempt because PayU posts from their server, not our browser.
Route::post('/payment/payu/success', [PayUMoneyController::class, 'payUApiSuccess'])
    ->name('payu.api.success')
    ->withoutMiddleware([VerifyCsrfToken::class]);

Route::post('/payment/payu/failure', [PayUMoneyController::class, 'payUApiFailure'])
    ->name('payu.api.failure')
    ->withoutMiddleware([VerifyCsrfToken::class]);




Route::get('cashfree/payments/create', [CashfreePaymentController::class, 'create'])->name('callback');
Route::post('cashfree/payments/store', [CashfreePaymentController::class, 'store'])->name('store');
Route::any('cashfree/payments/success', [CashfreePaymentController::class, 'success'])->name('success');

