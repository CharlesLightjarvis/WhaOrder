<?php

use App\Actions\Orders\UpdateOrderStatus;
use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Models\Delivery;
use App\Models\Merchant;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

beforeEach(function () {
    Bus::fake();

    $this->merchant = Merchant::factory()->create();
    app()->instance('currentMerchantId', $this->merchant->id);
});

it('mirrors the order status onto its delivery record', function (OrderStatus $orderStatus, DeliveryStatus $expected) {
    $order = Order::factory()->for($this->merchant)->create(['status' => OrderStatus::Pending]);
    $delivery = Delivery::factory()->for($order)->create(['status' => DeliveryStatus::Pending]);

    app(UpdateOrderStatus::class)->handle($order, $orderStatus);

    expect($delivery->fresh()->status)->toBe($expected);
})->with([
    'preparing stays pending' => [OrderStatus::Preparing, DeliveryStatus::Pending],
    'out for delivery' => [OrderStatus::OutForDelivery, DeliveryStatus::OutForDelivery],
    'delivered' => [OrderStatus::Delivered, DeliveryStatus::Delivered],
    'cancelled becomes failed' => [OrderStatus::Cancelled, DeliveryStatus::Failed],
]);

it('sets delivered_at when the order is marked delivered', function () {
    $order = Order::factory()->for($this->merchant)->create(['status' => OrderStatus::OutForDelivery]);
    $delivery = Delivery::factory()->for($order)->create(['status' => DeliveryStatus::OutForDelivery, 'delivered_at' => null]);

    app(UpdateOrderStatus::class)->handle($order, OrderStatus::Delivered);

    expect($delivery->fresh()->delivered_at)->not->toBeNull();
});

it('does nothing when the order has no delivery record', function () {
    $order = Order::factory()->for($this->merchant)->create(['status' => OrderStatus::Pending]);

    app(UpdateOrderStatus::class)->handle($order, OrderStatus::OutForDelivery)
        ->refresh();

    expect($order->status)->toBe(OrderStatus::OutForDelivery);
});
