<?php

namespace App\Jobs;

use App\Ai\Agents\ChiefAgent;
use App\Ai\Agents\OrderAgent;
use App\Ai\Agents\SupportAgent;
use App\Enums\ConversationStatus;
use App\Enums\MessageIntent;
use App\Models\Conversation;
use App\Models\Customer;
use App\Models\Merchant;
use App\Models\WhatsAppSession;
use App\Services\Waha\WahaClient;
use Carbon\CarbonImmutable;
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

        $whatsappNumber = $this->resolvePhoneNumber($chatId, $client);
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

        $agent = $this->resolveAgent($merchant, $conversation, $body, $client, $chatId);
        $participant = (object) ['id' => $conversation->customer_id];

        if ($conversation->agent_conversation_id) {
            $agent->continue($conversation->agent_conversation_id, $participant);
        } else {
            $agent->forUser($participant);
        }

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
            'abandoned_reminder_sent_at' => null,
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
    }

    /**
     * Route the message to the right specialized agent: OrderAgent for
     * anything about browsing/building an order, SupportAgent for
     * everything else (delivery/order-status questions, complaints, and
     * courtesy messages like "merci"). Both agents continue the same
     * conversation id, so the memory stays continuous no matter which one
     * handles a given message.
     */
    private function resolveAgent(Merchant $merchant, Conversation $conversation, string $body, WahaClient $client, string $chatId): OrderAgent|SupportAgent
    {
        if (filled($conversation->draft_order)) {
            return new OrderAgent($merchant, $conversation, $client, $this->whatsAppSession->waha_session_name, $chatId);
        }

        $intent = $this->classifyIntent($body);

        if ($intent === MessageIntent::Order) {
            return new OrderAgent($merchant, $conversation, $client, $this->whatsAppSession->waha_session_name, $chatId);
        }

        return new SupportAgent($merchant, $conversation);
    }

    private function classifyIntent(string $body): MessageIntent
    {
        try {
            $result = (new ChiefAgent)->prompt($body);

            return MessageIntent::tryFrom($result['intent'] ?? '') ?? MessageIntent::Order;
        } catch (RequestException|AiException $exception) {
            Log::warning('Message intent classification failed, defaulting to order.', [
                'message' => $exception->getMessage(),
            ]);

            return MessageIntent::Order;
        }
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

    /**
     * WhatsApp now routes some chats through an opaque @lid (Linked ID)
     * instead of the phone-number-based chat id. Resolve it to the real
     * phone number so customer records aren't keyed by the wrong value.
     */
    private function resolvePhoneNumber(string $chatId, WahaClient $client): string
    {
        if (! str_ends_with($chatId, '@lid')) {
            return $this->normalizeChatId($chatId);
        }

        $resolved = $client->resolveLidToPhoneNumber($this->whatsAppSession->waha_session_name, $chatId);

        if ($resolved) {
            return $resolved;
        }

        Log::warning('Could not resolve WhatsApp lid to a phone number, storing the lid instead.', ['chatId' => $chatId]);

        return $this->normalizeChatId($chatId);
    }
}
