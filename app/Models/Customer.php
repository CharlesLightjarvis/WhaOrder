<?php

namespace App\Models;

use App\Concerns\BelongsToMerchant;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/** @property Carbon|null $last_order_at */
#[Fillable(['whatsapp_number', 'name', 'notes', 'last_order_at'])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use BelongsToMerchant, HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'last_order_at' => 'datetime',
        ];
    }

    /** @return HasMany<Address, $this> */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /** @return HasMany<Conversation, $this> */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /** @return HasMany<Order, $this> */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
