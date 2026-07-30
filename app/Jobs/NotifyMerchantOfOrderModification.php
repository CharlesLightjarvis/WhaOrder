<?php

namespace App\Jobs;

use App\Actions\WhatsApp\BuildWhatsAppChatId;
use App\Actions\WhatsApp\ResolveMerchantWorkingSession;
use App\Models\Order;
use App\Services\Waha\WahaClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyMerchantOfOrderModification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly string $summary,
    ) {}

    public function handle(WahaClient $client, ResolveMerchantWorkingSession $resolveSession, BuildWhatsAppChatId $buildChatId): void
    {
        $merchant = $this->order->merchant;
        $adminNumber = $merchant->whatsapp_admin_number;

        if (! $adminNumber) {
            return;
        }

        $session = $resolveSession->handle($merchant);

        if (! $session) {
            Log::warning('No working WhatsApp session to notify merchant of an order modification.', [
                'orderId' => $this->order->id,
            ]);

            return;
        }

        $reference = mb_strtoupper(substr($this->order->id, 0, 8));
        $text = "✏️ Commande #{$reference} modifiée par le client : {$this->summary}. Nouveau total : "
            .number_format((float) $this->order->total, 2)." {$merchant->currency}";

        try {
            $client->sendText($session->waha_session_name, $buildChatId->handle($adminNumber), $text);
        } catch (RequestException $exception) {
            Log::warning('Failed to notify merchant of an order modification.', [
                'orderId' => $this->order->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
