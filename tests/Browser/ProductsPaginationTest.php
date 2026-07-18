<?php

use App\Models\Category;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('paginates products with the tanstack pager without freezing it', function () {
    $merchant = Merchant::factory()->create();
    $user = User::factory()->create(['merchant_id' => $merchant->id]);
    $category = Category::factory()->for($merchant)->create();
    Product::factory()->count(32)->for($merchant)->for($category)->create();

    $this->actingAs($user);

    $page = visit('/products');
    $page->assertNoJavaScriptErrors();
    $page->assertSee('Page 1 of 4');

    $page->click('Go to next page');
    $page->assertSee('Page 2 of 4');

    $page->click('Go to next page');
    $page->assertSee('Page 3 of 4');

    $page->click('Go to next page');
    $page->assertSee('Page 4 of 4');

    $page->click('Go to previous page');
    $page->assertSee('Page 3 of 4');
});
