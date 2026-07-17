<?php

namespace App\Jobs;

use App\Ai\Agents\OrderAgent;
use App\Enums\ConversationStatus;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\WhatsAppSession;
use App\Services\Waha\WahaClient;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessIncomingWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    /**
     * @param  array<string, mixed>  $message
     */
    public function __construct(
        public readonly WhatsAppSession $whatsAppSession,
        public readonly array $message,
    ) {}

    public function handle(WahaClient $client): void
    {
        app()->instance('currentMerchantId', $this->whatsAppSession->merchant_id);

        $whatsappNumber = $this->normalizeChatId($this->message['from'] ?? $this->message['chatId'] ?? '');
        $body = trim((string) ($this->message['body'] ?? ''));

        if ($whatsappNumber === '' || $body === '') {
            Log::warning('Skipping WhatsApp message with missing sender or body.', ['message' => $this->message]);

            return;
        }

        $merchant = $this->whatsAppSession->merchant;

        $conversation = $this->resolveConversation($whatsappNumber);

        $agent = new OrderAgent($merchant, $conversation);
        $participant = (object) ['id' => null];

        if ($conversation->agent_conversation_id) {
            $agent->continue($conversation->agent_conversation_id, $participant);
        } else {
            $agent->forUser($participant);
        }

        $startedAt = now();
        $response = $agent->prompt($body);

        $conversation->update([
            'agent_conversation_id' => $agent->currentConversation(),
            'last_message_at' => now(),
        ]);

        $replyChatId = $this->message['from'] ?? $this->message['chatId'] ?? null;

        if ($replyChatId && filled($response->text)) {
            $client->sendText($this->whatsAppSession->waha_session_name, $replyChatId, $response->text);
        }

        $this->notifyMerchantOfNewOrder($client, $merchant, $conversation, $startedAt);
    }

    private function resolveConversation(string $whatsappNumber): Conversation
    {
        return DB::transaction(function () use ($whatsappNumber) {
            $customer = Customer::query()->firstOrCreate([
                'merchant_id' => $this->whatsAppSession->merchant_id,
                'whatsapp_number' => $whatsappNumber,
            ]);

            $conversation = Conversation::query()
                ->where('customer_id', $customer->id)
                ->where('status', ConversationStatus::Active)
                ->latest()
                ->first();

            return $conversation ?? Conversation::query()->create([
                'customer_id' => $customer->id,
                'status' => ConversationStatus::Active,
            ]);
        });
    }

    private function notifyMerchantOfNewOrder(WahaClient $client, Merchant $merchant, Conversation $conversation, CarbonInterface $since): void
    {
        if (! $merchant->whatsapp_admin_number) {
            return;
        }

        $order = Order::query()
            ->where('conversation_id', $conversation->id)
            ->where('created_at', '>=', $since)
            ->latest()
            ->first();

        if (! $order) {
            return;
        }

        $text = "🆕 Nouvelle commande #{$order->id} — ".number_format((float) $order->total, 2)." {$merchant->currency->value}"
            .($order->delivery_city ? " — {$order->delivery_city}" : '');

        $client->sendText($this->whatsAppSession->waha_session_name, $this->toChatId($merchant->whatsapp_admin_number), $text);
    }

    private function normalizeChatId(string $chatId): string
    {
        return str_replace('@c.us', '', $chatId);
    }

    private function toChatId(string $phoneNumber): string
    {
        return preg_replace('/\D/', '', $phoneNumber).'@c.us';
    }
}
