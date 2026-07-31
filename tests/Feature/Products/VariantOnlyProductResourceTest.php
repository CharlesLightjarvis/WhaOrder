<?php

use App\Http\Resources\ProductResource;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\ProductImage;

beforeEach(function () {
    $this->merchant = Merchant::factory()->create();
    app()->instance('currentMerchantId', $this->merchant->id);
});

it('summarizes a variant product for the product list', function () {
    $product = Product::factory()->for($this->merchant)->create([
        'price' => null,
        'stock' => null,
    ]);
    $a11 = $product->variants()->create(['name' => 'A1.1', 'price' => 12000, 'stock' => 5]);
    $product->variants()->create(['name' => 'A1.2', 'price' => 15000, 'stock' => 8]);
    $image = ProductImage::factory()->for($product)->create([
        'variant_id' => $a11->id,
        'url' => 'https://ik.example.test/a11.png',
    ]);

    $product->load(['images', 'variants.images'])->loadCount('variants');
    $resource = ProductResource::make($product)->resolve();

    expect($resource['has_variants'])->toBeTrue()
        ->and($resource['cover_image'])->toBe(['id' => $image->id, 'url' => $image->url])
        ->and($resource['price_min'])->toBe(12000.0)
        ->and($resource['price_max'])->toBe(15000.0)
        ->and($resource['stock_total'])->toBe(13)
        ->and($resource['price'])->toBeNull()
        ->and($resource['stock'])->toBeNull();
});

it('uses parent values and image for a simple product', function () {
    $product = Product::factory()->for($this->merchant)->create(['price' => 9000, 'stock' => 4]);
    $image = ProductImage::factory()->for($product)->create([
        'variant_id' => null,
        'url' => 'https://ik.example.test/simple.png',
    ]);

    $product->load(['images', 'variants.images'])->loadCount('variants');
    $resource = ProductResource::make($product)->resolve();

    expect($resource['has_variants'])->toBeFalse()
        ->and($resource['cover_image'])->toBe(['id' => $image->id, 'url' => $image->url])
        ->and($resource['price_min'])->toBe(9000.0)
        ->and($resource['price_max'])->toBe(9000.0)
        ->and($resource['stock_total'])->toBe(4);
});
