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
        'https://123-testing-ps.nasida.na.gov.ng/*',
        'https://82.180.152.130/*',
        // 'https://api.pmall.com.ng/*',
        // 'http://207.174.213.131/*',
        // 'https://test.igeecloset.com/*',
        // 'http://18.119.84.184/*',
    ];
}
