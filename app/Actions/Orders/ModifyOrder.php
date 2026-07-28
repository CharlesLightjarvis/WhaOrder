<?php

namespace App\Actions\Orders;

use App\Actions\Products\CheckLowStock;
use App\Actions\Products\SyncProductStockFromVariants;
use App\Enums\PaymentMethod;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class ModifyOrder
{
    public function __construct(
        private readonly SyncProductStockFromVariants $syncProductStock,
        private readonly CheckLowStock $checkLowStock,
    ) {}

    public function addItem(Order $order, Product $product, ?ProductVariant $variant, int $quantity): Order
    {
        return DB::transaction(function () use ($order, $product, $variant, $quantity) {
            $unitPrice = (float) ($variant?->price ?? $product->price);

            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant?->id,
                'product_name_snapshot' => $product->name,
                'variant_name_snapshot' => $variant?->name,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'line_total' => $unitPrice * $quantity,
            ]);

            $this->decrementStock($product, $variant, $quantity);

            return $this->recalculateTotals($order);
        });
    }

    public function removeItem(Order $order, OrderItem $item): Order
    {
        return DB::transaction(function () use ($order, $item) {
            $this->restockItem($item);
            $item->delete();

            return $this->recalculateTotals($order);
        });
    }

    public function changeQuantity(Order $order, OrderItem $item, int $newQuantity): Order
    {
        return DB::transaction(function () use ($order, $item, $newQuantity) {
            $delta = $newQuantity - $item->quantity;

            if ($delta > 0) {
                $this->decrementStock($item->product, $item->variant, $delta);
            } elseif ($delta < 0) {
                $this->incrementStock($item->product, $item->variant, abs($delta));
            }

            $item->update([
                'quantity' => $newQuantity,
                'line_total' => $item->unit_price * $newQuantity,
            ]);

            return $this->recalculateTotals($order);
        });
    }

    public function changeDelivery(Order $order, ?string $city, ?string $address): Order
    {
        return DB::transaction(function () use ($order, $city, $address) {
            $order->update([
                'delivery_city' => $city ?? $order->delivery_city,
                'delivery_address_text' => $address ?? $order->delivery_address_text,
            ]);

            $order->delivery?->update([
                'city' => $order->delivery_city,
                'address_text' => $order->delivery_address_text,
            ]);

            return $order->fresh(['items', 'delivery']);
        });
    }

    public function changePaymentMethod(Order $order, PaymentMethod $method): Order
    {
        $order->update(['payment_method' => $method]);

        return $order->fresh(['items', 'delivery']);
    }

    private function decrementStock(Product $product, ?ProductVariant $variant, int $quantity): void
    {
        if ($variant) {
            $variant->decrement('stock', $quantity);
            $this->checkLowStock->handleVariant($variant);
        } else {
            $product->decrement('stock', $quantity);
        }

        $this->syncProductStock->handle($product);
        $this->checkLowStock->handle($product);
    }

    private function incrementStock(Product $product, ?ProductVariant $variant, int $quantity): void
    {
        if ($variant) {
            $variant->increment('stock', $quantity);
            $this->checkLowStock->handleVariant($variant);
        } else {
            $product->increment('stock', $quantity);
        }

        $this->syncProductStock->handle($product);
        $this->checkLowStock->handle($product);
    }

    private function restockItem(OrderItem $item): void
    {
        if (! $item->product) {
            return;
        }

        $this->incrementStock($item->product, $item->variant, $item->quantity);
    }

    private function recalculateTotals(Order $order): Order
    {
        $order->refresh();

        $subtotal = $order->items()->sum('line_total');

        $order->update([
            'subtotal' => $subtotal,
            'total' => $subtotal + $order->delivery_fee,
        ]);

        return $order->fresh(['items', 'delivery']);
    }
}
