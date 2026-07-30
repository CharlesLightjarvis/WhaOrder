<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WhatsAppSessionResource extends JsonResource
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
            'label' => $this->label,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'phone_number' => $this->phone_number,
            'profile_name' => $this->profile_name,
            'profile_picture_url' => $this->profile_picture_url,
            'qr_code' => $this->qr_code,
            'connected_at' => $this->connected_at?->toDateTimeString(),
            'last_active_at' => $this->last_active_at?->toDateTimeString(),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
