<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\ValidateSignature;

class ValidateSignature extends \Illuminate\Routing\Middleware\ValidateSignature
{
    /**
     * The names of the query string parameters that should be ignored during signature validation.
     *
     * @var array<int, string>
     */
    protected $except = [
        // 'fbclid',
        // 'utm_campaign',
        // 'utm_content',
        // 'utm_medium',
        // 'utm_source',
        // 'utm_term',
    ];
}
