<?php

namespace App\Actions\Products;

use App\Models\Product;

class SyncProductStockFromVariants
{
    public function handle(Product $product): void
    {
        if ($product->variants()->doesntExist()) {
            return;
        }

        $product->update(['price' => null, 'stock' => null]);
    }
}
