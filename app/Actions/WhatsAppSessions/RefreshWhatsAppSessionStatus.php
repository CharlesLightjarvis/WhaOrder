<?php

namespace App\Actions\WhatsAppSessions;

use App\Enums\WhatsAppSessionStatus;
use App\Models\WhatsAppSession;
use App\Repositories\WhatsAppSessions\WhatsAppSessionRepository;
use App\Services\Waha\WahaClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RefreshWhatsAppSessionStatus
{
    public function __construct(
        private readonly WhatsAppSessionRepository $repository,
        private readonly WahaClient $client,
    ) {}

    public function handle(WhatsAppSession $whatsAppSession): WhatsAppSession
    {
        $remote = $this->client->getStatus($whatsAppSession->waha_session_name);

        $status = WhatsAppSessionStatus::tryFrom($remote['status']) ?? WhatsAppSessionStatus::Failed;
        $me = is_array($remote['me'] ?? null) ? $remote['me'] : [];

        if ($status === WhatsAppSessionStatus::Working) {
            $me = $this->client->getMe($whatsAppSession->waha_session_name) ?? $me;
        }

        $data = [
            'status' => $status,
            'phone_number' => $this->phoneNumberFromJid(
                $me['id'] ?? null,
                $whatsAppSession->phone_number,
            ),
        ];

        if ($status === WhatsAppSessionStatus::Working) {
            $data['qr_code'] = null;
            $data['connected_at'] = $whatsAppSession->connected_at ?? now();
            $data['last_active_at'] = now();
            $data['profile_name'] = $me['pushName'] ?? $whatsAppSession->profile_name;

            if (! empty($me['id'])) {
                $data['profile_picture_url'] = $this->client->getProfilePicture(
                    $whatsAppSession->waha_session_name,
                    $me['id'],
                ) ?? $whatsAppSession->profile_picture_url;
            }
        } else {
            // The QR code is often ready before the remote "status" field
            // catches up, so we poll for it directly instead of waiting.
            try {
                $qr = $this->client->getQrCode($whatsAppSession->waha_session_name);

                if (! empty($qr['qrCode'])) {
                    $data['status'] = WhatsAppSessionStatus::ScanQrCode;
                    $data['qr_code'] = $qr['qrCode'];
                }
            } catch (RequestException) {
                // QR not available yet for the current state — keep the reported status.
            }
        }

        return DB::transaction(fn () => $this->repository->update($whatsAppSession, $data));
    }

    private function phoneNumberFromJid(mixed $jid, ?string $fallback): ?string
    {
        if (! is_string($jid) || blank($jid)) {
            return $fallback;
        }

        return Str::before($jid, '@');
    }
}
