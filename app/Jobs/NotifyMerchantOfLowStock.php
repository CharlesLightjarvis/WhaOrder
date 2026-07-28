<?php

namespace App\Jobs;

use App\Actions\WhatsApp\BuildWhatsAppChatId;
use App\Actions\WhatsApp\ResolveMerchantWorkingSession;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Waha\WahaClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyMerchantOfLowStock implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public readonly Product $product,
        public readonly ?ProductVariant $variant = null,
    ) {}

    public function handle(WahaClient $client, ResolveMerchantWorkingSession $resolveSession, BuildWhatsAppChatId $buildChatId): void
    {
        $merchant = $this->product->merchant;
        $adminNumber = $merchant->whatsapp_admin_number;

        if (! $adminNumber) {
            return;
        }

        $session = $resolveSession->handle($merchant);

        if (! $session) {
            Log::warning('No working WhatsApp session to notify merchant of low stock.', [
                'productId' => $this->product->id,
            ]);

            return;
        }

        $label = $this->product->name.($this->variant ? " ({$this->variant->name})" : '');
        $stock = $this->variant?->stock ?? $this->product->stock;

        $text = "⚠️ Stock faible : *{$label}* — plus que *{$stock}* en stock.";

        try {
            $client->sendText($session->waha_session_name, $buildChatId->handle($adminNumber), $text);
        } catch (RequestException $exception) {
            Log::warning('Failed to notify merchant of low stock.', [
                'productId' => $this->product->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
