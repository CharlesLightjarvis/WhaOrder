<?php

namespace App\Console\Commands;

use App\Enums\ConversationStatus;
use App\Jobs\SendAbandonedCartReminder;
use App\Models\Conversation;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:remind-abandoned-carts')]
#[Description('Remind customers who left items in their cart, then mark long-unanswered conversations as abandoned')]
class RemindAbandonedCarts extends Command
{
    /**
     * How long a cart sits untouched before we send a reminder.
     */
    private const int REMINDER_AFTER_HOURS = 2;

    /**
     * How long after the reminder (with no reply) before we give up and
     * mark the conversation abandoned, so it stops being reminded forever.
     */
    private const int ABANDON_AFTER_HOURS = 22;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $remindedCount = $this->sendReminders();
        $abandonedCount = $this->markAbandoned();

        $this->info("Sent {$remindedCount} abandoned cart reminder(s), marked {$abandonedCount} conversation(s) as abandoned.");
    }

    private function sendReminders(): int
    {
        $conversations = Conversation::query()
            ->where('status', ConversationStatus::Active)
            ->whereNotNull('draft_order')
            ->whereNull('abandoned_reminder_sent_at')
            ->where('last_message_at', '<', now()->subHours(self::REMINDER_AFTER_HOURS))
            ->get();

        foreach ($conversations as $conversation) {
            SendAbandonedCartReminder::dispatch($conversation);
        }

        return $conversations->count();
    }

    private function markAbandoned(): int
    {
        return Conversation::query()
            ->where('status', ConversationStatus::Active)
            ->whereNotNull('draft_order')
            ->whereNotNull('abandoned_reminder_sent_at')
            ->where('abandoned_reminder_sent_at', '<', now()->subHours(self::ABANDON_AFTER_HOURS))
            ->update(['status' => ConversationStatus::Abandoned]);
    }
}
