<?php

namespace App\Http\Controllers;

use App\Enums\WhatsAppSessionStatus;
use App\Jobs\ProcessIncomingWhatsAppMessage;
use App\Models\WhatsAppSession;
use App\Services\Waha\WahaClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        private readonly WahaClient $client,
    ) {}

    public function handle(Request $request): Response
    {
        if (! $this->hasValidSignature($request)) {
            abort(401, 'Invalid webhook signature.');
        }

        $event = $request->string('event')->toString();
        $session = $request->string('session')->toString();
        $payload = $request->array('payload');

        $whatsAppSession = WhatsAppSession::query()
            ->where('waha_session_name', $session)
            ->first();

        if (! $whatsAppSession) {
            Log::warning('Webhook received for unknown WhatsApp session.', ['session' => $session]);

            return response()->noContent();
        }

        match ($event) {
            'session.status' => $this->handleSessionStatus($whatsAppSession, $payload),
            'message' => $this->handleMessageReceived($whatsAppSession, $payload),
            default => Log::info('Unhandled WhatsApp webhook event.', ['event' => $event, 'session' => $session]),
        };

        return response()->noContent();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function handleSessionStatus(WhatsAppSession $whatsAppSession, array $payload): void
    {
        $status = WhatsAppSessionStatus::tryFrom($payload['status'] ?? '');

        if (! $status) {
            return;
        }

        $data = ['status' => $status];

        if ($status === WhatsAppSessionStatus::ScanQrCode) {
            try {
                $qr = $this->client->getQrCode($whatsAppSession->waha_session_name);
                $data['qr_code'] = $qr['qrCode'] ?? null;
            } catch (RequestException) {
                // QR not ready yet — the next status event will retry.
            }
        } elseif ($status === WhatsAppSessionStatus::Working) {
            $remote = $this->client->getStatus($whatsAppSession->waha_session_name);

            $data['qr_code'] = null;
            $data['phone_number'] = $remote['me']['id'] ?? $whatsAppSession->phone_number;
            $data['connected_at'] = $whatsAppSession->connected_at ?? now();
            $data['last_active_at'] = now();
        }

        $whatsAppSession->update($data);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function handleMessageReceived(WhatsAppSession $whatsAppSession, array $payload): void
    {
        if ($payload['fromMe'] ?? false) {
            return;
        }

        ProcessIncomingWhatsAppMessage::dispatch($whatsAppSession, $payload);
    }

    private function hasValidSignature(Request $request): bool
    {
        $secret = config('services.waha.webhook_hmac_key');
        $signature = $request->header('X-Webhook-Hmac', '');

        $expected = hash_hmac('sha512', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }
}
