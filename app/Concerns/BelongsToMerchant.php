<?php

namespace App\Concerns;

use App\Models\Merchant;
use App\Models\Scopes\PerMerchant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToMerchant
{
    protected static function bootBelongsToMerchant(): void
    {
        static::addGlobalScope(new PerMerchant);

        static::creating(function (self $model): void {
            if (! $model->merchant_id && app()->bound('currentMerchantId')) {
                $model->merchant_id = app('currentMerchantId');
            }
        });
    }

    /** @return BelongsTo<Merchant, $this> */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }
}
