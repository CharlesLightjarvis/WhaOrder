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
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\RequestException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Exceptions\AiException;

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

        $chatId = (string) ($this->message['from'] ?? $this->message['chatId'] ?? '');

        if ($this->isGroupOrBroadcastChat($chatId)) {
            Log::info('Skipping WhatsApp message from a group or broadcast chat.', ['chatId' => $chatId]);

            return;
        }

        if ($this->isHistoricalMessage()) {
            Log::info('Skipping WhatsApp message received before the session connected.', ['message' => $this->message]);

            return;
        }

        if ($this->alreadyProcessed()) {
            Log::info('Skipping already-processed WhatsApp message.', ['messageId' => $this->message['id'] ?? null]);

            return;
        }

        $whatsappNumber = $this->normalizeChatId($chatId);
        $body = trim((string) ($this->message['body'] ?? ''));

        if ($whatsappNumber === '' || $body === '') {
            Log::warning('Skipping WhatsApp message with missing sender or body.', ['message' => $this->message]);

            return;
        }

        $merchant = $this->whatsAppSession->merchant;

        $conversation = $this->resolveConversation($whatsappNumber);

        Log::info('Processing WhatsApp message with the AI agent.', [
            'chatId' => $chatId,
            'conversationId' => $conversation->id,
            'body' => $body,
        ]);

        $agent = new OrderAgent($merchant, $conversation);
        $participant = (object) ['id' => $conversation->customer_id];

        if ($conversation->agent_conversation_id) {
            $agent->continue($conversation->agent_conversation_id, $participant);
        } else {
            $agent->forUser($participant);
        }

        $startedAt = now();

        try {
            $response = $agent->prompt($body);
        } catch (RequestException|AiException $exception) {
            Log::error('AI agent prompt failed.', [
                'chatId' => $chatId,
                'conversationId' => $conversation->id,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
                'status' => $exception instanceof RequestException ? $exception->response->status() : null,
                'body' => $exception instanceof RequestException ? $exception->response->body() : null,
            ]);

            $replyChatId = $this->message['from'] ?? $this->message['chatId'] ?? null;

            if ($replyChatId) {
                $client->sendText(
                    $this->whatsAppSession->waha_session_name,
                    $replyChatId,
                    "Désolé, j'ai eu un souci technique. Pouvez-vous reformuler votre message ?",
                );
            }

            return;
        }

        $conversation->update([
            'agent_conversation_id' => $agent->currentConversation(),
            'last_message_at' => now(),
        ]);

        $replyChatId = $this->message['from'] ?? $this->message['chatId'] ?? null;

        if ($replyChatId && filled($response->text)) {
            $client->sendText($this->whatsAppSession->waha_session_name, $replyChatId, $response->text);

            Log::info('Sent AI agent reply to WhatsApp chat.', [
                'chatId' => $replyChatId,
                'conversationId' => $conversation->id,
            ]);
        } else {
            Log::warning('AI agent produced no reply text.', [
                'chatId' => $replyChatId,
                'conversationId' => $conversation->id,
            ]);
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

    private function isGroupOrBroadcastChat(string $chatId): bool
    {
        return str_ends_with($chatId, '@g.us') || str_ends_with($chatId, '@broadcast');
    }

    private function isHistoricalMessage(): bool
    {
        $timestamp = $this->message['timestamp'] ?? null;

        if (! $timestamp || ! $this->whatsAppSession->connected_at) {
            return false;
        }

        return CarbonImmutable::createFromTimestamp((int) $timestamp)->lt($this->whatsAppSession->connected_at);
    }

    private function alreadyProcessed(): bool
    {
        $messageId = $this->message['id'] ?? null;

        if (! $messageId) {
            return false;
        }

        return ! Cache::add("whatsapp-message-processed:{$messageId}", true, now()->addDay());
    }

    private function normalizeChatId(string $chatId): string
    {
        return str_replace(['@c.us', '@lid', '@s.whatsapp.net'], '', $chatId);
    }

    private function toChatId(string $phoneNumber): string
    {
        return preg_replace('/\D/', '', $phoneNumber).'@c.us';
    }
}
