<?php

namespace App\Jobs;

use App\Actions\WhatsApp\BuildWhatsAppChatId;
use App\Actions\WhatsApp\ResolveMerchantWorkingSession;
use App\Models\Conversation;
use App\Services\Waha\WahaClient;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAbandonedCartReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public function __construct(
        public readonly Conversation $conversation,
    ) {}

    public function handle(WahaClient $client, ResolveMerchantWorkingSession $resolveSession, BuildWhatsAppChatId $buildChatId): void
    {
        $conversation = $this->conversation;
        $customer = $conversation->customer;

        if (! $customer?->whatsapp_number || empty($conversation->draft_order['items'])) {
            return;
        }

        $session = $resolveSession->handle($conversation->merchant);

        if (! $session) {
            Log::warning('No working WhatsApp session to send an abandoned cart reminder.', [
                'conversationId' => $conversation->id,
                'merchantId' => $conversation->merchant_id,
            ]);

            return;
        }

        $text = $this->reminderText($conversation);

        try {
            $client->sendText($session->waha_session_name, $buildChatId->handle($customer->whatsapp_number), $text);

            $conversation->update(['abandoned_reminder_sent_at' => now()]);
        } catch (RequestException $exception) {
            Log::warning('Failed to send abandoned cart reminder.', [
                'conversationId' => $conversation->id,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function reminderText(Conversation $conversation): string
    {
        $draftOrder = $conversation->draft_order;
        $itemCount = count($draftOrder['items']);
        $itemWord = $itemCount > 1 ? 'articles' : 'article';
        $greeting = $draftOrder['customer_name'] ?? $conversation->customer?->name;

        return ($greeting ? "Bonjour *{$greeting}*, " : 'Bonjour, ')
            ."vous avez laissé *{$itemCount} {$itemWord}* dans votre panier chez *{$conversation->merchant->name}*. "
            .'Voulez-vous que je vous aide à finaliser votre commande ?';
    }
}
