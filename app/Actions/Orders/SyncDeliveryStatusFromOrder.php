<?php

namespace App\Actions\Orders;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Models\Order;

class SyncDeliveryStatusFromOrder
{
    /**
     * The order status is the merchant-facing source of truth; the
     * delivery record just mirrors it so both stay coherent instead of
     * drifting apart (order status changed, delivery never followed).
     */
    public function handle(Order $order): void
    {
        $delivery = $order->delivery;

        if (! $delivery) {
            return;
        }

        $status = match ($order->status) {
            OrderStatus::Pending, OrderStatus::Preparing => DeliveryStatus::Pending,
            OrderStatus::OutForDelivery => DeliveryStatus::OutForDelivery,
            OrderStatus::Delivered => DeliveryStatus::Delivered,
            OrderStatus::Cancelled => DeliveryStatus::Failed,
        };

        $delivery->update([
            'status' => $status,
            'delivered_at' => $status === DeliveryStatus::Delivered ? ($delivery->delivered_at ?? now()) : $delivery->delivered_at,
        ]);
    }
}
