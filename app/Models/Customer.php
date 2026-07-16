<?php

namespace App\Models;

use App\Concerns\BelongsToMerchant;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['whatsapp_number', 'name', 'notes', 'last_order_at'])]
class Customer extends Model
{
    /** @use HasFactory<CustomerFactory> */
    use BelongsToMerchant, HasFactory;

    protected function casts(): array
    {
        return [
            'last_order_at' => 'datetime',
        ];
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }
}
