<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'whatsapp_number' => $this->whatsapp_number,
            'name' => $this->name,
            'notes' => $this->notes,
            'last_order_at' => $this->last_order_at?->toDateTimeString(),
            'addresses_count' => $this->whenCounted('addresses'),
            'addresses' => $this->whenLoaded('addresses', fn () => $this->addresses->map(fn ($address) => [
                'id' => $address->id,
                'label' => $address->label,
                'full_name' => $address->full_name,
                'phone' => $address->phone,
                'line1' => $address->line1,
                'line2' => $address->line2,
                'city' => $address->city,
                'country' => $address->country,
                'is_default' => $address->is_default,
            ])),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
