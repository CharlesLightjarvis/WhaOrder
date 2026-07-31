<?php

use App\Jobs\Middleware\UseMerchantContext;

it('binds the merchant only while a queued job is running', function () {
    $middleware = new UseMerchantContext('merchant-b');

    app()->instance('currentMerchantId', 'merchant-a');

    $middleware->handle(new stdClass, function (): void {
        expect(app('currentMerchantId'))->toBe('merchant-b');
    });

    expect(app()->bound('currentMerchantId'))->toBeFalse();
});

it('clears the merchant context when a queued job fails', function () {
    $middleware = new UseMerchantContext('merchant-b');

    expect(fn () => $middleware->handle(new stdClass, function (): never {
        throw new RuntimeException('Job failed.');
    }))->toThrow(RuntimeException::class);

    expect(app()->bound('currentMerchantId'))->toBeFalse();
});
