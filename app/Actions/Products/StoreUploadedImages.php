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
            $folder = "whaorder/images/{$product->merchant->slug}/products";

            foreach ($files as $file) {
                $path = $folder.'/'.$file->getClientOriginalName();

                Storage::disk('imagekit')->put($path, $file->get());

                $product->images()->create([
                    'url' => rtrim((string) config('filesystems.disks.imagekit.endpoint_url'), '/').'/'.$path,
                    'variant_id' => $variant?->id,
                ]);
            }
        });
    }
}
