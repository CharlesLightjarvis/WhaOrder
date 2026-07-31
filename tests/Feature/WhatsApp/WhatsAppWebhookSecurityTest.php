<?php

it('rejects webhook requests when the HMAC secret is not configured', function () {
    config(['services.waha.webhook_hmac_key' => null]);
    $payload = json_encode(['event' => 'message', 'session' => 'unknown', 'payload' => [
        'id' => 'message-id',
        'from' => '221770000000@c.us',
        'body' => 'Hello',
    ]]);

    $this->call(
        'POST',
        route('webhooks.whatsapp'),
        server: ['CONTENT_TYPE' => 'application/json', 'HTTP_X_WEBHOOK_HMAC' => hash_hmac('sha512', $payload, '')],
        content: $payload,
    )->assertUnauthorized();
});

it('accepts a webhook carrying a valid HMAC signature', function () {
    config(['services.waha.webhook_hmac_key' => 'test-webhook-secret']);
    $payload = json_encode(['event' => 'message', 'session' => 'unknown', 'payload' => [
        'id' => 'message-id',
        'from' => '221770000000@c.us',
        'body' => 'Hello',
    ]]);

    $this->call(
        'POST',
        route('webhooks.whatsapp'),
        server: ['CONTENT_TYPE' => 'application/json', 'HTTP_X_WEBHOOK_HMAC' => hash_hmac('sha512', $payload, 'test-webhook-secret')],
        content: $payload,
    )->assertNoContent();
});

it('rejects a signed webhook with an invalid payload', function () {
    config(['services.waha.webhook_hmac_key' => 'test-webhook-secret']);
    $payload = json_encode(['event' => 'message'], JSON_THROW_ON_ERROR);

    $this->call(
        'POST',
        route('webhooks.whatsapp'),
        server: ['CONTENT_TYPE' => 'application/json', 'HTTP_X_WEBHOOK_HMAC' => hash_hmac('sha512', $payload, 'test-webhook-secret')],
        content: $payload,
    )->assertUnprocessable();
});

it('rate limits repeated signed webhook requests', function () {
    config(['services.waha.webhook_hmac_key' => 'test-webhook-secret']);
    $payload = json_encode([
        'event' => 'message',
        'session' => 'unknown',
        'payload' => ['id' => 'message-id', 'body' => 'Hello'],
    ], JSON_THROW_ON_ERROR);
    $server = [
        'REMOTE_ADDR' => '198.51.100.50',
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_WEBHOOK_HMAC' => hash_hmac('sha512', $payload, 'test-webhook-secret'),
    ];

    foreach (range(1, 60) as $attempt) {
        $this->call('POST', route('webhooks.whatsapp'), server: $server, content: $payload)->assertNoContent();
    }

    $this->call('POST', route('webhooks.whatsapp'), server: $server, content: $payload)->assertTooManyRequests();
});
