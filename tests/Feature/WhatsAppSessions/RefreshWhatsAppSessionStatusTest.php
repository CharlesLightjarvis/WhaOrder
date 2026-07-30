<?php

use App\Actions\WhatsAppSessions\RefreshWhatsAppSessionStatus;
use App\Enums\WhatsAppSessionStatus;
use App\Models\Merchant;
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
