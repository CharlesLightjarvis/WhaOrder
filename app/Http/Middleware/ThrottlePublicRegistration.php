<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class ThrottlePublicRegistration
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('POST') || ! $request->routeIs('register.store')) {
            return $next($request);
        }

        $attempts = RateLimiter::increment(
            'registration:'.$request->ip(),
            decaySeconds: 3600,
        );

        abort_if($attempts > 5, 429, 'Too many registration attempts.');

        return $next($request);
    }
}
