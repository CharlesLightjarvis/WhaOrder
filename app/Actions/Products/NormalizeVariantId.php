<?php

namespace App\Actions\Products;

use Illuminate\Support\Str;

class NormalizeVariantId
{
    /**
     * The model sometimes echoes back get_product_variants' "product_id=.../variant_id=..."
     * pair as a single slash-joined value instead of using variant_id alone.
     * Tolerate that by keeping only the segment after the last slash.
     */
    public function handle(?string $variantId): ?string
    {
        if (empty($variantId)) {
            return null;
        }

        return str_contains($variantId, '/') ? Str::afterLast($variantId, '/') : $variantId;
    }
}
