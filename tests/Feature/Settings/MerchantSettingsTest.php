<?php

use App\Models\Merchant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->merchant = Merchant::factory()->create([
        'currency' => 'XOF',
        'timezone' => 'Africa/Abidjan',
    ]);
    $this->user = User::factory()->create(['merchant_id' => $this->merchant->id]);
});

it('renders the business settings page with the merchant data', function () {
    $response = $this->actingAs($this->user)->get(route('merchant.edit'));

    $response->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/merchant')
            ->where('merchant.name', $this->merchant->name)
            ->where('merchant.currency', 'XOF')
            ->has('currencies'));
});

it('updates the merchant business info', function () {
    $response = $this->actingAs($this->user)->patch(route('merchant.update'), [
        'name' => 'Nouvelle Boutique',
        'whatsapp_number' => '+225 07 00 00 00 00',
        'whatsapp_admin_number' => '+225 07 11 11 11 11',
        'currency' => 'GHS',
        'timezone' => 'Africa/Accra',
        'delivery_fee' => 2000,
    ]);

    $response->assertRedirect(route('merchant.edit'));

    $fresh = $this->merchant->fresh();

    expect($fresh->name)->toBe('Nouvelle Boutique')
        ->and($fresh->whatsapp_number)->toBe('+225 07 00 00 00 00')
        ->and($fresh->currency)->toBe('GHS')
        ->and($fresh->timezone)->toBe('Africa/Accra')
        ->and((float) $fresh->delivery_fee)->toBe(2000.0);
});

it('rejects an unsupported currency', function () {
    $response = $this->actingAs($this->user)->patch(route('merchant.update'), [
        'name' => 'Nouvelle Boutique',
        'currency' => 'ZZZ',
        'timezone' => 'Africa/Accra',
    ]);

    $response->assertSessionHasErrors('currency');
});

it('returns detected currency and timezone as json', function () {
    Http::fake(['ipapi.co/*' => Http::response(['currency' => 'NGN', 'timezone' => 'Africa/Lagos'])]);

    $response = $this->actingAs($this->user)->call(
        'GET',
        route('merchant.detect-location'),
        server: ['REMOTE_ADDR' => '102.176.0.1'],
    );

    $response->assertOk()->assertJson(['currency' => 'NGN', 'timezone' => 'Africa/Lagos']);
});
