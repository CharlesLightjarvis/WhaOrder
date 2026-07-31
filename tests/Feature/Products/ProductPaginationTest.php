<?php

use App\Models\Category;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

it('returns a server-side paginated product collection', function () {
    $merchant = Merchant::factory()->create();
    $user = User::factory()->create(['merchant_id' => $merchant->id]);
    $category = Category::factory()->for($merchant)->create();

    Product::factory()->count(32)->for($merchant)->for($category)->create();

    $this->actingAs($user)
        ->get(route('products.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('products/index')
            ->has('products.data', 15)
            ->where('products.total', 32)
            ->where('products.last_page', 3));
});
