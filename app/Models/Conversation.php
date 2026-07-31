<?php

namespace App\Models;

use App\Concerns\BelongsToMerchant;
use App\Enums\ConversationStatus;
use Database\Factories\ConversationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property ConversationStatus $status
 * @property array<string, mixed>|null $draft_order
 * @property Carbon|null $last_message_at
 * @property Carbon|null $abandoned_reminder_sent_at
 */
#[Fillable(['customer_id', 'agent_conversation_id', 'status', 'draft_order', 'last_message_at', 'abandoned_reminder_sent_at'])]
class Conversation extends Model
{
    /** @use HasFactory<ConversationFactory> */
    use BelongsToMerchant, HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'status' => ConversationStatus::class,
            'draft_order' => 'array',
            'last_message_at' => 'datetime',
            'abandoned_reminder_sent_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** @return HasMany<Order, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
