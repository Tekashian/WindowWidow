<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo(Request $request): ?string
    {
        // This is an API-only app - never redirect, always return null
        // The Handler::unauthenticated() will return JSON 401
        return null;
    }
}
