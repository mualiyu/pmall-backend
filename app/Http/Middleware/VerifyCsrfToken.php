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
        //
        'http://test.celldiagnosticslimited.com/*',
        'http://50.87.172.66/*',
        // 'https://osaolt31a8.execute-api.us-east-2.amazonaws.com/*',
        // 'https://api.pmall.com.ng/*',
        // 'http://207.174.213.131/*',
        // 'https://test.igeecloset.com/*',
        // 'http://18.119.84.184/*',
    ];
}
