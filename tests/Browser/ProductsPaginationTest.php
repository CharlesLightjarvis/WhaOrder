<?php

use App\Models\Category;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('paginates products on the server without freezing the table', function () {
    $merchant = Merchant::factory()->create();
    $user = User::factory()->create(['merchant_id' => $merchant->id]);
    $category = Category::factory()->for($merchant)->create();
    Product::factory()->count(32)->for($merchant)->for($category)->create();

    $this->actingAs($user);

    $page = visit('/products');
    $page->assertNoJavaScriptErrors();
    $page->assertSee('Page 1 / 3');

    $page->click('Suivant');
    $page->assertSee('Page 2 / 3');

    $page->click('Suivant');
    $page->assertSee('Page 3 / 3');

    $page->click('PrÃ©cÃ©dent');
    $page->assertSee('Page 2 / 3');
});
