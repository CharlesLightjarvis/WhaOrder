<?php

use App\Actions\Orders\ModifyOrder;
use App\Actions\Products\NormalizeVariantId;
use App\Ai\Tools\ModifyOrderTool;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Laravel\Ai\Tools\Request;

uses(RefreshDatabase::class);

beforeEach(function () {
    Bus::fake();

    $this->merchant = Merchant::factory()->create();
    app()->instance('currentMerchantId', $this->merchant->id);

    $this->customer = Customer::factory()->for($this->merchant)->create();
    $this->conversation = Conversation::factory()->for($this->merchant)->for($this->customer)->create();

    $this->order = Order::factory()->for($this->merchant)->for($this->customer)->create([
        'status' => OrderStatus::Pending,
        'subtotal' => 10000,
        'delivery_fee' => 0,
        'total' => 10000,
    ]);

    $this->item = OrderItem::factory()->for($this->order)->create([
        'product_id' => null,
        'product_variant_id' => null,
        'quantity' => 2,
        'unit_price' => 5000,
        'line_total' => 10000,
    ]);

    $this->reference = mb_strtoupper(substr($this->order->id, 0, 8));
});

function callModifyOrder(array $args): string
{
    $tool = new ModifyOrderTool(
        test()->merchant,
        test()->conversation,
        app(ModifyOrder::class),
        app(NormalizeVariantId::class),
    );

    return (string) $tool->handle(new Request($args));
}

it('adds a new item and recalculates the order total', function () {
    $product = Product::factory()->for($this->merchant)->create(['price' => 3000, 'stock' => 10]);

    $message = callModifyOrder([
        'order_reference' => $this->reference,
        'action' => 'add_item',
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    expect($message)->toContain('Article ajouté')
        ->and($this->order->fresh()->total)->toBe('16000.00')
        ->and($product->fresh()->stock)->toBe(8);
});

it('refuses to add an item without enough stock', function () {
    $product = Product::factory()->for($this->merchant)->create(['price' => 3000, 'stock' => 1]);

    $message = callModifyOrder([
        'order_reference' => $this->reference,
        'action' => 'add_item',
        'product_id' => $product->id,
        'quantity' => 5,
    ]);

    expect($message)->toContain('Stock insuffisant')
        ->and($this->order->fresh()->total)->toBe('10000.00');
});

it('removes an item, restocks it, and recalculates the total', function () {
    $product = Product::factory()->for($this->merchant)->create(['stock' => 5]);
    $variant = ProductVariant::factory()->for($product)->create(['stock' => 3]);

    $secondItem = OrderItem::factory()->for($this->order)->create([
        'product_id' => $product->id,
        'product_variant_id' => $variant->id,
        'quantity' => 2,
        'unit_price' => 1000,
        'line_total' => 2000,
    ]);

    $this->order->update(['subtotal' => 12000, 'total' => 12000]);

    $message = callModifyOrder([
        'order_reference' => $this->reference,
        'action' => 'remove_item',
        'order_item_id' => $secondItem->id,
    ]);

    expect($message)->toContain('Article retiré')
        ->and($this->order->fresh()->total)->toBe('10000.00')
        ->and($variant->fresh()->stock)->toBe(5)
        ->and(OrderItem::query()->find($secondItem->id))->toBeNull();
});

it('refuses to remove the last remaining item', function () {
    $message = callModifyOrder([
        'order_reference' => $this->reference,
        'action' => 'remove_item',
        'order_item_id' => $this->item->id,
    ]);

    expect($message)->toContain('dernier article')
        ->and(OrderItem::query()->find($this->item->id))->not->toBeNull();
});

it('changes the quantity of an item and adjusts stock accordingly', function () {
    $product = Product::factory()->for($this->merchant)->create(['stock' => 5]);

    $this->item->update(['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 1000, 'line_total' => 2000]);
    $this->order->update(['subtotal' => 2000, 'total' => 2000]);

    $message = callModifyOrder([
        'order_reference' => $this->reference,
        'action' => 'change_quantity',
        'order_item_id' => $this->item->id,
        'quantity' => 4,
    ]);

    expect($message)->toContain('Quantité mise à jour')
        ->and($this->order->fresh()->total)->toBe('4000.00')
        ->and($product->fresh()->stock)->toBe(3);
});

it('updates delivery city and address', function () {
    $message = callModifyOrder([
        'order_reference' => $this->reference,
        'action' => 'change_delivery',
        'delivery_city' => 'Lomé',
        'delivery_address_text' => 'Rue 33 HDN',
    ]);

    expect($message)->toContain('Livraison mise à jour');

    $fresh = $this->order->fresh();
    expect($fresh->delivery_city)->toBe('Lomé')
        ->and($fresh->delivery_address_text)->toBe('Rue 33 HDN');
});

it('updates the payment method', function () {
    $message = callModifyOrder([
        'order_reference' => $this->reference,
        'action' => 'change_payment_method',
        'payment_method' => PaymentMethod::Card->value,
    ]);

    expect($message)->toContain('Moyen de paiement mis à jour')
        ->and($this->order->fresh()->payment_method)->toBe(PaymentMethod::Card);
});

it('refuses to modify an order that is already out for delivery', function () {
    $this->order->update(['status' => OrderStatus::OutForDelivery]);

    $message = callModifyOrder([
        'order_reference' => $this->reference,
        'action' => 'change_payment_method',
        'payment_method' => PaymentMethod::Card->value,
    ]);

    expect($message)->toContain('ne peut plus être modifiée');
});

it('returns a clear message for an unknown order reference', function () {
    $message = callModifyOrder([
        'order_reference' => 'FFFFFFFF',
        'action' => 'change_payment_method',
        'payment_method' => PaymentMethod::Card->value,
    ]);

    expect($message)->toContain('introuvable');
});
