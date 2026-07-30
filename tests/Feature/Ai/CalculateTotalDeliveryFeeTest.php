<?php

use App\Actions\Products\NormalizeVariantId;
use App\Ai\Tools\CalculateTotalTool;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Merchant;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Ai\Tools\Request;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->merchant = Merchant::factory()->create(['delivery_fee' => 1500]);
    app()->instance('currentMerchantId', $this->merchant->id);
});

it('applies the merchant flat delivery fee to the draft order total', function () {
    $customer = Customer::factory()->for($this->merchant)->create();
    $conversation = Conversation::factory()->for($this->merchant)->for($customer)->create();

    $product = Product::factory()->for($this->merchant)->create(['price' => 5000, 'stock' => 10]);

    $tool = new CalculateTotalTool($this->merchant, $conversation, app(NormalizeVariantId::class));

    $tool->handle(new Request([
        'items' => [[
            'product_id' => $product->id,
            'variant_id' => null,
            'quantite' => 2,
        ]],
    ]));

    $draftOrder = $conversation->fresh()->draft_order;

    expect((float) $draftOrder['subtotal'])->toBe(10000.0)
        ->and((float) $draftOrder['delivery_fee'])->toBe(1500.0)
        ->and((float) $draftOrder['total'])->toBe(11500.0);
});
