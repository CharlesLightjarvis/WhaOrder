<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => (float) $this->price,
            'stock' => $this->stock,
            'is_active' => $this->is_active,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
            'images' => $this->whenLoaded('images', fn () => $this->images
                ->whereNull('variant_id')
                ->values()
                ->map(fn ($image) => [
                    'id' => $image->id,
                    'url' => $image->url,
                ])),
            'variants' => $this->whenLoaded('variants', fn () => $this->variants->map(fn ($variant) => [
                'id' => $variant->id,
                'name' => $variant->name,
                'price' => $variant->price !== null ? (float) $variant->price : null,
                'stock' => $variant->stock,
                'images' => $variant->relationLoaded('images') ? $variant->images->map(fn ($image) => [
                    'id' => $image->id,
                    'url' => $image->url,
                ]) : [],
            ])),
            'variants_count' => $this->whenCounted('variants'),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
