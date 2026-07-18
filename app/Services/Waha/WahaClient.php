<?php

namespace App\Services\Waha;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class WahaClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
    ) {}

    /**
     * @param  array{url: string, events: array<int, string>, hmacKey: string}|null  $webhook
     * @return array{name: string, status: string}
     */
    public function startSession(string $name, ?array $webhook = null): array
    {
        return $this->http()->post('/api/sessions', [
            'name' => $name,
            'start' => true,
            ...($webhook ? ['config' => ['webhooks' => [$this->webhookConfig($webhook)]]] : []),
        ])->throw()->json();
    }

    /**
     * @param  array{url: string, events: array<int, string>, hmacKey: string}  $webhook
     * @return array{name: string, status: string}
     */
    public function updateSessionWebhook(string $name, array $webhook): array
    {
        return $this->http()->put("/api/sessions/{$name}", [
            'name' => $name,
            'config' => ['webhooks' => [$this->webhookConfig($webhook)]],
        ])->throw()->json();
    }

    /**
     * @param  array{url: string, events: array<int, string>, hmacKey: string}  $webhook
     * @return array{url: string, events: array<int, string>, hmac: array{key: string}}
     */
    private function webhookConfig(array $webhook): array
    {
        return [
            'url' => $webhook['url'],
            'events' => $webhook['events'],
            'hmac' => ['key' => $webhook['hmacKey']],
        ];
    }

    /**
     * @return array{name: string, status: string, me: ?array{id: string, pushName: string}}
     */
    public function getStatus(string $name): array
    {
        return $this->http()->get("/api/sessions/{$name}")->throw()->json();
    }

    /**
     * @return array{qrCode: string}
     */
    public function getQrCode(string $name): array
    {
        $qr = $this->http()->get("/api/{$name}/auth/qr")->throw()->json();

        return [
            'qrCode' => "data:{$qr['mimetype']};base64,{$qr['data']}",
        ];
    }

    public function stopSession(string $name): void
    {
        $this->http()->post("/api/sessions/{$name}/stop")->throw();
    }

    public function deleteSession(string $name): void
    {
        $this->http()->delete("/api/sessions/{$name}")->throw();
    }

    /**
     * @return array{id: string}
     */
    public function sendText(string $session, string $chatId, string $text): array
    {
        return $this->http()->post('/api/sendText', [
            'session' => $session,
            'chatId' => $chatId,
            'text' => $text,
        ])->throw()->json();
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl(rtrim($this->baseUrl, '/'))
            ->withHeaders(['X-Api-Key' => $this->apiKey])
            ->acceptJson()
            ->timeout(15);
    }
}
