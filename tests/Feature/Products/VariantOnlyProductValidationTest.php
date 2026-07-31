<?php

use App\Models\Merchant;
use App\Models\User;

beforeEach(function () {
    $this->merchant = Merchant::factory()->create();
    $this->user = User::factory()->create(['merchant_id' => $this->merchant->id]);
});

it('requires parent price and stock for a simple product', function () {
    $this->actingAs($this->user)
        ->post(route('products.store'), [
            'name' => 'Produit simple',
            'is_active' => true,
            'variants' => [],
        ])
        ->assertInvalid(['price', 'stock']);
});

it('requires price and stock on every variant but not on its parent', function () {
    $this->actingAs($this->user)
        ->post(route('products.store'), [
            'name' => 'Menschen',
            'is_active' => true,
            'variants' => [
                ['name' => 'A1.1'],
            ],
        ])
        ->assertValid(['price', 'stock'])
        ->assertInvalid(['variants.0.price', 'variants.0.stock']);
});

it('stores a variant product with null parent values', function () {
    $this->actingAs($this->user)
        ->post(route('products.store'), [
            'name' => 'Menschen',
            'is_active' => true,
            'variants' => [
                ['name' => 'A1.1', 'price' => 12000, 'stock' => 5],
            ],
        ])
        ->assertRedirect(route('products.index'));

    $product = $this->merchant->products()->where('name', 'Menschen')->firstOrFail();

    expect($product->price)->toBeNull()
        ->and($product->stock)->toBeNull()
        ->and($product->variants()->firstOrFail()->price)->toBe('12000.00');
});
