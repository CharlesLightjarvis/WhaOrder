<?php

namespace App\Models;

use App\Concerns\BelongsToMerchantThroughCustomer;
use Database\Factories\AddressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['customer_id', 'label', 'full_name', 'phone', 'line1', 'line2', 'city', 'country', 'is_default'])]
class Address extends Model
{
    /** @use HasFactory<AddressFactory> */
    use BelongsToMerchantThroughCustomer, HasFactory;

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
