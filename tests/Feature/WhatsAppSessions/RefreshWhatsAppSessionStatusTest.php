<?php

use App\Actions\WhatsAppSessions\RefreshWhatsAppSessionStatus;
use App\Enums\WhatsAppSessionStatus;
use App\Models\Merchant;
use App\Models\User;
use App\Models\WhatsAppSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->merchant = Merchant::factory()->create();
    app()->instance('currentMerchantId', $this->merchant->id);
});

it('stores the whatsapp profile name and picture once the session connects', function () {
    Http::fake([
        '*/api/sessions/session-test' => Http::response([
            'name' => 'session-test',
            'status' => 'WORKING',
            'me' => ['id' => '221770000000@c.us', 'pushName' => 'Boutique Aicha'],
        ]),
        '*/api/contacts/profile-picture*' => Http::response([
            'profilePictureURL' => 'https://pps.whatsapp.net/profile.jpg',
        ]),
    ]);

    $session = WhatsAppSession::query()->create([
        'label' => 'Test session',
        'waha_session_name' => 'session-test',
        'status' => WhatsAppSessionStatus::Starting,
    ]);

    app(RefreshWhatsAppSessionStatus::class)->handle($session);

    $fresh = $session->fresh();

    expect($fresh->status)->toBe(WhatsAppSessionStatus::Working)
        ->and($fresh->phone_number)->toBe('221770000000')
        ->and($fresh->profile_name)->toBe('Boutique Aicha')
        ->and($fresh->profile_picture_url)->toBe('https://pps.whatsapp.net/profile.jpg');
});

it('keeps the previous profile picture when WAHA has none on file', function () {
    Http::fake([
        '*/api/sessions/session-test' => Http::response([
            'name' => 'session-test',
            'status' => 'WORKING',
            'me' => ['id' => '221770000000@c.us', 'pushName' => 'Boutique Aicha'],
        ]),
        '*/api/contacts/profile-picture*' => Http::response([], 404),
    ]);

    $session = WhatsAppSession::query()->create([
        'label' => 'Test session',
        'waha_session_name' => 'session-test',
        'status' => WhatsAppSessionStatus::Starting,
        'profile_picture_url' => 'https://pps.whatsapp.net/old.jpg',
    ]);

    app(RefreshWhatsAppSessionStatus::class)->handle($session);

    expect($session->fresh()->profile_picture_url)->toBe('https://pps.whatsapp.net/old.jpg');
});

it('backfills missing profile information for an already connected session', function () {
    Http::fake([
        '*/api/sessions/session-test' => Http::response([
            'name' => 'session-test',
            'status' => 'WORKING',
            'me' => ['id' => '221770000000@c.us', 'pushName' => 'Boutique Aicha'],
        ]),
        '*/api/contacts/profile-picture*' => Http::response([
            'profilePictureURL' => 'https://pps.whatsapp.net/profile.jpg',
        ]),
    ]);

    $user = User::factory()->create([
        'merchant_id' => $this->merchant->id,
        'email_verified_at' => now(),
    ]);

    $session = WhatsAppSession::query()->create([
        'label' => 'Test session',
        'waha_session_name' => 'session-test',
        'status' => WhatsAppSessionStatus::Working,
        'phone_number' => '221770000000@c.us',
    ]);

    $this->actingAs($user)
        ->get(route('whatsapp-sessions.index'))
        ->assertOk();

    $fresh = $session->fresh();

    expect($fresh->phone_number)->toBe('221770000000')
        ->and($fresh->profile_name)->toBe('Boutique Aicha')
        ->and($fresh->profile_picture_url)->toBe('https://pps.whatsapp.net/profile.jpg');
});

it('stores complete profile information when the working webhook arrives', function () {
    config(['services.waha.webhook_hmac_key' => 'test-webhook-secret']);

    Http::fake([
        '*/api/sessions/session-test' => Http::response([
            'name' => 'session-test',
            'status' => 'WORKING',
            'me' => ['id' => '221770000000@c.us', 'pushName' => 'Boutique Aicha'],
        ]),
        '*/api/contacts/profile-picture*' => Http::response([
            'profilePictureURL' => 'https://pps.whatsapp.net/profile.jpg',
        ]),
    ]);

    $session = WhatsAppSession::query()->create([
        'label' => 'Test session',
        'waha_session_name' => 'session-test',
        'status' => WhatsAppSessionStatus::Starting,
    ]);

    $payload = json_encode([
        'event' => 'session.status',
        'session' => 'session-test',
        'payload' => ['status' => 'WORKING'],
    ], JSON_THROW_ON_ERROR);

    $this->call(
        'POST',
        route('webhooks.whatsapp'),
        server: [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_WEBHOOK_HMAC' => hash_hmac('sha512', $payload, 'test-webhook-secret'),
        ],
        content: $payload,
    )->assertNoContent();

    $fresh = $session->fresh();

    expect($fresh->phone_number)->toBe('221770000000')
        ->and($fresh->profile_name)->toBe('Boutique Aicha')
        ->and($fresh->profile_picture_url)->toBe('https://pps.whatsapp.net/profile.jpg');
});

it('uses the dedicated WAHA me endpoint when status omits the profile name', function () {
    Http::fake([
        '*/api/sessions/session-test/me' => Http::response([
            'id' => '221770000000@c.us',
            'pushName' => 'Boutique Aicha',
        ]),
        '*/api/sessions/session-test' => Http::response([
            'name' => 'session-test',
            'status' => 'WORKING',
            'me' => ['id' => '221770000000@c.us'],
        ]),
        '*/api/contacts/profile-picture*' => Http::response([
            'profilePictureURL' => 'https://pps.whatsapp.net/profile.jpg',
        ]),
    ]);

    $session = WhatsAppSession::query()->create([
        'label' => 'Test session',
        'waha_session_name' => 'session-test',
        'status' => WhatsAppSessionStatus::Working,
    ]);

    app(RefreshWhatsAppSessionStatus::class)->handle($session);

    expect($session->fresh()->profile_name)->toBe('Boutique Aicha');
});
