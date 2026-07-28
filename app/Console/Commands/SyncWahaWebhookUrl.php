<?php

namespace App\Console\Commands;

use App\Models\WhatsAppSession;
use App\Services\Waha\WahaClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SyncWahaWebhookUrl extends Command
{
    protected $signature = 'waha:sync-webhook {url : The current public tunnel URL, e.g. an ngrok URL}';

    protected $description = 'Update WAHA_WEBHOOK_URL in .env and push the new webhook URL to every WAHA session';

    public function handle(WahaClient $client): int
    {
        $url = rtrim((string) $this->argument('url'), '/');

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            $this->error("« {$url} » n'est pas une URL valide.");

            return self::FAILURE;
        }

        $this->updateEnvFile($url);
        Artisan::call('config:clear');

        $sessions = WhatsAppSession::all();

        if ($sessions->isEmpty()) {
            $this->warn('Aucune session WhatsApp trouvée.');

            return self::SUCCESS;
        }

        foreach ($sessions as $session) {
            $client->updateSessionWebhook($session->waha_session_name, [
                'url' => "{$url}/webhooks/whatsapp",
                'events' => ['message', 'session.status'],
                'hmacKey' => (string) config('services.waha.webhook_hmac_key'),
            ]);

            $this->info("Webhook mis à jour pour « {$session->label} » ({$session->waha_session_name}).");
        }

        return self::SUCCESS;
    }

    private function updateEnvFile(string $url): void
    {
        $path = base_path('.env');
        $content = file_get_contents($path);

        $content = preg_match('/^WAHA_WEBHOOK_URL=.*/m', $content)
            ? preg_replace('/^WAHA_WEBHOOK_URL=.*/m', "WAHA_WEBHOOK_URL={$url}", $content)
            : $content."\nWAHA_WEBHOOK_URL={$url}\n";

        file_put_contents($path, $content);
    }
}
