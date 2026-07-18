<?php

namespace App\Actions\WhatsAppSessions;

use App\Models\WhatsAppSession;
use App\Repositories\WhatsAppSessions\WhatsAppSessionRepository;
use App\Services\Waha\WahaClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DisconnectWhatsAppSession
{
    public function __construct(
        private readonly WhatsAppSessionRepository $repository,
        private readonly WahaClient $client,
    ) {}

    public function handle(WhatsAppSession $whatsAppSession): void
    {
        if ($whatsAppSession->waha_session_name) {
            try {
                $this->client->deleteSession($whatsAppSession->waha_session_name);
            } catch (RequestException $e) {
                if ($e->response->status() !== 404) {
                    Log::warning('Failed to delete WAHA session, removing local record anyway.', [
                        'session' => $whatsAppSession->waha_session_name,
                        'status' => $e->response->status(),
                    ]);
                }
            } catch (ConnectionException $e) {
                Log::warning('Could not reach WAHA to delete session, removing local record anyway.', [
                    'session' => $whatsAppSession->waha_session_name,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        DB::transaction(fn () => $this->repository->delete($whatsAppSession));
    }
}
