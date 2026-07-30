<?php

use App\Actions\Merchants\DetectMerchantDefaults;
use Illuminate\Support\Facades\Http;

it('maps a supported currency and timezone from the geolocation response', function () {
    Http::fake([
        'ipapi.co/*' => Http::response(['currency' => 'GHS', 'timezone' => 'Africa/Accra']),
    ]);

    $result = app(DetectMerchantDefaults::class)->handle('102.176.0.1');

    expect($result['currency'])->toBe('GHS')
        ->and($result['timezone'])->toBe('Africa/Accra');
});

it('falls back to USD/UTC when the currency is not a known ISO code', function () {
    Http::fake([
        'ipapi.co/*' => Http::response(['currency' => 'ZZZ', 'timezone' => 'Asia/Tokyo']),
    ]);

    $result = app(DetectMerchantDefaults::class)->handle('1.2.3.4');

    expect($result['currency'])->toBe('USD')
        ->and($result['timezone'])->toBe('Asia/Tokyo');
});

it('resolves the public IP first when given a private or local IP, instead of giving up immediately', function () {
    Http::fake([
        'api.ipify.org*' => Http::response(['ip' => '196.203.1.1']),
        'ipapi.co/196.203.1.1/*' => Http::response(['currency' => 'TND', 'timezone' => 'Africa/Tunis']),
    ]);

    $result = app(DetectMerchantDefaults::class)->handle('127.0.0.1');

    expect($result['currency'])->toBe('TND')
        ->and($result['timezone'])->toBe('Africa/Tunis');

    Http::assertSent(fn ($request) => str_contains((string) $request->url(), 'api.ipify.org'));
});

it('falls back to defaults when the public IP cannot be resolved either', function () {
    Http::fake();

    $result = app(DetectMerchantDefaults::class)->handle('127.0.0.1');

    expect($result['currency'])->toBe('USD')
        ->and($result['timezone'])->toBe('UTC');

    Http::assertNotSent(fn ($request) => str_contains((string) $request->url(), 'ipapi.co'));
});

it('falls back to defaults when the geolocation lookup fails', function () {
    Http::fake(['ipapi.co/*' => Http::response(['error' => true], 200)]);

    $result = app(DetectMerchantDefaults::class)->handle('5.6.7.8');

    expect($result['currency'])->toBe('USD')
        ->and($result['timezone'])->toBe('UTC');
});

it('remembers a successful lookup so repeated calls stay consistent instead of re-hitting a rate-limited API', function () {
    Http::fake([
        'ipapi.co/*' => Http::sequence()
            ->push(['currency' => 'TND', 'timezone' => 'Africa/Tunis'])
            ->push(['error' => true, 'reason' => 'RateLimited'], 429),
    ]);

    $first = app(DetectMerchantDefaults::class)->handle('41.226.1.1');
    $second = app(DetectMerchantDefaults::class)->handle('41.226.1.1');

    expect($first)->toBe(['currency' => 'TND', 'timezone' => 'Africa/Tunis'])
        ->and($second)->toBe(['currency' => 'TND', 'timezone' => 'Africa/Tunis']);
});
