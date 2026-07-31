<?php

use App\Support\IncomingMessageDeduplicator;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class);

it('allows a failed message to be acquired again', function () {
    Cache::flush();
    $deduplicator = app(IncomingMessageDeduplicator::class);

    expect($deduplicator->acquire('message-1'))->toBeTrue()
        ->and($deduplicator->acquire('message-1'))->toBeFalse();

    $deduplicator->release('message-1');

    expect($deduplicator->acquire('message-1'))->toBeTrue();
});

it('keeps a successfully processed message deduplicated', function () {
    Cache::flush();
    $deduplicator = app(IncomingMessageDeduplicator::class);

    expect($deduplicator->acquire('message-2'))->toBeTrue();

    $deduplicator->complete('message-2');

    expect($deduplicator->acquire('message-2'))->toBeFalse();
});

it('isolates identical message identifiers from different WhatsApp sessions', function () {
    Cache::flush();
    $deduplicator = app(IncomingMessageDeduplicator::class);

    expect($deduplicator->acquire('message-3', 'session-a'))->toBeTrue()
        ->and($deduplicator->acquire('message-3', 'session-b'))->toBeTrue();
});
