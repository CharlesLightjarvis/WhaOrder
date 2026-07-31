<?php

namespace App\Jobs\Middleware;

use Closure;

class UseMerchantContext
{
    public function __construct(
        private readonly string $merchantId,
    ) {}

    public function handle(object $job, Closure $next): void
    {
        app()->instance('currentMerchantId', $this->merchantId);

        try {
            $next($job);
        } finally {
            app()->forgetInstance('currentMerchantId');
        }
    }
}
