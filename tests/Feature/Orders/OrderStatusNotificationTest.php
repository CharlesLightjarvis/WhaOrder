<?php

use App\Actions\Orders\UpdateOrderStatus;
use App\Enums\OrderStatus;
use App\Enums\WhatsAppSessionStatus;
use App\Jobs\NotifyCustomerOfOrderStatusChange;
use App\Models\Customer;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\WhatsAppSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->merchant = Merchant::factory()->create();
    app()->instance('currentMerchantId', $this->merchant->id);
});

it('notifies the customer when the order status changes', function () {
    Bus::fake();

    $order = Order::factory()->for($this->merchant)->create(['status' => OrderStatus::Pending]);

    app(UpdateOrderStatus::class)->handle($order, OrderStatus::Preparing);

    Bus::assertDispatched(NotifyCustomerOfOrderStatusChange::class, fn ($job) => $job->order->is($order));
});

it('does not notify when the status is unchanged', function () {
    Bus::fake();

    $order = Order::factory()->for($this->merchant)->create(['status' => OrderStatus::Preparing]);

    app(UpdateOrderStatus::class)->handle($order, OrderStatus::Preparing);

    Bus::assertNotDispatched(NotifyCustomerOfOrderStatusChange::class);
});

it('sends a WhatsApp message with the new status when a working session exists', function () {
    Http::fake(['*' => Http::response(['id' => 'fake-message-id'])]);

    $session = WhatsAppSession::query()->create([
        'label' => 'Test session',
        'waha_session_name' => 'session-test',
        'status' => WhatsAppSessionStatus::Working,
        'last_active_at' => now(),
    ]);

    $customer = Customer::factory()->for($this->merchant)->create(['whatsapp_number' => '221770000000']);
    $order = Order::factory()->for($this->merchant)->for($customer)->create(['status' => OrderStatus::OutForDelivery]);

    NotifyCustomerOfOrderStatusChange::dispatchSync($order);

    Http::assertSent(function ($request) use ($session) {
        return $request['session'] === $session->waha_session_name
            && $request['chatId'] === '221770000000@c.us'
            && str_contains($request['text'], 'en cours de livraison');
    });
});

it('does not send anything when the merchant has no working WhatsApp session', function () {
    Http::fake(['*' => Http::response(['id' => 'fake-message-id'])]);

    $customer = Customer::factory()->for($this->merchant)->create(['whatsapp_number' => '221770000000']);
    $order = Order::factory()->for($this->merchant)->for($customer)->create(['status' => OrderStatus::Delivered]);

    NotifyCustomerOfOrderStatusChange::dispatchSync($order);

    Http::assertNothingSent();
});
