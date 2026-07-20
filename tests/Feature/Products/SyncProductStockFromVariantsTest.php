<?php

use App\Actions\Orders\FinalizeOrder;
use App\Actions\Products\CreateProduct;
use App\Actions\Products\UpdateProduct;
use App\Models\Category;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Merchant;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->merchant = Merchant::factory()->create();
    app()->instance('currentMerchantId', $this->merchant->id);
});

it('sums variant stock into the product stock when creating a product with variants', function () {
    $category = Category::factory()->for($this->merchant)->create();

    $product = app(CreateProduct::class)->handle([
        'category_id' => $category->id,
        'name' => 'Sac à main',
        'description' => null,
        'price' => 10000,
        'stock' => 999,
        'is_active' => true,
        'variants' => [
            ['name' => 'Bleu', 'price' => null, 'stock' => 5],
            ['name' => 'Rouge', 'price' => null, 'stock' => 3],
        ],
    ]);

    expect($product->fresh()->stock)->toBe(8);
});

it('keeps the submitted stock untouched for a product without variants', function () {
    $category = Category::factory()->for($this->merchant)->create();

    $product = app(CreateProduct::class)->handle([
        'category_id' => $category->id,
        'name' => 'Coque téléphone',
        'description' => null,
        'price' => 5000,
        'stock' => 42,
        'is_active' => true,
        'variants' => [],
    ]);

    expect($product->fresh()->stock)->toBe(42);
});

it('resyncs product stock when variants are updated', function () {
    $product = Product::factory()->for($this->merchant)->create(['stock' => 999]);
    $variant = $product->variants()->create(['name' => 'Taille M', 'stock' => 4]);

    app(UpdateProduct::class)->handle($product, [
        'category_id' => $product->category_id,
        'name' => $product->name,
        'description' => $product->description,
        'price' => $product->price,
        'stock' => 999,
        'is_active' => true,
        'variants' => [
            ['id' => $variant->id, 'name' => 'Taille M', 'price' => null, 'stock' => 9],
        ],
    ]);

    expect($product->fresh()->stock)->toBe(9);
});

it('resyncs product stock after an order decrements a variant', function () {
    $product = Product::factory()->for($this->merchant)->create(['stock' => 999]);
    $variant = $product->variants()->create(['name' => 'Taille L', 'stock' => 10]);

    $customer = Customer::factory()->for($this->merchant)->create();
    $conversation = Conversation::factory()->for($this->merchant)->for($customer)->create();

    app(FinalizeOrder::class)->handle($this->merchant, $conversation, [
        'items' => [[
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'product_name_snapshot' => $product->name,
            'variant_name_snapshot' => $variant->name,
            'quantity' => 3,
            'unit_price' => 1000,
            'line_total' => 3000,
        ]],
        'subtotal' => 3000,
        'delivery_fee' => 0,
        'total' => 3000,
        'customer_name' => 'Client Test',
        'delivery_address_text' => 'Rue Test',
        'delivery_city' => 'Abidjan',
        'payment_method' => 'cash',
    ]);

    expect($variant->fresh()->stock)->toBe(7)
        ->and($product->fresh()->stock)->toBe(7);
});
