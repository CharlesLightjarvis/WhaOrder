<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'payment_status' => $this->payment_status->value,
            'payment_status_label' => $this->payment_status->label(),
            'payment_method' => $this->payment_method?->value,
            'payment_method_label' => $this->payment_method?->label(),
            'delivery_address_text' => $this->delivery_address_text,
            'delivery_city' => $this->delivery_city,
            'subtotal' => (float) $this->subtotal,
            'delivery_fee' => (float) $this->delivery_fee,
            'total' => (float) $this->total,
            'customer' => $this->whenLoaded('customer', fn () => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'whatsapp_number' => $this->customer->whatsapp_number,
            ]),
            'items_count' => $this->whenCounted('items'),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item) => [
                'id' => $item->id,
                'product_name_snapshot' => $item->product_name_snapshot,
                'variant_name_snapshot' => $item->variant_name_snapshot,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) $item->line_total,
            ])),
            'payment_proofs' => $this->whenLoaded('paymentProofs', fn () => $this->paymentProofs->map(fn ($proof) => [
                'id' => $proof->id,
                'type' => $proof->type->value,
                'type_label' => $proof->type->label(),
                'image_url' => $proof->image_url,
                'raw_message' => $proof->raw_message,
                'status' => $proof->status->value,
                'status_label' => $proof->status->label(),
                'reviewed_at' => $proof->reviewed_at?->toDateTimeString(),
            ])),
            'delivery' => $this->whenLoaded('delivery', fn () => $this->delivery ? [
                'id' => $this->delivery->id,
                'status' => $this->delivery->status->value,
                'status_label' => $this->delivery->status->label(),
                'address_text' => $this->delivery->address_text,
                'city' => $this->delivery->city,
                'scheduled_at' => $this->delivery->scheduled_at?->toDateTimeString(),
                'delivered_at' => $this->delivery->delivered_at?->toDateTimeString(),
            ] : null),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}
