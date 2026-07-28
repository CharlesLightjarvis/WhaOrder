<?php

use App\Actions\Orders\FinalizeOrder;
use App\Actions\Products\CheckLowStock;
use App\Actions\Products\UpdateProduct;
use App\Enums\WhatsAppSessionStatus;
use App\Jobs\NotifyMerchantOfLowStock;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\WhatsAppSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->merchant = Merchant::factory()->create();
    app()->instance('currentMerchantId', $this->merchant->id);
});

it('alerts once when a product without variants crosses the low stock threshold', function () {
    Bus::fake();

    $product = Product::factory()->for($this->merchant)->create(['stock' => 4, 'low_stock_alerted_at' => null]);

    $product->update(['stock' => 3]);
    app(CheckLowStock::class)->handle($product->fresh());

    Bus::assertDispatched(NotifyMerchantOfLowStock::class, fn ($job) => $job->product->is($product));
    expect($product->fresh()->low_stock_alerted_at)->not->toBeNull();
});

it('does not alert twice while already below the threshold', function () {
    Bus::fake();

    $product = Product::factory()->for($this->merchant)->create(['stock' => 2, 'low_stock_alerted_at' => now()]);

    app(CheckLowStock::class)->handle($product);

    Bus::assertNotDispatched(NotifyMerchantOfLowStock::class);
});

it('clears the alert once stock is replenished above the threshold', function () {
    $product = Product::factory()->for($this->merchant)->create(['stock' => 20, 'low_stock_alerted_at' => now()]);

    app(CheckLowStock::class)->handle($product);

    expect($product->fresh()->low_stock_alerted_at)->toBeNull();
});

it('resets the alert flag through UpdateProduct when the merchant restocks', function () {
    $product = Product::factory()->for($this->merchant)->create(['stock' => 1, 'low_stock_alerted_at' => now()]);

    app(UpdateProduct::class)->handle($product, [
        'category_id' => $product->category_id,
        'name' => $product->name,
        'description' => $product->description,
        'price' => $product->price,
        'stock' => 50,
        'is_active' => true,
        'variants' => [],
    ]);

    expect($product->fresh()->low_stock_alerted_at)->toBeNull();
});

it('alerts on a specific variant running low even if the product total looks fine', function () {
    Bus::fake();

    $product = Product::factory()->for($this->merchant)->create(['stock' => 999]);
    $variant = ProductVariant::factory()->for($product)->create(['stock' => 5]);

    $variant->update(['stock' => 2]);
    app(CheckLowStock::class)->handleVariant($variant->fresh());

    Bus::assertDispatched(NotifyMerchantOfLowStock::class, fn ($job) => $job->variant->is($variant));
});

it('sends the merchant a WhatsApp message with the remaining stock', function () {
    Http::fake(['*' => Http::response(['id' => 'fake-message-id'])]);

    WhatsAppSession::query()->create([
        'label' => 'Test session',
        'waha_session_name' => 'session-test',
        'status' => WhatsAppSessionStatus::Working,
        'last_active_at' => now(),
    ]);

    $product = Product::factory()->for($this->merchant)->create(['stock' => 2]);

    NotifyMerchantOfLowStock::dispatchSync($product);

    Http::assertSent(fn ($request) => str_contains($request['text'], 'Stock faible')
        && str_contains($request['text'], $product->name)
        && str_contains($request['text'], '2'));
});

it('alerts through a real order via FinalizeOrder', function () {
    Bus::fake();

    $product = Product::factory()->for($this->merchant)->create(['price' => 1000, 'stock' => 4]);

    $customer = Customer::factory()->for($this->merchant)->create();
    $conversation = Conversation::factory()->for($this->merchant)->for($customer)->create();

    app(FinalizeOrder::class)->handle($this->merchant, $conversation, [
        'items' => [[
            'product_id' => $product->id,
            'variant_id' => null,
            'product_name_snapshot' => $product->name,
            'variant_name_snapshot' => null,
            'quantity' => 2,
            'unit_price' => 1000,
            'line_total' => 2000,
        ]],
        'subtotal' => 2000,
        'delivery_fee' => 0,
        'total' => 2000,
        'customer_name' => 'Client Test',
        'delivery_address_text' => 'Rue Test',
        'delivery_city' => 'Abidjan',
        'payment_method' => 'cash',
    ]);

    Bus::assertDispatched(NotifyMerchantOfLowStock::class, fn ($job) => $job->product->is($product));
    expect($product->fresh()->stock)->toBe(2);
});
