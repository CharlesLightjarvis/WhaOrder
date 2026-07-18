<?php

namespace App\Actions\WhatsAppSessions;

use App\Enums\WhatsAppSessionStatus;
use App\Models\WhatsAppSession;
use App\Repositories\WhatsAppSessions\WhatsAppSessionRepository;
use App\Services\Waha\WahaClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ConnectWhatsAppSession
{
    public function __construct(
        private readonly WhatsAppSessionRepository $repository,
        private readonly WahaClient $client,
    ) {}

    public function handle(string $label): WhatsAppSession
    {
        // WAHA session names must match ^[a-zA-Z0-9-]+$ (3-50 chars) and be
        // globally unique, unlike our own free-text, merchant-scoped label.
        $name = 'session-'.Str::lower(Str::random(20));

        $session = $this->client->startSession($name, [
            'url' => rtrim((string) config('services.waha.webhook_url'), '/').'/webhooks/whatsapp',
            'events' => ['message', 'session.status'],
            'hmacKey' => (string) config('services.waha.webhook_hmac_key'),
        ]);

        return DB::transaction(fn () => $this->repository->create([
            'label' => $label,
            'waha_session_name' => $name,
            'status' => WhatsAppSessionStatus::tryFrom($session['status'] ?? 'STARTING') ?? WhatsAppSessionStatus::Starting,
        ]));
    }
}
