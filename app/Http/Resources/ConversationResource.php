<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
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
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'draft_order' => $this->draft_order,
            'last_message_at' => $this->last_message_at?->toDateTimeString(),
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'whatsapp_number' => $this->customer->whatsapp_number,
            ]),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
