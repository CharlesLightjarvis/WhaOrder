<?php

use App\Ai\Agents\ChiefAgent;
use App\Ai\Agents\OrderAgent;
use App\Ai\Agents\SupportAgent;
use App\Enums\ConversationStatus;
use App\Enums\WhatsAppSessionStatus;
use App\Jobs\ProcessIncomingWhatsAppMessage;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Merchant;
use App\Models\WhatsAppSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->merchant = Merchant::factory()->create();
    app()->instance('currentMerchantId', $this->merchant->id);

    Http::fake(['*' => Http::response(['id' => 'fake-message-id'])]);

    $this->session = WhatsAppSession::query()->create([
        'label' => 'Test session',
        'waha_session_name' => 'session-test',
        'status' => WhatsAppSessionStatus::Working,
        'connected_at' => now()->subDay(),
    ]);

    $this->customer = Customer::factory()->for($this->merchant)->create([
        'whatsapp_number' => '221770000000',
    ]);

    $this->message = fn (string $body): array => [
        'id' => (string) Str::uuid(),
        'from' => '221770000000@c.us',
        'body' => $body,
        'timestamp' => now()->timestamp,
    ];
});

it('routes a delivery/support message to the SupportAgent', function () {
    Conversation::factory()->for($this->merchant)->for($this->customer)->create([
        'status' => ConversationStatus::Active,
        'draft_order' => null,
    ]);

    ChiefAgent::fake([['intent' => 'support']]);
    SupportAgent::fake(['Je comprends votre frustration, voici le statut de votre commande.']);
    OrderAgent::fake(['Ne devrait pas être appelé.']);

    ProcessIncomingWhatsAppMessage::dispatchSync(
        $this->session,
        ($this->message)("J'ai pas reçu ma livraison, c'est long !!"),
    );

    SupportAgent::assertPrompted(fn () => true);
    OrderAgent::assertNeverPrompted();
});

it('routes an ordinary catalog message to the OrderAgent', function () {
    Conversation::factory()->for($this->merchant)->for($this->customer)->create([
        'status' => ConversationStatus::Active,
        'draft_order' => null,
    ]);

    ChiefAgent::fake([['intent' => 'order']]);
    OrderAgent::fake(['Bonjour, que voulez-vous commander ?']);
    SupportAgent::fake(['Ne devrait pas être appelé.']);

    ProcessIncomingWhatsAppMessage::dispatchSync(
        $this->session,
        ($this->message)('Bonjour, vous avez des baskets ?'),
    );

    OrderAgent::assertPrompted(fn () => true);
    SupportAgent::assertNeverPrompted();
});

it('routes a simple courtesy message to the SupportAgent instead of relaunching the sales flow', function () {
    Conversation::factory()->for($this->merchant)->for($this->customer)->create([
        'status' => ConversationStatus::Active,
        'draft_order' => null,
    ]);

    ChiefAgent::fake([['intent' => 'social']]);
    SupportAgent::fake(['Avec plaisir, à bientôt !']);
    OrderAgent::fake(['Ne devrait pas être appelé.']);

    ProcessIncomingWhatsAppMessage::dispatchSync(
        $this->session,
        ($this->message)('merci'),
    );

    SupportAgent::assertPrompted(fn () => true);
    OrderAgent::assertNeverPrompted();
});

it('bypasses classification and stays on the OrderAgent while a draft order is in progress', function () {
    Conversation::factory()->for($this->merchant)->for($this->customer)->create([
        'status' => ConversationStatus::Active,
        'draft_order' => ['items' => [['product_id' => (string) Str::uuid(), 'quantity' => 1]]],
    ]);

    ChiefAgent::fake([['intent' => 'support']]);
    OrderAgent::fake(['Notée, autre chose ?']);
    SupportAgent::fake(['Ne devrait pas être appelé.']);

    ProcessIncomingWhatsAppMessage::dispatchSync(
        $this->session,
        ($this->message)('merci'),
    );

    OrderAgent::assertPrompted(fn () => true);
    SupportAgent::assertNeverPrompted();
    ChiefAgent::assertNeverPrompted();
});
