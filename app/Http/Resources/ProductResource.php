<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $variants = $this->relationLoaded('variants') ? $this->variants : collect();
        $hasVariants = $variants->isNotEmpty() || ((int) ($this->variants_count ?? 0) > 0);
        $parentImage = $this->relationLoaded('images')
            ? $this->images->firstWhere('variant_id', null)
            : null;
        $variantImage = $variants
            ->first(fn ($variant) => $variant->relationLoaded('images') && $variant->images->isNotEmpty())
            ?->images->first();
        $coverImage = $hasVariants ? $variantImage : $parentImage;
        $variantPrices = $variants->pluck('price')->filter(fn ($price) => $price !== null)->map(fn ($price) => (float) $price);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price !== null ? (float) $this->price : null,
            'stock' => $this->stock,
            'has_variants' => $hasVariants,
            'cover_image' => $coverImage ? ['id' => $coverImage->id, 'url' => $coverImage->url] : null,
            'price_min' => $hasVariants ? $variantPrices->min() : ($this->price !== null ? (float) $this->price : null),
            'price_max' => $hasVariants ? $variantPrices->max() : ($this->price !== null ? (float) $this->price : null),
            'stock_total' => $hasVariants ? $variants->sum('stock') : $this->stock,
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
            'variants' => $this->whenLoaded('variants', fn () => $this->variants->map(fn (ProductVariant $variant): array => [
                'id' => $variant->id,
                'name' => $variant->name,
                'price' => (float) $variant->price,
                'stock' => $variant->stock,
                'images' => $variant->relationLoaded('images') ? $variant->images->map(fn (ProductImage $image): array => [
                    'id' => $image->id,
                    'url' => $image->url,
                ])->values()->toArray() : [],
            ])->values()->toArray()),
            'variants_count' => $this->whenCounted('variants'),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
