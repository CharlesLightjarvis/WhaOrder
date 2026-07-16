<?php

namespace App\Actions\Products;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StoreUploadedImages
{
    /**
     * @param  array<int, UploadedFile>  $files
     */
    public function handle(Product $product, array $files, ?ProductVariant $variant = null): void
    {
        DB::transaction(function () use ($product, $files, $variant): void {
            foreach ($files as $file) {
                $path = $file->store("products/{$product->id}", 'public');

                $product->images()->create([
                    'url' => Storage::disk('public')->url($path),
                    'variant_id' => $variant?->id,
                ]);
            }
        });
    }
}
