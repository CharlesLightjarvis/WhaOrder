<?php

use App\Models\Address;
use App\Models\Customer;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\PaymentProof;

it('hides addresses belonging to another merchant', function () {
    $currentMerchant = Merchant::factory()->create();
    $otherMerchant = Merchant::factory()->create();
    $currentCustomer = Customer::factory()->for($currentMerchant)->create();
    $otherCustomer = Customer::factory()->for($otherMerchant)->create();
    $currentAddress = Address::factory()->for($currentCustomer)->create();
    $otherAddress = Address::factory()->for($otherCustomer)->create();

    app()->instance('currentMerchantId', $currentMerchant->id);

    expect(Address::query()->pluck('id')->all())
        ->toBe([$currentAddress->id])
        ->not->toContain($otherAddress->id);
});

it('hides payment proofs belonging to another merchant', function () {
    $currentMerchant = Merchant::factory()->create();
    $otherMerchant = Merchant::factory()->create();
    $currentOrder = Order::factory()->for($currentMerchant)->create();
    $otherOrder = Order::factory()->for($otherMerchant)->create();
    $currentProof = PaymentProof::factory()->for($currentOrder)->create();
    $otherProof = PaymentProof::factory()->for($otherOrder)->create();

    app()->instance('currentMerchantId', $currentMerchant->id);

    expect(PaymentProof::query()->pluck('id')->all())
        ->toBe([$currentProof->id])
        ->not->toContain($otherProof->id);
});
