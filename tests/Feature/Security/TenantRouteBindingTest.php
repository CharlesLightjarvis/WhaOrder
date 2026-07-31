<?php

use App\Models\Category;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\User;

it('does not bind a product owned by another merchant for mutations', function () {
    $merchant = Merchant::factory()->create();
    $otherMerchant = Merchant::factory()->create();
    $user = User::factory()->for($merchant)->create();
    $otherCategory = Category::factory()->for($otherMerchant)->create();
    $otherProduct = Product::factory()->for($otherMerchant)->for($otherCategory)->create([
        'name' => 'Private product',
        'price' => 1000,
        'stock' => 5,
    ]);

    $response = $this->actingAs($user)->patch(route('products.update', $otherProduct), [
        'category_id' => null,
        'name' => 'Compromised product',
        'description' => null,
        'price' => 1,
        'stock' => 0,
        'is_active' => true,
        'images' => [],
        'variants' => [],
    ]);

    $response->assertNotFound();
    expect($otherProduct->fresh()->name)->toBe('Private product');
});
