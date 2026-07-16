<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BindCurrentMerchant
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($merchantId = $request->user()?->merchant_id) {
            app()->instance('currentMerchantId', $merchantId);
        }

        return $next($request);
    }
}
