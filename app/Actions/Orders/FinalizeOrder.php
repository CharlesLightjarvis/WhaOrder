<?php

namespace App\Actions\Orders;

use App\Actions\Addresses\SaveDeliveryAddress;
use App\Actions\Products\CheckLowStock;
use App\Actions\Products\SyncProductStockFromVariants;
use App\Enums\ConversationStatus;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Conversation;
use App\Models\Delivery;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\Customers\CustomerRepository;
use Illuminate\Support\Facades\DB;

class FinalizeOrder
{
    public function __construct(
        private readonly SaveDeliveryAddress $saveDeliveryAddress,
        private readonly CustomerRepository $customers,
        private readonly SyncProductStockFromVariants $syncProductStock,
        private readonly CheckLowStock $checkLowStock,
    ) {}

    /**
     * @param  array<string, mixed>  $draftOrder
     */
    public function handle(Merchant $merchant, Conversation $conversation, array $draftOrder): Order
    {
        return DB::transaction(function () use ($merchant, $conversation, $draftOrder) {
            $order = Order::query()->create([
                'customer_id' => $conversation->customer_id,
                'conversation_id' => $conversation->id,
                'status' => OrderStatus::Pending,
                'payment_status' => PaymentStatus::Unpaid,
                'payment_method' => $draftOrder['payment_method'] ? PaymentMethod::from($draftOrder['payment_method']) : null,
                'delivery_address_text' => $draftOrder['delivery_address_text'],
                'delivery_city' => $draftOrder['delivery_city'],
                'subtotal' => $draftOrder['subtotal'],
                'delivery_fee' => $draftOrder['delivery_fee'],
                'total' => $draftOrder['total'],
            ]);

            foreach ($draftOrder['items'] as $item) {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['variant_id'],
                    'product_name_snapshot' => $item['product_name_snapshot'],
                    'variant_name_snapshot' => $item['variant_name_snapshot'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total'],
                ]);

                if ($item['variant_id']) {
                    ProductVariant::query()
                        ->where('id', $item['variant_id'])
                        ->whereHas('product', fn ($query) => $query->where('merchant_id', $merchant->id))
                        ->decrement('stock', $item['quantity']);

                    $product = Product::query()->find($item['product_id']);

                    if ($product) {
                        $this->syncProductStock->handle($product);
                        $this->checkLowStock->handle($product);
                    }

                    $variant = ProductVariant::query()->find($item['variant_id']);

                    if ($variant) {
                        $this->checkLowStock->handleVariant($variant);
                    }
                } else {
                    Product::query()
                        ->where('id', $item['product_id'])
                        ->where('merchant_id', $merchant->id)
                        ->decrement('stock', $item['quantity']);

                    $product = Product::query()->find($item['product_id']);

                    if ($product) {
                        $this->checkLowStock->handle($product);
                    }
                }
            }

            Delivery::query()->create([
                'order_id' => $order->id,
                'status' => DeliveryStatus::Pending,
                'address_text' => $draftOrder['delivery_address_text'],
                'city' => $draftOrder['delivery_city'],
            ]);

            $this->saveDeliveryAddress->handle(
                $conversation->customer_id,
                $draftOrder['delivery_address_text'],
                $draftOrder['delivery_city'],
            );

            $customerUpdates = ['last_order_at' => now()];

            if (! empty($draftOrder['customer_name'])) {
                $customerUpdates['name'] = $draftOrder['customer_name'];
            }

            $this->customers->update($conversation->customer, $customerUpdates);

            $conversation->update([
                'status' => ConversationStatus::Completed,
                'draft_order' => null,
            ]);

            return $order;
        });
    }
}
