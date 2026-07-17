<?php

namespace App\Actions\WhatsAppSessions;

use App\Enums\WhatsAppSessionStatus;
use App\Models\WhatsAppSession;
use App\Repositories\WhatsAppSessions\WhatsAppSessionRepository;
use App\Services\Waha\WahaClient;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;

class RefreshWhatsAppSessionStatus
{
    public function __construct(
        private readonly WhatsAppSessionRepository $repository,
        private readonly WahaClient $client,
    ) {}

    public function handle(WhatsAppSession $whatsAppSession): WhatsAppSession
    {
        $remote = $this->client->getStatus($whatsAppSession->waha_session_name);

        $status = WhatsAppSessionStatus::tryFrom($remote['status'] ?? 'FAILED') ?? WhatsAppSessionStatus::Failed;

        $data = [
            'status' => $status,
            'phone_number' => $remote['me']['id'] ?? $whatsAppSession->phone_number,
        ];

        if ($status === WhatsAppSessionStatus::Working) {
            $data['qr_code'] = null;
            $data['connected_at'] = $whatsAppSession->connected_at ?? now();
            $data['last_active_at'] = now();
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
}
