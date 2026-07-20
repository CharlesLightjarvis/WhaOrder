<?php

namespace App\Actions\Products;

use App\Models\Product;

class SyncProductStockFromVariants
{
    /**
     * Keep products.stock truthful for products that have variants: it must
     * always equal the sum of its variants' stock, never an independently
     * edited number that can drift out of sync with them.
     */
    public function handle(Product $product): void
    {
        if ($product->variants()->doesntExist()) {
            return;
        }

        $product->update(['stock' => $product->variants()->sum('stock')]);
    }
}
