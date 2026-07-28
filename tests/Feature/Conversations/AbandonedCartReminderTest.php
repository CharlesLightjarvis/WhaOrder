<?php

use App\Console\Commands\RemindAbandonedCarts;
use App\Enums\ConversationStatus;
use App\Enums\WhatsAppSessionStatus;
use App\Jobs\SendAbandonedCartReminder;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Merchant;
use App\Models\WhatsAppSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->merchant = Merchant::factory()->create();
    app()->instance('currentMerchantId', $this->merchant->id);

    $this->customer = Customer::factory()->for($this->merchant)->create([
        'whatsapp_number' => '221770000000',
    ]);

    $this->draftOrder = [
        'items' => [[
            'product_id' => (string) Str::uuid(),
            'variant_id' => null,
            'product_name_snapshot' => 'T-shirt',
            'variant_name_snapshot' => null,
            'quantity' => 2,
            'unit_price' => 5000,
            'line_total' => 10000,
        ]],
        'subtotal' => 10000,
        'delivery_fee' => 0,
        'total' => 10000,
        'customer_name' => 'Awa',
        'delivery_address_text' => null,
        'delivery_city' => null,
        'payment_method' => null,
    ];
});

it('dispatches a reminder for a cart left untouched past the threshold', function () {
    Bus::fake();

    $conversation = Conversation::factory()->for($this->merchant)->for($this->customer)->create([
        'status' => ConversationStatus::Active,
        'draft_order' => $this->draftOrder,
        'last_message_at' => now()->subHours(3),
        'abandoned_reminder_sent_at' => null,
    ]);

    $this->artisan(RemindAbandonedCarts::class)->assertSuccessful();

    Bus::assertDispatched(SendAbandonedCartReminder::class, fn ($job) => $job->conversation->is($conversation));
});

it('does not remind a cart that is still fresh', function () {
    Bus::fake();

    Conversation::factory()->for($this->merchant)->for($this->customer)->create([
        'status' => ConversationStatus::Active,
        'draft_order' => $this->draftOrder,
        'last_message_at' => now()->subMinutes(30),
        'abandoned_reminder_sent_at' => null,
    ]);

    $this->artisan(RemindAbandonedCarts::class)->assertSuccessful();

    Bus::assertNotDispatched(SendAbandonedCartReminder::class);
});

it('does not remind twice while a reminder is already pending', function () {
    Bus::fake();

    Conversation::factory()->for($this->merchant)->for($this->customer)->create([
        'status' => ConversationStatus::Active,
        'draft_order' => $this->draftOrder,
        'last_message_at' => now()->subHours(3),
        'abandoned_reminder_sent_at' => now()->subHour(),
    ]);

    $this->artisan(RemindAbandonedCarts::class)->assertSuccessful();

    Bus::assertNotDispatched(SendAbandonedCartReminder::class);
});

it('marks a conversation abandoned when the reminder went unanswered for too long', function () {
    $conversation = Conversation::factory()->for($this->merchant)->for($this->customer)->create([
        'status' => ConversationStatus::Active,
        'draft_order' => $this->draftOrder,
        'last_message_at' => now()->subDay(),
        'abandoned_reminder_sent_at' => now()->subHours(23),
    ]);

    $this->artisan(RemindAbandonedCarts::class)->assertSuccessful();

    expect($conversation->fresh()->status)->toBe(ConversationStatus::Abandoned);
});

it('sends the reminder text and records that it was sent', function () {
    Http::fake(['*' => Http::response(['id' => 'fake-message-id'])]);

    $session = WhatsAppSession::query()->create([
        'label' => 'Test session',
        'waha_session_name' => 'session-test',
        'status' => WhatsAppSessionStatus::Working,
        'last_active_at' => now(),
    ]);

    $conversation = Conversation::factory()->for($this->merchant)->for($this->customer)->create([
        'status' => ConversationStatus::Active,
        'draft_order' => $this->draftOrder,
        'abandoned_reminder_sent_at' => null,
    ]);

    SendAbandonedCartReminder::dispatchSync($conversation);

    Http::assertSent(function ($request) use ($session) {
        return $request['session'] === $session->waha_session_name
            && $request['chatId'] === '221770000000@c.us'
            && str_contains($request['text'], '1 article')
            && str_contains($request['text'], 'Awa');
    });

    expect($conversation->fresh()->abandoned_reminder_sent_at)->not->toBeNull();
});
