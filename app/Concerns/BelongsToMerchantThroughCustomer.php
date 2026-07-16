<?php

namespace App\Concerns;

use App\Models\Scopes\PerMerchantThroughCustomer;

trait BelongsToMerchantThroughCustomer
{
    protected static function bootBelongsToMerchantThroughCustomer(): void
    {
        static::addGlobalScope(new PerMerchantThroughCustomer);
    }
}
