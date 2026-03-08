<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // PayU payment gateway callbacks (PayU POSTs from their server — no CSRF token)
        'checkout/success',
        'pay-u-cancel',
        'payu-hash',
        'payment/payu/success',
        'payment/payu/failure',
    ];
}
