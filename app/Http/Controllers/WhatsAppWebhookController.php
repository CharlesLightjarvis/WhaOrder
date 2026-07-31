<?php

namespace App\Http\Controllers;

use App\Actions\WhatsAppSessions\RefreshWhatsAppSessionStatus;
use App\Enums\WhatsAppSessionStatus;
use App\Jobs\ProcessIncomingWhatsAppMessage;
use App\Models\WhatsAppSession;
use App\Services\Waha\WahaClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        private readonly WahaClient $client,
        private readonly RefreshWhatsAppSessionStatus $refreshWhatsAppSessionStatus,
    ) {}

    public function handle(Request $request): Response
    {
        if (! $this->hasValidSignature($request)) {
            abort(401, 'Invalid webhook signature.');
        }

        $validator = Validator::make($request->all(), [
            'event' => ['required', 'string', Rule::in(['message', 'session.status'])],
            'session' => ['required', 'string', 'max:255'],
            'payload' => ['required', 'array'],
            'payload.id' => ['required_if:event,message', 'string', 'max:255'],
            'payload.from' => ['nullable', 'string', 'max:255'],
            'payload.chatId' => ['nullable', 'string', 'max:255'],
            'payload.fromMe' => ['nullable', 'boolean'],
            'payload.body' => ['nullable', 'string', 'max:10000'],
            'payload.timestamp' => ['nullable', 'integer', 'min:0'],
            'payload.status' => ['required_if:event,session.status', 'string', 'max:100'],
        ]);

        abort_if($validator->fails(), 422, 'Invalid webhook payload.');

        $validated = $validator->validated();

        $event = $validated['event'];
        $session = $validated['session'];
        $payload = $validated['payload'];

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

        if ($status === WhatsAppSessionStatus::Working) {
            $this->refreshWhatsAppSessionStatus->handle($whatsAppSession);

            return;
        }

        $data = ['status' => $status];

        if ($status === WhatsAppSessionStatus::ScanQrCode) {
            try {
                $qr = $this->client->getQrCode($whatsAppSession->waha_session_name);
                $data['qr_code'] = $qr['qrCode'];
            } catch (RequestException) {
                // QR not ready yet — the next status event will retry.
            }
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

        if (! is_string($secret) || blank($secret)) {
            Log::critical('WhatsApp webhook rejected because its HMAC secret is not configured.');

            return false;
        }

        $signature = $request->header('X-Webhook-Hmac', '');

        $expected = hash_hmac('sha512', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }
}
