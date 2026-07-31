<?php

namespace App\Actions\Products;

use App\Jobs\NotifyMerchantOfLowStock;
use App\Models\Product;
use App\Models\ProductVariant;
use Carbon\CarbonInterface;

class CheckLowStock
{
    private const int THRESHOLD = 3;

    /**
     * Alert the merchant once when a product's stock crosses at or below
     * the threshold, then stay quiet until it's restocked above it (at
     * which point the alert resets, ready to fire again next time).
     */
    public function handle(Product $product): void
    {
        if ($product->stock === null) {
            return;
        }

        if ($this->isLowStock($product->stock, $product->low_stock_alerted_at)) {
            NotifyMerchantOfLowStock::dispatch($product);
            $product->update(['low_stock_alerted_at' => now()]);

            return;
        }

        if ($this->shouldClearAlert($product->stock, $product->low_stock_alerted_at)) {
            $product->update(['low_stock_alerted_at' => null]);
        }
    }

    public function handleVariant(ProductVariant $variant): void
    {
        if ($this->isLowStock($variant->stock, $variant->low_stock_alerted_at)) {
            NotifyMerchantOfLowStock::dispatch($variant->product, $variant);
            $variant->update(['low_stock_alerted_at' => now()]);

            return;
        }

        if ($this->shouldClearAlert($variant->stock, $variant->low_stock_alerted_at)) {
            $variant->update(['low_stock_alerted_at' => null]);
        }
    }

    private function isLowStock(int $stock, ?CarbonInterface $alertedAt): bool
    {
        return $stock <= self::THRESHOLD && $alertedAt === null;
    }

    private function shouldClearAlert(int $stock, ?CarbonInterface $alertedAt): bool
    {
        return $stock > self::THRESHOLD && $alertedAt !== null;
    }
}
