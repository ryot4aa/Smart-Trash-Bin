<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

class ApiAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated via Sanctum token
        if (!$request->user('sanctum')) {
            throw new AuthenticationException('Unauthenticated');
        }

        return $next($request);
    }
}
