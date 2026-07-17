<?php

namespace App\Concerns;

use App\Models\Scopes\PerMerchantThroughOrder;

trait BelongsToMerchantThroughOrder
{
    protected static function bootBelongsToMerchantThroughOrder(): void
    {
        static::addGlobalScope(new PerMerchantThroughOrder);
    }
}
