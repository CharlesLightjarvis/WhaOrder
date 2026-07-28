<?php

namespace App\Jobs;

use App\Actions\WhatsApp\BuildWhatsAppChatId;
use App\Models\Order;
use App\Services\Waha\WahaClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyMerchantOfNewOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public readonly Order $order,
        public readonly string $wahaSessionName,
    ) {}

    public function handle(WahaClient $client, BuildWhatsAppChatId $buildChatId): void
    {
        $merchant = $this->order->merchant;
        $adminNumber = $merchant->whatsapp_admin_number;

        if (! $adminNumber) {
            return;
        }

        $text = "🆕 Nouvelle commande #{$this->order->id} — ".number_format((float) $this->order->total, 2)." {$merchant->currency->value}"
            .($this->order->delivery_city ? " — {$this->order->delivery_city}" : '');

        try {
            $client->sendText($this->wahaSessionName, $buildChatId->handle($adminNumber), $text);
        } catch (RequestException $exception) {
            Log::warning('Failed to notify merchant admin of new order.', [
                'orderId' => $this->order->id,
                'adminNumber' => $adminNumber,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
