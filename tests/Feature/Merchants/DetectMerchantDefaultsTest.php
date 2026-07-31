<?php

use App\Actions\Merchants\DetectMerchantDefaults;
use Illuminate\Support\Facades\Http;

it('maps a supported currency and timezone from the primary provider', function () {
    Http::fake([
        'freeipapi.com/*' => Http::response(['currencies' => ['GHS'], 'timeZones' => ['Africa/Accra']]),
    ]);

    $result = app(DetectMerchantDefaults::class)->handle('102.176.0.1');

    expect($result['currency'])->toBe('GHS')
        ->and($result['timezone'])->toBe('Africa/Accra');
});

it('falls back to USD/UTC when the currency is not a known ISO code', function () {
    Http::fake([
        'freeipapi.com/*' => Http::response(['currencies' => ['ZZZ'], 'timeZones' => ['Asia/Tokyo']]),
    ]);

    $result = app(DetectMerchantDefaults::class)->handle('1.2.3.4');

    expect($result['currency'])->toBe('USD')
        ->and($result['timezone'])->toBe('Asia/Tokyo');
});

it('resolves the public IP first when given a private or local IP, instead of giving up immediately', function () {
    Http::fake([
        'api.ipify.org*' => Http::response(['ip' => '196.203.1.1']),
        'freeipapi.com/api/json/196.203.1.1' => Http::response(['currencies' => ['TND'], 'timeZones' => ['Africa/Tunis']]),
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

    Http::assertNotSent(fn ($request) => str_contains((string) $request->url(), 'freeipapi.com'));
});

it('falls back to the secondary provider when the primary one fails', function () {
    Http::fake([
        'freeipapi.com/*' => Http::response([], 500),
        'ipapi.co/*' => Http::response(['currency' => 'MAD', 'timezone' => 'Africa/Casablanca']),
    ]);

    $result = app(DetectMerchantDefaults::class)->handle('41.140.0.1');

    expect($result['currency'])->toBe('MAD')
        ->and($result['timezone'])->toBe('Africa/Casablanca');
});

it('falls back to the tertiary provider and derives currency from the country code', function () {
    Http::fake([
        'freeipapi.com/*' => Http::response([], 500),
        'ipapi.co/*' => Http::response(['error' => true], 429),
        'ip-api.com/*' => Http::response(['status' => 'success', 'countryCode' => 'TN', 'timezone' => 'Africa/Tunis']),
    ]);

    $result = app(DetectMerchantDefaults::class)->handle('5.6.7.8');

    expect($result['currency'])->toBe('TND')
        ->and($result['timezone'])->toBe('Africa/Tunis');
});

it('falls back to defaults when every provider fails', function () {
    Http::fake([
        'freeipapi.com/*' => Http::response([], 500),
        'ipapi.co/*' => Http::response([], 500),
        'ip-api.com/*' => Http::response([], 500),
    ]);

    $result = app(DetectMerchantDefaults::class)->handle('9.9.9.9');

    expect($result['currency'])->toBe('USD')
        ->and($result['timezone'])->toBe('UTC');
});

it('remembers a successful lookup so repeated calls stay consistent instead of re-hitting a rate-limited API', function () {
    Http::fake([
        'freeipapi.com/*' => Http::sequence()
            ->push(['currencies' => ['TND'], 'timeZones' => ['Africa/Tunis']])
            ->push(['message' => 'RateLimited'], 429),
    ]);

    $first = app(DetectMerchantDefaults::class)->handle('41.226.1.1');
    $second = app(DetectMerchantDefaults::class)->handle('41.226.1.1');

    expect($first)->toBe(['currency' => 'TND', 'timezone' => 'Africa/Tunis'])
        ->and($second)->toBe(['currency' => 'TND', 'timezone' => 'Africa/Tunis']);
});
