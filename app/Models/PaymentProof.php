<?php

namespace App\Models;

use App\Concerns\BelongsToMerchantThroughOrder;
use App\Enums\PaymentProofStatus;
use App\Enums\PaymentProofType;
use Database\Factories\PaymentProofFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property PaymentProofType $type
 * @property PaymentProofStatus $status
 * @property Carbon|null $reviewed_at
 */
#[Fillable(['order_id', 'type', 'image_url', 'raw_message', 'status', 'reviewed_by', 'reviewed_at'])]
class PaymentProof extends Model
{
    /** @use HasFactory<PaymentProofFactory> */
    use BelongsToMerchantThroughOrder, HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'type' => PaymentProofType::class,
            'status' => PaymentProofStatus::class,
            'reviewed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Order, $this> */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
