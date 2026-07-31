<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Laravel\Fortify\Features;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register, along with their own merchant', function () {
    Http::fake(['freeipapi.com/*' => Http::response(['currencies' => ['XOF'], 'timeZones' => ['Africa/Abidjan']])]);

    $response = $this->call('POST', route('register.store'), [
        'name' => 'Test User',
        'business_name' => 'Ma Boutique',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ], server: ['REMOTE_ADDR' => '102.176.0.1']);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'test@example.com')->firstOrFail();

    expect($user->merchant)->not->toBeNull()
        ->and($user->merchant->name)->toBe('Ma Boutique')
        ->and($user->merchant->currency)->toBe('XOF')
        ->and($user->merchant->timezone)->toBe('Africa/Abidjan');
});

test('registration requires a business name', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors('business_name');
    $this->assertGuest();
});

test('public registration is rate limited', function () {
    foreach (range(1, 5) as $attempt) {
        $this->call(
            'POST',
            route('register.store'),
            server: ['REMOTE_ADDR' => '198.51.100.60'],
        )->assertRedirect();
    }

    $response = $this->call(
        'POST',
        route('register.store'),
        server: ['REMOTE_ADDR' => '198.51.100.60'],
    );

    expect($response->getStatusCode())->toBe(429);
});
