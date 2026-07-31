<?php

namespace App\Jobs;

use App\Actions\WhatsApp\BuildWhatsAppChatId;
use App\Actions\WhatsApp\ResolveMerchantWorkingSession;
use App\Enums\OrderStatus;
use App\Jobs\Middleware\UseMerchantContext;
use App\Models\Order;
use App\Services\Waha\WahaClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyCustomerOfOrderStatusChange implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public readonly Order $order,
    ) {}

    /** @return array<int, UseMerchantContext> */
    public function middleware(): array
    {
        return [new UseMerchantContext($this->order->merchant_id)];
    }

    public function handle(WahaClient $client, ResolveMerchantWorkingSession $resolveSession, BuildWhatsAppChatId $buildChatId): void
    {
        $order = $this->order;
        $customer = $order->customer;

        if (! $customer?->whatsapp_number) {
            return;
        }

        $session = $resolveSession->handle($order->merchant);

        if (! $session) {
            Log::warning('No working WhatsApp session to notify customer of order status change.', [
                'orderId' => $order->id,
                'merchantId' => $order->merchant_id,
            ]);

            return;
        }

        $text = $this->messageFor($order);

        try {
            $client->sendText($session->waha_session_name, $buildChatId->handle($customer->whatsapp_number), $text);
        } catch (RequestException $exception) {
            Log::warning('Failed to notify customer of order status change.', [
                'orderId' => $order->id,
                'status' => $order->status->value,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function messageFor(Order $order): string
    {
        $reference = mb_strtoupper(substr($order->id, 0, 8));

        return match ($order->status) {
            OrderStatus::Pending => "Votre commande *#{$reference}* est *en attente de traitement*.",
            OrderStatus::Preparing => "Votre commande *#{$reference}* est *en cours de préparation*.",
            OrderStatus::OutForDelivery => "Bonne nouvelle : votre commande *#{$reference}* est *en cours de livraison* !",
            OrderStatus::Delivered => "Votre commande *#{$reference}* a été *livrée*. Merci pour votre confiance !",
            OrderStatus::Cancelled => "Votre commande *#{$reference}* a été *annulée*. Contactez-nous si vous avez des questions.",
        };
    }
}
